<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(): View
    {
        $users = User::query()->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:user,admin'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Đã thêm tài khoản mới.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'required', 'in:user,admin'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if ($request->user()->is($user) && isset($validated['role']) && $validated['role'] !== $user->role) {
            return back()->withErrors(['role' => 'Bạn không thể tự thay đổi quyền của mình.']);
        }

        DB::transaction(function () use ($user, $validated): void {
            $admins = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $target = User::query()->lockForUpdate()->findOrFail($user->id);
            $role = $validated['role'] ?? $target->role;

            if ($target->role === 'admin' && $role === 'user' && $admins->count() <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Hệ thống cần có ít nhất một admin.',
                ]);
            }

            $attributes = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $role,
            ];
            if (! empty($validated['password'])) {
                $attributes['password'] = $validated['password'];
            }

            $target->update($attributes);
        });

        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật tài khoản.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'Bạn không thể tự xóa tài khoản của mình.']);
        }

        DB::transaction(function () use ($user): void {
            $admins = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $target = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($target->role === 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages([
                    'user' => 'Không thể xóa admin cuối cùng của hệ thống.',
                ]);
            }

            $target->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa tài khoản.');
    }
}
