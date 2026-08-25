<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Publish Article</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { gold: { 300: '#fde047', 400: '#facc15', 500: '#eab308', 600: '#ca8a04' } } } } }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- jQuery & Trumbowyg WYSIWYG Editor CDN -->
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
        .trumbowyg-editor { color: #f4f4f5 !important; padding: 1.25rem !important; min-height: 250px !important; }

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
                <a href="{{ url('/housekeeping/articles') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold transition">&larr; Back to Articles</a>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">Publish New Article</h1>

                <form action="{{ url('/housekeeping/articles/create') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300">Article Title</label>
                        <input type="text" name="title" required placeholder="e.g. Rare Trade Event Coming This Weekend!" class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- HEADLINE BANNER IMAGE UPLOAD -->
                    <div class="p-4 rounded-2xl bg-black/40 border border-amber-500/20 space-y-3">
                        <span class="block text-xs font-extrabold text-amber-400 uppercase tracking-wide">1. Headline Banner Image</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-bold text-zinc-300">Upload Banner File</label>
                                <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-xs text-zinc-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-black hover:file:bg-amber-400">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-bold text-zinc-300">...or Direct Banner URL</label>
                                <input type="text" name="image" placeholder="https://loungehotel.org/assets/images/news/banner.png" class="w-full px-4 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>

                    <!-- INLINE BODY IMAGE UPLOAD -->
                    <div class="p-4 rounded-2xl bg-black/40 border border-amber-500/20 space-y-3">
                        <span class="block text-xs font-extrabold text-amber-400 uppercase tracking-wide">2. Inline Content Image (Optional)</span>
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-zinc-300">Upload Image File for Article Body</label>
                            <input type="file" name="body_image_file" accept="image/*" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-xs text-zinc-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-black hover:file:bg-amber-400">
                            <p class="text-[10px] text-zinc-400 mt-1">If uploaded, this picture will automatically attach inside your article body.</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300">Short Summary</label>
                        <input type="text" name="short_story" required placeholder="A brief teaser displayed on the main news card..." class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- WYSIWYG EDITOR BODY WITH PROMINENT TOOLBAR -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300">Article Body Content (Formatted Text & Images)</label>
                        <textarea id="wysiwyg-editor" name="full_story" required placeholder="Write your full news update..."></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="can_comment" name="can_comment" value="1" checked class="rounded border-amber-500/30 bg-black/50 text-amber-500 focus:ring-amber-500">
                        <label for="can_comment" class="text-xs font-bold text-zinc-300">Allow user comments on this article</label>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <a href="{{ url('/housekeeping/articles') }}" class="px-5 py-2.5 rounded-xl bg-black/60 border border-zinc-700 font-bold text-zinc-300 text-xs uppercase tracking-wider">Cancel</a>
                        <button type="submit" class="px-8 py-2.5 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider transition shadow-lg">Publish Article</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        $('#wysiwyg-editor').trumbowyg({
            btns: [
                ['viewHTML'],
                ['formatting'],
                ['strong', 'em', 'underline', 'del'],
                ['link'],
                ['insertImage'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat']
            ],
            autogrow: true
        });
    </script>
</body>
</html>