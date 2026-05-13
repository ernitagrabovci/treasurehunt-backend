@extends('layouts.app')

@section('title', 'Hyrje - Gjueti Thesari Kosova')

@section('content')
<div class="flex items-center justify-center min-h-[70vh]">
    <div class="card w-full max-w-md">
        <h1 class="text-2xl font-bold text-brand text-center mb-6">Mirësevini në Gjuetinë e Thesarit Kosova</h1>

        <div id="login-form">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" placeholder="shëmbull@email.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-transparent">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fjalëkalimi</label>
                <input type="password" id="password" placeholder="Shkruani fjalëkalimin" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-transparent">
            </div>
            <button onclick="handleLogin()" class="btn-gold w-full text-center text-lg py-3">Fillo Gjuetinë</button>
            <p id="login-error" class="text-red-500 text-sm mt-3 text-center hidden"></p>
            <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                <p class="text-xs text-gray-400 mb-2">Admin</p>
                <button onclick="adminLogin()" class="text-sm text-brand underline">Hyr si Admin</button>
            </div>
        </div>

        <div id="register-form" class="hidden mt-6 pt-6 border-t border-gray-200">
            <h2 class="text-lg font-semibold text-brand mb-4">I ri? Regjistrohu</h2>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Emri</label>
                <input type="text" id="reg-name" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="reg-email" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fjalëkalimi</label>
                <input type="password" id="reg-password" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmo Fjalëkalimin</label>
                <input type="password" id="reg-password-confirm" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <button onclick="handleRegister()" class="bg-brand text-white w-full text-center py-3 rounded-lg font-bold hover:opacity-90">Regjistrohu</button>
            <p class="text-center mt-3">
                <a href="#" onclick="toggleForms(); return false;" class="text-sm text-brand underline">Keni llogari? Hyni</a>
            </p>
        </div>

        <p class="text-center mt-4">
            <a href="#" onclick="toggleForms(); return false;" class="text-sm text-brand underline" id="toggle-link">I ri? Krijoni një llogari</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleForms() {
    document.getElementById('login-form').classList.toggle('hidden');
    document.getElementById('register-form').classList.toggle('hidden');
    const link = document.getElementById('toggle-link');
    link.textContent = link.textContent.includes('Hyni') ? 'I ri? Krijoni një llogari' : 'Keni llogari? Hyni';
}

async function handleLogin() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorEl = document.getElementById('login-error');
    errorEl.classList.add('hidden');

    const data = await apiFetch('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password })
    });

    if (data.success) {
        localStorage.setItem('token', data.data.access_token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
        window.location.href = '/play';
    } else if (data.requires_verification) {
        errorEl.innerHTML = 'Ju lutemi verifikoni email-in fillimisht. '
            + '<a href="#" onclick="resendVerification(\'' + data.email + '\'); return false;" class="text-brand underline font-semibold">Ridërgo email-in e verifikimit</a>';
        errorEl.classList.remove('hidden');
    } else {
        errorEl.textContent = data.message || 'Hyrja dështoi';
        errorEl.classList.remove('hidden');
    }
}

async function resendVerification(email) {
    const data = await apiFetch('/resend-verification', {
        method: 'POST',
        body: JSON.stringify({ email })
    });
    alert(data.success ? 'Email-i i verifikimit u dërgua! Kontrolloni inbox-in tuaj.' : data.message || 'Dërgimi dështoi');
}

async function adminLogin() {
    // Quick admin login for testing
    const data = await apiFetch('/login', {
        method: 'POST',
        body: JSON.stringify({ email: 'admin@test.com', password: 'password' })
    });
    if (data.success) {
        localStorage.setItem('token', data.data.access_token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
        window.location.href = '/admin';
    } else {
        alert('Admin login failed: ' + (data.message || 'Unknown error'));
    }
}

async function handleRegister() {
    const name = document.getElementById('reg-name').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const passwordConfirmation = document.getElementById('reg-password-confirm').value;

    const data = await apiFetch('/register', {
        method: 'POST',
        body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation })
    });

    if (data.success) {
        alert('Regjistrimi u krye me sukses! Kontrolloni email-in për të verifikuar llogarinë, pastaj hyni.');
        toggleForms();
    } else {
        alert('Gabim: ' + (data.errors ? Object.values(data.errors).flat().join(', ') : data.message));
    }
}
</script>
@endpush
