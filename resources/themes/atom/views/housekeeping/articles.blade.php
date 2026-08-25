<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Manage Articles</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { gold: { 300: '#fde047', 400: '#facc15', 500: '#eab308', 600: '#ca8a04' } } } } }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- jQuery & Trumbowyg CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.0/ui/trumbowyg.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.0/trumbowyg.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed; background-size: cover; }

        /* HIGH-CONTRAST RED & GOLD TOOLBAR STYLING */
        .trumbowyg-box { background-color: rgba(0, 0, 0, 0.75) !important; border: 2px solid rgba(234, 179, 8, 0.4) !important; border-radius: 1rem !important; overflow: hidden; }
        .trumbowyg-button-pane {
            background: linear-gradient(90deg, #450a0a 0%, #7f1d1d 50%, #78350f 100%) !important;
            border-bottom: 2px solid rgba(250, 204, 21, 0.5) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .trumbowyg-button-pane button { color: #fde047 !important; font-weight: bold; }
        .trumbowyg-button-pane button:hover { background-color: rgba(250, 204, 21, 0.25) !important; color: #ffffff !important; }
        .trumbowyg-editor { color: #f4f4f5 !important; padding: 1.25rem !important; min-height: 200px !important; }

        .trumbowyg-modal-box { background-color: #18181b !important; border: 2px solid #eab308 !important; color: #fff !important; border-radius: 1rem !important; }
        .trumbowyg-modal-box label input { background-color: #000 !important; color: #fff !important; border: 1px solid rgba(234, 179, 8, 0.4) !important; border-radius: 0.5rem !important; }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased flex flex-col justify-between">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-[2px] z-0"></div>
    <div class="relative z-10">

        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-8 w-auto">
                        <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
                    </a>
                </div>
                <a href="{{ url('/housekeeping') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold transition">&larr; Back to Dashboard</a>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <div class="flex justify-between items-center border-b border-amber-500/20 pb-4">
                    <div>
                        <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide">Manage Published Articles</h1>
                        <p class="text-xs text-zinc-400 mt-0.5">Edit existing news content or remove old articles.</p>
                    </div>

                    @if(canAccessHkPermission('create_article'))
                    <a href="{{ url('/housekeeping/articles/create') }}" class="px-5 py-2 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider transition">+ Create Article</a>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-amber-500/20 text-amber-300 uppercase tracking-wider text-[10px]">
                                <th class="pb-3 px-2">ID</th>
                                <th class="pb-3 px-2">Title</th>
                                <th class="pb-3 px-2">Summary</th>
                                <th class="pb-3 px-2">Comments</th>
                                <th class="pb-3 px-2">Published Date</th>
                                <th class="pb-3 px-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-500/10 text-zinc-300">
                            @forelse($articles as $article)
                                <tr class="hover:bg-black/30 transition">
                                    <td class="py-3 px-2 font-mono text-zinc-500">#{{ $article->id }}</td>
                                    <td class="py-3 px-2 font-bold text-white">{{ $article->title }}</td>
                                    <td class="py-3 px-2 text-zinc-400 max-w-xs truncate">{{ $article->short_story }}</td>
                                    <td class="py-3 px-2 font-bold {{ $article->can_comment ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $article->can_comment ? 'Enabled' : 'Disabled' }}
                                    </td>
                                    <td class="py-3 px-2 text-zinc-500">{{ date('d M Y, H:i', strtotime($article->created_at ?? 'now')) }}</td>
                                    <td class="py-3 px-2 text-right flex justify-end gap-2">
                                        <button type="button" onclick='openEditArticleModal(@json($article))' class="px-3 py-1 rounded-lg bg-black/60 border border-amber-500/30 text-amber-300 font-bold hover:bg-amber-500/20 transition">Edit</button>

                                        @if(canAccessHkPermission('delete_article'))
                                        <form action="{{ url('/housekeeping/articles/' . $article->id) }}" method="POST" onsubmit="return confirm('Delete this article?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-red-950 border border-red-500/40 text-red-300 font-bold hover:bg-red-900 transition">Delete</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-zinc-500 italic">No news articles found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-amber-500/20">
                    {{ $articles->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- EDIT ARTICLE MODAL -->
    <div id="editArticleModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-900 border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 max-w-3xl w-full space-y-4 relative shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                <h3 class="text-base font-extrabold text-amber-400 uppercase tracking-wide">Edit News Article</h3>
                <button type="button" onclick="closeEditArticleModal()" class="text-zinc-400 hover:text-white font-bold">&times;</button>
            </div>

            <form action="{{ url('/housekeeping/articles') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" id="modal_article_id" name="article_id">

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Title</label>
                    <input type="text" id="modal_article_title" name="title" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>

                <!-- BANNER IMAGE FILE UPLOAD -->
                <div class="p-3.5 rounded-2xl bg-black/40 border border-amber-500/20 space-y-2">
                    <span class="block text-xs font-extrabold text-amber-400 uppercase">1. Headline Banner Image</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-300 mb-1">Upload New Banner File</label>
                            <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-xs text-zinc-300 file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-black">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-300 mb-1">...or Banner URL</label>
                            <input type="text" id="modal_article_image" name="image" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                <!-- INLINE CONTENT IMAGE FILE UPLOAD -->
                <div class="p-3.5 rounded-2xl bg-black/40 border border-amber-500/20 space-y-2">
                    <span class="block text-xs font-extrabold text-amber-400 uppercase">2. Inline Body Image (Optional)</span>
                    <div>
                        <label class="block font-bold text-zinc-300 mb-1">Upload File to Append Inside Article Body</label>
                        <input type="file" name="body_image_file" accept="image/*" class="w-full px-3 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-xs text-zinc-300 file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-black">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Short Summary</label>
                    <input type="text" id="modal_article_short" name="short_story" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Full Article Body Content</label>
                    <textarea id="modal_article_full" name="full_story" required></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="modal_article_comments" name="can_comment" value="1" class="rounded border-amber-500/30 bg-black/50 text-amber-500 focus:ring-amber-500">
                    <label for="modal_article_comments" class="text-xs font-bold text-zinc-300">Allow user comments on this article</label>
                </div>

                <div class="pt-3 border-t border-amber-500/20 flex justify-end gap-2">
                    <button type="button" onclick="closeEditArticleModal()" class="px-4 py-2 rounded-xl bg-black/60 border border-zinc-700 text-zinc-300 font-bold">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 font-bold text-amber-100 uppercase tracking-wider">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#modal_article_full').trumbowyg({
                btns: [
                    ['formatting'],
                    ['strong', 'em', 'underline', 'del'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight'],
                    ['unorderedList', 'orderedList'],
                    ['removeformat']
                ],
                autogrow: true
            });
        });

        function openEditArticleModal(article) {
            document.getElementById('modal_article_id').value = article.id;
            document.getElementById('modal_article_title').value = article.title;
            document.getElementById('modal_article_image').value = article.image || '';
            document.getElementById('modal_article_short').value = article.short_story || '';
            document.getElementById('modal_article_comments').checked = article.can_comment == 1;

            $('#modal_article_full').trumbowyg('html', article.full_story || '');
            document.getElementById('editArticleModal').classList.remove('hidden');
        }

        function closeEditArticleModal() {
            document.getElementById('editArticleModal').classList.add('hidden');
        }
    </script>
</body>
</html>