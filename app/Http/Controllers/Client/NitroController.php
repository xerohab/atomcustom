<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NitroController extends Controller
{
    public function __invoke(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Save IP explicitly
        $user->ip_current = request()->ip();
        $user->save();

        // Generate and persist SSO ticket using model method
        $ssoTicket = $user->ssoTicket();

        return view('client.nitro', [
            'sso' => $ssoTicket,
        ]);
    }
}