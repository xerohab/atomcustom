<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountSettingsFormRequest;
use App\Services\RconService;
use App\Services\User\SessionService;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function __construct(private readonly SessionService $sessionService, private readonly UserService $userService, private readonly RconService $rconService) {}

    public function edit(): View
    {
        return view('user.settings.account', [
            'user' => Auth::user()->load('settings:allow_name_change'),
        ]);
    }

    public function sessionLogs(Request $request): View
    {
        $sessions = $this->sessionService->fetchSessionLogs($request);

        return view('user.settings.session-logs', [
            'logs' => $sessions,
        ]);
    }

    public function update(AccountSettingsFormRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->back()->withErrors('User not found');
        }

        if (! $this->rconService->isConnected() && Auth::user()->online) {
            return back()->withErrors('You must be offline to change your account settings');
        }

        if ($user->mail !== $request->input('mail')) {
            $this->userService->updateField($user, 'mail', $request->input('mail'));
        }

        if ($user->motto !== $request->input('motto')) {
            $this->rconService->setMotto($user, $request->input('motto'));
            $this->userService->updateField($user, 'motto', $request->input('motto'));
        }

        // Validate Quote / Prompt input
        $request->validate([
            'profile_quote_prefix' => 'nullable|string|max:100',
            'profile_quote' => 'nullable|string|max:250',
        ]);

        $this->userService->updateField($user, 'profile_quote_prefix', $request->input('profile_quote_prefix'));
        $this->userService->updateField($user, 'profile_quote', $request->input('profile_quote'));

        // Process profile banner image/GIF upload
        if ($request->hasFile('profile_banner')) {
            $request->validate([
                'profile_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            if ($user->profile_banner && Storage::disk('public')->exists($user->profile_banner)) {
                Storage::disk('public')->delete($user->profile_banner);
            }

            $path = $request->file('profile_banner')->store('banners', 'public');
            $this->userService->updateField($user, 'profile_banner', $path);
        }

        // Process profile background image/GIF upload
        if ($request->hasFile('profile_background')) {
            $request->validate([
                'profile_background' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            if ($user->profile_background && Storage::disk('public')->exists($user->profile_background)) {
                Storage::disk('public')->delete($user->profile_background);
            }

            $path = $request->file('profile_background')->store('backgrounds', 'public');
            $this->userService->updateField($user, 'profile_background', $path);
        }

        return redirect()->route('settings.account.show')->with('success', __('Your account settings has been updated'));
    }

    public function twoFactor(): View
    {
        return view('user.settings.two-factor');
    }
}