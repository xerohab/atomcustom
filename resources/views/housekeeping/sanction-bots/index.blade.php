<x-app-layout>
    <div class="min-h-screen bg-zinc-950 text-zinc-100 py-10">
        <div class="max-w-7xl mx-auto px-4 space-y-8">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-amber-400">
                        Sanction Bot Manager
                    </h1>

                    <p class="text-sm text-zinc-400 mt-1">
                        Configure existing hotel bots to escort sanctioned users.
                    </p>
                </div>

                <a
                    href="{{ url('/housekeeping') }}"
                    class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-sm font-bold"
                >
                    ← Housekeeping
                </a>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-500/30 bg-green-950/40 p-4 text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-2xl border border-red-500/30 bg-red-950/40 p-4 text-red-300">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-zinc-900 border border-amber-500/20 rounded-3xl p-6">
                <h2 class="font-black text-lg mb-5">
                    Create Sanction Bot Profile
                </h2>

                <form
                    method="POST"
                    action="{{ route('housekeeping.sanction-bots.store') }}"
                    class="grid grid-cols-1 lg:grid-cols-2 gap-5"
                >
                    @csrf

                    <div>
                        <label class="text-xs font-bold text-zinc-400">
                            Profile Name
                        </label>

                        <input
                            name="name"
                            required
                            maxlength="100"
                            placeholder="Sanction Officer"
                            class="mt-2 w-full rounded-xl bg-black border-zinc-700"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-bold text-zinc-400">
                            Existing Bot
                        </label>

                        <select
                            name="bot_id"
                            required
                            class="mt-2 w-full rounded-xl bg-black border-zinc-700"
                        >
                            @foreach($bots as $bot)
                                <option value="{{ $bot->id }}">
                                    #{{ $bot->id }} — {{ $bot->name }} ({{ $bot->gender }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-zinc-400">
                            Speech Interval (seconds)
                        </label>

                        <input
                            type="number"
                            name="speech_interval"
                            min="10"
                            max="86400"
                            value="300"
                            required
                            class="mt-2 w-full rounded-xl bg-black border-zinc-700"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-bold text-zinc-400">
                            Follow Distance
                        </label>

                        <input
                            type="number"
                            name="follow_distance"
                            min="1"
                            max="10"
                            value="2"
                            required
                            class="mt-2 w-full rounded-xl bg-black border-zinc-700"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-bold text-zinc-400">
                            Speech Order
                        </label>

                        <select
                            name="speech_mode"
                            class="mt-2 w-full rounded-xl bg-black border-zinc-700"
                        >
                            <option value="random">Random</option>
                            <option value="sequential">Sequential</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-6 pt-7">
                        <label class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="enabled"
                                value="1"
                                checked
                            >
                            Enabled
                        </label>

                        <label class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="speech_enabled"
                                value="1"
                                checked
                            >
                            Bot Speech
                        </label>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="text-xs font-bold text-zinc-400">
                            Speech Lines — one per line
                        </label>

                        <textarea
                            name="lines"
                            rows="8"
                            class="mt-2 w-full rounded-xl bg-black border-zinc-700 font-mono text-sm"
                            placeholder="I'm keeping an eye on you, %username%.
Remember The Habbo Way.
I'll be here until your sanction expires.
Behave yourself, %username%!"
                        ></textarea>

                        <p class="text-xs text-zinc-500 mt-2">
                            Tokens: %username% and %gender%
                        </p>
                    </div>

                    <div class="lg:col-span-2">
                        <button
                            class="px-6 py-3 rounded-xl bg-amber-500 text-black font-black hover:bg-amber-400"
                        >
                            Create Sanction Bot
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-5">
                @forelse($profiles as $profile)

                    @php
                        $lines = DB::table('sanction_bot_lines')
                            ->where('sanction_bot_id', $profile->id)
                            ->orderBy('sort_order')
                            ->pluck('line_text')
                            ->implode("\n");
                    @endphp

                    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
                        <div class="flex flex-wrap justify-between gap-4 mb-5">
                            <div>
                                <h2 class="font-black text-lg">
                                    {{ $profile->name }}
                                </h2>

                                <p class="text-xs text-zinc-500">
                                    Profile #{{ $profile->id }}
                                    · Bot #{{ $profile->bot_id }}
                                    · {{ $profile->bot_name ?? 'Missing Bot' }}
                                </p>
                            </div>

                            <span class="px-3 py-1 rounded-full text-xs font-black {{ $profile->enabled ? 'bg-green-950 text-green-300' : 'bg-red-950 text-red-300' }}">
                                {{ $profile->enabled ? 'ENABLED' : 'DISABLED' }}
                            </span>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('housekeeping.sanction-bots.update', $profile->id) }}"
                            class="grid grid-cols-1 lg:grid-cols-2 gap-4"
                        >
                            @csrf
                            @method('PUT')

                            <input
                                name="name"
                                value="{{ $profile->name }}"
                                required
                                class="rounded-xl bg-black border-zinc-700"
                            >

                            <select
                                name="bot_id"
                                class="rounded-xl bg-black border-zinc-700"
                            >
                                @foreach($bots as $bot)
                                    <option
                                        value="{{ $bot->id }}"
                                        @selected($bot->id == $profile->bot_id)
                                    >
                                        #{{ $bot->id }} — {{ $bot->name }}
                                    </option>
                                @endforeach
                            </select>

                            <input
                                type="number"
                                name="speech_interval"
                                min="10"
                                max="86400"
                                value="{{ $profile->speech_interval }}"
                                class="rounded-xl bg-black border-zinc-700"
                            >

                            <input
                                type="number"
                                name="follow_distance"
                                min="1"
                                max="10"
                                value="{{ $profile->follow_distance }}"
                                class="rounded-xl bg-black border-zinc-700"
                            >

                            <select
                                name="speech_mode"
                                class="rounded-xl bg-black border-zinc-700"
                            >
                                <option
                                    value="random"
                                    @selected($profile->speech_random)
                                >
                                    Random
                                </option>

                                <option
                                    value="sequential"
                                    @selected(!$profile->speech_random)
                                >
                                    Sequential
                                </option>
                            </select>

                            <div class="flex gap-6 items-center">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="enabled"
                                        value="1"
                                        @checked($profile->enabled)
                                    >
                                    Enabled
                                </label>

                                <label>
                                    <input
                                        type="checkbox"
                                        name="speech_enabled"
                                        value="1"
                                        @checked($profile->speech_enabled)
                                    >
                                    Speech
                                </label>
                            </div>

                            <textarea
                                name="lines"
                                rows="7"
                                class="lg:col-span-2 rounded-xl bg-black border-zinc-700 font-mono text-sm"
                            >{{ $lines }}</textarea>

                            <div>
                                <button
                                    class="px-5 py-2 rounded-xl bg-amber-500 text-black font-black"
                                >
                                    Save Profile
                                </button>
                            </div>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('housekeeping.sanction-bots.destroy', $profile->id) }}"
                            class="mt-3"
                            onsubmit="return confirm('Delete or disable this sanction bot profile?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                class="text-xs text-red-400 hover:text-red-300"
                            >
                                Delete / Disable Profile
                            </button>
                        </form>
                    </div>

                @empty

                    <div class="bg-zinc-900 rounded-3xl p-8 text-zinc-400">
                        No sanction bot profiles have been created yet.
                    </div>

                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
