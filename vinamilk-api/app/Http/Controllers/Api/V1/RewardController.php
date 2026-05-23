<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        // Lấy banner cho trang rewards
        $banners = Banner::where('position', 'rewards_hero')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($banner) {
                return [
                    'id' => $banner->id,
                    'image' => $banner->image ? asset('storage/' . $banner->image) : null,
                    'link' => $banner->link,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'show_text' => $banner->show_text,
                ];
            });

        // Lấy user từ request (đã qua middleware auth:sanctum)
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'banners' => $banners,
                'user' => ['name' => 'Khách', 'reward_points' => 0],
                'rewards' => []
            ]);
        }
        
        $rewards = Reward::where('is_active', true)->get()->map(function ($reward) use ($user) {
            $userRedemptions = RewardRedemption::where('user_id', $user->id)
                ->where('reward_id', $reward->id)
                ->count();
                
            // Tránh lỗi chia cho 0
            $pointsRequired = $reward->points_required > 0 ? $reward->points_required : 1;
            $progress = min(100, round(($user->reward_points / $pointsRequired) * 100));

            return [
                'id' => $reward->id,
                'name' => $reward->name,
                'type' => $reward->type,
                'description' => $reward->description,
                'image' => $reward->image ? asset('storage/' . $reward->image) : null,
                'points_required' => $reward->points_required,
                'stock_quantity' => $reward->stock_quantity,
                'user_limit' => $reward->user_limit,
                'user_redemptions' => $userRedemptions,
                'can_redeem' => ($user->reward_points >= $reward->points_required) && 
                               ($userRedemptions < $reward->user_limit) && 
                               ($reward->stock_quantity > 0),
                'progress' => $progress
            ];
        });

        return response()->json([
            'banners' => $banners,
            'user' => [
                'name' => $user->name,
                'reward_points' => $user->reward_points
            ],
            'rewards' => $rewards
        ]);
    }

    public function redeem(Request $request, $id)
    {
        $user = $request->user();
        $reward = Reward::findOrFail($id);

        if (!$reward->is_active || $reward->stock_quantity <= 0) {
            return response()->json(['message' => 'Phần quà này đã hết hoặc không còn hoạt động.'], 400);
        }

        if ($user->reward_points < $reward->points_required) {
            return response()->json(['message' => 'Bạn không đủ điểm để đổi quà này.'], 400);
        }

        $userRedemptions = RewardRedemption::where('user_id', $user->id)
            ->where('reward_id', $reward->id)
            ->count();

        if ($userRedemptions >= $reward->user_limit) {
            return response()->json(['message' => 'Bạn đã đạt giới hạn đổi phần quà này.'], 400);
        }

        DB::transaction(function () use ($user, $reward) {
            // Trừ điểm user
            $user->decrement('reward_points', $reward->points_required);

            // Trừ kho
            $reward->decrement('stock_quantity', 1);

            // Lưu lịch sử
            RewardRedemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_required,
                'status' => 'completed'
            ]);
        });

        return response()->json([
            'message' => 'Đổi quà thành công!',
            'new_points' => $user->fresh()->reward_points
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        
        $orders = \App\Models\Order::with(['items.variant.product.category'])
            ->where('user_id', $user->id)
            ->get();
            
        $history = [];
        
        foreach ($orders as $order) {
            $totalPointsEarned = 0;
            foreach ($order->items as $item) {
                if (!$item->variant || !$item->variant->product) continue;
                
                $isGift = ($item->price == 0) || $item->marketing_gift_id;
                if ($isGift) continue;
                
                $itemPrice = $item->price > 0 && $item->price < 10000 ? $item->price * 1000 : $item->price;
                $itemTotal = $itemPrice * $item->quantity;
                
                $rate = $item->variant->product->loyalty_rate;
                if (is_null($rate)) {
                    $rate = $item->variant->product->category->loyalty_rate ?? null;
                }
                if (is_null($rate)) {
                    $rate = 0.2;
                }
                
                $totalPointsEarned += ceil(($itemTotal * $rate) / 100);
            }
            
            if ($totalPointsEarned > 0) {
                $history[] = [
                    'id' => 'order_' . $order->id,
                    'date' => $order->created_at->format('d/m/Y'),
                    'title' => 'Thưởng điểm từ đơn hàng ' . $order->order_number,
                    'points' => (int) $totalPointsEarned,
                    'timestamp' => $order->created_at->timestamp
                ];
            }
        }
        
        $redemptions = \App\Models\RewardRedemption::with('reward')
            ->where('user_id', $user->id)
            ->get();
            
        foreach ($redemptions as $redemption) {
            $history[] = [
                'id' => 'redeem_' . $redemption->id,
                'date' => $redemption->created_at->format('d/m/Y'),
                'title' => 'Đổi quà: ' . ($redemption->reward->name ?? 'Không rõ'),
                'points' => (int) -$redemption->points_spent,
                'timestamp' => $redemption->created_at->timestamp
            ];
        }
        
        // Sắp xếp tăng dần theo thời gian
        usort($history, function($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });
        
        // Khớp runningTotal với số điểm hiện tại của user để không bị lệch do seed data
        $currentTotal = $user->reward_points;
        $history = array_reverse($history); // Đảo ngược để xếp mới nhất lên đầu
        
        foreach ($history as &$item) {
            $item['runningTotal'] = $currentTotal;
            $currentTotal -= $item['points'];
        }
        
        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    public function myRewards(Request $request)
    {
        $user = $request->user();
        
        $redemptions = RewardRedemption::with('reward')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNull('order_id')
            ->get()
            ->map(function ($redemption) {
                if (!$redemption->reward) {
                    return null;
                }
                return [
                    'id' => $redemption->id,
                    'reward_id' => $redemption->reward_id,
                    'points_spent' => $redemption->points_spent,
                    'created_at' => $redemption->created_at->toISOString(),
                    'reward' => [
                        'id' => $redemption->reward->id,
                        'name' => $redemption->reward->name,
                        'type' => $redemption->reward->type,
                        'description' => $redemption->reward->description,
                        'image' => $redemption->reward->image ? asset('storage/' . $redemption->reward->image) : null,
                        'points_required' => $redemption->reward->points_required,
                    ]
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $redemptions
        ]);
    }
}

