<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->latest()->get();
        return response()->json(['data' => $addresses]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'detail' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();

        // If it's the first address or explicitly set as default
        $isDefault = $validated['is_default'] ?? false;
        if ($user->addresses()->count() === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            ...$validated,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'message' => 'Thêm địa chỉ thành công',
            'data' => $address
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);

        $validated = $request->validate([
            'last_name' => 'sometimes|required|string|max:255',
            'first_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'district' => 'sometimes|required|string|max:255',
            'ward' => 'sometimes|required|string|max:255',
            'detail' => 'sometimes|required|string|max:255',
            'is_default' => 'boolean',
        ]);

        if (isset($validated['is_default']) && $validated['is_default']) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $address
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $wasDefault = $address->is_default;
        
        $address->delete();

        // If we deleted the default address, make the most recently updated one default
        if ($wasDefault) {
            $newDefault = $request->user()->addresses()->latest('updated_at')->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'message' => 'Xóa địa chỉ thành công'
        ]);
    }
}
