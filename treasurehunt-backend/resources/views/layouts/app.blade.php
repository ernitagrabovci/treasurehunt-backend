<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Treasure Hunt Kosovo')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .bg-brand { background-color: #021044; }
        .text-brand { color: #021044; }
        .bg-gold { background-color: #D8B129; }
        .text-gold { color: #D8B129; }
        .btn-gold { background-color: #D8B129; color: #021044; font-weight: bold; padding: 0.5rem 1.5rem; border-radius: 0.5rem; border: none; cursor: pointer; }
        .btn-gold:hover { background-color: #c4a020; }
        .card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    </style>
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-brand text-white px-6 py-3 flex items-center justify-between">
        <a href="/" class="text-xl font-bold tracking-tight">🗺️ Gjueti Thesari Kosova</a>
        <div class="flex gap-4 text-sm" id="nav-links">
            <a href="/play" class="hover:text-gold transition">Luaj</a>
            <a href="/leaderboard" class="hover:text-gold transition">Renditja</a>
            <a href="/profile" class="hover:text-gold transition">Profili</a>
            <button onclick="logout()" class="hover:text-gold transition" id="logout-btn" style="display:none">Dil</button>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

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
            const res = await fetch(API + path, { ...options, headers });
            // Don't intercept 401 on auth endpoints — they return proper error messages
            if (res.status === 401 && !['/login', '/register', '/logout'].includes(path)) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                if (!window.location.pathname.includes('login')) {
                    window.location.href = '/';
                }
                return { success: false, message: 'Unauthenticated' };
            }
            return res.json();
        }

        function logout() {
            if (!getToken()) return;
            apiFetch('/logout', { method: 'POST' }).then(() => {
                localStorage.removeItem('token');
                window.location.href = '/';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('logout-btn');
            if (btn && getToken()) btn.style.display = 'inline';
        });
    </script>
    @stack('scripts')
</body>
</html>
