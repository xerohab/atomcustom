<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SanctionBotController extends Controller
{
    private function guard(): void
    {
        abort_unless(
            canAccessHkPermission(
                'manage_sanction_bots'
            ),
            403
        );
    }

    public function clientProfiles()
    {
        $this->guard();

        $profiles = DB::table('sanction_bots as sb')
            ->join('bots as b', 'b.id', '=', 'sb.bot_id')
            ->where('sb.enabled', 1)
            ->select(
                'sb.id',
                'sb.name',
                'sb.bot_id',
                'b.name as bot_name'
            )
            ->orderBy('sb.name')
            ->get();

        return response()->json([
            'ok' => true,
            'profiles' => $profiles,
        ]);
    }


    public function index()
    {
        $this->guard();

        $profiles = DB::table('sanction_bots as sb')
            ->leftJoin(
                'bots as b',
                'b.id',
                '=',
                'sb.bot_id'
            )
            ->select(
                'sb.*',
                'b.name as bot_name',
                'b.figure as bot_figure',
                'b.gender as bot_gender'
            )
            ->orderBy('sb.id')
            ->get();

        $bots = DB::table('bots')
            ->select(
                'id',
                'name',
                'figure',
                'gender',
                'user_id'
            )
            ->orderBy('name')
            ->get();

        return view(
            'housekeeping.sanction-bots.index',
            compact(
                'profiles',
                'bots'
            )
        );
    }

    public function store(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'bot_id' => 'required|integer|min:1',
            'name' => 'required|string|max:100',
            'speech_interval' =>
                'required|integer|min:10|max:86400',
            'follow_distance' =>
                'required|integer|min:1|max:10',
            'speech_mode' =>
                'required|in:random,sequential',
            'lines' =>
                'nullable|string|max:50000',
        ]);

        abort_unless(
            DB::table('bots')
                ->where(
                    'id',
                    $data['bot_id']
                )
                ->exists(),
            422
        );

        DB::transaction(
            function () use ($request, $data) {
                $now = time();

                $id = DB::table(
                    'sanction_bots'
                )->insertGetId([
                    'bot_id' =>
                        $data['bot_id'],
                    'name' =>
                        $data['name'],
                    'enabled' =>
                        $request->boolean(
                            'enabled'
                        ),
                    'speech_enabled' =>
                        $request->boolean(
                            'speech_enabled'
                        ),
                    'speech_random' =>
                        $data['speech_mode']
                            === 'random',
                    'speech_interval' =>
                        $data[
                            'speech_interval'
                        ],
                    'follow_distance' =>
                        $data[
                            'follow_distance'
                        ],
                    'created_by' =>
                        auth()->id(),
                    'created_at' =>
                        $now,
                    'updated_at' =>
                        $now,
                ]);

                $this->replaceLines(
                    $id,
                    $data['lines'] ?? ''
                );
            }
        );

        return back()->with(
            'success',
            'Sanction bot profile created.'
        );
    }

    public function update(
        Request $request,
        int $id)
    {
        $this->guard();

        abort_unless(
            DB::table('sanction_bots')
                ->where('id', $id)
                ->exists(),
            404
        );

        $data = $request->validate([
            'bot_id' => 'required|integer|min:1',
            'name' => 'required|string|max:100',
            'speech_interval' =>
                'required|integer|min:10|max:86400',
            'follow_distance' =>
                'required|integer|min:1|max:10',
            'speech_mode' =>
                'required|in:random,sequential',
            'lines' =>
                'nullable|string|max:50000',
        ]);

        abort_unless(
            DB::table('bots')
                ->where(
                    'id',
                    $data['bot_id']
                )
                ->exists(),
            422
        );

        DB::transaction(
            function () use (
                $request,
                $data,
                $id
            ) {
                DB::table('sanction_bots')
                    ->where('id', $id)
                    ->update([
                        'bot_id' =>
                            $data['bot_id'],
                        'name' =>
                            $data['name'],
                        'enabled' =>
                            $request->boolean(
                                'enabled'
                            ),
                        'speech_enabled' =>
                            $request->boolean(
                                'speech_enabled'
                            ),
                        'speech_random' =>
                            $data['speech_mode']
                                === 'random',
                        'speech_interval' =>
                            $data[
                                'speech_interval'
                            ],
                        'follow_distance' =>
                            $data[
                                'follow_distance'
                            ],
                        'updated_at' =>
                            time(),
                    ]);

                $this->replaceLines(
                    $id,
                    $data['lines'] ?? ''
                );
            }
        );

        return back()->with(
            'success',
            'Sanction bot profile updated.'
        );
    }

    public function destroy(int $id)
    {
        $this->guard();

        /*
         * Do not delete a profile that is attached to
         * sanction history. Disable it instead.
         */
        $used = DB::table('sanctions')
            ->where(
                'sanction_bot_id',
                $id
            )
            ->exists();

        if ($used) {
            DB::table('sanction_bots')
                ->where('id', $id)
                ->update([
                    'enabled' => 0,
                    'updated_at' => time(),
                ]);

            return back()->with(
                'success',
                'Profile is used by sanction history, so it was disabled instead of deleted.'
            );
        }

        DB::transaction(function () use ($id) {
            DB::table('sanction_bot_lines')
                ->where(
                    'sanction_bot_id',
                    $id
                )
                ->delete();

            DB::table('sanction_bots')
                ->where('id', $id)
                ->delete();
        });

        return back()->with(
            'success',
            'Sanction bot profile deleted.'
        );
    }

    private function replaceLines(
        int $profileId,
        string $text)
    {
        DB::table('sanction_bot_lines')
            ->where(
                'sanction_bot_id',
                $profileId
            )
            ->delete();

        $lines = preg_split(
            '/\r\n|\r|\n/',
            $text
        );

        $rows = [];
        $order = 0;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $rows[] = [
                'sanction_bot_id' =>
                    $profileId,
                'line_text' =>
                    mb_substr(
                        $line,
                        0,
                        500
                    ),
                'sort_order' =>
                    $order++,
                'enabled' =>
                    1,
            ];
        }

        if ($rows) {
            DB::table(
                'sanction_bot_lines'
            )->insert($rows);
        }
    }
}
