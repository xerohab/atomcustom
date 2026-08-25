<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Articles\WebsiteArticle;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MeController extends Controller
{
    public function __invoke(): View
    {
        // 1. Fetch total online users count
        $onlineUsersCount = User::where('online', '1')->count();

        // 2. Fetch online staff members (rank >= min_staff_rank setting or default 4)
        $minStaffRank = setting('min_staff_rank') ?? 4;

        $onlineStaff = User::where('online', '1')
            ->where('rank', '>=', $minStaffRank)
            ->with('permission:id,rank_name')
            ->get();

        return view('user.me', [
            'onlineFriends'    => Auth::user()?->getOnlineFriends(),
            'user'             => Auth::user()?->load('permission:id,rank_name'),
            'articles'         => WebsiteArticle::whereHas('user')->with('user:id,username,look')->latest()->take(5)->get(),
            'onlineUsersCount' => $onlineUsersCount,
            'onlineStaff'      => $onlineStaff,
        ]);
    }
}