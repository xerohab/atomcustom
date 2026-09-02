<?php

use App\Actions\Fortify\Controllers\TwoFactorAuthenticatedSessionController;
use App\Http\Controllers\Articles\ArticleController;
use App\Http\Controllers\Articles\WebsiteArticleCommentsController;
use App\Http\Controllers\Badge\BadgeController;
use App\Http\Controllers\Client\FlashController;
use App\Http\Controllers\Client\NitroController;
use App\Http\Controllers\Community\LeaderboardController;
use App\Http\Controllers\Community\PhotosController;
use App\Http\Controllers\Community\Staff\StaffApplicationsController;
use App\Http\Controllers\Community\Staff\StaffController;
use App\Http\Controllers\Community\Staff\WebsiteTeamApplicationsController;
use App\Http\Controllers\Community\Staff\WebsiteTeamsController;
use App\Http\Controllers\Community\WebsiteRareValuesController;
use App\Http\Controllers\Help\WebsiteRulesController;
use App\Http\Controllers\Home\HomeController as UserHomeController;
use App\Http\Controllers\Home\ItemController as HomeItemController;
use App\Http\Controllers\Home\MessageController as HomeMessageController;
use App\Http\Controllers\Home\RatingController as HomeRatingController;
use App\Http\Controllers\Home\ShopController as HomeShopController;
use App\Http\Controllers\Miscellaneous\HomeController;
use App\Http\Controllers\Miscellaneous\InstallationController;
use App\Http\Controllers\Miscellaneous\LocaleController;
use App\Http\Controllers\Miscellaneous\LogoGeneratorController;
use App\Http\Controllers\Miscellaneous\MaintenanceController;
use App\Http\Controllers\Shop\PaypalController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\ShopVoucherController;
use App\Http\Controllers\User\AccountSettingsController;
use App\Http\Controllers\User\BannedController;
use App\Http\Controllers\User\DiscordController;
use App\Http\Controllers\User\ForgotPasswordController;
use App\Http\Controllers\User\GuestbookController;
use App\Http\Controllers\User\MeController;
use App\Http\Controllers\User\PasswordSettingsController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ReferralController;
use App\Http\Controllers\User\TwoFactorAuthenticationController;
use App\Http\Controllers\User\UserReferralController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

// REAL-TIME ONLINE USER COUNT ENDPOINT
Route::get('/api/online-count', function () {
    $onlineCount = User::where('online', '1')->count();

    return response()->json([
        'online_count' => $onlineCount,
        'onlineCount'  => $onlineCount,
    ]);
})->name('api.online-count');

// Permission Check Helper
if (!function_exists('canAccessHkPermission')) {
    function canAccessHkPermission($permissionKey) {
        if (!auth()->check()) return false;
        $perm = DB::table('housekeeping_permissions')->where('permission', $permissionKey)->first();
        $minRank = $perm ? $perm->min_rank : 6;
        return auth()->user()->rank >= $minRank;
    }
}

// Language route
Route::get('/language/{locale}', LocaleController::class)->name('language.select');

// Installation routes
Route::prefix('installation')->controller(InstallationController::class)->group(function () {
    Route::get('/', 'index')->name('installation.index');
    Route::get('/step/{step}', 'showStep')->name('installation.show-step');

    Route::post('/start-installation', 'storeInstallationKey')->name('installation.start-installation');
    Route::post('/restart-installation', 'restartInstallation')->name('installation.restart');
    Route::post('/previous-step', 'previousStep')->name('installation.previous-step');
    Route::post('/save-step', 'saveStepSettings')->name('installation.save-step');
    Route::post('/complete', 'completeInstallation')->name('installation.complete');
});

