@props(['user'])

<div class="relative flex h-24 w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition hover:shadow-md">

    <!-- Left Avatar Box: Head-Only Render -->
    <div class="relative w-16 h-full bg-gray-900/60 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0">
        <a href="{{ route('profile.show', $user->username) }}" class="flex items-center justify-center">
            <img style="image-rendering: pixelated; background: transparent !important;"
                 class="h-[65px] w-auto max-w-none transition duration-200 hover:scale-105 drop-shadow-[0_4px_4px_rgba(0,0,0,0.6)]"
                 src="{{ setting('avatar_imager') }}{{ $user->look }}&direction=2&head_direction=2&headonly=1"
                 alt="{{ $user->username }}">
        </a>
    </div>

    <!-- Right Details Section -->
    <div class="flex flex-1 flex-col justify-center px-3 py-2 overflow-hidden min-w-0">
        <div class="flex items-center gap-1.5 min-w-0">
            <!-- Online Status Indicator Dot -->
            <span class="inline-block h-2 w-2 min-w-[8px] rounded-full {{ $user->online ? 'bg-green-500' : 'bg-gray-400' }}"
                  title="{{ $user->online ? 'Online' : 'Offline' }}"></span>

            <!-- Username -->
            <a href="{{ route('profile.show', $user->username) }}" class="text-sm font-bold text-gray-900 dark:text-white hover:underline truncate">
                {{ $user->username }}
            </a>
        </div>

        <!-- Rank Title -->
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 truncate mt-0.5">
            {{ $user->permission?->rank_name ?? 'Staff' }}
        </span>

        <!-- Motto -->
        <p class="text-[11px] italic text-gray-600 dark:text-gray-300 truncate mt-0.5">
            "{{ Str::limit($user->motto, 18) }}"
        </p>
    </div>
</div>