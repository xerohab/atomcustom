@props(['icon'])

<div class="flex min-w-0 items-center gap-2 rounded-lg bg-white/70 px-2 py-1.5 text-[10px] sm:bg-transparent sm:px-0 sm:py-0 sm:text-sm dark:bg-gray-800/70 sm:dark:bg-transparent">
    <div class="h-[20px] w-[20px] sm:h-[25px] sm:w-[25px] shrink-0 rounded-full {{ $icon }} outline-offset-[2px] sm:outline-offset-[3px]"></div>
    <div class="min-w-0 leading-tight dark:text-gray-400">
        <span class="block truncate font-semibold text-gray-900 dark:text-white">{{ $currency }}</span>
        <span class="block truncate">{{ $slot }}</span>
    </div>
</div>
