<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $oldAvatar = $user->avatar;
        $avatar = $request->file('avatar');
        $removeAvatar = $validated['remove_avatar'] ?? false;

        unset($validated['avatar'], $validated['remove_avatar']);

        if ($avatar) {
            $avatarPath = $avatar->store('avatars', 'public');
            if ($avatarPath === false) {
                throw new \RuntimeException('Không thể lưu ảnh đại diện.');
            }
            $validated['avatar'] = $avatarPath;
        } elseif ($removeAvatar) {
            $validated['avatar'] = null;
        }

        $user->fill($validated);
        $user->save();

        if ($oldAvatar && ($avatar || $removeAvatar)) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
