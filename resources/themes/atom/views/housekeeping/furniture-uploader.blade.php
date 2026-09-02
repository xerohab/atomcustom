<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Furniture Uploader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { gold: { 300:'#fde047',400:'#facc15',500:'#eab308',600:'#ca8a04' }}}}}}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed; background-size:cover; }
        .drop-active { border-color:#facc15 !important; background:rgba(120,53,15,.35) !important; }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased">
<div class="fixed inset-0 bg-black/75 backdrop-blur-[2px] z-0"></div>
<div class="relative z-10">
    <header class="w-full bg-zinc-950/90 border-b border-amber-500/30 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
                <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-8 w-auto">
                <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
            </a>
            <a href="{{ url('/housekeeping') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold">&larr; Back to Dashboard</a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/90 border border-emerald-500/40 text-emerald-300 font-bold text-xs">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-4 rounded-2xl bg-red-950/90 border border-red-500/40 text-red-200 text-xs space-y-1">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="bg-zinc-900/95 border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-amber-500/20 pb-5 mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Furniture Uploader</h1>
                    <p class="text-xs text-zinc-400 mt-1">Upload multiple SWF or Nitro files, choose an existing catalogue page or create a new page under another section, then let the worker convert and install everything.</p>
                    <a
                        href="{{ route('housekeeping.swf-nitro-converter') }}"
                        class="inline-flex mt-3 px-3 py-2 rounded-xl bg-black/50 hover:bg-black/70 border border-amber-500/30 text-amber-300 text-xs font-bold"
                    >
                        Open SWF → Nitro Converter
                    </a>
                </div>
                <div class="text-[11px] text-zinc-400 bg-black/50 border border-amber-500/20 rounded-2xl px-4 py-3">
                    <div>Nitro destination: <span class="font-mono text-amber-300">/gamedata/furniture</span></div>
                    <div>Icons: <span class="font-mono text-amber-300">/gamedata/icons</span></div>
                </div>
            </div>

            <form method="POST" action="{{ route('housekeeping.furniture-uploader.store') }}" enctype="multipart/form-data" class="space-y-6" id="uploaderForm">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div id="furniDrop" class="rounded-3xl border-2 border-dashed border-amber-500/30 bg-black/40 p-8 text-center cursor-pointer transition">
                        <input id="furniture" name="furniture[]" type="file" accept=".swf,.nitro" multiple class="hidden" required>
                        <div class="text-3xl mb-2">⬆</div>
                        <div class="font-extrabold text-amber-300">Drop SWF / Nitro files here</div>
                        <div class="text-xs text-zinc-500 mt-1">or click to browse • up to 100 files per batch</div>
                    </div>
                    <div id="iconDrop" class="rounded-3xl border-2 border-dashed border-zinc-600 bg-black/30 p-8 text-center cursor-pointer transition">
                        <input id="icons" name="icons[]" type="file" accept="image/png" multiple class="hidden">
                        <div class="text-3xl mb-2">▣</div>
                        <div class="font-extrabold text-zinc-300">Optional custom PNG icons</div>
                        <div class="text-xs text-zinc-500 mt-1">Match icon filename to furniture filename</div>
                    </div>
                </div>

                <div id="filePreview" class="hidden rounded-2xl bg-black/40 border border-amber-500/20 overflow-hidden">
                    <div class="px-4 py-3 border-b border-amber-500/20 text-xs font-extrabold text-amber-300 uppercase">Batch files</div>
                    <div id="fileRows" class="divide-y divide-zinc-800"></div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    <section class="rounded-3xl bg-black/40 border border-amber-500/20 p-5 space-y-4">
                        <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Catalogue destination</h2>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <label class="rounded-xl border border-amber-500/20 p-3 flex items-center gap-2"><input type="radio" name="target_mode" value="existing" checked> Existing page</label>
                            <label class="rounded-xl border border-amber-500/20 p-3 flex items-center gap-2"><input type="radio" name="target_mode" value="new"> Create new page</label>
                        </div>

                        <div id="existingPageBox" class="space-y-1.5">
                            <label class="text-xs font-bold text-amber-300">Add all furniture to</label>
                            <input
                                id="existingPageSearch"
                                type="text"
                                list="existingPageOptions"
                                autocomplete="off"
                                placeholder="Type a page name or ID..."
                                class="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-amber-500/30 text-white text-xs"
                            >
                            <input type="hidden" name="existing_page_id" id="existingPageId">
                            <datalist id="existingPageOptions">
                                @foreach($pages as $page)
                                    <option
                                        value="{{ $page->caption }} ({{ $page->id }})"
                                        data-id="{{ $page->id }}"
                                    ></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div id="newPageBox" class="hidden space-y-4">
                            <div>
                                <label class="text-xs font-bold text-amber-300">New page name</label>
                                <input type="text" name="new_page_caption" placeholder="e.g. NFT 2027" class="w-full mt-1 px-4 py-3 rounded-xl bg-zinc-950 border border-amber-500/30 text-white text-xs">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-amber-300">Put new page inside</label>
                                <input
                                    id="parentPageSearch"
                                    type="text"
                                    list="parentPageOptions"
                                    autocomplete="off"
                                    placeholder="Type a parent page name or ID..."
                                    class="w-full mt-1 px-4 py-3 rounded-xl bg-zinc-950 border border-amber-500/30 text-white text-xs"
                                >
                                <input type="hidden" name="new_page_parent_id" id="parentPageId">
                                <datalist id="parentPageOptions">
                                    @foreach($pages as $page)
                                        <option
                                            value="{{ $page->caption }} ({{ $page->id }})"
                                            data-id="{{ $page->id }}"
                                        ></option>
                                    @endforeach
                                </datalist>
                                <p class="text-[10px] text-zinc-500 mt-1">Example: create “NFT 2027” and choose “New Furniture” here.</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-amber-300">Minimum rank</label>
                                <input type="number" name="new_page_min_rank" value="1" min="1" max="10" class="w-full mt-1 px-4 py-3 rounded-xl bg-zinc-950 border border-amber-500/30 text-white text-xs">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl bg-black/40 border border-amber-500/20 p-5 space-y-4">
                        <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Catalogue pricing</h2>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div><label class="font-bold text-amber-300">Credits</label><input type="number" name="price_credits" value="0" min="0" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"></div>
                            <div><label class="font-bold text-amber-300">Points</label><input type="number" name="price_points" value="0" min="0" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"></div>
                            <div><label class="font-bold text-amber-300">Points type</label><input type="number" name="points_type" value="0" min="0" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"></div>
                            <div><label class="font-bold text-amber-300">Amount</label><input type="number" name="amount" value="1" min="1" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"></div>
                        </div>
                    </section>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    <section class="rounded-3xl bg-black/40 border border-amber-500/20 p-5 space-y-4">
                        <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Furniture defaults</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                            <div><label class="font-bold text-amber-300">Default type</label><select name="kind" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"><option value="s">Floor</option><option value="i">Wall</option></select><p class="text-[10px] text-zinc-500 mt-1">Used as the starting type for each uploaded file.</p></div>
                            <div><label class="font-bold text-amber-300">Width</label><input name="width" type="number" value="1" min="0" max="20" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"></div>
                            <div><label class="font-bold text-amber-300">Length</label><input name="length" type="number" value="1" min="0" max="20" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"></div>
                            <div><label class="font-bold text-amber-300">Stack height</label><input name="stack_height" type="number" step="0.01" value="1" min="0" max="50" class="w-full mt-1 px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/30"></div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                            <label class="flex gap-2 items-center"><input type="checkbox" name="allow_stack" value="1" checked> Stackable</label>
                            <label class="flex gap-2 items-center"><input type="checkbox" name="allow_sit" value="1"> Sit</label>
                            <label class="flex gap-2 items-center"><input type="checkbox" name="allow_lay" value="1"> Lay</label>
                            <label class="flex gap-2 items-center"><input type="checkbox" name="allow_walk" value="1"> Walk</label>
                        </div>
                    </section>

                    <section class="rounded-3xl bg-black/40 border border-amber-500/20 p-5 space-y-4">
                        <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Icon & delivery</h2>
                        <div class="space-y-2 text-xs">
                            <label class="flex gap-2 items-center"><input type="radio" name="icon_mode" value="auto" checked> Auto-generate icon from Nitro bundle</label>
                            <label class="flex gap-2 items-center"><input type="radio" name="icon_mode" value="upload"> Use matching uploaded PNG icon</label>
                            <label class="flex gap-2 items-center"><input type="radio" name="icon_mode" value="none"> No icon</label>
                        </div>
                        <label class="flex gap-2 items-center text-xs"><input type="checkbox" name="give_inventory" value="1"> Give one copy of each installed item to my inventory</label>
                    </section>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-extrabold text-amber-100 text-xs uppercase tracking-wider shadow-lg">Queue Furniture Batch</button>
                </div>
            </form>
        </div>

        @if($selectedBatch)
        <section class="bg-zinc-900/95 border border-amber-500/30 rounded-3xl p-6 shadow-xl" data-batch="{{ $selectedBatch->id }}" id="batchPanel">
            <div class="flex items-center justify-between mb-4">
                <div><h2 class="font-extrabold text-amber-400">Batch #{{ $selectedBatch->id }}</h2><p class="text-xs text-zinc-500">Target: {{ $selectedBatch->target_page_caption }} ({{ $selectedBatch->target_page_id }})</p></div>
                <span id="batchRefreshState" class="text-[10px] text-zinc-500">Checking status automatically…</span>
            </div>
            <div id="jobRows" class="space-y-2">
                @foreach($jobs as $job)
                    <div class="job-row rounded-2xl bg-black/40 border border-zinc-800 p-3 text-xs" data-job="{{ $job->id }}">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div><strong class="text-white">{{ $job->original_name }}</strong><span class="text-zinc-500 ml-2 font-mono">{{ $job->class_name }}</span></div>
                            <div class="status font-extrabold {{ $job->status === 'done' ? 'text-emerald-400' : ($job->status === 'error' ? 'text-red-400' : 'text-amber-300') }}">{{ strtoupper($job->status) }}</div>
                        </div>
                        <div class="message text-zinc-400 mt-1">{{ $job->message }}</div>
                        <div class="ids text-[10px] text-zinc-500 mt-1 {{ $job->assigned_id ? '' : 'hidden' }}">
                            @if($job->assigned_id)
                                Furniture ID {{ $job->assigned_id }} @if($job->catalog_item_id) • Catalogue ID {{ $job->catalog_item_id }} @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        @if($recentBatches->count())
        <section class="bg-zinc-900/90 border border-amber-500/20 rounded-3xl p-5">
            <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider mb-3">Recent batches</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($recentBatches as $batch)
                    <a href="{{ route('housekeeping.furniture-uploader', ['batch'=>$batch->id]) }}" class="px-3 py-2 rounded-xl bg-black/50 border border-zinc-700 hover:border-amber-500/50 text-xs">#{{ $batch->id }} · {{ $batch->target_page_caption }} · {{ $batch->file_count }} files</a>
                @endforeach
            </div>
        </section>
        @endif
    </main>
</div>
<script>
const furniInput = document.getElementById('furniture');
const iconInput = document.getElementById('icons');
const furniDrop = document.getElementById('furniDrop');
const iconDrop = document.getElementById('iconDrop');
function wireDrop(box,input){
    box.addEventListener('click',()=>input.click());
    ['dragenter','dragover'].forEach(ev=>box.addEventListener(ev,e=>{e.preventDefault();box.classList.add('drop-active');}));
    ['dragleave','drop'].forEach(ev=>box.addEventListener(ev,e=>{e.preventDefault();box.classList.remove('drop-active');}));
    box.addEventListener('drop',e=>{input.files=e.dataTransfer.files; if(input===furniInput) renderFiles();});
}
wireDrop(furniDrop,furniInput); wireDrop(iconDrop,iconInput); furniInput.addEventListener('change',renderFiles);
function prettyFurnitureName(className){
    let parts=String(className)
        .replace(/([a-z0-9])([A-Z])/g,'$1_$2')
        .split(/[_\-\s]+/)
        .filter(Boolean);

    const isReleaseCode=value =>
        /^(?:(?:c|r|h|ltd|sc|bc|hc|v|s)\d{1,4}|\d{2,4})$/i.test(String(value));

    if(parts.length >= 3 && isReleaseCode(parts[1])){
        parts=parts.slice(2);
    }else{
        while(parts.length > 1 && isReleaseCode(parts[0])){
            parts.shift();
        }
    }

    if(!parts.length){
        parts=String(className).split(/[_\-\s]+/).filter(Boolean);
    }

    const knownCompounds={
        gallerywall:'gallery wall',
        lightfence:'light fence',
        darkfence:'dark fence',
        lightgate:'light gate',
        darkgate:'dark gate',
        lightearth:'light earth',
        darkearth:'dark earth',
        freshgrass:'fresh grass',
        giantpumpkin:'giant pumpkin',
        grapebarrel:'grape barrel',
        farmtools:'farm tools',
        carrotsack:'carrot sack',
        molehill:'mole hill',
        plantingplot:'planting plot',
        smallplantingplot:'small planting plot',
        saplingsprout:'sapling sprout',
        bigcorn:'big corn',
        biggrape:'big grape',
        bigtomato:'big tomato',
        basketcorn:'basket corn',
        basketgrapes:'basket grapes',
        basketpumpkins:'basket pumpkins',
        baskettomatoes:'basket tomatoes',
        snowchair:'snow chair',
        heartlamp:'heart lamp',
        witchbook:'witch book',
        towernook:'tower nook'
    };

    const splitWord=word=>{
        const camel=String(word).replace(/([a-z])([A-Z])/g,'$1 $2');
        const mapped=knownCompounds[camel.toLowerCase()] || camel;
        return mapped.split(/\s+/).filter(Boolean);
    };

    return parts
        .flatMap(splitWord)
        .map(word => word.charAt(0).toUpperCase()+word.slice(1).toLowerCase())
        .join(' ');
}

function bindPageSearch(inputId, listId, hiddenId){
    const input=document.getElementById(inputId);
    const list=document.getElementById(listId);
    const hidden=document.getElementById(hiddenId);

    if(!input || !list || !hidden) return;

    const sync=()=>{
        hidden.value='';

        const typed=input.value.trim();
        const direct=typed.match(/\((\d+)\)\s*$/);

        if(direct){
            hidden.value=direct[1];
            return;
        }

        if(/^\d+$/.test(typed)){
            hidden.value=typed;
            return;
        }

        for(const option of list.options){
            if(option.value.toLowerCase()===typed.toLowerCase()){
                hidden.value=option.dataset.id || '';
                return;
            }
        }
    };

    input.addEventListener('input',sync);
    input.addEventListener('change',sync);

    if(list.options.length){
        input.value=list.options[0].value;
        hidden.value=list.options[0].dataset.id || '';
    }
}

bindPageSearch('existingPageSearch','existingPageOptions','existingPageId');
bindPageSearch('parentPageSearch','parentPageOptions','parentPageId');

function renderFiles(){
    const wrap=document.getElementById('filePreview'), rows=document.getElementById('fileRows'); rows.innerHTML='';
    [...furniInput.files].forEach((f,i)=>{
        const base=f.name.replace(/\.(swf|nitro)$/i,'').replace(/[^A-Za-z0-9_]/g,'_');
        const display=prettyFurnitureName(base);
        const defaultKind=(document.querySelector('select[name="kind"]')||{}).value||'s';
        rows.insertAdjacentHTML('beforeend',`<div class="p-3 grid grid-cols-1 md:grid-cols-4 gap-2 text-xs"><div class="font-bold text-zinc-200 flex items-center">${escapeHtml(f.name)}</div><input name="class_names[${i}]" value="${escapeHtml(base)}" class="px-3 py-2 rounded-lg bg-zinc-950 border border-zinc-700 font-mono" placeholder="Class name"><input name="display_names[${i}]" value="${escapeHtml(display)}" class="px-3 py-2 rounded-lg bg-zinc-950 border border-zinc-700" placeholder="Display name"><select name="kinds[${i}]" class="px-3 py-2 rounded-lg bg-zinc-950 border border-zinc-700"><option value="s" ${defaultKind==='s'?'selected':''}>Floor item</option><option value="i" ${defaultKind==='i'?'selected':''}>Wall item</option></select></div>`);
    });
    wrap.classList.toggle('hidden',furniInput.files.length===0);
}
function escapeHtml(v){return String(v).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
document.querySelectorAll('input[name="target_mode"]').forEach(r=>r.addEventListener('change',()=>{
    const isNew=document.querySelector('input[name="target_mode"]:checked').value==='new';
    document.getElementById('existingPageBox').classList.toggle('hidden',isNew);
    document.getElementById('newPageBox').classList.toggle('hidden',!isNew);
}));
const panel=document.getElementById('batchPanel');
if(panel){
    const batch=panel.dataset.batch;
    const refreshState=document.getElementById('batchRefreshState');
    const statusUrlTemplate=@json(route('housekeeping.furniture-uploader.batch', ['batch' => '__BATCH__']));
    const statusUrl=statusUrlTemplate.replace('__BATCH__',encodeURIComponent(batch));
    let timer=null;

    const setRefreshState=(text,kind='normal')=>{
        if(!refreshState) return;
        refreshState.textContent=text;
        refreshState.className='text-[10px] '+(
            kind==='ok' ? 'text-emerald-400' :
            kind==='error' ? 'text-red-400' :
            'text-zinc-500'
        );
    };

    const pollBatchStatus=async()=>{
        try{
            const r=await fetch(statusUrl,{
                method:'GET',
                credentials:'same-origin',
                cache:'no-store',
                headers:{
                    'Accept':'application/json',
                    'X-Requested-With':'XMLHttpRequest'
                }
            });

            if(!r.ok){
                setRefreshState(`Status check failed (${r.status})`,'error');
                return;
            }

            const data=await r.json();
            const jobs=Array.isArray(data.jobs) ? data.jobs : [];

            jobs.forEach(j=>{
                const row=document.querySelector(`[data-job="${j.id}"]`);
                if(!row) return;

                const s=row.querySelector('.status');
                if(s){
                    const status=String(j.status || '').toLowerCase();
                    s.textContent=status.toUpperCase();
                    s.className='status font-extrabold '+(
                        status==='done' ? 'text-emerald-400' :
                        status==='error' ? 'text-red-400' :
                        status==='running' ? 'text-sky-400' :
                        'text-amber-300'
                    );
                }

                const message=row.querySelector('.message');
                if(message) message.textContent=j.message || '';

                const ids=row.querySelector('.ids');
                if(ids){
                    const parts=[];
                    if(j.assigned_id) parts.push(`Furniture ID ${j.assigned_id}`);
                    if(j.catalog_item_id) parts.push(`Catalogue ID ${j.catalog_item_id}`);
                    ids.textContent=parts.join(' • ');
                    ids.classList.toggle('hidden',parts.length===0);
                }
            });

            const complete=jobs.length > 0 && jobs.every(j =>
                ['done','error'].includes(String(j.status || '').toLowerCase())
            );

            if(complete){
                setRefreshState('Batch complete · status is up to date','ok');
                if(timer){
                    clearInterval(timer);
                    timer=null;
                }
            }else{
                setRefreshState('Status refreshes automatically every 3 seconds');
            }
        }catch(e){
            setRefreshState('Could not refresh status automatically','error');
            console.error('Furniture uploader status refresh failed:',e);
        }
    };

    pollBatchStatus();
    timer=setInterval(pollBatchStatus,3000);
}
</script>
</body>
</html>
