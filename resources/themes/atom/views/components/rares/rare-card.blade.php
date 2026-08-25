@props(['rare'])

<div class="p-3 rounded bg-gray-200 dark:bg-gray-700 flex gap-x-4 items-center overflow-hidden">
    <!-- Icon Container -->
    <div class="w-12 h-12 flex-shrink-0">
        <div class="w-12 h-12 overflow-hidden rounded-full flex items-center justify-center bg-gray-300 dark:bg-gray-800 border border-gray-400/30">
            @php
                $iconUrl = Str::startsWith($rare->furniture_icon, ['http://', 'https://'])
                    ? $rare->furniture_icon
                    : sprintf('%s/%s', setting('furniture_icons_path'), $rare->furniture_icon);
            @endphp
            <img style="image-rendering: pixelated;" class="max-w-[32px] max-h-[32px] object-contain drop-shadow" src="{{ $iconUrl }}" alt="{{ $rare->name }}">
        </div>
    </div>

    <!-- Details Section -->
    <div class="flex flex-col w-full min-w-0">
        <div class="font-bold text-gray-700 dark:text-gray-200 truncate flex items-center gap-x-[5px]">
            @if($rare->item_id)
                <a href="{{ route('values.value', $rare) }}" class="underline truncate">
                    {{ Str::limit($rare->name, 18) }}
                </a>
            @else
                <span class="truncate">{{ Str::limit($rare->name, 20) }}</span>
            @endif

            @if(method_exists($rare, 'isLimitedEdition') && $rare->isLimitedEdition())
                <img class="w-4 h-4 flex-shrink-0" src="{{ asset('/assets/images/icons/ltd.png') }}" alt="LTD">
            @endif
        </div>

        <!-- Credits Price Box -->
        <div class="w-full bg-yellow-400 rounded h-[32px] flex items-center mt-2 overflow-hidden text-xs font-bold text-gray-900 shadow-sm">
            <div class="bg-yellow-500 w-10 h-full flex items-center justify-center flex-shrink-0">
                <img class="w-4 h-4" src="{{ asset('assets/images/icons/currency/credits.png') }}" alt="Credits">
            </div>
            <p class="w-full text-center truncate px-1">
                {{ number_format((int)($rare->credit_value ?? 0)) }} {{ __('credits') }}
            </p>
        </div>

        <!-- Secondary Currency Box (Diamonds / Currency 5) -->
        <div class="w-full bg-sky-500 rounded h-[32px] flex items-center mt-1 overflow-hidden text-xs font-bold text-white shadow-sm">
            <div class="bg-sky-600 w-10 h-full flex items-center justify-center flex-shrink-0">
                <img class="w-4 h-4" src="{{ asset('assets/images/icons/currency/diamonds.png') }}" alt="Diamonds">
            </div>

            <p class="w-full text-center truncate px-1">
                {{ number_format((int)($rare->currency_value ?? 0)) }}
                {{ in_array((string)$rare->currency_type, ['5', 'diamonds'], true) ? __('Diamonds') : (in_array((string)$rare->currency_type, ['0', 'duckets'], true) ? __('Duckets') : __('Other')) }}
            </p>
        </div>
    </div>
</div>