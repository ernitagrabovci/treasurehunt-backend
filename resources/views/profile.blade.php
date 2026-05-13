@extends('layouts.app')

@section('title', 'Profili - Gjueti Thesari Kosova')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Level Card -->
    <div class="card text-center">
        <h2 class="font-bold text-brand text-lg mb-4">Niveli Im</h2>
        <div id="level-display">
            <div class="text-5xl mb-2" id="level-icon">🏁</div>
            <div class="text-2xl font-bold text-brand" id="level-name">-</div>
            <div class="text-gray-500">Niveli <span id="level-number">-</span></div>
        </div>
    </div>

    <!-- Stats Card -->
    <div class="card">
        <h2 class="font-bold text-brand text-lg mb-4">Statistikat</h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Thesare të Gjetura</span>
                <span class="font-bold" id="stat-treasures">0</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Pikët Totale</span>
                <span class="font-bold text-gold" id="stat-points">0</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Niveli i Ardhshëm</span>
                <span class="font-bold" id="stat-next">-</span>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="mt-4">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span id="progress-label">Progresi</span>
                <span id="progress-pct">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div id="progress-bar" class="bg-gold h-full rounded-full" style="width:0%"></div>
            </div>
        </div>
    </div>

    <!-- Account Card -->
    <div class="card">
        <h2 class="font-bold text-brand text-lg mb-4">Llogaria</h2>
        <div id="account-info">
            <div class="text-gray-500 text-sm">Duke u ngarkuar...</div>
        </div>
        <button onclick="logout()" class="mt-6 bg-red-500 text-white px-4 py-2 rounded-lg w-full hover:bg-red-600">Dil</button>
    </div>
</div>

<!-- Treasures List -->
<div class="card mt-6">
    <h2 class="font-bold text-brand text-lg mb-4">Thesaret e Mia</h2>
    <div id="treasures-list">
        <div class="text-center py-8 text-gray-400">Duke u ngarkuar...</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const levelIcons = { 1: '🏁', 2: '🔍', 3: '🏹', 4: '💎', 5: '👑' };

async function loadProfile() {
    // Level & stats
    const levelData = await apiFetch('/user/level');
    if (levelData.success) {
        const d = levelData.data;
        document.getElementById('level-icon').textContent = levelIcons[d.level] || '🏁';
        document.getElementById('level-name').textContent = d.title.sq;
        document.getElementById('level-number').textContent = d.level;
        document.getElementById('stat-treasures').textContent = d.total_treasures;
        document.getElementById('stat-points').textContent = d.points;
        document.getElementById('stat-next').textContent = d.next_level_at ? `${d.treasures_to_next} thesar${d.treasures_to_next !== 1 ? 'ë' : ''} të mbetura` : 'Niveli maksimal';

        if (d.next_level_at) {
            const nextMin = d.next_level_at;
            const currentMin = d.level === 1 ? 0 : [0, 0, 2, 4, 7][d.level] || 0;
            const range = nextMin - currentMin;
            const progress = d.level === 5 ? 100 : ((d.total_treasures - currentMin) / range) * 100;
            document.getElementById('progress-bar').style.width = Math.min(progress, 100) + '%';
            document.getElementById('progress-pct').textContent = Math.round(Math.min(progress, 100)) + '%';
            document.getElementById('progress-label').textContent = `${d.total_treasures} / ${nextMin} thesare`;
        } else {
            document.getElementById('progress-bar').style.width = '100%';
            document.getElementById('progress-pct').textContent = '100%';
            document.getElementById('progress-label').textContent = 'Niveli Maksimal!';
        }
    }

    // Account
    try {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        document.getElementById('account-info').innerHTML = `
            <p class="font-semibold">${user.name}</p>
            <p class="text-gray-500 text-sm">${user.email}</p>
            <p class="text-gray-500 text-sm mt-1">Gjuha: ${user.locale === 'sq' ? 'Shqip' : 'English'}</p>
        `;
    } catch(e) {
        document.getElementById('account-info').textContent = 'Nuk disponohet';
    }

    // Treasures list
    const tresData = await apiFetch('/treasures');
    const container = document.getElementById('treasures-list');
    if (tresData.success && tresData.data.length > 0) {
        container.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                ${tresData.data.map(t => `
                    <div class="border border-gray-200 rounded-lg p-3 flex items-center gap-3">
                        <span class="text-2xl">💎</span>
                        <div>
                            <p class="font-medium text-sm">Niveli ${t.hotspot.scene?.level || '?'}</p>
                            <p class="text-xs text-gray-500">Gjetur: ${new Date(t.found_at).toLocaleDateString()} ${t.time_spent_seconds ? '· Koha: ' + (t.time_spent_seconds >= 60 ? Math.floor(t.time_spent_seconds/60) + 'm ' + (t.time_spent_seconds%60) + 's' : t.time_spent_seconds + 's') : ''}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        container.innerHTML = '<div class="text-center py-8 text-gray-400">Nuk u gjet asnjë thesar. Fillo eksplorimin!</div>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!getToken()) { window.location.href = '/'; return; }
    loadProfile();
});
</script>
@endpush
