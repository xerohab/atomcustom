<x-app-layout>
    @push('title', __('Shop'))

    <!-- TERMS & CONDITIONS BANNER -->
    <div class="col-span-12">
        <x-modals.modal-wrapper>
            <div class="w-full py-2.5 px-5 text-center bg-amber-500 text-zinc-950 font-extrabold rounded-xl shadow flex items-center justify-between text-xs">
                <span>{{ __('Please make sure to read our shop terms before making a purchase.') }}</span>
                <button type="button" class="bg-black/20 hover:bg-black/40 text-zinc-950 px-3 py-1 rounded-lg underline font-black transition" x-on:click="open = true">
                    {{ __('Terms & Conditions') }}
                </button>
            </div>

            <x-modals.regular-modal>
                <x-slot name="title">
                    <h2 class="text-xl font-extrabold text-amber-400">
                        {{ __('Shop Terms & Conditions') }}
                    </h2>
                </x-slot>

                <div class="space-y-3 p-2 text-xs text-zinc-300">
                    <p>{{ __('Here at :hotel Hotel we are accepting donations to keep the hotel up & running and as a thank you, you will in return receive in-game goods.', ['hotel' => setting('hotel_name', 'Lounge')]) }}</p>
                    <p class="font-semibold text-amber-300">{{ __('Our terms') }}</p>
                    <p>{{ __('Once a donation has been made, it is non-refundable under any circumstances. Balance cannot be converted back into cash. By donating, you agree not to initiate a chargeback.') }}</p>
                </div>
            </x-modals.regular-modal>
        </x-modals.modal-wrapper>
    </div>

    <!-- COLUMN 1: CATEGORIES (3-COL) -->
    <div class="col-span-12 md:col-span-3">
        <x-content.content-card icon="catalog-icon" classes="border border-zinc-800 bg-zinc-900/90">
            <x-slot:title>
                {{ __('Categories') }}
            </x-slot:title>

            <div class="space-y-2 mt-3">
                <a href="{{ route('shop.index') }}"
                   class="flex items-center gap-3 py-2 px-3 rounded-lg border text-xs font-bold transition {{ !request()->route('category') ? 'bg-amber-500/20 border-amber-400 text-amber-300' : 'bg-zinc-950/60 border-zinc-800 text-zinc-300 hover:border-zinc-600' }}">
                    <img class="h-6 w-6 object-contain" src="{{ asset('/assets/images/icons/navigation/shop.png') }}" alt="">
                    <span>{{ __('All Packages') }}</span>
                </a>

                @foreach($categories as $category)
                    <a href="{{ route('shop.index', $category->slug) }}"
                       class="flex items-center gap-3 py-2 px-3 rounded-lg border text-xs font-bold transition {{ request()->route('category') === $category->slug ? 'bg-amber-500/20 border-amber-400 text-amber-300' : 'bg-zinc-950/60 border-zinc-800 text-zinc-300 hover:border-zinc-600' }}">
                        @if($category->icon)
                            <img class="h-6 w-6 object-contain" src="{{ $category->icon }}" alt="">
                        @endif
                        <span>{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </x-content.content-card>
    </div>

    <!-- COLUMN 2: NEW SHOP PACKAGES (6-COL) -->
    <div class="col-span-12 md:col-span-6 space-y-3">
        @if(session('success'))
            <div class="p-3 rounded-xl bg-emerald-950 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded-xl bg-red-950 border border-red-500/40 text-red-300 font-bold text-xs">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-3">
            @forelse ($articles as $article)
                <div class="p-4 rounded-2xl bg-zinc-900 border border-amber-500/30 space-y-3 shadow-lg">
                    <div class="flex items-start justify-between border-b border-amber-500/20 pb-3 gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $article->icon_url ?: asset('/assets/images/icons/navigation/shop.png') }}"
                                 class="h-10 w-10 object-contain p-1 rounded-xl bg-black/40 border border-amber-500/20"
                                 onerror="this.src='/assets/images/icons/navigation/shop.png'">
                            <div>
                                <h3 class="font-extrabold text-amber-400 text-sm">{{ $article->name }}</h3>
                                <p class="text-[11px] text-zinc-400 leading-snug">{{ $article->info }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-amber-950 border border-amber-500/40 text-amber-300 font-black text-xs shrink-0">
                            ${{ number_format($article->costs, 2) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        @if($article->credits > 0)
                            <div class="p-2 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center justify-between">
                                <span>Credits</span>
                                <span class="text-amber-400 font-extrabold">+{{ number_format($article->credits) }}</span>
                            </div>
                        @endif
                        @if($article->duckets > 0)
                            <div class="p-2 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center justify-between">
                                <span>Duckets</span>
                                <span class="text-amber-400 font-extrabold">+{{ number_format($article->duckets) }}</span>
                            </div>
                        @endif
                        @if($article->diamonds > 0)
                            <div class="p-2 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center justify-between">
                                <span>Diamonds</span>
                                <span class="text-amber-400 font-extrabold">+{{ number_format($article->diamonds) }}</span>
                            </div>
                        @endif
                        @if($article->give_rank)
                            <div class="p-2 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center justify-between">
                                <span>Rank Reward</span>
                                <span class="text-amber-400 font-extrabold">Rank #{{ $article->give_rank }}</span>
                            </div>
                        @endif
                    </div>

                    @if(!empty($article->badges))
                        <div class="pt-2 border-t border-amber-500/10 flex items-center gap-2">
                            <span class="text-[10px] text-zinc-500 font-bold uppercase">Badges:</span>
                            @foreach(explode(',', $article->badges) as $badge)
                                <span class="px-2 py-0.5 rounded bg-black/60 border border-amber-500/20 text-amber-300 text-[10px] font-mono font-bold">{{ trim($badge) }}</span>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('shop.buy', $article->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Purchase {{ $article->name }} for ${{ $article->costs }}?');" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-extrabold text-amber-100 uppercase tracking-wider text-xs transition shadow-lg">
                            {{ __('Purchase Package') }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center bg-zinc-900 border border-zinc-800 rounded-xl text-zinc-500 italic text-xs">
                    {{ __('No shop packages available in this category.') }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- COLUMN 3: TOP UP & VOUCHER (3-COL) -->
    <div class="col-span-12 md:col-span-3 space-y-3">
        <x-content.content-card icon="currency-icon" classes="border border-zinc-800 bg-zinc-900/90">
            <x-slot:title>
                {{ __('Top up account') }}
            </x-slot:title>

            <x-slot:under-title>
                {{ __('Donate to :hotel', ['hotel' => setting('hotel_name', 'Lounge')]) }}
            </x-slot:under-title>

            <div class="text-xs text-center py-2 px-3 rounded-xl bg-zinc-950 border border-zinc-800 text-white my-2">
                <span class="block text-[10px] uppercase text-zinc-400 font-bold">{{ __('Current Balance') }}</span>
                <span class="text-base font-black text-amber-400">${{ number_format(auth()->user()->website_balance ?? 0, 2) }}</span>
            </div>

            @if(config('paypal.live.client_id') || config('paypal.sandbox.client_id'))
                <form action="{{ route('paypal.process-transaction') }}" method="GET" class="mt-2 space-y-2">
                    @csrf
                    <x-form.input name="amount" type="number" min="1" step="1" value="5" required />
                    <button type="submit" class="w-full py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wider transition">
                        {{ __('Donate via PayPal') }}
                    </button>
                </form>
            @else
                <p class="text-zinc-500 mt-2 text-xs text-center italic">
                    {{ __('PayPal credentials offline.') }}
                </p>
            @endif
        </x-content.content-card>

        <x-content.content-card icon="catalog-icon" classes="border border-zinc-800 bg-zinc-900/90">
            <x-slot:title>
                {{ __('Voucher') }}
            </x-slot:title>

            <x-slot:under-title>
                {{ __('Redeem promotional code') }}
            </x-slot:under-title>

            <form action="{{ route('shop.use-voucher') }}" method="POST" class="mt-2 space-y-2">
                @csrf
                <x-form.input name="code" type="text" placeholder="Voucher Code" required />
                <x-form.secondary-button classes="w-full">
                    {{ __('Use voucher') }}
                </x-form.secondary-button>
            </form>
        </x-content.content-card>
    </div>
</x-app-layout>