<?php

namespace App\Http\Controllers\Api;

use App\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::where('id', '!=', auth()->id())
                ->orderBy('role')
                ->orderBy('name')
                ->get()
                ->makeHidden(['password']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::default()],
            'role' => ['required', 'in:cashier,kitchen'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_admin' => false,
        ]);
        AuditLogger::record($request->user(), 'user.created', $user, ['role' => $user->role]);

        return response()->json([
            'message' => 'Pegawai berhasil dibuat.',
            'user' => $user->makeHidden(['password']),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak dapat mengubah profil sendiri di sini.'], 422);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:cashier,kitchen'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->update(array_filter($data));
        AuditLogger::record($request->user(), 'user.updated', $user, $data);

        return response()->json([
            'message' => 'Pegawai berhasil diperbarui.',
            'user' => $user->makeHidden(['password']),
        ]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak dapat mereset password sendiri di sini.'], 422);
        }

        $data = $request->validate([
            'password' => ['required', Password::default()],
        ]);

        $user->update(['password' => $data['password']]);
        AuditLogger::record($request->user(), 'user.password_reset', $user);

        return response()->json([
            'message' => 'Password berhasil direset.',
        ]);
    }
}
