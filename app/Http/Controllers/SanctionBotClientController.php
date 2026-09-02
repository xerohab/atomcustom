<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SanctionBotClientController extends Controller
{
    public function index()
    {
        $bots = DB::table('sanction_bots as sb')
            ->leftJoin(
                'bots as b',
                'b.id',
                '=',
                'sb.bot_id'
            )
            ->where(
                'sb.enabled',
                1
            )
            ->orderBy('sb.name')
            ->select([
                'sb.id',
                'sb.name',
                'sb.bot_id',
                'sb.speech_enabled',
                'sb.speech_interval',
                'b.name as bot_name',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'bots' => $bots,
        ])->header(
            'Cache-Control',
            'no-store, no-cache, must-revalidate'
        );
    }
}
