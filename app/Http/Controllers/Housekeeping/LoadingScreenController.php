<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Models\Miscellaneous\WebsiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoadingScreenController extends Controller
{
    private const SETTING_KEY = 'client_loading_screen_config';

    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'background_url' => 'https://loungehotel.org/assets/images/loading.png',
            'logo_url' => 'https://loungehotel.org/assets/images/lounge.png',
            'tips_enabled' => true,
            'rotation_seconds' => 5,
            'panel_opacity' => 0.72,
            'headings' => [
                'preparing' => 'Preparing Lounge',
                'loading' => 'Loading Hotel',
                'almost' => 'Almost there...',
                'complete' => 'Welcome to Lounge!',
            ],
            'progress_colors' => [
                'start' => '#8a4d00',
                'middle' => '#eba915',
                'end' => '#ffd85e',
            ],
            'tips' => [
                'Meet friends, build rooms and make Lounge your own.',
                'Check the catalogue for new furniture and seasonal releases.',
                'Use the Navigator to discover rooms, games and events.',
                'Never share your password or account details with anybody.',
                'Keep an eye on the Lounge website for hotel news and updates.',
            ],
        ];
    }

    public function index(): View|RedirectResponse
    {
        if (! function_exists('canAccessHkPermission') || ! canAccessHkPermission('manage_settings')) {
            return redirect('/housekeeping');
        }

        return view('housekeeping.loading-screen', [
            'config' => $this->readConfig(),
            'endpointUrl' => url('/api/client/loading-screen'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! function_exists('canAccessHkPermission') || ! canAccessHkPermission('manage_settings')) {
            return redirect('/housekeeping');
        }

        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'background_url' => ['required', 'string', 'max:2048'],
            'logo_url' => ['required', 'string', 'max:2048'],
            'tips_enabled' => ['nullable', 'boolean'],
            'rotation_seconds' => ['required', 'integer', 'min:2', 'max:30'],
            'panel_opacity' => ['required', 'numeric', 'min:0.35', 'max:0.95'],
            'heading_preparing' => ['required', 'string', 'max:80'],
            'heading_loading' => ['required', 'string', 'max:80'],
            'heading_almost' => ['required', 'string', 'max:80'],
            'heading_complete' => ['required', 'string', 'max:80'],
            'progress_start' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'progress_middle' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'progress_end' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tips_text' => ['nullable', 'string', 'max:20000'],
        ]);

        $tips = collect(preg_split('/\R/u', (string) ($validated['tips_text'] ?? '')))
            ->map(fn ($tip) => trim((string) $tip))
            ->filter()
            ->map(fn ($tip) => mb_substr($tip, 0, 240))
            ->take(100)
            ->values()
            ->all();

        $config = [
            'enabled' => $request->boolean('enabled'),
            'background_url' => trim($validated['background_url']),
            'logo_url' => trim($validated['logo_url']),
            'tips_enabled' => $request->boolean('tips_enabled'),
            'rotation_seconds' => (int) $validated['rotation_seconds'],
            'panel_opacity' => round((float) $validated['panel_opacity'], 2),
            'headings' => [
                'preparing' => trim($validated['heading_preparing']),
                'loading' => trim($validated['heading_loading']),
                'almost' => trim($validated['heading_almost']),
                'complete' => trim($validated['heading_complete']),
            ],
            'progress_colors' => [
                'start' => strtoupper($validated['progress_start']),
                'middle' => strtoupper($validated['progress_middle']),
                'end' => strtoupper($validated['progress_end']),
            ],
            'tips' => $tips,
        ];

        WebsiteSetting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'value' => json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'comment' => 'Housekeeping-managed Nitro loading screen configuration',
            ]
        );

        return redirect('/housekeeping/loading-screen')->with('success', 'Loading screen settings saved. New clients will use the changes immediately.');
    }

    public function publicConfig(): JsonResponse
    {
        return response()
            ->json($this->readConfig())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function readConfig(): array
    {
        $defaults = self::defaults();

        $raw = WebsiteSetting::query()->where('key', self::SETTING_KEY)->value('value');
        if (! is_string($raw) || $raw === '') {
            return $defaults;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $defaults;
        }

        $merged = array_replace_recursive($defaults, $decoded);

        if (! isset($merged['tips']) || ! is_array($merged['tips'])) {
            $merged['tips'] = $defaults['tips'];
        }

        $merged['tips'] = array_values(array_filter(array_map(
            fn ($tip) => is_string($tip) ? trim($tip) : '',
            $merged['tips']
        )));

        return $merged;
    }
}
