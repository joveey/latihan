<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Spotify Churn Data</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 font-sans antialiased">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Spotify User Churn Data</h1>

        <div id="notification-container">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
             @if (session('validation_errors'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Impor Gagal!</strong>
                <span class="block sm:inline">Beberapa data tidak valid:</span>
                <ul class="mt-2 list-disc list-inside">
                    @foreach (session('validation_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Unggah & Unduh Data (XLSX)</h2>
            <div class="flex justify-between items-center">
                <form id="upload-form" action="{{ route('spotify.preview') }}" method="POST" enctype="multipart/form-data" class="flex items-center">
                    @csrf
                    <input type="file" name="file" id="file-input" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100" required>
                    <button type="submit" id="upload-button" class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 disabled:opacity-50 transition">
                        Unggah
                    </button>
                </form>
                <a href="{{ route('spotify.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700">
                    Unduh Data
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-x-auto">
            <form method="GET" action="{{ route('spotify.index') }}">
                <table class="w-full whitespace-nowrap">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-sm">
                        <tr class="text-left font-bold">
                            <th class="px-6 py-3">User ID</th>
                            <th class="px-6 py-3">Gender</th>
                            <th class="px-6 py-3">Age</th>
                            <th class="px-6 py-3">Country</th>
                            <th class="px-6 py-3">Subscription</th>
                            <th class="px-6 py-3">Device</th>
                            <th class="px-6 py-3">Churned</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-4 py-2"><input type="text" name="search[user_id]" placeholder="Cari User ID..." class="w-full px-2 py-1.5 border rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-sm" value="{{ request('search.user_id') }}"></td>
                            <td class="px-4 py-2">
                                <select name="search[gender]" class="w-full px-2 py-1.5 border rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                    <option value="">Semua Gender</option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender }}" @selected(request('search.gender') == $gender)>{{ $gender }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2"><input type="text" name="search[age]" placeholder="Cari Umur..." class="w-full px-2 py-1.5 border rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-sm" value="{{ request('search.age') }}"></td>
                            <td class="px-4 py-2">
                                <select name="search[country]" class="w-full px-2 py-1.5 border rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                    <option value="">Semua Negara</option>
                                     @foreach($countries as $country)
                                        <option value="{{ $country }}" @selected(request('search.country') == $country)>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <select name="search[subscription_type]" class="w-full px-2 py-1.5 border rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                    <option value="">Semua Tipe</option>
                                     @foreach($subscriptions as $subscription)
                                        <option value="{{ $subscription }}" @selected(request('search.subscription_type') == $subscription)>{{ $subscription }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                 <select name="search[device_type]" class="w-full px-2 py-1.5 border rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                    <option value="">Semua Device</option>
                                     @foreach($devices as $device)
                                        <option value="{{ $device }}" @selected(request('search.device_type') == $device)>{{ $device }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <select name="search[is_churned]" class="w-full px-2 py-1.5 border rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                    <option value="">Semua Status</option>
                                    <option value="1" @selected(request('search.is_churned') == '1')>Yes</option>
                                    <option value="0" @selected(request('search.is_churned') == '0')>No</option>
                                </select>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <button type="submit" class="p-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($spotifyUsers as $user)
                            <tr>
                                <td class="px-6 py-4">{{ $user->user_id }}</td>
                                <td class="px-6 py-4">{{ $user->gender }}</td>
                                <td class="px-6 py-4">{{ $user->age }}</td>
                                <td class="px-6 py-4">{{ $user->country }}</td>
                                <td class="px-6 py-4">{{ $user->subscription_type }}</td>
                                <td class="px-6 py-4">{{ $user->device_type }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_churned ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_churned ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center">Tidak ada data ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
        <div class="mt-6">
            {{ $spotifyUsers->appends(request()->input())->links() }}
        </div>
    </div>

    <div id="preview-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col">
            <div class="p-6 border-b">
                <h3 class="text-2xl font-semibold">Preview Data Unggahan</h3>
            </div>
            <div class="p-6 overflow-y-auto">
                <div id="preview-table-container" class="overflow-x-auto"></div>
            </div>
            <div class="p-6 border-t flex justify-end gap-4">
                <button id="cancel-button" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-md">Batal</button>
                <button id="confirm-button" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                    Konfirmasi & Simpan
                </button>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.getElementById('upload-form');
    const fileInput = document.getElementById('file-input');
    const uploadButton = document.getElementById('upload-button');
    const modal = document.getElementById('preview-modal');
    const cancelButton = document.getElementById('cancel-button');
    const confirmButton = document.getElementById('confirm-button');
    const tableContainer = document.getElementById('preview-table-container');
    const notificationContainer = document.getElementById('notification-container');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    uploadForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!fileInput.files.length) {
            alert('Pilih file terlebih dahulu!');
            return;
        }

        uploadButton.disabled = true;
        uploadButton.textContent = 'Memproses...';
        const formData = new FormData(this);

        try {
            const response = await fetch("{{ route('spotify.preview') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });

            const result = await response.json();

            if (!response.ok) {
                let errorMsg = result.message || 'Terjadi kesalahan.';
                if (result.errors && result.errors.file) {
                    errorMsg = result.errors.file[0];
                }
                showNotification(errorMsg, 'error');
                return;
            }

            let table = '<table class="w-full whitespace-nowrap"><thead><tr class="text-left font-bold">';
            result.headings.forEach(header => {
                table += `<th class="px-6 py-3 bg-gray-50 dark:bg-gray-700">${header.replace(/_/g, ' ').toUpperCase()}</th>`;
            });
            table += '</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">';
            result.data.forEach(row => {
                table += '<tr>';
                result.headings.forEach(header => {
                    table += `<td class="px-6 py-4">${row[header] || ''}</td>`;
                });
                table += '</tr>';
            });
            table += '</tbody></table>';

            tableContainer.innerHTML = table;
            modal.classList.remove('hidden');

        } catch (error) {
            console.error('Error during preview:', error);
            showNotification('Tidak dapat terhubung ke server atau terjadi kesalahan script.', 'error');
        } finally {
            uploadButton.disabled = false;
            uploadButton.textContent = 'Unggah';
            fileInput.value = '';
        }
    });

    confirmButton.addEventListener('click', async function () {
        this.disabled = true;
        this.textContent = 'Menyimpan...';

        try {
            const response = await fetch("{{ route('spotify.store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });

            const result = await response.json();

            if (!response.ok) {
                let errorMsg = 'Gagal menyimpan data.';
                if (result.validation_errors) {
                    errorMsg = result.validation_errors.join('<br>');
                } else if (result.error) {
                    errorMsg = result.error;
                }
                showNotification(errorMsg, 'error');
                throw new Error(errorMsg);
            }
            window.location.reload();
        } catch (error) {
            console.error('Error during store:', error);
        } finally {
            this.disabled = false;
            this.textContent = 'Konfirmasi & Simpan';
            modal.classList.add('hidden');
        }
    });

    cancelButton.addEventListener('click', function () {
        modal.classList.add('hidden');
        tableContainer.innerHTML = '';
    });

    function showNotification(message, type = 'success') {
        const bgColor = type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
        const notification = `
            <div class="${bgColor} border px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">${message}</span>
            </div>
        `;
        notificationContainer.innerHTML = notification;
    }
});
</script>

</body>
</html>