<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Admin Authentication Controller
 */
class AdminAuthController extends Controller
{
    /**
     * Login admin user
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $validated['email'])->first();
        $adminRoles = ['superadmin', 'admin', 'manager', 'staff', 'operator'];

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đăng nhập không thành công. Vui lòng kiểm tra email và mật khẩu.',
            ], 401);
        }

        if (! in_array($user->role, $adminRoles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản không có quyền truy cập admin.',
            ], 403);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản đã bị khóa.',
            ], 403);
        }

        $token = $user->createToken('admin-api-token')->plainTextToken;
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    /**
     * Logout admin user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng xuất thành công.',
        ]);
    }

    /**
     * Get current authenticated admin
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:30',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật hồ sơ thành công.',
            'data' => $this->formatUser($user),
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['old_password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu cũ không đúng.',
            ], 422);
        }

        $user->update([
            'password' => bcrypt($validated['new_password']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đổi mật khẩu thành công.',
        ]);
    }

    /**
     * Standardize admin user payload
     */
    private function formatUser(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'last_login' => $user->last_login_at?->format('Y-m-d H:i:s'),
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
