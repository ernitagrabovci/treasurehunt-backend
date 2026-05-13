<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paneli Admin - Gjueti Thesari Kosova</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .bg-brand { background-color: #021044; }
        .text-brand { color: #021044; }
        .bg-gold { background-color: #D8B129; }
        .text-gold { color: #D8B129; }
        .card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

        .scene-btn { padding: 0.4rem 0.8rem; border-radius: 0.5rem; border: 2px solid #e5e7eb; background: white; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: all 0.15s ease; }
        .scene-btn.active { border-color: #021044; background: #021044; color: white; }
        .scene-btn:hover:not(.active) { border-color: #021044; }

        #panorama { width: 100%; height: 55vh; border-radius: 1rem; overflow: hidden; background: #000; position: relative; }
        .pnlm-container { border-radius: 1rem !important; }
        .pnlm-loading, .pnlm-load-button, .pnlm-load-box, .pnlm-lbox, .pnlm-lbar, .pnlm-lmsg { display: none !important; }

        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.2s ease;
        }
        .modal-overlay.open { opacity: 1; pointer-events: auto; }
        .modal-box { background: white; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; transform: scale(0.95); transition: transform 0.2s ease; }
        .modal-overlay.open .modal-box { transform: scale(1); }

        .hs-item { border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.6rem; cursor: pointer; transition: all 0.15s ease; }
        .hs-item:hover { border-color: #021044; }
        .hs-item.selected { border-color: #D8B129; background: #fffdf0; }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: #021044; color: white; padding: 1rem 2rem; border-radius: 0.75rem; z-index: 2000; transform: translateY(200%); transition: transform 0.3s ease; }
        .toast.show { transform: translateY(0); }
        input, select, textarea { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.9rem; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #021044; ring: 2px solid #021044; }
        label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; }
        .btn { padding: 0.5rem 1.2rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem; cursor: pointer; border: none; transition: all 0.15s ease; }
        .btn-primary { background: #021044; color: white; }
        .btn-primary:hover { background: #031a6e; }
        .btn-gold { background: #D8B129; color: #021044; }
        .btn-gold:hover { background: #c4a020; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
        .tag { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }
        .tag-nav { background: #dbeafe; color: #1d4ed8; }
        .tag-treasure { background: #fef3c7; color: #b45309; }
        .pnlm-tooltip-content { background: rgba(0,0,0,0.85); color: white; padding: 4px 10px; border-radius: 6px; font-size: 12px; white-space: nowrap; pointer-events: none; }
        .hotspot-marker { width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4); cursor: pointer; }
        .hotspot-marker:hover { transform: scale(1.3); z-index: 99999; }
        .hotspot-marker.nav { background: #3b82f6; }
        .hotspot-marker.treasure { background: #eab308; }
        #click-overlay {
            position: absolute; top: 0.5rem; right: 0.5rem; bottom: 0.5rem; left: 0.5rem; z-index: 100; border-radius: 1rem;
            background: transparent; cursor: crosshair; display: none;
        }
        #click-overlay.active { display: block; }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-brand text-white px-6 py-3 flex items-center justify-between">
        <a href="/admin" class="text-xl font-bold tracking-tight">🗺️ Paneli Admin</a>
        <div class="flex gap-4 text-sm items-center">
            <span id="admin-name" class="text-gray-300"></span>
            <a href="/" class="hover:text-gold transition">Faqja Kryesore</a>
            <button onclick="logout()" class="hover:text-gold transition">Dil</button>
        </div>
    </nav>

    <div id="toast" class="toast"></div>
    <div id="loading-overlay" class="modal-overlay" style="opacity:1;pointer-events:auto;background:rgba(255,255,255,0.8);z-index:999999;">
        <div class="text-brand text-lg font-bold">Duke u ngarkuar...</div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-4" id="app" style="display:none">
        <!-- Top Bar -->
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold text-brand">Menaxho Skenat & Hotspot-et</h1>
                <p class="text-sm text-gray-500">Kliko në imazhin 360° për të vendosur hotspot</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openNewSceneModal()" class="btn btn-gold">+ Skenë e Re</button>
                <button onclick="reloadScenes()" class="btn btn-primary">Rifresko</button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Left: Scene List -->
            <div class="w-full lg:w-72 space-y-3">
                <div class="card">
                    <h2 class="font-bold text-brand mb-3">Skenat</h2>
                    <div id="scene-list" class="space-y-2"></div>
                </div>

                <!-- Hotspots list for selected scene -->
                <div class="card" id="hotspot-panel" style="display:none">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-bold text-brand">Hotspot-et</h2>
                        <div class="flex gap-1">
                            <button id="toggle-hotspots-btn" onclick="toggleHotspots()" class="btn btn-sm" style="background:#6b7280;color:white;">Fshihe</button>
                            <button onclick="startPlacingHotspot()" class="btn btn-primary btn-sm">+ Shto</button>
                        </div>
                    </div>
                    <div id="hotspot-list" class="space-y-1"></div>
                </div>
            </div>

            <!-- Right: Panorama + Forms -->
            <div class="flex-1 space-y-4">
                <!-- Panorama -->
                <div class="card p-2" style="position:relative">
                    <div id="panorama"></div>
                    <div id="click-overlay"></div>
                    <p id="scene-title-display" class="text-center text-sm font-semibold text-brand mt-2">Zgjidh një skenë</p>
                </div>

                <!-- Placing form (shows after clicking on panorama) -->
                <div class="card" id="place-form" style="display:none">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-bold text-brand" id="place-form-title">Vendos Hotspot të Ri</h2>
                        <button onclick="cancelPlace()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div><label>Pitch</label><input type="number" id="new-pitch" step="0.1"></div>
                        <div><label>Yaw</label><input type="number" id="new-yaw" step="0.1"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label>Tipi</label>
                            <select id="new-type" onchange="toggleHotspotForm()">
                                <option value="nav">Navigim (Nav)</option>
                                <option value="treasure">Thesar (Treasure)</option>
                            </select>
                        </div>
                        <div id="target-scene-group">
                            <label>Skena e Destinacionit</label>
                            <select id="new-target-scene"></select>
                        </div>
                    </div>
                    <!-- Treasure fields -->
                    <div id="treasure-fields" style="display:none">
                        <div class="mb-3">
                            <label>Pyetja (Shqip)</label>
                            <input type="text" id="new-question" placeholder="Shkruaj pyetjen...">
                        </div>
                        <div id="answers-container">
                            <label>Përgjigjet</label>
                            <div class="space-y-2 mt-1" id="answers-list">
                                <div class="flex gap-2 items-center">
                                    <input type="text" class="flex-1 answer-text" placeholder="Përgjigjja 1">
                                    <label class="flex items-center gap-1 text-sm whitespace-nowrap"><input type="radio" name="correct-answer" value="0" class="w-auto"> Sakte</label>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <input type="text" class="flex-1 answer-text" placeholder="Përgjigjja 2">
                                    <label class="flex items-center gap-1 text-sm whitespace-nowrap"><input type="radio" name="correct-answer" value="1" class="w-auto"> Sakte</label>
                                </div>
                            </div>
                            <button onclick="addAnswerField()" class="text-sm text-brand mt-1">+ Shto përgjigje</button>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button onclick="saveHotspot()" class="btn btn-primary">Ruaj Hotspot-in</button>
                        <button onclick="cancelPlace()" class="btn" style="background:#e5e7eb;">Anulo</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Scene Modal -->
    <div id="new-scene-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-brand">Skenë e Re</h2>
                <button onclick="closeNewSceneModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="space-y-3">
                <div>
                    <label>Titulli (Shqip)</label>
                    <input type="text" id="new-scene-title-sq" placeholder="p.sh. Dhoma e Ndenjes">
                </div>
                <div>
                    <label>Titulli (Anglisht)</label>
                    <input type="text" id="new-scene-title-en" placeholder="p.sh. Living Room">
                </div>
                <div>
                    <label>Niveli</label>
                    <select id="new-scene-level">
                        <option value="1">Niveli 1</option>
                        <option value="2">Niveli 2</option>
                        <option value="3">Niveli 3</option>
                        <option value="4">Niveli 4</option>
                        <option value="5">Niveli 5</option>
                    </select>
                </div>
                <div>
                    <label>Imazhi 360°</label>
                    <input type="file" id="new-scene-image" accept="image/jpeg,image/png" class="border-2 border-dashed border-gray-300 p-4 rounded-lg cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">Max 50MB. JPEG ose PNG.</p>
                </div>
                <div id="upload-preview" class="hidden text-center">
                    <img id="preview-img" class="max-h-32 mx-auto rounded">
                    <p id="preview-name" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button onclick="saveNewScene()" class="btn btn-primary">Krijo Skenën</button>
                <button onclick="closeNewSceneModal()" class="btn" style="background:#e5e7eb;">Anulo</button>
            </div>
        </div>
    </div>

    <script>
        const API = window.location.origin + '/api';
        function getToken() { return localStorage.getItem('token'); }

        async function apiFetch(path, options = {}) {
            const token = getToken();
            const headers = { 'Accept': 'application/json', ...options.headers };
            if (token) headers['Authorization'] = `Bearer ${token}`;
            if (options.body && !(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }
            let res;
            try {
                res = await fetch(API + path, { ...options, headers });
            } catch(e) {
                console.error('Network error:', e);
                return { success: false, message: 'Gabim rrjeti: ' + e.message };
            }
            if (res.status === 401) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/';
                return { success: false };
            }
            try {
                return await res.json();
            } catch(e) {
                const text = await res.text().catch(() => '');
                console.error('JSON parse error, status=' + res.status + ', body=' + text.slice(0, 500));
                return { success: false, message: 'Gabim nga serveri (kodi ' + res.status + ')' };
            }
        }

        function msg(m) { const t=document.getElementById('toast'); t.textContent=m; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500); }

        // State
        let scenes = [], V = null, curSceneId = null;
        let placingMode = false, clickPos = null;
        let selectedHsId = null, editingHotspotId = null;
        let showHotspots = true, selectVersion = 0;

        // Init
        document.addEventListener('DOMContentLoaded', async () => {
            if (!getToken()) { window.location.href = '/'; return; }

            // Check admin
            const userData = await apiFetch('/user');
            if (!userData.success || userData.data.role !== 'admin') {
                alert('Ju nuk jeni admin!');
                window.location.href = '/';
                return;
            }
            document.getElementById('admin-name').textContent = 'Admin: ' + userData.data.name;
            document.getElementById('loading-overlay').style.display = 'none';
            document.getElementById('app').style.display = 'block';

            await reloadScenes();
        });

        function logout() {
            if (!getToken()) return;
            apiFetch('/logout', { method: 'POST' }).then(() => {
                localStorage.removeItem('token');
                window.location.href = '/';
            });
        }

        async function reloadScenes() {
            const r = await apiFetch('/admin/scenes');
            if (r.success) {
                scenes = r.data;
                renderSceneList();
                if (curSceneId) {
                    const stillExists = scenes.find(s => s.id === curSceneId);
                    if (stillExists) selectScene(curSceneId);
                    else selectScene(null);
                } else if (scenes.length > 0) {
                    selectScene(scenes[0].id);
                }
            }
        }

        function renderSceneList() {
            const container = document.getElementById('scene-list');
            container.innerHTML = scenes.map(s => `
                <div class="flex items-center justify-between p-2 rounded-lg cursor-pointer transition ${s.id === curSceneId ? 'bg-brand text-white' : 'hover:bg-gray-100'}"
                     onclick="selectScene(${s.id})">
                    <div>
                        <div class="font-semibold text-sm">${s.title.sq}</div>
                        <div class="text-xs opacity-75">Niveli ${s.level} · ${s.hotspots?.length || 0} hotspot-e</div>
                    </div>
                    <button onclick="event.stopPropagation();deleteScene(${s.id})" class="text-red-400 hover:text-red-600 text-lg">&times;</button>
                </div>
            `).join('');
        }

        function fadeToScene(id) {
            const panorama = document.getElementById('panorama');
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:absolute;inset:0;z-index:9999;background:#021044;border-radius:1rem;opacity:0;transition:opacity 0.3s ease;pointer-events:none;';
            panorama.style.position = 'relative';
            panorama.appendChild(overlay);
            // Trigger fade-in
            requestAnimationFrame(() => requestAnimationFrame(() => {
                overlay.style.opacity = '1';
            }));
            // After fade-in, switch scene and fade-out
            setTimeout(async () => {
                await selectScene(id);
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 300);
            }, 300);
        }

        async function selectScene(id) {
            selectVersion++;
            const sv = selectVersion;
            curSceneId = id;
            renderSceneList();

            if (!id) {
                document.getElementById('hotspot-panel').style.display = 'none';
                document.getElementById('place-form').style.display = 'none';
                document.getElementById('scene-title-display').textContent = 'Zgjidh një skenë';
                if (V) { V.destroy(); V = null; }
                return;
            }

            const sc = scenes.find(s => s.id === id);
            if (!sc) return;

            document.getElementById('scene-title-display').textContent = sc.title.sq + ' (Niveli ' + sc.level + ')';
            document.getElementById('hotspot-panel').style.display = 'block';
            document.getElementById('place-form').style.display = 'none';
            document.getElementById('click-overlay').classList.remove('active');
            placingMode = false;
            selectedHsId = null;
            editingHotspotId = null;
            if (!showHotspots) {
                showHotspots = true;
                document.getElementById('toggle-hotspots-btn').textContent = 'Fshihe';
            }
            renderHotspotList(sc.hotspots || []);
            await loadPanorama(sc, sv);
        }

        async function loadPanorama(sc, sv) {
            const imgUrl = '/api/scene-image/' + sc.id;

            if (V) {
                V.destroy();
                V = null;
                // Small delay to let Pannellum fully clean up before creating a new viewer
                await new Promise(r => setTimeout(r, 100));
                if (sv !== selectVersion) return;
            }

            const hc = buildHotspotConfig(sc.hotspots || []);
            // Add admin click handlers to hotspot markers
            hc.forEach(h => {
                if (h._hs) {
                    const hs = h._hs;
                    h.clickHandlerFunc = function() {
                        if (placingMode) return;
                        selectHotspot(hs.id);
                        if (hs.type === 'nav' && hs.target_scene) {
                            const ts = scenes.find(s => s.id === hs.target_scene.id);
                            if (ts) fadeToScene(ts.id);
                        } else if (hs.type === 'treasure' && hs.data) {
                            // Populate form for editing
                            document.getElementById('new-pitch').value = hs.pitch;
                            document.getElementById('new-yaw').value = hs.yaw;
                            document.getElementById('new-type').value = 'treasure';
                            toggleHotspotForm();
                            document.getElementById('new-question').value = hs.data.question || '';
                            const answersList = document.getElementById('answers-list');
                            answersList.innerHTML = '';
                            const correctIdx = hs.data.answers.findIndex(a => a.correct);
                            hs.data.answers.forEach((a, i) => {
                                const div = document.createElement('div');
                                div.className = 'flex gap-2 items-center';
                                div.innerHTML = '<input type="text" class="flex-1 answer-text" value="' + a.text.replace(/"/g, '&quot;').replace(/'/g, '&#39;') + '" placeholder="Përgjigjja ' + (i+1) + '">' +
                                    '<label class="flex items-center gap-1 text-sm whitespace-nowrap"><input type="radio" name="correct-answer" value="' + i + '" class="w-auto"' + (i === correctIdx ? ' checked' : '') + '> Sakte</label>';
                                answersList.appendChild(div);
                            });
                            document.getElementById('place-form').style.display = 'block';
                            document.getElementById('place-form-title').textContent = 'Ndrysho Hotspot Thesar';
                            document.getElementById('place-form').scrollIntoView({ behavior: 'smooth' });
                            editingHotspotId = hs.id;
                        }
                    };
                    delete h._hs;
                }
            });

            V = pannellum.viewer('panorama', {
                type: 'equirectangular',
                panorama: imgUrl,
                autoLoad: true,
                autoRotate: -2,
                compass: true,
                hotSpots: hc
            });

            V.on('load', () => {
                try { V.stopAutoRotate?.(); } catch(e) {}
                if (sv && sv !== selectVersion) return;
                if (placingMode) {
                    document.getElementById('click-overlay').classList.add('active');
                    toggleMarkerVisibility(false);
                }
            });
        }

        // Click overlay for placing hotspots
        document.getElementById('click-overlay').addEventListener('click', function(e) {
            if (!placingMode || !V) return;
            e.stopPropagation();

            editingHotspotId = null;
            document.getElementById('place-form-title').textContent = 'Vendos Hotspot të Ri';

            let pitch, yaw;
            let got = false;

            // Method 1: Pannellum's precise coordinate conversion (viewer must be loaded)
            if (typeof V.mouseEventToCoords === 'function') {
                try {
                    const coords = V.mouseEventToCoords(e);
                    if (coords && typeof coords.pitch === 'number' && typeof coords.yaw === 'number') {
                        pitch = Math.round(coords.pitch * 10) / 10;
                        yaw = Math.round(coords.yaw * 10) / 10;
                        got = true;
                    }
                } catch(_) {}
            }

            // Method 2: fallback using getBoundingClientRect + viewer angles
            if (!got && typeof V.getYaw === 'function') {
                try {
                    const rect = document.getElementById('panorama').getBoundingClientRect();
                    if (rect.width > 0 && rect.height > 0) {
                        const nx = (e.clientX - rect.left) / rect.width;
                        const ny = (e.clientY - rect.top) / rect.height;
                        const viewYaw = V.getYaw();
                        const viewPitch = V.getPitch();
                        const viewHfov = V.getHfov();
                        const aspect = rect.width / rect.height;
                        pitch = viewPitch + (0.5 - ny) * viewHfov / aspect;
                        yaw = (viewYaw + (nx - 0.5) * viewHfov) % 360;
                        if (yaw < 0) yaw += 360;
                        pitch = Math.round(pitch * 10) / 10;
                        yaw = Math.round(yaw * 10) / 10;
                        got = true;
                    }
                } catch(_) {}
            }

            if (!got) {
                msg('Imazhi 360° nuk është ngarkuar për këtë skenë. Ngarko një imazh fillimisht.');
                return;
            }

            clickPos = { pitch, yaw };
            document.getElementById('new-pitch').value = pitch;
            document.getElementById('new-yaw').value = yaw;
            document.getElementById('place-form').style.display = 'block';
            toggleHotspotForm();
            document.getElementById('place-form').scrollIntoView({ behavior: 'smooth' });
            placingMode = false;
            document.getElementById('click-overlay').classList.remove('active');
        });

        // --- Hotspot visibility & markers ---
        function toggleHotspots() {
            showHotspots = !showHotspots;
            document.getElementById('toggle-hotspots-btn').textContent = showHotspots ? 'Fshihe' : 'Shfaq';
            toggleMarkerVisibility(showHotspots);
        }

        function buildHotspotConfig(hotspots) {
            if (!showHotspots || !hotspots) return [];
            return hotspots.map(h => {
                const label = h.type === 'nav'
                    ? '→ ' + (h.target_scene?.title?.sq || 'Navigim')
                    : '💎 ' + (h.data?.question || 'Thesar');
                return {
                    pitch: h.pitch,
                    yaw: h.yaw,
                    type: 'custom',
                    cssClass: 'hotspot-marker ' + h.type,
                    _hs: h,  // keep reference for click handler in loadPanorama
                    createTooltipFunc: function(hotspot, args) {
                        if (!hotspot.div) return; // Pannellum 2.5.6 may not set div for custom hotspots
                        var div = document.createElement('div');
                        div.className = 'pnlm-tooltip-content';
                        div.textContent = args.text;
                        hotspot.div.appendChild(div);
                    },
                    createTooltipArgs: { text: label }
                };
            });
        }

        // --- Hotspot List ---
        function renderHotspotList(hotspots) {
            const container = document.getElementById('hotspot-list');
            if (!hotspots || hotspots.length === 0) {
                container.innerHTML = '<div class="text-sm text-gray-400 text-center py-4">Nuk ka hotspot-e në këtë skenë</div>';
                return;
            }
            container.innerHTML = hotspots.map(h => `
                <div class="hs-item ${selectedHsId === h.id ? 'selected' : ''}" onclick="selectHotspot(${h.id})">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="tag ${h.type === 'nav' ? 'tag-nav' : 'tag-treasure'}">${h.type === 'nav' ? 'Nav' : 'Thesar'}</span>
                            <span class="text-xs text-gray-500">p:${h.pitch} y:${h.yaw}</span>
                        </div>
                        <button onclick="event.stopPropagation();deleteHotspot(${h.id})" class="text-red-400 hover:text-red-600 text-sm">&times;</button>
                    </div>
                    ${h.type === 'treasure' && h.data ? `<div class="text-xs text-gray-600 mt-1">${h.data.question || ''}</div>` : ''}
                    ${h.type === 'nav' && h.target_scene ? `<div class="text-xs text-gray-600 mt-1">→ ${h.target_scene.title.sq}</div>` : ''}
                </div>
            `).join('');
        }

        function selectHotspot(id) {
            selectedHsId = id;
            const sc = scenes.find(s => s.id === curSceneId);
            if (sc) renderHotspotList(sc.hotspots || []);
            // Center panorama on selected hotspot
            if (V && sc && sc.hotspots) {
                const hs = sc.hotspots.find(h => h.id === id);
                if (hs) V.lookAt(hs.yaw, hs.pitch);
            }
        }

        // --- Place Hotspot ---
        function startPlacingHotspot() {
            if (!curSceneId) { msg('Zgjidh një skenë fillimisht'); return; }
            if (!V) { msg('Imazhi 360° nuk është ngarkuar ende.'); return; }
            placingMode = true;
            editingHotspotId = null;
            document.getElementById('place-form').style.display = 'none';
            document.getElementById('place-form-title').textContent = 'Vendos Hotspot të Ri';
            const q = document.getElementById('new-question');
            if (q) q.value = '';
            document.getElementById('new-type').value = 'nav';
            toggleHotspotForm();
            // Auto-hide hotspot markers for easier clicking
            if (showHotspots) {
                showHotspots = false;
                document.getElementById('toggle-hotspots-btn').textContent = 'Shfaq';
                toggleMarkerVisibility(false);
            }
            document.getElementById('click-overlay').classList.add('active');
            msg('Kliko në imazhin 360° për të vendosur hotspot-in');
        }

        function toggleMarkerVisibility(visible) {
            const markers = document.querySelectorAll('.pnlm-hotspot-base');
            markers.forEach(m => { m.style.display = visible ? '' : 'none'; });
        }

        function cancelPlace() {
            placingMode = false;
            clickPos = null;
            editingHotspotId = null;
            document.getElementById('place-form').style.display = 'none';
            document.getElementById('place-form-title').textContent = 'Vendos Hotspot të Ri';
            document.getElementById('click-overlay').classList.remove('active');
            // Restore hotspot visibility
            if (!showHotspots) {
                showHotspots = true;
                document.getElementById('toggle-hotspots-btn').textContent = 'Fshihe';
                toggleMarkerVisibility(true);
            }
        }

        function toggleHotspotForm() {
            const type = document.getElementById('new-type').value;
            document.getElementById('target-scene-group').style.display = type === 'nav' ? 'block' : 'none';
            document.getElementById('treasure-fields').style.display = type === 'treasure' ? 'block' : 'none';

            // Populate target scenes dropdown
            if (type === 'nav') {
                const sel = document.getElementById('new-target-scene');
                sel.innerHTML = scenes.filter(s => s.id !== curSceneId).map(s =>
                    `<option value="${s.id}">${s.title.sq} (Niveli ${s.level})</option>`
                ).join('');
            }
        }

        let answerCount = 2;
        function addAnswerField() {
            answerCount++;
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center';
            div.innerHTML = `
                <input type="text" class="flex-1 answer-text" placeholder="Përgjigjja ${answerCount}">
                <label class="flex items-center gap-1 text-sm whitespace-nowrap"><input type="radio" name="correct-answer" value="${answerCount - 1}" class="w-auto"> Sakte</label>
            `;
            document.getElementById('answers-list').appendChild(div);
        }

        async function saveHotspot() {
            const isEdit = editingHotspotId !== null;
            if (!isEdit && (!curSceneId || !clickPos)) { msg('Zgjidh pozitën në imazh'); return; }

            const type = document.getElementById('new-type').value;
            const data = { scene_id: curSceneId, type };

            if (isEdit) {
                // When editing, use the existing pitch/yaw from the DB
                data.pitch = parseFloat(document.getElementById('new-pitch').value);
                data.yaw = parseFloat(document.getElementById('new-yaw').value);
            } else {
                data.pitch = clickPos.pitch;
                data.yaw = clickPos.yaw;
            }

            if (type === 'nav') {
                data.target_scene_id = parseInt(document.getElementById('new-target-scene').value);
                if (!data.target_scene_id) { msg('Zgjidh skenën e destinacionit'); return; }
            } else {
                const question = document.getElementById('new-question').value.trim();
                const answers = Array.from(document.querySelectorAll('.answer-text')).map((el, i) => ({
                    text: el.value.trim(),
                    correct: document.querySelector('input[name="correct-answer"]:checked')?.value == i
                }));

                if (!question) { msg('Shkruaj pyetjen'); return; }
                if (answers.length < 2 || answers.some(a => !a.text)) { msg('Plotëso të paktën 2 përgjigje'); return; }
                if (!answers.some(a => a.correct)) { msg('Zgjidh përgjigjen e saktë'); return; }

                data.data = { question, answers };
            }

            const url = isEdit ? '/admin/hotspots/' + editingHotspotId : '/admin/hotspots';
            const method = isEdit ? 'PUT' : 'POST';

            const r = await apiFetch(url, { method, body: JSON.stringify(data) });

            if (r.success) {
                msg(isEdit ? 'Hotspot-i u përditësua!' : 'Hotspot-i u ruajt!');
                cancelPlace();
                try { await reloadScenes(); } catch(e) { console.error(e); }
                // Update hotspot list without reloading the panorama (avoids Pannellum destroy/recreate errors)
                const sc = scenes.find(s => s.id === curSceneId);
                if (sc) renderHotspotList(sc.hotspots || []);
            } else {
                msg('Gabim: ' + (r.errors ? Object.values(r.errors).flat().join(', ') : r.message));
            }
        }

        // --- Delete Hotspot ---
        async function deleteHotspot(id) {
            if (!confirm('A jeni i sigurt që doni ta fshini këtë hotspot?')) return;
            const r = await apiFetch('/admin/hotspots/' + id, { method: 'DELETE' });
            if (r.success) {
                msg('Hotspot-i u fshi!');
                await reloadScenes();
                // Update hotspot list without reloading the panorama
                const sc = scenes.find(s => s.id === curSceneId);
                if (sc) renderHotspotList(sc.hotspots || []);
            }
        }

        // --- Delete Scene ---
        async function deleteScene(id) {
            if (!confirm('A jeni i sigurt? Kjo do të fshijë skenën dhe të gjitha hotspot-et e saj.')) return;
            const r = await apiFetch('/admin/scenes/' + id, { method: 'DELETE' });
            if (r.success) {
                msg('Skena u fshi!');
                if (curSceneId === id) curSceneId = null;
                await reloadScenes();
                if (scenes.length > 0) selectScene(scenes[0].id);
            }
        }

        // --- New Scene Modal ---
        function openNewSceneModal() {
            document.getElementById('new-scene-modal').classList.add('open');
            document.getElementById('new-scene-title-sq').value = '';
            document.getElementById('new-scene-title-en').value = '';
            document.getElementById('new-scene-level').value = '1';
            document.getElementById('new-scene-image').value = '';
            document.getElementById('upload-preview').classList.add('hidden');
        }

        function closeNewSceneModal() {
            document.getElementById('new-scene-modal').classList.remove('open');
        }

        // Image preview
        document.addEventListener('change', function(e) {
            if (e.target.id === 'new-scene-image') {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        document.getElementById('preview-img').src = ev.target.result;
                        document.getElementById('preview-name').textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)';
                        document.getElementById('upload-preview').classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        async function saveNewScene() {
            try {
                const fileInput = document.getElementById('new-scene-image');
                const file = fileInput.files[0];
                const titleSq = document.getElementById('new-scene-title-sq').value.trim();
                const titleEn = document.getElementById('new-scene-title-en').value.trim();
                const level = parseInt(document.getElementById('new-scene-level').value);

                if (!titleSq || !titleEn) { msg('Plotëso titullin në shqip dhe anglisht'); return; }
                if (!file) { msg('Zgjidh një imazh 360°'); return; }

                // Upload image first
                const formData = new FormData();
                formData.append('image', file);

                let uploadRes, uploadData;
                try {
                    uploadRes = await fetch(API + '/admin/upload-image', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + getToken() },
                        body: formData
                    });
                    uploadData = await uploadRes.json();
                } catch (e) {
                    let detail = '';
                    try {
                        const text = await uploadRes?.text?.() || 'pa përgjigje';
                        detail = text.slice(0, 300);
                    } catch(_) { detail = e.message; }
                    msg('Gabim gjatë ngarkimit të imazhit: ' + detail);
                    return;
                }

                if (!uploadData.success) {
                    let errMsg = uploadData.message || '';
                    if (uploadData.errors) {
                        errMsg = Object.entries(uploadData.errors).map(([field, msgs]) => field + ': ' + msgs.join(', ')).join(' | ');
                    }
                    msg('Gabim gjatë ngarkimit të imazhit: ' + errMsg);
                    return;
                }

                // Create scene
                const sceneRes = await apiFetch('/admin/scenes', {
                    method: 'POST',
                    body: JSON.stringify({
                        title: { en: titleEn, sq: titleSq },
                        image_path: uploadData.data.image_path,
                        level: level,
                        is_initial: scenes.length === 0,
                    })
                });

                if (sceneRes.success) {
                    msg('Skena u krijua!');
                    closeNewSceneModal();
                    await reloadScenes();
                    selectScene(sceneRes.data.id);
                } else {
                    let errMsg = sceneRes.message || '';
                    if (sceneRes.errors) {
                        errMsg = Object.entries(sceneRes.errors).map(([field, msgs]) => field + ': ' + msgs.join(', ')).join(' | ');
                    }
                    msg('Gabim: ' + errMsg);
                }
            } catch (e) {
                msg('Gabim i papritur: ' + e.message);
                console.error(e);
            }
        }
    </script>
</body>
</html>
