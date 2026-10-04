<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace - Hotel News</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            300: '#fde047',
                            400: '#facc15',
                            500: '#eab308',
                            600: '#ca8a04'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f0507 url('https://solacehotel.pw/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed; background-size: cover; }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased flex flex-col justify-between">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[2px] z-0"></div>

    <div class="relative z-10">
        @include('components.navigation.navigation-menu')

        <main class="max-w-6xl mx-auto px-4 py-8 space-y-6">
            @php
                $activeArticle = isset($article) ? $article : \Illuminate\Support\Facades\DB::table('website_articles')->whereNull('deleted_at')->orderBy('id', 'desc')->first();
                $recentArticles = \Illuminate\Support\Facades\DB::table('website_articles')->whereNull('deleted_at')->orderBy('id', 'desc')->take(8)->get();
                $author = $activeArticle ? \App\Models\User::find($activeArticle->user_id) : null;
                $comments = $activeArticle ? \Illuminate\Support\Facades\DB::table('website_article_comments')->where('article_id', $activeArticle->id)->orderBy('id', 'desc')->get() : [];
            @endphp

            @if($activeArticle)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- RECENT ARTICLES SIDEBAR -->
                <div class="space-y-3">
                    <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider px-2">Latest News</h2>
                    <div class="space-y-2">
                        @foreach($recentArticles as $item)
                            <a href="{{ url('/community/article/' . $item->slug) }}"
                               class="block p-3.5 rounded-2xl bg-zinc-900/90 border {{ $activeArticle->id == $item->id ? 'border-amber-400 bg-amber-500/10' : 'border-amber-500/20 hover:border-amber-500/40' }} transition">
                                <h3 class="text-xs font-bold text-white truncate">{{ $item->title }}</h3>
                                <p class="text-[10px] text-zinc-400 mt-1 truncate">{{ $item->short_story }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- MAIN ARTICLE CONTENT -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4">
                        <div class="h-48 w-full rounded-2xl bg-cover bg-center border border-amber-500/20"
                             style="background-image: url('{{ $activeArticle->image }}');"></div>

                        <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                            <div>
                                <h1 class="text-xl font-extrabold text-amber-400">{{ $activeArticle->title }}</h1>
                                <p class="text-[11px] text-zinc-400">Published: {{ date('d M Y, H:i', strtotime($activeArticle->created_at ?? 'now')) }}</p>
                            </div>
                            @if($author)
                            <div class="flex items-center gap-2">
                                <img src="{{ setting('avatar_imager') }}{{ $author->look }}&direction=2&headonly=1" alt="Author" class="h-8 w-8 rounded-full border border-amber-500/30 bg-black">
                                <span class="text-xs font-bold text-amber-300">{{ $author->username }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="text-xs text-zinc-200 leading-relaxed space-y-3">
                            {!! nl2br(e($activeArticle->full_story)) !!}
                        </div>
                    </div>

                    <!-- COMMENTS SECTION -->
                    @if($activeArticle->can_comment)
                    <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4">
                        <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">Comments</h2>

                        @auth
                        <form action="{{ url('/community/article/' . $activeArticle->slug . '/comment') }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="3" required placeholder="Write a comment..." class="w-full p-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 font-bold text-amber-100 text-xs uppercase tracking-wider">Post Comment</button>
                            </div>
                        </form>
                        @endauth

                        <div class="space-y-3 pt-2">
                            @forelse($comments as $c)
                                @php $cAuthor = \App\Models\User::find($c->user_id); @endphp
                                <div class="p-3 rounded-2xl bg-black/50 border border-amber-500/20 flex gap-3 items-start">
                                    <img src="{{ setting('avatar_imager') }}{{ $cAuthor->look ?? '' }}&direction=2&headonly=1" class="h-8 w-8 rounded-full border border-amber-500/30 bg-black">
                                    <div class="space-y-1">
                                        <span class="text-xs font-bold text-amber-300">{{ $cAuthor->username ?? 'Unknown' }}</span>
                                        <p class="text-xs text-zinc-300">{{ $c->comment }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-xs text-zinc-500 italic py-4">No comments posted yet.</p>
                            @endforelse
                        </div>
                    </div>
                    @endif
                </div>

            </div>
            @else
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-8 text-center text-zinc-400">
                No news articles found.
            </div>
            @endif
        </main>
    </div>
</body>
</html>