// All routes within this group protected by maintenance, ban and 2FA middleware
Route::middleware(['maintenance', 'check.ban', 'force.staff.2fa'])->group(function () {
    Route::get('/maintenance', MaintenanceController::class)->name('maintenance.show');
    Route::get('/banned', BannedController::class)->name('banned.show');

    // Guest routes
    Route::middleware(['guest', 'throttle:15,1'])->withoutMiddleware('force.staff.2fa')->group(function () {
        Route::get('/login', static fn () => to_route('welcome'))->name('login');
        Route::get('/', HomeController::class)->name('welcome');

        Route::get('/register', [RegisteredUserController::class, 'create']);
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
        Route::get('/register/{referral_code}', UserReferralController::class)->name('register.referral');

        // Password Reset
        Route::get('forgot-password', ForgotPasswordController::class)->name('forgot.password.get');
        Route::post('forgot-password', [ForgotPasswordController::class, 'submitForgetPassword'])->name('forgot.password.post');
        Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPassword'])->name('reset.password.get');
        Route::post('reset-password/{token}', [ForgotPasswordController::class, 'submitResetPassword'])->name('reset.password.post');
    });

    // Auth routes
    Route::middleware('auth')->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/me', MeController::class)->name('me.show');
            Route::get('/claim/referral-reward', ReferralController::class)->name('claim.referral-reward');

            Route::prefix('settings')->group(function () {
                Route::get('/account', [AccountSettingsController::class, 'edit'])->name('settings.account.show');
                Route::put('/account', [AccountSettingsController::class, 'update'])->name('settings.account.update');

                Route::get('/password', [PasswordSettingsController::class, 'edit'])->name('settings.password.show');
                Route::put('/password', [PasswordSettingsController::class, 'update'])->name('settings.password.update');

                Route::get('/session-logs', [AccountSettingsController::class, 'sessionLogs'])->name('settings.session-logs');

                Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'index'])->name('settings.two-factor');
                Route::post('/user/settings/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])->name('user.two-factor.enable');
                Route::post('/2fa-verify', [TwoFactorAuthenticationController::class, 'verify'])->name('two-factor.verify');
                Route::delete('/user/settings/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])->name('user.two-factor.disable');

                // DISCORD OAUTH CONNECTED ACCOUNTS
                Route::prefix('discord')->as('settings.discord.')->group(function () {
                    Route::get('/connect', [DiscordController::class, 'redirect'])->name('connect');
                    Route::get('/callback', [DiscordController::class, 'callback'])->name('callback');
                    Route::post('/disconnect', [DiscordController::class, 'disconnect'])->name('disconnect');
                });
            });
        });

        // FRONTEND HELP CENTER & TICKETS
        Route::prefix('help-center')->as('help-center.')->withoutMiddleware('check.ban')->group(function () {
            Route::get('/', function () {
                $myTickets = DB::table('website_tickets')
                    ->where('user_id', auth()->id())
                    ->orderBy('id', 'desc')
                    ->take(5)
                    ->get();
                return view('help-center.index', compact('myTickets'));
            })->name('index');

            Route::prefix('tickets')->as('ticket.')->group(function () {
                Route::get('/', function () {
                    $tickets = DB::table('website_tickets')
                        ->where('user_id', auth()->id())
                        ->orderBy('id', 'desc')
                        ->paginate(10);
                    return view('help-center.tickets.index', compact('tickets'));
                })->name('index');

                Route::get('/create', function () {
                    return view('help-center.tickets.create');
                })->name('create');

                Route::post('/store', function (\Illuminate\Http\Request $request) {
                    $request->validate([
                        'department' => 'required|string',
                        'subject' => 'required|string|max:150',
                        'message' => 'required|string',
                    ]);

                    $id = DB::table('website_tickets')->insertGetId([
                        'user_id' => auth()->id(),
                        'department' => $request->input('department'),
                        'subject' => $request->input('subject'),
                        'message' => $request->input('message'),
                        'status' => 'open',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return redirect()->route('help-center.ticket.show', $id)->with('success', 'Ticket created successfully!');
                })->name('store');

                Route::get('/show/{ticket}', function ($id) {
                    $ticket = DB::table('website_tickets')->where('id', $id)->where('user_id', auth()->id())->first();
                    if (!$ticket) abort(404);

                    $replies = DB::table('website_ticket_replies')
                        ->leftJoin('users', 'website_ticket_replies.user_id', '=', 'users.id')
                        ->select('website_ticket_replies.*', 'users.username', 'users.look', 'users.rank')
                        ->where('ticket_id', $id)
                        ->orderBy('id', 'asc')
                        ->get();

                    return view('help-center.tickets.show', compact('ticket', 'replies'));
                })->name('show');

                Route::post('/reply/{ticket}/store', function (\Illuminate\Http\Request $request, $id) {
                    $ticket = DB::table('website_tickets')->where('id', $id)->where('user_id', auth()->id())->first();
                    if (!$ticket || $ticket->status === 'closed') abort(403);

                    DB::table('website_ticket_replies')->insert([
                        'ticket_id' => $id,
                        'user_id' => auth()->id(),
                        'message' => $request->input('message'),
                        'created_at' => now(),
                    ]);

                    DB::table('website_tickets')->where('id', $id)->update(['updated_at' => now()]);

                    return redirect()->back()->with('success', 'Reply posted!');
                })->name('reply.store');
            });

            Route::get('/rules', WebsiteRulesController::class)->name('rules.index')->withoutMiddleware('auth');
        });

        // HOUSEKEEPING ADMIN SUITE
        Route::prefix('housekeeping')->group(function () {

            // DASHBOARD ROUTE
            Route::match(['get', 'post'], '/', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('housekeeping_access')) return redirect('/user/me');

                if ($request->isMethod('post') && $request->has('welcome_message')) {
                    if (!canAccessHkPermission('edit_welcome_message')) {
                        return redirect()->back()->with('error', 'No permission to edit welcome message.');
                    }

                    DB::table('website_settings')->updateOrInsert(
                        ['key' => 'ase_welcome_message'],
                        ['value' => $request->input('welcome_message')]
                    );

                    return redirect()->back()->with('success', 'Welcome message updated!');
                }

                $welcomeMessage = DB::table('website_settings')->where('key', 'ase_welcome_message')->value('value')
                    ?? 'Welcome to the Housekeeping Administration Console.';

                $pinnedNotices = collect();
                if (\Illuminate\Support\Facades\Schema::hasTable('housekeeping_notices')) {
                    $pinnedNotices = DB::table('housekeeping_notices')
                        ->leftJoin('users', 'housekeeping_notices.user_id', '=', 'users.id')
                        ->select('housekeeping_notices.*', 'users.username')
                        ->where('is_pinned', 1)
                        ->orderBy('id', 'desc')
                        ->take(3)
                        ->get();
                }

                return view('housekeeping.dashboard', compact('welcomeMessage', 'pinnedNotices'));
            })->name('housekeeping.dashboard');

            // HOUSEKEEPING TICKET MANAGER ROUTE
            Route::match(['get', 'post'], '/tickets', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_tickets')) return redirect('/housekeeping');

                // CLAIM TICKET / UPDATE STATUS / REPLY TICKET
                if ($request->isMethod('post')) {
                    $ticketId = $request->input('ticket_id');

                    if ($request->has('action_claim')) {
                        DB::table('website_tickets')->where('id', $ticketId)->update([
                            'assigned_to' => auth()->id(),
                            'status' => 'in_progress',
                            'updated_at' => now(),
                        ]);
                        return redirect('/housekeeping/tickets?inspect=' . $ticketId)->with('success', 'Ticket claimed!');
                    }

                    if ($request->has('action_status')) {
                        DB::table('website_tickets')->where('id', $ticketId)->update([
                            'status' => $request->input('status'),
                            'updated_at' => now(),
                        ]);
                        return redirect('/housekeeping/tickets?inspect=' . $ticketId)->with('success', 'Status updated!');
                    }

                    if ($request->has('reply_message')) {
                        DB::table('website_ticket_replies')->insert([
                            'ticket_id' => $ticketId,
                            'user_id' => auth()->id(),
                            'message' => $request->input('reply_message'),
                            'created_at' => now(),
                        ]);

                        DB::table('website_tickets')->where('id', $ticketId)->update([
                            'status' => 'in_progress',
                            'assigned_to' => auth()->id(),
                            'updated_at' => now(),
                        ]);

                        return redirect('/housekeeping/tickets?inspect=' . $ticketId)->with('success', 'Reply sent successfully!');
                    }
                }

                // FILTER BY ALLOWED DEPARTMENTS PER STAFF RANK
                $deptMap = [
                    'ban_appeal' => 'dept_ticket_ban_appeal',
                    'whitelisting' => 'dept_ticket_whitelisting',
                    'general_help' => 'dept_ticket_general_help',
                    'room_ads_request' => 'dept_ticket_room_ads_request',
                    'report_scammer' => 'dept_ticket_report_scammer',
                ];

                $allowedDepts = [];
                foreach ($deptMap as $deptKey => $permKey) {
                    if (canAccessHkPermission($permKey)) {
                        $allowedDepts[] = $deptKey;
                    }
                }

                $inspectTicket = null;
                $inspectReplies = collect();
                if ($request->has('inspect')) {
                    $inspectId = (int) $request->input('inspect');
                    $inspectTicket = DB::table('website_tickets')
                        ->leftJoin('users as submitter', 'website_tickets.user_id', '=', 'submitter.id')
                        ->leftJoin('users as staff', 'website_tickets.assigned_to', '=', 'staff.id')
                        ->select('website_tickets.*', 'submitter.username as author_name', 'submitter.look as author_look', 'staff.username as staff_name')
                        ->where('website_tickets.id', $inspectId)
                        ->whereIn('website_tickets.department', $allowedDepts)
                        ->first();

                    if ($inspectTicket) {
                        $inspectReplies = DB::table('website_ticket_replies')
                            ->leftJoin('users', 'website_ticket_replies.user_id', '=', 'users.id')
                            ->select('website_ticket_replies.*', 'users.username', 'users.look', 'users.rank')
                            ->where('ticket_id', $inspectId)
                            ->orderBy('id', 'asc')
                            ->get();
                    }
                }

                $tickets = DB::table('website_tickets')
                    ->leftJoin('users as submitter', 'website_tickets.user_id', '=', 'submitter.id')
                    ->leftJoin('users as staff', 'website_tickets.assigned_to', '=', 'staff.id')
                    ->select('website_tickets.*', 'submitter.username as author_name', 'staff.username as staff_name')
                    ->whereIn('website_tickets.department', $allowedDepts)
                    ->orderBy('id', 'desc')
                    ->paginate(15);

                return view('housekeeping.tickets', compact('tickets', 'inspectTicket', 'inspectReplies'));
            })->name('housekeeping.tickets');

            // USERS MANAGEMENT
            Route::match(['get', 'post'], '/users', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_users')) return redirect('/housekeeping');

                if ($request->isMethod('post') && $request->has('user_id') && !$request->has('action_type')) {
                    $userId = (int) $request->input('user_id');
                    $duckets = (int) $request->input('duckets', 0);
                    $diamonds = (int) $request->input('diamonds', 0);

                    $updateData = [
                        'motto' => $request->input('motto'),
                        'mail' => $request->input('mail'),
                        'rank' => (int) $request->input('rank'),
                    ];

                    if (canAccessHkPermission('manage_user_currencies')) {
                        $updateData['credits'] = (int) $request->input('credits', 0);

                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'activity_points')) {
                            $updateData['activity_points'] = $duckets;
                        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('users', 'duckets')) {
                            $updateData['duckets'] = $duckets;
                        }

                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'vip_points')) {
                            $updateData['vip_points'] = $diamonds;
                        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('users', 'diamonds')) {
                            $updateData['diamonds'] = $diamonds;
                        }

                        if (\Illuminate\Support\Facades\Schema::hasTable('user_currency')) {
                            DB::table('user_currency')->updateOrInsert(['user_id' => $userId, 'type' => 0], ['amount' => $duckets]);
                            DB::table('user_currency')->updateOrInsert(['user_id' => $userId, 'type' => 5], ['amount' => $diamonds]);
                        }
                    }

                    if ($request->filled('password')) {
                        if (!canAccessHkPermission('manage_user_password')) {
                            return redirect()->back()->with('error', 'No permission to reset passwords.');
                        }
                        $updateData['password'] = Hash::make($request->input('password'));
                    }

                    DB::table('users')->where('id', $userId)->update($updateData);

                    return redirect('/housekeeping/users?search=' . $request->input('search_back', ''))->with('success', 'User updated!');
                }

                if ($request->isMethod('post') && $request->input('action_type') === 'update_furniture') {
                    if (!canAccessHkPermission('manage_user_furniture')) {
                        return redirect()->back()->with('error', 'No permission to modify furniture.');
                    }

                    $targetUserId = $request->input('target_user_id');
                    $itemDefId = $request->input('item_id');
                    $targetLocation = $request->input('target_location');
                    $newQuantity = (int) $request->input('new_quantity', 0);

                    $query = DB::table('items')->where('user_id', $targetUserId)->where('item_id', $itemDefId);
                    if ($targetLocation === 'inventory') {
                        $query->where('room_id', 0);
                    } else {
                        $query->where('room_id', (int) $targetLocation);
                    }

                    $currentItems = $query->get();
                    $currentCount = $currentItems->count();

                    if ($newQuantity < $currentCount) {
                        $deleteCount = $currentCount - $newQuantity;
                        $idsToDelete = $currentItems->take($deleteCount)->pluck('id');
                        DB::table('items')->whereIn('id', $idsToDelete)->delete();
                    } elseif ($newQuantity > $currentCount && $currentCount > 0) {
                        $sampleItem = (array) $currentItems->first();
                        unset($sampleItem['id']);

                        $addCount = $newQuantity - $currentCount;
                        for ($i = 0; $i < $addCount; $i++) {
                            DB::table('items')->insert($sampleItem);
                        }
                    }

                    return redirect('/housekeeping/users?inspect_user=' . $targetUserId)->with('success', 'Furniture updated!');
                }

                $inspectUser = null;
                $inventoryFurni = collect();
                $roomFurni = collect();

                if ($request->has('inspect_user')) {
                    $inspectUserId = (int) $request->input('inspect_user');
                    $inspectUser = DB::table('users')->where('id', $inspectUserId)->first();

                    if ($inspectUser) {
                        $inventoryFurni = DB::table('items')
                            ->leftJoin('items_base', 'items.item_id', '=', 'items_base.id')
                            ->where('items.user_id', $inspectUserId)
                            ->where('items.room_id', 0)
                            ->select('items.item_id', 'items_base.item_name', 'items_base.public_name', DB::raw('count(*) as total_count'))
                            ->groupBy('items.item_id', 'items_base.item_name', 'items_base.public_name')
                            ->get();

                        $roomNameCol = \Illuminate\Support\Facades\Schema::hasColumn('rooms', 'caption') ? 'rooms.caption' : (\Illuminate\Support\Facades\Schema::hasColumn('rooms', 'name') ? 'rooms.name' : 'items.room_id');

                        $roomFurni = DB::table('items')
                            ->leftJoin('items_base', 'items.item_id', '=', 'items_base.id')
                            ->leftJoin('rooms', 'items.room_id', '=', 'rooms.id')
                            ->where('items.user_id', $inspectUserId)
                            ->where('items.room_id', '>', 0)
                            ->select('items.room_id', DB::raw("{$roomNameCol} as room_name"), 'items.item_id', 'items_base.item_name', 'items_base.public_name', DB::raw('count(*) as total_count'))
                            ->groupBy('items.room_id', 'room_name', 'items.item_id', 'items_base.item_name', 'items_base.public_name')
                            ->get()
                            ->groupBy('room_id');
                    }
                }

                $searchQuery = $request->input('search');
                $users = DB::table('users')
                    ->when($searchQuery, function($q) use ($searchQuery) {
                        return $q->where('username', 'LIKE', "%{$searchQuery}%")
                                 ->orWhere('mail', 'LIKE', "%{$searchQuery}%");
                    })
                    ->orderBy('id', 'desc')
                    ->paginate(15)
                    ->appends(['search' => $searchQuery]);

                $users->getCollection()->transform(function ($u) {
                    $u->duckets_amount = $u->activity_points ?? $u->duckets ?? 0;
                    $u->diamonds_amount = $u->vip_points ?? $u->diamonds ?? 0;

                    if (\Illuminate\Support\Facades\Schema::hasTable('user_currency')) {
                        $ducketsRow = DB::table('user_currency')->where('user_id', $u->id)->where('type', 0)->first();
                        $diamondsRow = DB::table('user_currency')->where('user_id', $u->id)->where('type', 5)->first();

                        if ($ducketsRow) $u->duckets_amount = $ducketsRow->amount;
                        if ($diamondsRow) $u->diamonds_amount = $diamondsRow->amount;
                    }

                    return $u;
                });

                return view('housekeeping.users', compact('users', 'inspectUser', 'inventoryFurni', 'roomFurni'));
            })->name('housekeeping.users');

            // NOTICEBOARD
            Route::match(['get', 'post'], '/notices', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_noticeboard')) return redirect('/housekeeping');

                if (!\Illuminate\Support\Facades\Schema::hasTable('housekeeping_notices')) {
                    \Illuminate\Support\Facades\Schema::create('housekeeping_notices', function ($table) {
                        $table->id();
                        $table->integer('user_id');
                        $table->string('title');
                        $table->text('content');
                        $table->boolean('is_pinned')->default(false);
                        $table->timestamps();
                    });
                }

                if ($request->isMethod('post')) {
                    if ($request->has('action_type') && $request->input('action_type') === 'toggle_pin') {
                        $noticeId = $request->input('notice_id');
                        $current = DB::table('housekeeping_notices')->where('id', $noticeId)->value('is_pinned');
                        DB::table('housekeeping_notices')->where('id', $noticeId)->update(['is_pinned' => !$current]);
                        return redirect()->back()->with('success', 'Notice pin status updated!');
                    }

                    DB::table('housekeeping_notices')->insert([
                        'user_id' => auth()->id(),
                        'title' => $request->input('title'),
                        'content' => $request->input('content'),
                        'is_pinned' => $request->has('is_pinned') ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return redirect()->back()->with('success', 'Notice posted successfully!');
                }

                $notices = DB::table('housekeeping_notices')
                    ->leftJoin('users', 'housekeeping_notices.user_id', '=', 'users.id')
                    ->select('housekeeping_notices.*', 'users.username', 'users.look')
                    ->orderBy('is_pinned', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate(10);

                return view('housekeeping.notices', compact('notices'));
            })->name('housekeeping.notices');

            Route::delete('/notices/{id}', function ($id) {
                if (!canAccessHkPermission('manage_noticeboard')) return redirect('/housekeeping');
                DB::table('housekeeping_notices')->where('id', $id)->delete();
                return redirect()->back()->with('success', 'Notice deleted successfully!');
            })->name('housekeeping.notices.delete');

            // CHATLOGS
            Route::get('/chatlogs', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_logs')) return redirect('/housekeeping');

                $tab = $request->input('tab', 'room');
                $search = $request->input('search');

                $roomLogs = collect();
                $privateLogs = collect();

                $roomNameCol = \Illuminate\Support\Facades\Schema::hasColumn('rooms', 'caption') ? 'rooms.caption' : (\Illuminate\Support\Facades\Schema::hasColumn('rooms', 'name') ? 'rooms.name' : 'chatlogs_room.room_id');

                if ($tab === 'room') {
                    $roomLogs = DB::table('chatlogs_room')
                        ->leftJoin('users as u_from', 'chatlogs_room.user_from_id', '=', 'u_from.id')
                        ->leftJoin('rooms', 'chatlogs_room.room_id', '=', 'rooms.id')
                        ->select('chatlogs_room.*', 'u_from.username as sender_name', DB::raw("{$roomNameCol} as room_name"))
                        ->when($search, function ($q) use ($search) {
                            return $q->where('chatlogs_room.message', 'LIKE', "%{$search}%")
                                     ->orWhere('u_from.username', 'LIKE', "%{$search}%");
                        })
                        ->orderBy('chatlogs_room.id', 'desc')
                        ->paginate(25)
                        ->appends(['tab' => 'room', 'search' => $search]);
                } else {
                    $privateLogs = DB::table('chatlogs_private')
                        ->leftJoin('users as u_from', 'chatlogs_private.user_from_id', '=', 'u_from.id')
                        ->leftJoin('users as u_to', 'chatlogs_private.user_to_id', '=', 'u_to.id')
                        ->select('chatlogs_private.*', 'u_from.username as sender_name', 'u_to.username as receiver_name')
                        ->when($search, function ($q) use ($search) {
                            return $q->where('chatlogs_private.message', 'LIKE', "%{$search}%")
                                     ->orWhere('u_from.username', 'LIKE', "%{$search}%")
                                     ->orWhere('u_to.username', 'LIKE', "%{$search}%");
                        })
                        ->orderBy('chatlogs_private.id', 'desc')
                        ->paginate(25)
                        ->appends(['tab' => 'private', 'search' => $search]);
                }

                return view('housekeeping.chatlogs', compact('roomLogs', 'privateLogs', 'tab', 'search'));
            })->name('housekeeping.chatlogs');

            // ARTICLES & OTHER HK ROUTES
            Route::match(['get', 'post'], '/articles', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('edit_article')) return redirect('/housekeeping');

                if ($request->isMethod('post') && $request->has('article_id')) {
                    $imagePath = $request->input('image');

                    if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
                        $file = $request->file('image_file');
                        $fileName = time() . '_banner_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/articles'), $fileName);
                        $imagePath = '/uploads/articles/' . $fileName;
                    }

                    $fullStory = $request->input('full_story');
                    if ($request->hasFile('body_image_file') && $request->file('body_image_file')->isValid()) {
                        $bodyFile = $request->file('body_image_file');
                        $bodyFileName = time() . '_body_' . Str::slug(pathinfo($bodyFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $bodyFile->getClientOriginalExtension();
                        $bodyFile->move(public_path('uploads/articles'), $bodyFileName);
                        $fullStory .= '<p><img src="/uploads/articles/' . $bodyFileName . '" alt="Article Image" style="max-width:100%; border-radius:1rem; margin:1rem 0;"></p>';
                    }

                    DB::table('website_articles')->where('id', $request->input('article_id'))->update([
                        'title' => $request->input('title'),
                        'short_story' => $request->input('short_story'),
                        'full_story' => $fullStory,
                        'image' => $imagePath ?? '/assets/images/news/default.png',
                        'can_comment' => $request->has('can_comment') ? 1 : 0,
                        'updated_at' => now(),
                    ]);
                    return redirect()->back()->with('success', 'Article updated successfully!');
                }

                $articles = DB::table('website_articles')->whereNull('deleted_at')->orderBy('id', 'desc')->paginate(10);
                return view('housekeeping.articles', compact('articles'));
            })->name('housekeeping.articles');

            Route::match(['get', 'post'], '/articles/create', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('create_article')) return redirect('/housekeeping');

                if ($request->isMethod('post')) {
                    $imagePath = $request->input('image');

                    if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
                        $file = $request->file('image_file');
                        $fileName = time() . '_banner_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/articles'), $fileName);
                        $imagePath = '/uploads/articles/' . $fileName;
                    }

                    $fullStory = $request->input('full_story');
                    if ($request->hasFile('body_image_file') && $request->file('body_image_file')->isValid()) {
                        $bodyFile = $request->file('body_image_file');
                        $bodyFileName = time() . '_body_' . Str::slug(pathinfo($bodyFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $bodyFile->getClientOriginalExtension();
                        $bodyFile->move(public_path('uploads/articles'), $bodyFileName);
                        $fullStory .= '<p><img src="/uploads/articles/' . $bodyFileName . '" alt="Article Image" style="max-width:100%; border-radius:1rem; margin:1rem 0;"></p>';
                    }

                    DB::table('website_articles')->insert([
                        'title' => $request->input('title'),
                        'slug' => Str::slug($request->input('title')),
                        'short_story' => $request->input('short_story'),
                        'full_story' => $fullStory,
                        'image' => $imagePath ?? '/assets/images/news/default.png',
                        'can_comment' => $request->has('can_comment') ? 1 : 0,
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    return redirect('/housekeeping/articles')->with('success', 'Article published!');
                }

                return view('housekeeping.articles-create');
            })->name('housekeeping.articles.create');

            Route::delete('/articles/{id}', function ($id) {
                if (!canAccessHkPermission('delete_article')) return redirect('/housekeeping');
                DB::table('website_articles')->where('id', $id)->delete();
                return redirect()->back()->with('success', 'Article deleted!');
            })->name('housekeeping.articles.delete');

            // BANS, APPLICATIONS, SHOP, SETTINGS & PERMISSIONS
            Route::match(['get', 'post'], '/bans', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_bans')) return redirect('/housekeeping');

                if ($request->isMethod('post')) {
                    $targetValue = trim($request->input('value'));
                    $banType = $request->input('type', 'account');
                    $duration = (int) $request->input('duration', 86400);
                    $userId = 0; $ip = ''; $machineId = '';

                    if ($banType === 'account' || filter_var($targetValue, FILTER_VALIDATE_IP) === false) {
                        $user = DB::table('users')->where('username', $targetValue)->first();
                        if ($user) {
                            $userId = $user->id;
                            $ip = $user->ip_last ?? '';
                            $machineId = $user->machine_id ?? '';
                        } elseif ($banType === 'account') {
                            return redirect()->back()->with('error', 'Target user not found!');
                        }
                    }

                    if ($banType === 'ip') $ip = $targetValue;
                    elseif ($banType === 'machine') $machineId = $targetValue;

                    DB::table('bans')->insert([
                        'user_id' => $userId,
                        'ip' => $ip,
                        'machine_id' => $machineId,
                        'user_staff_id' => auth()->id(),
                        'timestamp' => time(),
                        'ban_expire' => min(time() + $duration, 2147483647),
                        'ban_reason' => substr($request->input('ban_reason'), 0, 200),
                        'type' => $banType,
                        'cfh_topic' => -1,
                    ]);

                    return redirect()->back()->with('success', 'Ban applied!');
                }

                $bans = DB::table('bans')->orderBy('id', 'desc')->paginate(15);
                return view('housekeeping.bans', compact('bans'));
            })->name('housekeeping.bans');

            Route::get('/applications', function () {
                if (!canAccessHkPermission('manage_applications')) return redirect('/housekeeping');
                $applications = DB::table('website_staff_applications')->orderBy('id', 'desc')->paginate(15);
                return view('housekeeping.applications', compact('applications'));
            })->name('housekeeping.applications');

            Route::match(['get', 'post'], '/shop', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_shop')) return redirect('/housekeeping');
                $shopArticles = DB::table('website_shop_articles')->orderBy('costs', 'asc')->paginate(15);
                return view('housekeeping.shop', compact('shopArticles'));
            })->name('housekeeping.shop');

            Route::match(['get', 'post'], '/settings', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_settings')) return redirect('/housekeeping');
                $settings = DB::table('website_settings')->pluck('value', 'key')->toArray();
                return view('housekeeping.settings', compact('settings'));
            })->name('housekeeping.settings');

            Route::match(['get', 'post'], '/permissions', function (\Illuminate\Http\Request $request) {
                if (!canAccessHkPermission('manage_permissions')) return redirect('/housekeeping');
                $permissions = DB::table('housekeeping_permissions')->orderBy('permission', 'asc')->get();
                return view('housekeeping.permissions', compact('permissions'));
            })->name('housekeeping.permissions');
        });

        // Core routes
        Route::get('/draw-badge', [BadgeController::class, 'show'])->name('draw-badge');
        Route::post('/buy-badge', [BadgeController::class, 'buy'])->name('badge.buy');
        Route::get('/profile/{user:username}', ProfileController::class)->name('profile.show');
        Route::post('/profile/{user}/guestbook', [GuestbookController::class, 'store'])->name('guestbook.store');
        Route::delete('/profile/{user}/{guestbook}/delete', [GuestbookController::class, 'destroy'])->name('guestbook.destroy');

        Route::prefix('home')->as('home.')->group(function () {
            Route::get('/{user:username}', [UserHomeController::class, 'show'])->name('show')->withoutMiddleware('auth');
            Route::get('/{user:username}/placed-items', [UserHomeController::class, 'getPlacedItems'])->name('placed-items')->withoutMiddleware('auth');
            Route::get('/{user:username}/widget-content/{itemId}', [HomeItemController::class, 'getWidgetContent'])->name('widget-content')->withoutMiddleware('auth');
            Route::post('/{user:username}/save', [UserHomeController::class, 'save'])->name('save')->middleware('throttle:10,1');
            Route::post('/{user:username}/buy-item', [HomeItemController::class, 'store'])->name('buy-item')->middleware('throttle:30,1');
            Route::post('/{user:username}/rating', [HomeRatingController::class, 'store'])->name('rating')->middleware('throttle:10,1');
            Route::post('/{user:username}/message', [HomeMessageController::class, 'store'])->name('message')->middleware('throttle:10,1');

            Route::prefix('shop')->as('shop.')->group(function () {
                Route::get('/categories', [HomeShopController::class, 'categories'])->name('categories');
                Route::get('/category/{category}/items', [HomeShopController::class, 'itemsByCategory'])->name('category-items');
                Route::get('/type/{type}/items', [HomeShopController::class, 'itemsByType'])->name('type-items');
                Route::get('/balance', [HomeShopController::class, 'balance'])->name('balance');
            });

            Route::get('/{user:username}/inventory', [HomeShopController::class, 'inventory'])->name('inventory');
        });

        Route::prefix('community')->group(function () {
            Route::get('/photos', PhotosController::class)->name('photos.index');

            Route::withoutMiddleware('auth')->group(function () {
                Route::get('/articles', [ArticleController::class, 'index'])->name('article.index');
                Route::get('/article/{article:slug}', [ArticleController::class, 'show'])->name('article.show');
            });

            Route::get('/staff', StaffController::class)->name('staff.index');
            Route::get('/teams', WebsiteTeamsController::class)->name('teams.index');

            Route::get('/staff-applications', [StaffApplicationsController::class, 'index'])->name('staff-applications.index');
            Route::get('/staff-applications/{position}', [StaffApplicationsController::class, 'show'])->name('staff-applications.show');
            Route::post('/staff-applications/{position}', [StaffApplicationsController::class, 'store'])->name('staff-applications.store');

            Route::get('/team-applications', [WebsiteTeamApplicationsController::class, 'index'])->name('team-applications.index');
            Route::get('/team-applications/{position}', [WebsiteTeamApplicationsController::class, 'show'])->name('team-applications.show');
            Route::post('/team-applications/{position}', [WebsiteTeamApplicationsController::class, 'store'])->name('team-applications.store');

            Route::post('/article/{article:slug}/comment', [WebsiteArticleCommentsController::class, 'store'])->name('article.comment.store');
            Route::delete('/article/{comment}/comment', [WebsiteArticleCommentsController::class, 'destroy'])->name('article.comment.destroy');
            Route::post('/article/{article:slug}/toggle-reaction', [ArticleController::class, 'toggleReaction'])->name('article.toggle-reaction')->middleware('throttle:30,1');
        });

        Route::get('/leaderboard', LeaderboardController::class)->name('leaderboard.index');

        Route::prefix('shop')->group(function () {
            Route::get('/{category:slug?}', ShopController::class)->name('shop.index');
            Route::post('/purchase/{package}', [ShopController::class, 'purchase'])->name('shop.buy');
            Route::post('/voucher', ShopVoucherController::class)->name('shop.use-voucher');
        });

        Route::controller(PaypalController::class)->prefix('paypal')->group(function () {
            Route::get('/process-transaction', 'process')->name('paypal.process-transaction');
            Route::get('/successful-transaction', 'successful')->name('paypal.successful-transaction');
            Route::get('/cancelled-transaction', 'cancelled')->name('paypal.cancelled-transaction');
        });

        Route::get('/values', [WebsiteRareValuesController::class, 'index'])->name('values.index');
        Route::post('/values/search', [WebsiteRareValuesController::class, 'search'])->name('values.search');
        Route::get('/values/{value}', [WebsiteRareValuesController::class, 'value'])->name('values.value');

        // CLIENT ROUTES (Cleaned of restrictive middleware)
        Route::prefix('game')->group(function () {
            Route::get('/nitro', NitroController::class)->name('nitro-client');
            Route::get('/flash', FlashController::class)->name('flash-client');
        });

        Route::get('/logo-generator', [LogoGeneratorController::class, 'index'])->name('logo-generator.index');
        Route::post('/logo-generator', [LogoGeneratorController::class, 'store'])->name('store.generated-logo');
    });
});

if (Features::enabled(Features::twoFactorAuthentication())) {
    $twoFactorLimiter = config('fortify.limiters.two-factor');
    Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter(['guest:' . config('fortify.guard'), $twoFactorLimiter ? 'throttle:' . $twoFactorLimiter : null]));
}