<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $scene->title['sq'] ?? '360 View' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; overflow: hidden; background: #000; font-family: -apple-system, sans-serif; }

        #viewer {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
            cursor: grab;
            touch-action: none;
        }
        #viewer.grabbing { cursor: grabbing; }

        #viewer img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100%;
            min-height: 100%;
            max-width: none;
            user-select: none;
            -webkit-user-drag: none;
            pointer-events: none;
        }

        /* Hotspots */
        .hotspot {
            position: absolute;
            transform: translate(-50%, -50%);
            cursor: pointer;
            z-index: 10;
            pointer-events: auto;
        }
        .hotspot-nav {
            width: 46px;
            height: 46px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            box-shadow: 0 2px 12px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            color: #000E47;
            border: 2px solid #000E47;
        }
        .hotspot-nav::after {
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
            text-shadow: 0 0 8px rgba(0,0,0,0.9);
            pointer-events: none;
        }
        .hotspot-treasure {
            width: 50px;
            height: 50px;
            background: rgba(255,215,0,0.3);
            border: 3px solid rgba(255,215,0,0.6);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            50% { transform: translate(-50%, -50%) scale(1.15); opacity: 0.7; }
        }

        /* Zoom controls */
        .zoom-controls {
            position: fixed;
            bottom: 30px;
            right: 20px;
            z-index: 20;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .zoom-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.4);
            color: white;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(4px);
            user-select: none;
        }
        .zoom-btn:active { background: rgba(255,255,255,0.4); }

        /* Quiz overlay */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 8, 22, 0.75);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }
        .overlay.active { display: flex; }
        .modal {
            background: linear-gradient(145deg, #0a1628, #0f1f3d);
            border-radius: 20px;
            padding: 32px 24px 24px;
            max-width: 340px;
            width: 90%;
            color: white;
            box-shadow: 0 20px 60px rgba(0, 20, 60, 0.6);
            border: 1px solid rgba(56, 152, 255, 0.15);
        }
        .modal h2 { font-size: 18px; font-weight: 700; margin-bottom: 24px; line-height: 1.5; text-align: center; color: #e8f0ff; }
        .modal .answer-btn { display: block; width: 100%; padding: 16px 18px; margin-bottom: 10px; background: rgba(56, 152, 255, 0.06); border: 1.5px solid rgba(56, 152, 255, 0.2); border-radius: 14px; color: #c8d8ff; font-size: 15px; text-align: left; cursor: pointer; font-weight: 500; }
        .modal .answer-btn.selected { border-color: #3898ff; background: rgba(56, 152, 255, 0.15); color: white; }
        .modal .answer-btn.correct { border-color: #2ecc71; background: rgba(46, 204, 113, 0.12); color: #2ecc71; }
        .modal .answer-btn.incorrect { border-color: #e74c3c; background: rgba(231, 76, 60, 0.12); color: #e74c3c; }
        .modal .feedback { text-align: center; font-size: 14px; margin-top: 12px; padding: 8px; border-radius: 8px; display: none; }
        .modal .feedback.incorrect { background: rgba(231, 76, 60, 0.1); color: #ff6b6b; border: 1px solid rgba(231, 76, 60, 0.2); display: block; }
        .modal .celebration { display: none; text-align: center; padding: 16px 0; }
        .modal .celebration.active { display: block; }
        .modal .celebration .trophy { font-size: 64px; display: block; margin-bottom: 12px; animation: trophyBounce 0.6s ease infinite alternate; }
        @keyframes trophyBounce { from { transform: translateY(0) scale(1); } to { transform: translateY(-8px) scale(1.1); } }
        .modal .celebration h3 { font-size: 20px; font-weight: 800; margin-bottom: 8px; color: #ffd700; }
        .modal .celebration p { font-size: 14px; color: #ccc; line-height: 1.5; }
        .modal .action-btn { display: block; width: 100%; padding: 14px; margin-top: 10px; border-radius: 14px; font-size: 15px; cursor: pointer; font-weight: 600; background: linear-gradient(135deg, #3898ff, #2563eb); border: none; color: white; }
        .modal .close-btn { display: block; width: 100%; padding: 14px; margin-top: 10px; border-radius: 14px; font-size: 15px; cursor: pointer; font-weight: 600; background: transparent; border: 1px solid rgba(56, 152, 255, 0.2); color: #6b8fc8; }
    </style>
</head>
<body>

<div id="viewer"></div>

<div class="zoom-controls">
    <div class="zoom-btn" id="zoomIn">+</div>
    <div class="zoom-btn" id="zoomOut">−</div>
</div>

<div class="overlay" id="questionOverlay">
    <div class="modal" id="questionModal">
        <h2 id="questionText"></h2>
        <div id="answersContainer"></div>
        <div class="feedback" id="feedbackEl"></div>
        <div class="celebration" id="celebrationEl">
            <span class="trophy">🏆</span>
            <h3>Urime!</h3>
            <p>Kalove nivelin dhe fituat<br>nje shperblim!</p>
        </div>
        <button class="action-btn" id="continueBtn" style="display:none">Vazhdo</button>
        <button class="close-btn" id="closeBtn" style="display:none">Mbyll</button>
    </div>
</div>

<script>
    const scene = @json($scene);
    const scenesByLevel = @json($scenesByLevel);
    const sceneImageUrl = @json($scene->image_path);

    function sendToApp(data) {
        if (window.ReactNativeWebView && window.ReactNativeWebView.postMessage) {
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
    }

    const isInAppBrowser = !(window.ReactNativeWebView && window.ReactNativeWebView.postMessage);

    // ─── Build the 360° viewer ───
    const viewer = document.getElementById('viewer');
    const img = document.createElement('img');
    img.draggable = false;
    viewer.appendChild(img);

    let scale = 1;
    let translateX = 0;
    let translateY = 0;
    let isDragging = false;
    let startX, startY, startTranslateX, startTranslateY;
    let imgLoaded = false;

    function applyTransform() {
        img.style.transform = `translate(calc(-50% + ${translateX}px), calc(-50% + ${translateY}px)) scale(${scale})`;
    }

    function updateHotspotPositions() {
        document.querySelectorAll('.hotspot').forEach(hs => {
            const pitch = parseFloat(hs.dataset.pitch);
            const yaw = parseFloat(hs.dataset.yaw);
            const pos = projectToScreen(pitch, yaw);
            if (pos) {
                hs.style.left = pos.x + '%';
                hs.style.top = pos.y + '%';
                hs.style.display = '';
            } else {
                hs.style.display = 'none';
            }
        });
    }

    function projectToScreen(pitch, yaw) {
        // Simple equirectangular projection
        const fov = 90 / scale; // field of view decreases as zoom increases
        const halfFov = fov / 2;

        // Calculate where the center of the view is pointing
        const centerYaw = -translateX * (360 / (img.naturalWidth * scale));
        const centerPitch = translateY * (180 / (img.naturalHeight * scale));

        let relYaw = yaw - centerYaw;
        let relPitch = pitch - centerPitch;

        // Normalize yaw to [-180, 180]
        while (relYaw > 180) relYaw -= 360;
        while (relYaw < -180) relYaw += 360;

        if (Math.abs(relYaw) > halfFov || Math.abs(relPitch) > halfFov * (img.naturalHeight / img.naturalWidth)) {
            return null;
        }

        const x = 50 + (relYaw / halfFov) * 50;
        const y = 50 + (relPitch / halfFov) * 50 * (img.naturalWidth / img.naturalHeight);
        return { x, y };
    }

    // Load the image
    function loadScene() {
        const absoluteUrl = sceneImageUrl.startsWith('http')
            ? sceneImageUrl
            : window.location.origin + (sceneImageUrl.startsWith('/') ? '' : '/') + sceneImageUrl;

        img.onload = function() {
            imgLoaded = true;
            img.style.display = '';
            applyTransform();
            renderHotspots();
            sendToApp({ type: 'loaded', scene_id: scene.id });
        };
        img.onerror = function() {
            img.src = absoluteUrl;
            // Retry with API endpoint if direct fails
            img.onerror = function() {
                sendToApp({ type: 'loaded', scene_id: scene.id, error: true });
            };
        };
        img.src = absoluteUrl;
    }

    // ─── Hotspot rendering ───
    function renderHotspots() {
        document.querySelectorAll('.hotspot').forEach(el => el.remove());

        (scene.hotspots || []).forEach(h => {
            const el = document.createElement('div');
            el.className = 'hotspot';
            el.dataset.pitch = h.pitch;
            el.dataset.yaw = h.yaw;
            el.dataset.type = h.type;
            el.dataset.id = h.id;
            if (h.target_scene_id) el.dataset.targetSceneId = h.target_scene_id;

            if (h.type === 'nav') {
                el.innerHTML = '<div class="hotspot-nav">→</div>';
                el.addEventListener('click', function() {
                    sendToApp({ type: 'navigate', hotspot_id: h.id, target_scene_id: h.target_scene_id });
                    if (isInAppBrowser && h.target_scene_id) {
                        window.location.href = '/embed/scene/' + h.target_scene_id;
                    }
                });
            } else if (h.type === 'treasure') {
                el.innerHTML = '<div class="hotspot-treasure"></div>';
                el.addEventListener('click', function() {
                    if (h.data) { showQuestion(h.data.question, h.data.answers, h.id); }
                });
            }

            viewer.appendChild(el);
        });
        updateHotspotPositions();
    }

    // ─── Drag to pan ───
    viewer.addEventListener('mousedown', function(e) {
        if (e.target.closest('.hotspot')) return;
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        startTranslateX = translateX;
        startTranslateY = translateY;
        viewer.classList.add('grabbing');
    });
    window.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        translateX = startTranslateX + dx;
        translateY = startTranslateY + dy;
        applyTransform();
        updateHotspotPositions();
    });
    window.addEventListener('mouseup', function() {
        isDragging = false;
        viewer.classList.remove('grabbing');
    });

    // Touch support
    let lastTouchDist = 0;
    viewer.addEventListener('touchstart', function(e) {
        if (e.target.closest('.hotspot')) return;
        if (e.touches.length === 1) {
            isDragging = true;
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            startTranslateX = translateX;
            startTranslateY = translateY;
        } else if (e.touches.length === 2) {
            lastTouchDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
        }
    }, { passive: true });
    viewer.addEventListener('touchmove', function(e) {
        if (e.touches.length === 1 && isDragging) {
            const dx = e.touches[0].clientX - startX;
            const dy = e.touches[0].clientY - startY;
            translateX = startTranslateX + dx;
            translateY = startTranslateY + dy;
            applyTransform();
            updateHotspotPositions();
        } else if (e.touches.length === 2 && lastTouchDist > 0) {
            const dist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const delta = dist / lastTouchDist;
            scale = Math.min(5, Math.max(0.5, scale * delta));
            applyTransform();
            updateHotspotPositions();
            lastTouchDist = dist;
        }
    }, { passive: true });
    viewer.addEventListener('touchend', function() {
        isDragging = false;
        lastTouchDist = 0;
    }, { passive: true });

    // ─── Zoom ───
    document.getElementById('zoomIn').addEventListener('click', function() {
        scale = Math.min(5, scale * 1.3);
        applyTransform();
        updateHotspotPositions();
    });
    document.getElementById('zoomOut').addEventListener('click', function() {
        scale = Math.max(0.5, scale / 1.3);
        applyTransform();
        updateHotspotPositions();
    });
    viewer.addEventListener('wheel', function(e) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? 0.8 : 1.25;
        scale = Math.min(5, Math.max(0.5, scale * delta));
        applyTransform();
        updateHotspotPositions();
    }, { passive: false });

    // ─── Quiz ───
    function showQuestion(question, answers, hotspotId) {
        const overlay = document.getElementById('questionOverlay');
        const questionEl = document.getElementById('questionText');
        const answersContainer = document.getElementById('answersContainer');
        const feedbackEl = document.getElementById('feedbackEl');
        const continueBtn = document.getElementById('continueBtn');
        const closeBtn = document.getElementById('closeBtn');
        questionEl.textContent = question;
        questionEl.style.display = 'block';
        answersContainer.style.display = 'block';
        answersContainer.innerHTML = '';
        feedbackEl.className = 'feedback';
        feedbackEl.textContent = '';
        document.getElementById('celebrationEl').classList.remove('active');
        continueBtn.style.display = 'none';
        closeBtn.style.display = 'none';
        let selectedIndex = -1;
        answers.forEach((answer, index) => {
            const btn = document.createElement('button');
            btn.className = 'answer-btn';
            btn.textContent = answer.text || answer;
            btn.addEventListener('click', function() {
                answersContainer.querySelectorAll('.answer-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedIndex = index;
                continueBtn.style.display = 'block';
            });
            answersContainer.appendChild(btn);
        });
        overlay._hotspotId = hotspotId;
        overlay._answers = answers;
        closeBtn._nextLevel = false;
        closeBtn.textContent = 'Mbyll';
        overlay.classList.add('active');
    }

    document.getElementById('continueBtn').addEventListener('click', function() {
        const overlay = document.getElementById('questionOverlay');
        const answersContainer = document.getElementById('answersContainer');
        const feedbackEl = document.getElementById('feedbackEl');
        const continueBtn = document.getElementById('continueBtn');
        const closeBtn = document.getElementById('closeBtn');
        const selectedBtn = answersContainer.querySelector('.answer-btn.selected');
        if (!selectedBtn) return;
        const index = Array.from(answersContainer.children).indexOf(selectedBtn);
        const hotspotId = overlay._hotspotId;
        const answers = overlay._answers;
        answersContainer.querySelectorAll('.answer-btn').forEach(b => b.style.pointerEvents = 'none');
        const isCorrect = answers[index].correct || (answers.length === 1);
        if (isCorrect) {
            answersContainer.style.display = 'none';
            document.getElementById('questionText').style.display = 'none';
            document.getElementById('celebrationEl').classList.add('active');
            sendToApp({ type: 'treasure_found', hotspot_id: hotspotId, correct: true, answer_index: index });
            continueBtn.style.display = 'none';
            closeBtn.textContent = 'Niveli Tjeter';
            closeBtn.style.display = 'block';
            closeBtn._nextLevel = true;
        } else {
            selectedBtn.classList.add('incorrect');
            feedbackEl.className = 'feedback incorrect';
            feedbackEl.textContent = 'Gabim!';
            answers.forEach((a, i) => { if (a.correct) { answersContainer.children[i].classList.add('correct'); } });
            sendToApp({ type: 'treasure_found', hotspot_id: hotspotId, correct: false, answer_index: index });
            continueBtn.style.display = 'none';
            closeBtn.textContent = 'Mbyll';
            closeBtn.style.display = 'block';
            closeBtn._nextLevel = false;
        }
    });

    document.getElementById('closeBtn').addEventListener('click', function() {
        document.getElementById('questionOverlay').classList.remove('active');
        if (this._nextLevel) {
            sendToApp({ type: 'level_complete', level: scene.level });
            if (isInAppBrowser) {
                const nextLevel = scene.level + 1;
                const nextScenes = scenesByLevel[nextLevel];
                if (nextScenes && nextScenes.length > 0) {
                    window.location.href = '/embed/scene/' + nextScenes[0].id;
                }
            }
        }
    });

    // ─── Start ───
    loadScene();
</script>
</body>
</html>
