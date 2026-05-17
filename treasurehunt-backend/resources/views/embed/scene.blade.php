<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $scene->title['sq'] ?? '360 View' }}</title>
    <link rel="stylesheet" href="/pannellum/pannellum.css">
    <script src="/pannellum/pannellum.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; overflow: hidden; background: #000; }
        #panorama { width: 100%; height: 100%; }
        .pnlm-container { width: 100% !important; height: 100% !important; }
        .pnlm-loading, .pnlm-load-button, .pnlm-load-box, .pnlm-lbox, .pnlm-lbar, .pnlm-lmsg { display: none !important; }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 8, 22, 0.75);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .overlay.active { display: flex; }
        .modal {
            background: linear-gradient(145deg, #0a1628, #0f1f3d);
            border-radius: 20px;
            padding: 32px 24px 24px;
            max-width: 340px;
            width: 90%;
            color: white;
            font-family: -apple-system, sans-serif;
            box-shadow: 0 20px 60px rgba(0, 20, 60, 0.6), 0 0 0 1px rgba(56, 152, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(56, 152, 255, 0.15);
        }
        .modal h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 24px;
            line-height: 1.5;
            text-align: center;
            color: #e8f0ff;
            letter-spacing: -0.2px;
        }
        .modal .answer-btn {
            display: block;
            width: 100%;
            padding: 16px 18px;
            margin-bottom: 10px;
            background: rgba(56, 152, 255, 0.06);
            border: 1.5px solid rgba(56, 152, 255, 0.2);
            border-radius: 14px;
            color: #c8d8ff;
            font-size: 15px;
            text-align: left;
            cursor: pointer;
            transition: all 0.25s ease;
            font-weight: 500;
        }
        .modal .answer-btn:hover {
            background: rgba(56, 152, 255, 0.12);
            border-color: rgba(56, 152, 255, 0.4);
            color: white;
            transform: translateY(-1px);
        }
        .modal .answer-btn.selected {
            border-color: #3898ff;
            background: rgba(56, 152, 255, 0.15);
            color: white;
            box-shadow: 0 0 20px rgba(56, 152, 255, 0.15);
        }
        .modal .answer-btn.correct {
            border-color: #2ecc71;
            background: rgba(46, 204, 113, 0.12);
            color: #2ecc71;
        }
        .modal .answer-btn.incorrect {
            border-color: #e74c3c;
            background: rgba(231, 76, 60, 0.12);
            color: #e74c3c;
        }
        .modal .feedback {
            text-align: center;
            font-size: 14px;
            margin-top: 12px;
            padding: 8px;
            border-radius: 8px;
            display: none;
        }
        .modal .feedback.incorrect {
            background: rgba(231, 76, 60, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(231, 76, 60, 0.2);
            display: block;
        }
        .modal .celebration {
            display: none;
            text-align: center;
            padding: 16px 0;
        }
        .modal .celebration.active { display: block; }
        .modal .celebration .trophy {
            font-size: 64px;
            display: block;
            margin-bottom: 12px;
            animation: trophyBounce 0.6s ease infinite alternate;
        }
        @keyframes trophyBounce {
            from { transform: translateY(0) scale(1); }
            to { transform: translateY(-8px) scale(1.1); }
        }
        .modal .celebration h3 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #ffd700;
        }
        .modal .celebration p {
            font-size: 14px;
            color: #ccc;
            line-height: 1.5;
        }
        .confetti-container {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 10000;
            overflow: hidden;
        }
        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            top: -10px;
            animation: confettiFall linear forwards;
        }
        @keyframes confettiFall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .modal .feedback.correct { display: none; }
        .modal .close-btn, .modal .action-btn {
            display: block;
            width: 100%;
            padding: 14px;
            margin-top: 10px;
            border-radius: 14px;
            font-size: 15px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.25s ease;
        }
        .modal .close-btn {
            background: transparent;
            border: 1px solid rgba(56, 152, 255, 0.2);
            color: #6b8fc8;
        }
        .modal .close-btn:hover {
            color: white;
            border-color: rgba(56, 152, 255, 0.5);
            background: rgba(56, 152, 255, 0.08);
        }
        .modal .action-btn {
            background: linear-gradient(135deg, #3898ff, #2563eb);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(56, 152, 255, 0.3);
        }
        .modal .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(56, 152, 255, 0.4);
        }
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
            text-shadow: 0 0 8px rgba(0,0,0,0.9);
            pointer-events: none;
        }
        .pnlm-hotspot-base.treasure-hs {
            width: 50px !important;
            height: 50px !important;
            background: rgba(255,255,255,0.01) !important;
            border: none !important;
            cursor: pointer !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body>
    <div id="panorama"></div>

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

        const navHs = (scene.hotspots || []).filter(h => h.type === 'nav').map(h => ({
            pitch: h.pitch,
            yaw: h.yaw,
            type: 'custom',
            cssClass: 'nav-hs',
            clickHandlerFunc: function() {
                sendToApp({ type: 'navigate', hotspot_id: h.id, target_scene_id: h.target_scene_id });
                if (isInAppBrowser && h.target_scene_id) {
                    window.location.href = '/embed/scene/' + h.target_scene_id;
                }
            }
        }));

        const tresHs = (scene.hotspots || []).filter(h => h.type === 'treasure').map(h => ({
            pitch: h.pitch,
            yaw: h.yaw,
            type: 'custom',
            cssClass: 'treasure-hs',
            clickHandlerFunc: function() {
                if (h.data) {
                    showQuestion(h.data.question, h.data.answers, h.id);
                }
            }
        }));

        function spawnConfetti() {
            const container = document.createElement('div');
            container.className = 'confetti-container';
            const colors = ['#3898ff', '#2563eb', '#60a5fa', '#ffd700', '#2ecc71', '#a78bfa', '#f59e0b', '#34d399'];
            for (let i = 0; i < 60; i++) {
                const piece = document.createElement('div');
                piece.className = 'confetti-piece';
                piece.style.left = Math.random() * 100 + '%';
                piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                piece.style.width = (Math.random() * 8 + 4) + 'px';
                piece.style.height = (Math.random() * 8 + 4) + 'px';
                piece.style.animationDuration = (Math.random() * 2 + 1.5) + 's';
                piece.style.animationDelay = (Math.random() * 0.8) + 's';
                container.appendChild(piece);
            }
            document.body.appendChild(container);
            setTimeout(() => container.remove(), 4000);
        }

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

            // Disable all buttons
            answersContainer.querySelectorAll('.answer-btn').forEach(b => b.style.pointerEvents = 'none');

            const isCorrect = answers[index].correct || (answers.length === 1);

            if (isCorrect) {
                answersContainer.style.display = 'none';
                document.getElementById('questionText').style.display = 'none';
                document.getElementById('celebrationEl').classList.add('active');
                sendToApp({ type: 'treasure_found', hotspot_id: hotspotId, correct: true, answer_index: index });
                spawnConfetti();
                continueBtn.style.display = 'none';
                closeBtn.textContent = 'Niveli Tjeter';
                closeBtn.style.display = 'block';
                closeBtn._nextLevel = true;
            } else {
                selectedBtn.classList.add('incorrect');
                feedbackEl.className = 'feedback incorrect';
                feedbackEl.textContent = 'Gabim!';
                answers.forEach((a, i) => {
                    if (a.correct) {
                        answersContainer.children[i].classList.add('correct');
                    }
                });
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

        const viewer = pannellum.viewer('panorama', {
            type: 'equirectangular',
            panorama: sceneImageUrl,
            autoLoad: true,
            autoRotate: -2,
            compass: true,
            hotSpots: [...navHs, ...tresHs],
        });

        viewer.on('load', function() {
            try { viewer.stopAutoRotate?.(); } catch(e) {}
            sendToApp({ type: 'loaded', scene_id: scene.id });
        });

        viewer.on('error', function(err) {
            console.error('Pannellum error:', err);
            sendToApp({ type: 'loaded', scene_id: scene.id, error: true });
        });
    </script>
</body>
</html>
