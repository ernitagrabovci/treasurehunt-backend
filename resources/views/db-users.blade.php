<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Database Users</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-brand mb-6">Database Users</h1>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-100">
                <tr class="text-gray-600 text-sm uppercase">
                    <th class="p-3">ID</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Current Level</th>
                    <th class="p-3">Locale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="p-3">{{ $u->id }}</td>
                    <td class="p-3 font-medium">{{ $u->name }}</td>
                    <td class="p-3 text-gray-600">{{ $u->email }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $u->role }}
                        </span>
                    </td>
                    <td class="p-3 font-bold">{{ $u->current_level }}</td>
                    <td class="p-3 text-gray-500">{{ $u->locale }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="text-sm text-gray-400 mt-4">Total: {{ count($users) }} users</p>
</div>
</body>
</html>
