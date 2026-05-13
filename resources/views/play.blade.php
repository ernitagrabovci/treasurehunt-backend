@extends('layouts.app')

@section('title', 'Eksploro - Gjueti Thesari Kosova')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
<script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
<style>
    #panorama { width: 100%; height: 70vh; border-radius: 1rem; overflow: hidden; position: relative; background: #000; }
    .pnlm-container { border-radius: 1rem !important; }
    .pnlm-loading, .pnlm-load-button, .pnlm-load-box, .pnlm-lbox, .pnlm-lbar, .pnlm-lmsg { display: none !important; }

    /* Hotspot: white circle with label */
    .pnlm-hotspot-base.nav-hs {
        background: white !important;
        border-radius: 50% !important;
        width: 46px !important;
        height: 46px !important;
        box-shadow: 0 2px 12px rgba(0,0,0,0.4) !important;
        cursor: pointer !important;
    }
    .pnlm-hotspot-base.nav-hs::after {
        content: 'Pamja Tjeter';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-top: 8px;
        color: white;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        text-shadow: 0 0 8px rgba(0,0,0,0.9), 0 2px 4px rgba(0,0,0,0.8);
        pointer-events: none;
    }

    /* Pixel overlay */
    .px {
        position: absolute;
        inset: 0;
        z-index: 99999;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100% 100%;
        image-rendering: pixelated;
        image-rendering: crisp-edges;
        pointer-events: none;
        border-radius: 1rem;
        transition: background-size 0.65s cubic-bezier(0.4,0,0.2,1);
    }

    /* Treasure hotspot: completely invisible, player must discover it */
    .pnlm-hotspot-base.treasure-hs {
        width: 60px !important;
        height: 60px !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        cursor: pointer !important;
    }

    /* Treasure modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        z-index: 100000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .modal-overlay.open {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-box {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }
    .modal-overlay.open .modal-box {
        transform: scale(1);
    }
    .modal-question {
        font-size: 1.1rem;
        font-weight: 700;
        color: #021044;
        margin-bottom: 1.5rem;
        line-height: 1.4;
    }
    .modal-answers {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .modal-answer {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        background: white;
        cursor: pointer;
        font-size: 0.95rem;
        text-align: left;
        transition: all 0.15s ease;
    }
    .modal-answer:hover {
        border-color: #021044;
        background: #f8f9ff;
    }
    .modal-answer.correct {
        border-color: #16a34a;
        background: #f0fdf4;
        color: #16a34a;
    }
    .modal-answer.wrong {
        border-color: #dc2626;
        background: #fef2f2;
        color: #dc2626;
    }
    .modal-answer:disabled {
        cursor: default;
    }
    .modal-answer.selected {
        border-color: #021044 !important;
        background: #eef2ff !important;
        box-shadow: 0 0 0 3px rgba(2, 16, 68, 0.15);
    }
    .modal-result {
        margin-top: 1rem;
        padding: 0.75rem;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 1.1rem;
        text-align: center;
        display: none;
    }
    .modal-result.show {
        display: block;
    }
    .modal-result.success {
        background: #f0fdf4;
        color: #16a34a;
    }
    .modal-result.fail {
        background: #fef2f2;
        color: #dc2626;
    }
    .modal-close {
        margin-top: 1rem;
        padding: 0.5rem 1.5rem;
        background: #021044;
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        font-weight: 600;
        display: inline-block;
    }
    .modal-close:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .scene-selector { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .scene-btn { padding: 0.5rem 1rem; border-radius: 0.5rem; border: 2px solid #e5e7eb; background: white; cursor: pointer; font-size: 0.875rem; }
    .scene-btn.active { border-color: #021044; background: #021044; color: white; }
    .toast { position: fixed; bottom: 2rem; right: 2rem; background: #021044; color: white; padding: 1rem 2rem; border-radius: 0.75rem; z-index: 2000; transform: translateY(200%); transition: transform 0.3s ease; }
    .toast.show { transform: translateY(0); }
</style>
@endpush

@section('content')
<div class="flex flex-col lg:flex-row gap-6">
    <div class="flex-1">
        <div class="card p-2">
            <div id="panorama"></div>
        </div>
        <p id="stitle" class="text-center text-lg font-semibold text-brand mt-3"></p>
    </div>
    <div class="w-full lg:w-72 space-y-4">
        <div class="card">
            <h3 class="font-bold text-brand mb-3">Dhomat <span id="lvl-badge" class="text-sm font-normal text-gray-500"></span></h3>
            <div id="slist" class="scene-selector"></div>
        </div>
        <div class="card">
            <h3 class="font-bold text-brand mb-2">Statistikat</h3>
            <p class="text-sm">Niveli: <span id="slvl">-</span></p>
            <p class="text-sm">Thesare: <span id="stres">0</span></p>
            <p class="text-sm">Koha: <span id="stimer">0s</span></p>
        </div>
    </div>
</div>
<div id="toast" class="toast"></div>

<!-- Treasure question modal -->
<div id="modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-question" id="mq"></div>
        <div class="modal-answers" id="ma"></div>
        <div class="modal-result" id="mr"></div>
        <button class="modal-close" id="mc" onclick="handleContinue()">Continue</button>
    </div>
</div>

<script id="sdata" type="application/json">@json([
    'scenes' => $scenes,
])</script>

<script>
let V = null, scenes = [], curId = null, busy = false, currentLevel = 1;
let timerSeconds = 0, timerInterval = null;

function msg(m) { const t=document.getElementById('toast'); t.textContent=m; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500); }

function fmtTime(s) {
    const m = Math.floor(s / 60);
    const sec = s % 60;
    return m > 0 ? m + 'm ' + sec + 's' : sec + 's';
}

function startTimer() {
    if (timerInterval) return;
    timerInterval = setInterval(() => {
        timerSeconds++;
        document.getElementById('stimer').textContent = fmtTime(timerSeconds);
    }, 1000);
}

function loadData() {
    try {
        const d = JSON.parse(document.getElementById('sdata').textContent);
        scenes = d.scenes || [];
    } catch(e) {}
}

function sceneImg(sc) {
    return '/api/scene-image/' + sc.id;
}

async function init() {
    loadData();
    try {
        const r = await apiFetch('/user/level');
        if (r.success) {
            currentLevel = r.data.current_level || 1;
        }
    } catch(e) {}
    const levelScene = scenes.filter(s => s.level === currentLevel)[0];
    if (levelScene) curId = levelScene.id;
    renderList();
    if (curId) showScene(curId);
    stats();
    startTimer();
}

async function stats() {
    try {
        const r = await apiFetch('/user/level');
        if (r.success) {
            document.getElementById('slvl').textContent = 'Lv.' + (r.data.current_level || 1) + ' - ' + r.data.title.sq;
            document.getElementById('stres').textContent = r.data.total_treasures;
            document.getElementById('lvl-badge').textContent = '(Niveli ' + (r.data.current_level || 1) + ')';
        }
    } catch(e) {}
}

function renderList() {
    const levelScenes = scenes.filter(s => s.level === currentLevel);
    document.getElementById('slist').innerHTML = levelScenes.map(s =>
        `<button class="scene-btn ${s.id===curId?'active':''}" onclick="go(${s.id})">${s.title.sq}</button>`
    ).join('');
}

async function reloadScenes() {
    try {
        const r = await apiFetch('/game/scenes');
        if (r.success && r.data) {
            scenes = r.data;
            // Stay on current scene if it still exists, else show first scene of current level
            if (curId && scenes.find(s => s.id === curId)) {
                showScene(curId);
            } else {
                const levelScene = scenes.find(s => s.level === currentLevel);
                if (levelScene) go(levelScene.id);
            }
        }
    } catch(e) {}
}

function showScene(id, cb) {
    curId = id;
    const sc = scenes.find(s=>s.id===id);
    if(!sc) return;
    document.getElementById('stitle').textContent = sc.title.sq;
    renderList();

    const navHs = sc.hotspots.filter(h=>h.type==='nav').map(h => ({
        pitch: h.pitch, yaw: h.yaw,
        type: 'custom', cssClass: 'nav-hs',
        clickHandlerFunc: function() { nav(h); }
    }));

    const tresHs = sc.hotspots.filter(h=>h.type==='treasure').map(h => ({
        pitch: h.pitch, yaw: h.yaw,
        type: 'custom', cssClass: 'treasure-hs',
        clickHandlerFunc: function() { openTreasure(h); }
    }));

    const hs = [...navHs, ...tresHs];

    if(V) V.destroy();
    V = pannellum.viewer('panorama', {
        type: 'equirectangular',
        panorama: sceneImg(sc),
        autoLoad: true,
        autoRotate: -2,
        compass: true,
        hotSpots: hs,
    });

    let loaded = false;
    function onLoad() {
        if (loaded) return; loaded = true;
        try { V.stopAutoRotate?.(); } catch(e) {}
        if (cb) cb();
    }

    V.on('load', onLoad);
    setTimeout(() => { if (!loaded) onLoad(); }, 300);
}

function go(id) {
    apiFetch('/game/scene/'+id).then(r=>{
        if(r.scene) {
            const e=scenes.find(s=>s.id===r.scene.id);
            if(e) Object.assign(e,r.scene); else scenes.push(r.scene);
            showScene(id);
        }
    });
}

async function nav(hs) {
    if(busy) return; busy=true;
    const r = await apiFetch('/game/navigate', { method:'POST', body:JSON.stringify({hotspot_id:hs.id}) });
    if(!r.success||!r.scene) { msg('Dështoi'); busy=false; return; }
    const e = scenes.find(s=>s.id===r.scene.id);
    if(e) Object.assign(e,r.scene); else scenes.push(r.scene);
    try { await pixelate(r.scene.id); } catch(e) { showScene(r.scene.id); }
    busy=false;
}

function pixelate(nid) {
    return new Promise(res => {
        const old = scenes.find(s=>s.id===curId);
        const target = scenes.find(s=>s.id===nid);
        if(!old||!target) { showScene(nid); res(); return; }

        const ov = document.createElement('div');
        ov.style.cssText = 'position:absolute;inset:0;z-index:99999;border-radius:1rem;background-size:cover;background-position:center';
        ov.style.backgroundImage = 'url('+sceneImg(old)+')';
        document.getElementById('panorama').appendChild(ov);

        showScene(nid, () => {
            ov.style.transition = 'opacity 0.4s ease';
            ov.style.opacity = '0';
            setTimeout(() => { ov.remove(); res(); }, 450);
        });
    });
}

// --- Treasure modal ---
let tresHotspot = null;
let selectedAnswer = null;
let treasureFound = false;

function openTreasure(h) {
    if (busy) return;
    const data = h.data;
    if (!data || !data.question) return;
    tresHotspot = h;
    selectedAnswer = null;
    treasureFound = false;

    apiFetch('/treasures/check/'+h.id).then(r => {
        if (r.found) { msg('E keni gjetur tashmë këtë thesar!'); return; }

        document.getElementById('mq').textContent = 'Niveli ' + currentLevel + ' - ' + data.question;
        const ma = document.getElementById('ma');
        ma.innerHTML = data.answers.map((a, i) =>
            `<button class="modal-answer" data-idx="${i}" onclick="selectAnswer(this, ${i})">${a.text}</button>`
        ).join('');
        document.getElementById('mr').className = 'modal-result';
        document.getElementById('mr').textContent = '';
        const mc = document.getElementById('mc');
        mc.textContent = 'Continue';
        mc.disabled = true;
        document.getElementById('modal').classList.add('open');
    });
}

function selectAnswer(btn, idx) {
    if (!tresHotspot || !tresHotspot.data) return;
    const answers = tresHotspot.data.answers;
    if (!answers || !answers[idx]) return;

    document.querySelectorAll('.modal-answer').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedAnswer = idx;
    document.getElementById('mc').disabled = false;
}

function submitAnswer() {
    if (selectedAnswer === null || !tresHotspot || !tresHotspot.data) return;
    const answers = tresHotspot.data.answers;
    if (!answers || !answers[selectedAnswer]) return;

    const btn = document.querySelector(`.modal-answer[data-idx="${selectedAnswer}"]`);
    const isCorrect = answers[selectedAnswer].correct;

    document.querySelectorAll('.modal-answer').forEach(b => {
        b.disabled = true;
        b.classList.remove('selected');
    });
    document.getElementById('mc').disabled = true;

    if (isCorrect) {
        treasureFound = true;
        btn.classList.add('correct');

        apiFetch('/treasures/found', { method:'POST', body:JSON.stringify({hotspot_id:tresHotspot.id, time_spent_seconds: timerSeconds}) }).then(r => {
            const mr = document.getElementById('mr');
            mr.className = 'modal-result show success';
            mr.textContent = 'Thesari u gjet!';
            document.getElementById('mc').textContent = 'Next Level';
            document.getElementById('mc').disabled = false;
            stats();
        });
    } else {
        btn.classList.add('wrong');

        const mr = document.getElementById('mr');
        mr.className = 'modal-result show fail';
        mr.textContent = 'Përgjigje e gabuar! Provo përsëri.';

        setTimeout(() => {
            document.querySelectorAll('.modal-answer').forEach(b => {
                b.disabled = false;
                b.classList.remove('wrong');
            });
            document.getElementById('mr').className = 'modal-result';
            selectedAnswer = null;
        }, 1500);
    }
}

function handleContinue() {
    if (document.getElementById('mc').textContent === 'Next Level') {
        closeModal();
    } else {
        submitAnswer();
    }
}

function closeModal() {
    document.getElementById('modal').classList.remove('open');

    if (tresHotspot && treasureFound) {
        apiFetch('/game/advance-level', { method: 'POST' }).then(r => {
            if (r.success) {
                currentLevel = r.current_level;
                reloadScenes();
                msg('Mirësevini në Nivelin ' + currentLevel + '!');
                stats();
            } else if (r.message === 'Already at max level.') {
                msg('Keni përfunduar të 5 nivelet! Urime!');
            }
        });
    }

    tresHotspot = null;
    selectedAnswer = null;
    treasureFound = false;
}

document.addEventListener('DOMContentLoaded', ()=>{
    if(!getToken()){ window.location.href='/'; return; }
    init();
});
</script>
@endsection
