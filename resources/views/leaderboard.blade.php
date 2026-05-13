@extends('layouts.app')

@section('title', 'Renditja - Gjueti Thesari Kosova')

@section('content')
<div class="card">
    <h1 class="text-2xl font-bold text-brand mb-2">Renditja</h1>
    <p class="text-gray-500 mb-4">Gjuetarët më të mirë të renditur sipas zbulimeve</p>

    <div class="flex gap-2 mb-4 flex-wrap">
        <button class="lvl-btn active" data-level="" onclick="filterLevel('')">Të gjithë</button>
        <button class="lvl-btn" data-level="1" onclick="filterLevel(1)">Niveli 1</button>
        <button class="lvl-btn" data-level="2" onclick="filterLevel(2)">Niveli 2</button>
        <button class="lvl-btn" data-level="3" onclick="filterLevel(3)">Niveli 3</button>
        <button class="lvl-btn" data-level="4" onclick="filterLevel(4)">Niveli 4</button>
        <button class="lvl-btn" data-level="5" onclick="filterLevel(5)">Niveli 5</button>
    </div>

    <div id="stats-bar" class="flex gap-6 mb-4 text-sm text-gray-600">
        <div><strong id="total-players">0</strong> lojtarë</div>
        <div><strong id="total-found">0</strong> thesare të gjetura</div>
    </div>

    <div id="leaderboard-body">
        <div class="text-center py-8 text-gray-400">Duke u ngarkuar...</div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .lvl-btn {
        padding: 0.4rem 1rem;
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
        background: white;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    .lvl-btn.active {
        border-color: #021044;
        background: #021044;
        color: white;
    }
    .lvl-btn:hover:not(.active) {
        border-color: #021044;
    }
</style>
<script>
let currentFilter = '';

async function loadLeaderboard() {
    const url = currentFilter ? '/leaderboard?level=' + currentFilter : '/leaderboard';
    const data = await apiFetch(url);

    const container = document.getElementById('leaderboard-body');

    if (currentFilter) {
        // Per-level time view
        document.getElementById('stats-bar').style.display = 'none';

        if (!data.leaderboard || data.leaderboard.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-400">Akoma asnjë lojtar.</div>';
            return;
        }

        const medals = ['🥇', '🥈', '🥉'];
        container.innerHTML = `
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500 text-sm uppercase">
                        <th class="pb-3 pr-2">Renditja</th>
                        <th class="pb-3">Lojtari</th>
                        <th class="pb-3 text-right">Koha</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.leaderboard.map(p => `
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 pr-2 text-lg">${medals[p.rank - 1] || '#' + p.rank}</td>
                            <td class="py-3 font-medium">${p.name}</td>
                            <td class="py-3 text-right font-bold ${p.rank === 1 ? 'text-gold' : ''}">${p.total_time >= 60 ? Math.floor(p.total_time/60) + 'm ' + (p.total_time%60) + 's' : p.total_time + 's'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    } else {
        // Default total view
        document.getElementById('stats-bar').style.display = 'flex';
        document.getElementById('total-players').textContent = data.total_players;
        document.getElementById('total-found').textContent = data.total_treasures_found;

        if (!data.leaderboard || data.leaderboard.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-400">Nuk u gjet asnjë thesar. Fillo eksplorimin!</div>';
            return;
        }

        const medals = ['🥇', '🥈', '🥉'];
        container.innerHTML = `
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500 text-sm uppercase">
                        <th class="pb-3 pr-2">Renditja</th>
                        <th class="pb-3">Lojtari</th>
                        <th class="pb-3 text-center">Niveli</th>
                        <th class="pb-3 text-center">Thesare</th>
                        <th class="pb-3 text-center">Koha</th>
                        <th class="pb-3 text-right">Pikët</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.leaderboard.map(p => `
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 pr-2 text-lg">${medals[p.rank - 1] || '#' + p.rank}</td>
                            <td class="py-3 font-medium">${p.name}</td>
                            <td class="py-3 text-center text-sm">${p.level.title.en}</td>
                            <td class="py-3 text-center font-bold">${p.total_treasures}</td>
                            <td class="py-3 text-center text-sm text-gray-600">${p.total_time ? (p.total_time >= 60 ? Math.floor(p.total_time/60) + 'm ' + (p.total_time%60) + 's' : p.total_time + 's') : '-'}</td>
                            <td class="py-3 text-right font-bold text-gold">${p.points}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }
}

function filterLevel(level) {
    currentFilter = level;
    document.querySelectorAll('.lvl-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.level == level);
    });
    document.getElementById('leaderboard-body').innerHTML = '<div class="text-center py-8 text-gray-400">Duke u ngarkuar...</div>';
    loadLeaderboard();
}

document.addEventListener('DOMContentLoaded', () => {
    if (!getToken()) { window.location.href = '/'; return; }
    loadLeaderboard();
});
</script>
@endpush
