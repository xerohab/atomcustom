<?php

namespace App\Models;

use App\Enums\CurrencyTypes;
use App\Models\Articles\WebsiteArticle;
use App\Models\Articles\WebsiteArticleComment;
use App\Models\Community\Staff\WebsiteStaffApplications;
use App\Models\Community\Staff\WebsiteTeam;
use App\Models\Compositions\HasHome;
use App\Models\Game\Furniture\Item;
use App\Models\Game\Permission;
use App\Models\Game\Player\MessengerFriendship;
use App\Models\Game\Player\UserBadge;
use App\Models\Game\Player\UserCurrency;
use App\Models\Game\Player\UserSetting;
use App\Models\Game\Player\UserSubscription;
use App\Models\Game\Room;
use App\Models\Help\WebsiteHelpCenterTicket;
use App\Models\Miscellaneous\CameraWeb;
use App\Models\Miscellaneous\WebsiteBetaCode;
use App\Models\Shop\WebsitePaypalTransaction;
use App\Models\Shop\WebsiteUsedShopVoucher;
use App\Models\User\Ban;
use App\Models\User\ClaimedReferralLog;
use App\Models\User\Referral;
use App\Models\User\UserReferral;
use App\Models\User\WebsiteUserGuestbook;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $username
 * @property string $real_name
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property int $two_factor_confirmed
 * @property string|null $two_factor_confirmed_at
 * @property string|null $mail
 * @property string $mail_verified
 * @property int $account_created
 * @property int $account_day_of_birth
 * @property int $last_login
 * @property int $last_online
 * @property string $motto
 * @property string $look
 * @property string|null $profile_banner
 * @property string|null $profile_background
 * @property string|null $profile_quote_prefix
 * @property string|null $profile_quote
 * @property string $gender
 * @property int $rank
 * @property bool $hidden_staff
 * @property int $credits
 * @property int $pixels
 * @property int $points
 * @property bool $online
 * @property string $auth_ticket
 * @property string $ip_register
 * @property string $ip_current Have your CMS update this IP. If you do not do this IP banning won't work!
 * @property string $machine_id
 * @property int $home_room
 * @property string|null $referral_code
 * @property int $website_balance
 * @property string|null $secret_key
 * @property string|null $pincode
 * @property int|null $extra_rank
 * @property int|null $team_id
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, HasHome, LogsActivity, Notifiable, TwoFactorAuthenticatable;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $fillable = [
        'username',
        'password',
        'mail',
        'gender',
        'motto',
        'look',
        'profile_banner',
        'profile_background',
        'profile_quote_prefix',
        'profile_quote',
        'auth_ticket',
        'ip_register',
        'ip_current',
        'account_created',
        'last_login',
        'last_online',
        'referral_code',
    ];

    protected $hidden = ['id', 'password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hidden_staff' => 'boolean',
            'online' => 'boolean',
        ];
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(UserCurrency::class, 'user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function currency(string $currency): int
    {
        if (! $this->relationLoaded('currencies')) {
            $this->load('currencies');
        }

        $type = CurrencyTypes::fromCurrencyName($currency);

        if ($type === null) {
            return 0;
        }

        return $this->currencies->where('type', $type->value)->first()?->amount ?? 0;
    }

    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class, 'id', 'rank');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(WebsiteArticle::class);
    }

    public function referrals(): HasOne
    {
        return $this->hasOne(UserReferral::class);
    }

    public function userReferrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function claimedReferralLog(): HasMany
    {
        return $this->hasMany(ClaimedReferralLog::class);
    }

    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'owner_id');
    }

    public function friends(): HasMany
    {
        return $this->hasMany(MessengerFriendship::class, 'user_one_id');
    }

    public function referralsNeeded(): int
    {
        $referrals = $this->referrals?->referrals_total ?? 0;

        return setting('referrals_needed') - $referrals;
    }

    public function ban(): HasOne
    {
        return $this->hasOne(Ban::class, 'user_id')->where('ban_expire', '>', time())->whereIn('type', ['account', 'super']);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class, 'user_id', 'id');
    }

    public function ssoTicket(): string
    {
        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $sso = sprintf('%s-%s', Str::replace(' ', '', setting('hotel_name')), Str::uuid());

            if (! User::where('auth_ticket', $sso)->exists()) {
                $this->auth_ticket = $sso;
                $this->save();

                return $sso;
            }
        }

        throw new \RuntimeException('Failed to generate unique SSO ticket after ' . $maxAttempts . ' attempts.');
    }

    public function betaCode(): HasOne
    {
        return $this->hasOne(WebsiteBetaCode::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(WebsiteTeam::class, 'team_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(WebsiteStaffApplications::class, 'user_id');
    }

    public function hcSubscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class);
    }

    public function articleComments(): HasMany
    {
        return $this->hasMany(WebsiteArticleComment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WebsitePaypalTransaction::class);
    }

    public function usedShopVouchers(): HasMany
    {
        return $this->hasMany(WebsiteUsedShopVoucher::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(WebsiteHelpCenterTicket::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CameraWeb::class);
    }

    public function profileGuestbook(): HasMany
    {
        return $this->hasMany(WebsiteUserGuestbook::class, 'profile_id');
    }

    public function guestbook(): HasMany
    {
        return $this->hasMany(WebsiteUserGuestbook::class, 'user_id');
    }

    public function chatLogs(): HasMany
    {
        return $this->hasMany(ChatlogRoom::class, 'user_from_id');
    }

    public function chatLogsPrivate(): HasMany
    {
        return $this->hasMany(ChatlogPrivate::class, 'user_from_id');
    }

    public function getOnlineFriends(int $total = 10): Collection
    {
        return $this->friends()
            ->select(['user_two_id', 'users.id', 'users.username', 'users.look', 'users.motto', 'users.last_online'])
            ->join('users', 'users.id', '=', 'user_two_id')
            ->where('users.online', '1')
            ->inRandomOrder()
            ->limit($total)
            ->get();
    }

    public function confirmTwoFactorAuthentication($code): bool
    {
        $codeIsValid = app(TwoFactorAuthenticationProvider::class)
            ->verify(decrypt($this->two_factor_secret), $code);

        if (! $codeIsValid) {
            return false;
        }

        $this->update([
            'two_factor_confirmed' => true,
        ]);

        return true;
    }

    public function hasAppliedForPosition(int $rankId): bool
    {
        return $this->applications()->where('rank_id', '=', $rankId)->exists();
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = Hash::make($newPassword);
        $this->save();
    }

    public function getFilamentName(): string
    {
        return $this->username ?? 'Guest';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return hasHousekeepingPermission('can_access_housekeeping');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id', 'username', 'motto', 'rank', 'credits'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function save(array $options = []): bool
    {
        if (! $this->isDirty()) {
            return true;
        }

        return parent::save($options);
    }

    public function hasAppliedForTeam(int $teamId): bool
    {
        if (! $teamId) {
            return false;
        }

        return $this->applications()
            ->where('team_id', $teamId)
            ->exists();
    }
}