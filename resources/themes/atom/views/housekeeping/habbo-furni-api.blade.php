<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HabboFurni API Sync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Plus Jakarta Sans',sans-serif;background:#09090b;color:#f4f4f5}
        .panel{background:rgba(24,24,27,.94);border:1px solid rgba(245,158,11,.25);box-shadow:0 20px 60px rgba(0,0,0,.25)}
        .input{width:100%;border-radius:1rem;background:#09090b;border:1px solid #3f3f46;padding:.75rem .9rem;color:#f4f4f5;font-size:.8rem;outline:none}
        .input:focus{border-color:#f59e0b;box-shadow:0 0 0 2px rgba(245,158,11,.12)}
        .btn{border-radius:1rem;padding:.75rem 1rem;font-size:.78rem;font-weight:800;transition:.15s}
        .btn-primary{background:#f59e0b;color:#18181b}.btn-primary:hover{background:#fbbf24}.btn-primary:disabled{opacity:.45;cursor:not-allowed}
        .btn-dark{background:#18181b;border:1px solid #52525b;color:#e4e4e7}.btn-dark:hover{border-color:#f59e0b;color:#fbbf24}
        .badge{font-size:.64rem;font-weight:800;padding:.3rem .55rem;border-radius:999px;border:1px solid}
        .scrollbar::-webkit-scrollbar{width:8px;height:8px}.scrollbar::-webkit-scrollbar-thumb{background:#3f3f46;border-radius:99px}
    </style>
</head>
<body class="min-h-screen">
<div class="max-w-[1500px] mx-auto px-5 py-7 space-y-6">
    <header class="panel rounded-3xl p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="text-[11px] uppercase tracking-[.25em] text-amber-500 font-extrabold">Furniture & Catalog</div>
            <h1 class="text-2xl font-extrabold mt-1">HabboFurni API Sync</h1>
            <p class="text-sm text-zinc-400 mt-2 max-w-3xl">Manually scan HabboFurni for furniture your hotel does not currently have. Use the result filters to hide clothing/wearables, or send selected items into a separate hidden API Clothing catalogue. Nothing is changed during a scan.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('housekeeping.dashboard') }}" class="btn btn-dark">Housekeeping</a>
            <a href="{{ route('housekeeping.furniture-uploader') }}" class="btn btn-dark">Normal Uploader</a>
        </div>
    </header>

    @if($apiWarning)
        <div class="rounded-2xl border border-red-500/30 bg-red-950/30 px-4 py-3 text-sm text-red-200">{{ $apiWarning }}</div>
    @endif

    <section class="panel rounded-3xl p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-amber-500/20 pb-4">
            <div>
                <h2 class="font-extrabold text-amber-400">1. Scan options</h2>
                <p class="text-xs text-zinc-500 mt-1">Scan one page for a quick check, or scan the whole selected Habbo hotel.</p>
            </div>
            <span id="apiState" class="badge border-zinc-600 text-zinc-400">Ready</span>
        </div>

        <div class="grid md:grid-cols-2 xl:grid-cols-5 gap-3">
            <div><label class="text-[11px] text-zinc-400 font-bold">Hotel</label><select id="hotelId" class="input mt-1">
                @php($hotels=[1=>'.COM',2=>'.COM.BR',3=>'.ES',4=>'.FI',5=>'.FR',6=>'.DE',7=>'.IT',8=>'.NL',9=>'.COM.TR',10=>'Sandbox'])
                @foreach($hotels as $id=>$label)<option value="{{ $id }}" @selected($defaultHotel===$id)>{{ $id }} — {{ $label }}</option>@endforeach
            </select></div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Search</label><input id="search" class="input mt-1" placeholder="name / classname / description"></div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Category</label><select id="category" class="input mt-1"><option value="">All categories</option>@foreach($categories as $v)<option value="{{ $v }}">{{ $v }}</option>@endforeach</select></div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Type</label><select id="type" class="input mt-1"><option value="">All types</option>@foreach($types as $v)<option value="{{ $v }}">{{ $v }}</option>@endforeach<option value="room">room</option><option value="wall">wall</option></select></div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Revision</label><input id="revision" class="input mt-1" placeholder="optional"></div>
        </div>

        <div class="grid md:grid-cols-3 gap-3 border-t border-zinc-800 pt-4">
            <div>
                <label class="text-[11px] text-zinc-400 font-bold">Result view</label>
                <select id="resultView" class="input mt-1">
                    <option value="furniture">Furniture only (hide clothing matches)</option>
                    <option value="clothing">Clothing / wearable matches only</option>
                    <option value="all">Show all missing results</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-[11px] text-zinc-400 font-bold">Clothing keywords</label>
                <input id="clothingKeywords" class="input mt-1" value="clothing,clothes,wearable,garment,wardrobe,fashion,shirt,tshirt,hoodie,jacket,jumper,sweater,trousers,pants,shorts,skirt,dress,shoes,boots,trainers,hat,cap,helmet,mask,hair,hairstyle,beard,glasses,eyewear" placeholder="comma-separated words">
                <p class="text-[10px] text-zinc-600 mt-1">Matched against classname, name, description, type, category and furni line. Add any word you notice HabboFurni uses.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button id="scanPage" class="btn btn-dark">Scan first 100</button>
            <button id="scanAll" class="btn btn-primary">Scan whole hotel</button>
            <button id="cancelScan" class="btn btn-dark hidden">Stop scan</button>
            <button id="clearResults" class="btn btn-dark">Clear results</button>
        </div>
    </section>

    <section class="grid sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-9 gap-3">
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">API total</div><div id="statTotal" class="text-2xl font-extrabold mt-1">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">Scanned</div><div id="statScanned" class="text-2xl font-extrabold mt-1">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">Already installed</div><div id="statInstalled" class="text-2xl font-extrabold mt-1 text-emerald-400">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">Already queued</div><div id="statQueued" class="text-2xl font-extrabold mt-1 text-sky-400">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">Clothing ignored</div><div id="statClothing" class="text-2xl font-extrabold mt-1 text-zinc-400">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">Missing / Importable</div><div id="statMissing" class="text-2xl font-extrabold mt-1 text-amber-400">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">Missing Source Asset</div><div id="statMissingSource" class="text-2xl font-extrabold mt-1 text-orange-300">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">FurnitureData Conflicts</div><div id="statFurnidataConflict" class="text-2xl font-extrabold mt-1 text-red-400">0</div></div>
        <div class="panel rounded-2xl p-4"><div class="text-[10px] uppercase text-zinc-500 font-bold">Bundle Conflicts</div><div id="statBundleConflict" class="text-2xl font-extrabold mt-1 text-red-400">0</div></div>
    </section>

    <section class="panel rounded-3xl p-6 space-y-5">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4 border-b border-amber-500/20 pb-4">
            <div>
                <h2 class="font-extrabold text-amber-400">2. Missing furniture &amp; local conflicts</h2>
                <p class="text-xs text-zinc-500 mt-1">Missing API records are shown here. The local Result view/keyword filter can hide clothing even when HabboFurni does not label it consistently. Existing furniture is never replaced.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button id="selectAll" class="btn btn-dark">Select all missing</button>
                <button id="selectNone" class="btn btn-dark">Select none</button>
            </div>
        </div>

        <div class="overflow-auto scrollbar max-h-[650px] rounded-2xl border border-zinc-800">
            <table class="min-w-full text-xs">
                <thead class="sticky top-0 bg-zinc-950 z-10 text-zinc-400 uppercase text-[10px] tracking-wider">
                <tr><th class="p-3 text-left w-10">Pick</th><th class="p-3 text-left">Furniture</th><th class="p-3 text-left">Classname</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Folder</th><th class="p-3 text-left">Revision</th><th class="p-3 text-left">Size</th></tr>
                </thead>
                <tbody id="resultsBody" class="divide-y divide-zinc-800"></tbody>
            </table>
            <div id="emptyState" class="p-10 text-center text-zinc-500 text-sm">Run a scan to find missing furniture.</div>
        </div>
    </section>

    <section class="panel rounded-3xl p-6 space-y-5">
        <div class="border-b border-amber-500/20 pb-4">
            <h2 class="font-extrabold text-amber-400">3. Import selected</h2>
            <p class="text-xs text-zinc-500 mt-1">Choose where the selected results should go. Normal imports use <b>API Furniture</b>. Clothing can be sent to a separate <b>API Clothing</b> root which is created with <code>visible = 0</code>, so it stays hidden in the catalogue. Existing page contents are never replaced.</p>
        </div>
        <div class="grid md:grid-cols-6 gap-3">
            <div>
                <label class="text-[11px] text-zinc-400 font-bold">Catalogue destination</label>
                <select id="catalogTarget" class="input mt-1">
                    <option value="furniture">API Furniture (normal)</option>
                    <option value="clothing">API Clothing (hidden)</option>
                </select>
            </div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Credits</label><input id="credits" type="number" min="0" value="3" class="input mt-1"></div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Points</label><input id="points" type="number" min="0" value="0" class="input mt-1"></div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Points type</label><input id="pointsType" type="number" min="0" value="0" class="input mt-1"></div>
            <div><label class="text-[11px] text-zinc-400 font-bold">Amount</label><input id="amount" type="number" min="1" value="1" class="input mt-1"></div>
            <label class="flex items-center gap-3 rounded-2xl bg-black/40 border border-zinc-800 px-4 py-3 mt-5"><input id="giveInventory" type="checkbox"><span class="text-xs font-bold">Give to my inventory</span></label>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button id="importSelected" class="btn btn-primary">Import selected</button>
            <span id="selectedCount" class="text-xs text-zinc-400">0 selected</span>
        </div>
        <div id="importProgress" class="hidden rounded-2xl border border-zinc-800 bg-black/40 p-4 text-xs space-y-2">
            <div class="flex justify-between"><span id="importText">Preparing...</span><span id="importPercent">0%</span></div>
            <div class="h-2 rounded-full bg-zinc-800 overflow-hidden"><div id="importBar" class="h-full bg-amber-500" style="width:0%"></div></div>
            <div id="importErrors" class="text-red-300 space-y-1"></div>
        </div>
    </section>
    <section class="panel rounded-3xl p-6 space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-amber-500/20 pb-4">
            <div>
                <h2 class="font-extrabold text-amber-400">4. API import status & history</h2>
                <p class="text-xs text-zinc-500 mt-1">This reads the furniture worker status directly. <b>Installed</b> means the worker finished successfully; <b>Failed</b> shows the worker error. It refreshes automatically every 3 seconds.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select id="historyFilter" class="input !w-auto">
                    <option value="all">All statuses</option>
                    <option value="done">Installed only</option>
                    <option value="error">Failed only</option>
                    <option value="active">Waiting / processing</option>
                </select>
                <button id="refreshHistory" class="btn btn-dark">Refresh now</button>
                <span id="historyRefreshState" class="badge border-zinc-600 text-zinc-400">Loading...</span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="rounded-2xl bg-black/40 border border-zinc-800 p-3"><div class="text-[10px] uppercase text-zinc-500 font-bold">Recent jobs</div><div id="histTotal" class="text-xl font-extrabold mt-1">0</div></div>
            <div class="rounded-2xl bg-black/40 border border-zinc-800 p-3"><div class="text-[10px] uppercase text-zinc-500 font-bold">Installed</div><div id="histDone" class="text-xl font-extrabold mt-1 text-emerald-400">0</div></div>
            <div class="rounded-2xl bg-black/40 border border-zinc-800 p-3"><div class="text-[10px] uppercase text-zinc-500 font-bold">Failed</div><div id="histError" class="text-xl font-extrabold mt-1 text-red-300">0</div></div>
            <div class="rounded-2xl bg-black/40 border border-zinc-800 p-3"><div class="text-[10px] uppercase text-zinc-500 font-bold">Waiting</div><div id="histWaiting" class="text-xl font-extrabold mt-1 text-sky-300">0</div></div>
            <div class="rounded-2xl bg-black/40 border border-zinc-800 p-3"><div class="text-[10px] uppercase text-zinc-500 font-bold">Processing</div><div id="histRunning" class="text-xl font-extrabold mt-1 text-amber-300">0</div></div>
        </div>

        <div class="overflow-auto scrollbar max-h-[650px] rounded-2xl border border-zinc-800">
            <table class="min-w-full text-xs">
                <thead class="sticky top-0 bg-zinc-950 z-10 text-zinc-400 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Furniture</th>
                        <th class="p-3 text-left">Classname</th>
                        <th class="p-3 text-left">Batch</th>
                        <th class="p-3 text-left">Furniture ID</th>
                        <th class="p-3 text-left">Catalogue ID</th>
                        <th class="p-3 text-left">Message</th>
                        <th class="p-3 text-left">Finished</th>
                    </tr>
                </thead>
                <tbody id="historyBody" class="divide-y divide-zinc-800"></tbody>
            </table>
            <div id="historyEmpty" class="p-8 text-center text-zinc-500 text-sm hidden">No API import jobs match this filter yet.</div>
        </div>
    </section>

</div>

<script>
const scanUrl=@json(route('housekeeping.habbo-furni-api.scan'));
const importUrl=@json(route('housekeeping.habbo-furni-api.import'));
const statusUrl=@json(route('housekeeping.habbo-furni-api.status'));
const csrf=document.querySelector('meta[name="csrf-token"]').content;
let missing=new Map();
let selected=new Set();
let cancelled=false;
let stats={
    total:0,
    scanned:0,
    installed:0,
    queued:0,
    clothing:0,
    missing:0,
    missingSource:0,
    furnidataConflict:0,
    bundleConflict:0
};
const $=id=>document.getElementById(id);

function params(page){
    const p=new URLSearchParams({page,hotel_id:$('hotelId').value});
    ['search','category','type','revision'].forEach(k=>{
        const v=$(k).value.trim();
        if(v)p.set(k,v)
    });
    return p
}

function state(text,kind='normal'){
    $('apiState').textContent=text;
    $('apiState').className='badge '+(
        kind==='ok'
            ?'border-emerald-500/40 text-emerald-400'
            :kind==='bad'
                ?'border-red-500/40 text-red-300'
                :'border-zinc-600 text-zinc-400'
    )
}

function reset(){
    missing.clear();
    selected.clear();
    stats={
        total:0,
        scanned:0,
        installed:0,
        queued:0,
        clothing:0,
        missing:0,
        missingSource:0,
        furnidataConflict:0,
        bundleConflict:0
    };
    render();
    updateStats()
}

function updateStats(){
    $('statTotal').textContent=stats.total.toLocaleString();
    $('statScanned').textContent=stats.scanned.toLocaleString();
    $('statInstalled').textContent=stats.installed.toLocaleString();
    $('statQueued').textContent=stats.queued.toLocaleString();
    $('statClothing').textContent=stats.clothing.toLocaleString();
    $('statMissing').textContent=stats.missing.toLocaleString();
    $('statMissingSource').textContent=stats.missingSource.toLocaleString();
    $('statFurnidataConflict').textContent=stats.furnidataConflict.toLocaleString();
    $('statBundleConflict').textContent=stats.bundleConflict.toLocaleString();
    $('selectedCount').textContent=selected.size.toLocaleString()+' selected'
}

function folderFor(i){
    let s=(i.furni_line||i.category||'Other').replace(/[_-]+/g,' ').trim();
    return s?s.replace(/\b\w/g,c=>c.toUpperCase()):'Other'
}

function esc(v){
    return String(v??'').replace(/[&<>'"]/g,c=>({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        "'":'&#39;',
        '"':'&quot;'
    }[c]))
}

function clothingWords(){
    return $('clothingKeywords').value
        .split(',')
        .map(v=>v.trim().toLowerCase())
        .filter(Boolean)
}

function looksClothing(i){
    if(i.is_clothing)return true;
    const hay=[
        i.classname,
        i.name,
        i.description,
        i.type,
        i.category,
        i.furni_line
    ].join(' ').toLowerCase();

    return clothingWords().some(w=>hay.includes(w))
}

function visibleItems(){
    const mode=$('resultView').value;

    return [...missing.values()].filter(i=>
        mode==='all' ||
        (mode==='clothing' ? looksClothing(i) : !looksClothing(i))
    )
}

function statusMeta(i){
    if(i.status==='missing_source'){
        return {
            label:'Missing source asset',
            cls:'border-orange-500/40 text-orange-300',
            selectable:false
        }
    }

    if(i.status==='furnidata_duplicate'){
        return {
            label:'FurnitureData conflict',
            cls:'border-red-500/40 text-red-300',
            selectable:false
        }
    }

    if(i.status==='bundle_only'){
        return {
            label:'Bundle conflict',
            cls:'border-orange-500/40 text-orange-300',
            selectable:false
        }
    }

    return {
        label:'Missing / Importable',
        cls:'border-amber-500/40 text-amber-300',
        selectable:true
    }
}

function render(){
    const body=$('resultsBody');
    body.innerHTML='';

    const items=visibleItems();

    $('emptyState').classList.toggle('hidden',items.length>0);

    const frag=document.createDocumentFragment();

    for(const i of items){
        const meta=statusMeta(i);
        const tr=document.createElement('tr');

        tr.className=
            'hover:bg-white/[.025] '+
            (meta.selectable?'':'bg-red-950/10');

        const checkbox=meta.selectable
            ? `<input type="checkbox" data-class="${esc(i.classname)}" ${selected.has(i.classname)?'checked':''}>`
            : `<span class="text-zinc-700">—</span>`;

        const problem=i.local_problem
            ? `<div class="mt-1 text-[10px] text-red-300 max-w-sm">${esc(i.local_problem)}</div>`
            : '';

        tr.innerHTML=`
            <td class="p-3">${checkbox}</td>
            <td class="p-3">
                <div class="flex items-center gap-3">
                    ${i.icon_url?`<img src="${esc(i.icon_url)}" class="w-10 h-10 object-contain rounded-lg bg-black/40" loading="lazy">`:''}
                    <div>
                        <div class="font-bold text-zinc-100">${esc(i.name)}</div>
                        <div class="text-[10px] text-zinc-500 max-w-xs truncate">${esc(i.description)}</div>
                        ${problem}
                    </div>
                </div>
            </td>
            <td class="p-3 font-mono text-amber-200">${esc(i.classname)}</td>
            <td class="p-3">
                <span class="badge ${meta.cls}">${esc(meta.label)}</span>
                <div class="mt-1 text-[10px] text-zinc-500">${esc(i.type||'?')}</div>
            </td>
            <td class="p-3">${esc(folderFor(i))}</td>
            <td class="p-3">${esc(i.revision??'')}</td>
            <td class="p-3">${esc(i.xdim??'?')} × ${esc(i.ydim??'?')} × ${esc(i.zdim??'?')}</td>
        `;

        frag.appendChild(tr)
    }

    body.appendChild(frag);

    body.querySelectorAll('input[data-class]').forEach(cb=>
        cb.addEventListener('change',()=>{
            cb.checked
                ? selected.add(cb.dataset.class)
                : selected.delete(cb.dataset.class);

            updateStats()
        })
    );

    updateStats()
}

async function getPage(page){
    const r=await fetch(
        scanUrl+'?'+params(page).toString(),
        {headers:{Accept:'application/json'}}
    );

    const j=await r.json();

    if(!r.ok)throw new Error(j.message||'Scan failed');

    return j
}

function consume(j){
    const items=j.items||[];

    stats.total=Number(j.meta?.total||stats.total);
    stats.clothing+=Number(j.clothing_detected||0);
    stats.scanned+=items.length;

    for(const i of items){
        if(i.status==='installed'){
            stats.installed++;
            continue
        }

        if(i.status==='queued'){
            stats.queued++;
            continue
        }

        if(i.status==='missing_source'){
            stats.missingSource++;

            if(!missing.has(i.classname)){
                missing.set(i.classname,i)
            }

            continue
        }

        if(i.status==='furnidata_duplicate'){
            stats.furnidataConflict++;

            if(!missing.has(i.classname)){
                missing.set(i.classname,i)
            }

            continue
        }

        if(i.status==='bundle_only'){
            stats.bundleConflict++;

            if(!missing.has(i.classname)){
                missing.set(i.classname,i)
            }

            continue
        }

        /*
         * Only the explicit "missing" state is importable.
         * Unknown future states are deliberately NOT selectable.
         */
        if(i.status==='missing'){
            if(!missing.has(i.classname)){
                missing.set(i.classname,i);
                stats.missing++
            }

            continue
        }

        i.local_problem=i.local_problem||('Unknown scanner state: '+String(i.status||'unknown'));
        i.status='scanner_conflict';

        if(!missing.has(i.classname)){
            missing.set(i.classname,i)
        }
    }

    updateStats()
}

async function scan(all){
    reset();
    cancelled=false;

    $('cancelScan').classList.remove('hidden');
    $('scanAll').disabled=$('scanPage').disabled=true;

    try{
        state('Scanning...');

        let page=1,last=1;

        do{
            if(cancelled)break;

            const j=await getPage(page);
            consume(j);

            last=Number(j.meta?.last_page||1);

            state(`Scanning ${page} / ${last}`);

            if(!all)break;

            page++
        }while(page<=last);

        render();

        if(cancelled){
            state('Stopped');
        }else{
            const conflicts=stats.furnidataConflict+stats.bundleConflict;

            state(
                `Scan complete — ${stats.missing.toLocaleString()} importable, `+
                `${stats.missingSource.toLocaleString()} missing source asset${stats.missingSource===1?'':'s'}, `+
                `${conflicts.toLocaleString()} local conflicts `+
                `(${stats.clothing.toLocaleString()} clothing matches detected)`,
                'ok'
            )
        }
    }catch(e){
        state(e.message,'bad');
        alert(e.message)
    }finally{
        $('cancelScan').classList.add('hidden');
        $('scanAll').disabled=$('scanPage').disabled=false
    }
}

$('scanPage').onclick=()=>scan(false);
$('scanAll').onclick=()=>scan(true);
$('cancelScan').onclick=()=>cancelled=true;

$('clearResults').onclick=()=>{
    cancelled=true;
    reset();
    state('Ready')
};

$('selectAll').onclick=()=>{
    for(const i of visibleItems()){
        if(i.status==='missing'){
            selected.add(i.classname)
        }
    }

    render()
};

$('selectNone').onclick=()=>{
    selected.clear();
    render()
};

$('resultView').onchange=render;
$('clothingKeywords').oninput=render;

let historyJobs=[];
function historyStatusMeta(status){
    if(status==='done')return {label:'Installed',cls:'border-emerald-500/40 text-emerald-400'};
    if(status==='error')return {label:'Failed',cls:'border-red-500/40 text-red-300'};
    if(status==='running')return {label:'Processing',cls:'border-amber-500/40 text-amber-300'};
    if(status==='waiting')return {label:'Waiting',cls:'border-sky-500/40 text-sky-300'};
    return {label:status||'Unknown',cls:'border-zinc-600 text-zinc-400'};
}
function visibleHistoryJobs(){
    const f=$('historyFilter').value;
    if(f==='all')return historyJobs;
    if(f==='active')return historyJobs.filter(j=>j.status==='waiting'||j.status==='running');
    return historyJobs.filter(j=>j.status===f);
}
function renderHistory(){
    const body=$('historyBody');body.innerHTML='';
    const rows=visibleHistoryJobs();$('historyEmpty').classList.toggle('hidden',rows.length>0);
    const frag=document.createDocumentFragment();
    for(const j of rows){
        const meta=historyStatusMeta(j.status);
        const tr=document.createElement('tr');tr.className='hover:bg-white/[.025] align-top';
        const message=j.message||((j.status==='done')?'Installed successfully':'');
        tr.innerHTML=`<td class="p-3"><span class="badge ${meta.cls}">${esc(meta.label)}</span></td><td class="p-3"><div class="font-bold text-zinc-100">${esc(j.display_name||j.class_name)}</div><div class="text-[10px] text-zinc-600">Job #${esc(j.id)}</div></td><td class="p-3 font-mono text-amber-200">${esc(j.class_name)}</td><td class="p-3"><div>#${esc(j.batch_id)}</div><div class="text-[10px] text-zinc-600">${esc(j.batch_caption||'')}</div></td><td class="p-3 font-mono">${j.assigned_id?esc(j.assigned_id):'—'}</td><td class="p-3 font-mono">${j.catalog_item_id?esc(j.catalog_item_id):'—'}</td><td class="p-3 max-w-md"><div class="whitespace-pre-wrap break-words ${j.status==='error'?'text-red-300':'text-zinc-400'}">${esc(message)||'—'}</div></td><td class="p-3 whitespace-nowrap text-zinc-500">${esc(j.finished_at||j.started_at||j.created_at||'—')}</td>`;
        frag.appendChild(tr);
    }
    body.appendChild(frag);
}
async function loadHistory(silent=false){
    try{
        if(!silent)$('historyRefreshState').textContent='Refreshing...';
        const r=await fetch(statusUrl+'?limit=150',{headers:{Accept:'application/json'},cache:'no-store'});
        const j=await r.json();if(!r.ok)throw new Error(j.message||'Could not load import history');
        historyJobs=j.jobs||[];const c=j.counts||{};
        $('histTotal').textContent=Number(c.total||0).toLocaleString();
        $('histDone').textContent=Number(c.done||0).toLocaleString();
        $('histError').textContent=Number(c.error||0).toLocaleString();
        $('histWaiting').textContent=Number(c.waiting||0).toLocaleString();
        $('histRunning').textContent=Number(c.running||0).toLocaleString();
        $('historyRefreshState').textContent='Live';$('historyRefreshState').className='badge border-emerald-500/40 text-emerald-400';
        renderHistory();
    }catch(e){
        $('historyRefreshState').textContent='Status error';$('historyRefreshState').className='badge border-red-500/40 text-red-300';
        if(!silent)alert(e.message);
    }
}
$('historyFilter').onchange=renderHistory;
$('refreshHistory').onclick=()=>loadHistory(false);
loadHistory(true);
setInterval(()=>loadHistory(true),3000);

async function importChunk(classes){const r=await fetch(importUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({hotel_id:Number($('hotelId').value),class_names:classes,price_credits:Number($('credits').value),price_points:Number($('points').value),points_type:Number($('pointsType').value),amount:Number($('amount').value),give_inventory:$('giveInventory').checked?1:0,catalog_target:$('catalogTarget').value})});const j=await r.json();if(!r.ok)throw new Error(j.message||'Import request failed');return j}
$('importSelected').onclick=async()=>{const classes=[...selected];if(!classes.length)return alert('Select at least one missing furniture item first.');const destination=$('catalogTarget').value==='clothing'?'hidden API Clothing':'API Furniture';if(!confirm(`Queue ${classes.length} item${classes.length===1?'':'s'} from HabboFurni into ${destination}?`))return;const btn=$('importSelected');btn.disabled=true;$('importProgress').classList.remove('hidden');$('importErrors').innerHTML='';let done=0,queued=0;try{for(let n=0;n<classes.length;n+=10){const chunk=classes.slice(n,n+10);$('importText').textContent=`Downloading and queueing ${Math.min(n+10,classes.length)} of ${classes.length}...`;try{const j=await importChunk(chunk);for(const c of (j.queued||[])){queued++;missing.delete(c);selected.delete(c)}for(const c of (j.skipped||[])){missing.delete(c);selected.delete(c)}for(const er of (j.errors||[]))$('importErrors').insertAdjacentHTML('beforeend',`<div>${esc(er.classname)}: ${esc(er.message)}</div>`)}catch(e){$('importErrors').insertAdjacentHTML('beforeend',`<div>Chunk failed: ${esc(e.message)}</div>`)}done+=chunk.length;const pct=Math.round(done/classes.length*100);$('importBar').style.width=pct+'%';$('importPercent').textContent=pct+'%';render()}$('importText').textContent=`Finished. ${queued} item${queued===1?'':'s'} queued for the furniture worker.`;state('Import queued','ok');loadHistory(true)}finally{btn.disabled=false}}
</script>
</body>
</html>
