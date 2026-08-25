<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DiscordController extends Controller
{
    public function redirect()
    {
        $query = http_build_query([
            'client_id' => config('services.discord.client_id'),
            'redirect_uri' => config('services.discord.redirect'),
            'response_type' => 'code',
            'scope' => 'identify',
        ]);

        return redirect('https://discord.com/api/oauth2/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('settings.account.show')->with('error', 'Discord connection was canceled.');
        }

        // 1. Exchange OAuth code for Access Token
        $response = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id' => config('services.discord.client_id'),
            'client_secret' => config('services.discord.client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $request->get('code'),
            'redirect_uri' => config('services.discord.redirect'),
        ]);

        if ($response->failed()) {
            return redirect()->route('settings.account.show')->with('error', 'Failed to connect with Discord.');
        }

        $tokenData = $response->json();

        // 2. Fetch User Profile Details from Discord
        $userResponse = Http::withToken($tokenData['access_token'])->get('https://discord.com/api/users/@me');

        if ($userResponse->failed()) {
            return redirect()->route('settings.account.show')->with('error', 'Failed to retrieve Discord profile.');
        }

        $discordUser = $userResponse->json();

        // 3. Update logged-in user profile without updated_at column
        DB::table('users')->where('id', auth()->id())->update([
            'discord_id' => $discordUser['id'],
            'discord_username' => $discordUser['username'] . (isset($discordUser['discriminator']) && $discordUser['discriminator'] !== '0' ? '#' . $discordUser['discriminator'] : ''),
            'discord_avatar' => $discordUser['avatar'] ?? null,
        ]);

        return redirect()->route('settings.account.show')->with('success', 'Discord account linked successfully!');
    }

    public function disconnect()
    {
        DB::table('users')->where('id', auth()->id())->update([
            'discord_id' => null,
            'discord_username' => null,
            'discord_avatar' => null,
        ]);

        return redirect()->route('settings.account.show')->with('success', 'Discord account unlinked.');
    }
}