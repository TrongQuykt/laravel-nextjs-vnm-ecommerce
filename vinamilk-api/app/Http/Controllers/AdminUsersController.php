<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin Users Controller
 * Quản lý người dùng admin
 */
class AdminUsersController extends Controller
{
    /**
     * Get all users
     * GET /api/v1/admin/users?page=1&limit=50
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 50);

        $query = User::query();

        $total = $query->count();
        $users = $query->orderByDesc('created_at')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->map(fn($user) => $this->formatUser($user));

        return response()->json([
            'status' => 'success',
            'data' => $users,
            'meta' => [
                'page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
            ],
        ]);
    }

    /**
     * Get single user
     * GET /api/v1/admin/users/{id}
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $this->formatUser($user),
        ]);
    }

    /**
     * Create user
     * POST /api/v1/admin/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:superadmin,admin,manager,staff,operator',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        AdminAuditLogController::log('create', 'User', $user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Người dùng được tạo thành công',
            'data' => $this->formatUser($user),
        ], 201);
    }

    /**
     * Update user
     * PUT /api/v1/admin/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $id,
            'role' => 'in:superadmin,admin,manager,staff,operator',
            'is_active' => 'boolean',
        ]);

        $changes = array_diff_assoc($validated, $user->only(array_keys($validated)));

        if (!empty($changes)) {
            $user->update($validated);
            AdminAuditLogController::log('update', 'User', $user->id, auth()->id(), $changes);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Người dùng được cập nhật thành công',
            'data' => $this->formatUser($user),
        ]);
    }

    /**
     * Delete user
     * DELETE /api/v1/admin/users/{id}
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa tài khoản của chính mình',
            ], 403);
        }

        AdminAuditLogController::log('delete', 'User', $user->id);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Người dùng được xóa thành công',
        ]);
    }

    /**
     * Assign role
     * POST /api/v1/admin/users/{id}/assign-role
     */
    public function assignRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|in:superadmin,admin,manager,staff,operator',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        AdminAuditLogController::log('update', 'User', $user->id, auth()->id(), [
            'role' => $oldRole . ' → ' . $validated['role'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vai trò được cập nhật thành công',
            'data' => $this->formatUser($user),
        ]);
    }

    /**
     * Format user for response
     */
    private function formatUser($user)
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
