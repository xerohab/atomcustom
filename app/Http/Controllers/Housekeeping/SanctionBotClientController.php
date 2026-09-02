<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SanctionBotClientController extends Controller
{
    private function requireLogin(): int
    {
        abort_unless(auth()->check(), 401);

        $userId = (int) auth()->id();

        abort_unless(
            DB::table('users')
                ->where('id', $userId)
                ->exists(),
            401
        );

        return $userId;
    }

    public function profiles()
    {
        $this->requireLogin();

        $profiles = DB::table('sanction_bots as sb')
            ->leftJoin(
                'bots as b',
                'b.id',
                '=',
                'sb.bot_id'
            )
            ->where('sb.enabled', 1)
            ->select([
                'sb.id',
                'sb.name',
                'sb.bot_id',
                'sb.speech_enabled',
                'sb.speech_random',
                'sb.speech_interval',
                'sb.follow_distance',
                'b.name as bot_name',
                'b.figure as bot_figure',
                'b.gender as bot_gender',
            ])
            ->orderBy('sb.name')
            ->get();

        return response()->json([
            'success' => true,
            'profiles' => $profiles,
        ]);
    }

    public function prepare(Request $request)
    {
        $moderatorId = $this->requireLogin();

        $data = $request->validate([
            'target_user_id' =>
                'required|integer|min:1',

            'sanction_bot_id' =>
                'required|integer|min:0',
        ]);

        $targetUserId =
            (int) $data['target_user_id'];

        $sanctionBotId =
            (int) $data['sanction_bot_id'];

        abort_unless(
            DB::table('users')
                ->where('id', $targetUserId)
                ->exists(),
            422,
            'Target user does not exist.'
        );

        if ($sanctionBotId > 0) {
            abort_unless(
                DB::table('sanction_bots')
                    ->where('id', $sanctionBotId)
                    ->where('enabled', 1)
                    ->exists(),
                422,
                'That sanction bot is unavailable.'
            );
        }

        $now = time();

        /*
         * The selection only lives for two minutes.
         * The emulator consumes and deletes it when the
         * actual Mod Tool sanction arrives.
         */
        DB::table('sanction_bot_pending')
            ->updateOrInsert(
                [
                    'moderator_id' =>
                        $moderatorId,

                    'target_user_id' =>
                        $targetUserId,
                ],
                [
                    'sanction_bot_id' =>
                        $sanctionBotId,

                    'created_at' =>
                        $now,

                    'expires_at' =>
                        $now + 120,
                ]
            );

        /*
         * Housekeeping cleanup while we're here.
         */
        DB::table('sanction_bot_pending')
            ->where(
                'expires_at',
                '<',
                $now
            )
            ->delete();

        return response()->json([
            'success' => true,
            'sanction_bot_id' => $sanctionBotId,
        ]);
    }
}
