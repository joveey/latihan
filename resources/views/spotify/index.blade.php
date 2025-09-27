@extends('layouts.app')

@section('title', 'Tabel Data Pengguna')

@section('content')
    <header class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Data Pengguna Spotify</h1>
        <p class="mt-1 text-slate-600">Gunakan filter untuk mencari data spesifik di dalam tabel.</p>
    </header>

    {{-- Notifikasi --}}
    <div id="notification-container" class="mb-6">
         @if (session('success'))
            <div class="flex items-center bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
                <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg relative" role="alert">
                <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
         @if (session('validation_errors'))
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <strong class="font-bold">Impor Gagal!</strong>
                        <span class="block sm:inline">Beberapa data tidak valid:</span>
                    </div>
                </div>
                <ul class="mt-3 list-disc list-inside ml-9">
                    @foreach (session('validation_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Tabel dan Filter --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-lg overflow-x-auto">
        <form id="filter-form" method="GET" action="{{ route('spotify.index') }}">
            <div class="p-4 bg-slate-50/50 border-b border-slate-200">
                 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                    <input type="number" name="search[user_id]" placeholder="Cari User ID..." oninput="this.value = this.value.replace(/\D/g, '')" class="col-span-1 lg:col-span-1 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ request('search.user_id') }}">
                    <select name="search[gender]" class="col-span-1 lg:col-span-1 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                         <option value="">Semua Gender</option>
                         @foreach($genders as $gender)
                            <option value="{{ $gender }}" @selected(request('search.gender') == $gender)>{{ $gender }}</option>
                         @endforeach
                    </select>
                    <input type="number" name="search[age]" placeholder="Cari Umur..." oninput="this.value = this.value.replace(/\D/g, '')" class="col-span-1 lg:col-span-1 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ request('search.age') }}">
                    <select name="search[country]" class="col-span-1 lg:col-span-1 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Negara</option>
                         @foreach($countries as $country)
                            <option value="{{ $country }}" @selected(request('search.country') == $country)>{{ $country }}</option>
                         @endforeach
                    </select>
                    <select name="search[subscription_type]" class="col-span-1 lg:col-span-1 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Tipe</option>
                        @foreach($subscriptions as $subscription)
                            <option value="{{ $subscription }}" @selected(request('search.subscription_type') == $subscription)>{{ $subscription }}</option>
                        @endforeach
                    </select>
                    <select name="search[device_type]" class="col-span-1 lg:col-span-1 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Device</option>
                        @foreach($devices as $device)
                            <option value="{{ $device }}" @selected(request('search.device_type') == $device)>{{ $device }}</option>
                        @endforeach
                    </select>
                     <select name="search[is_churned]" class="col-span-1 lg:col-span-1 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="1" @selected(request('search.is_churned') == '1')>Yes</option>
                        <option value="0" @selected(request('search.is_churned') == '0')>No</option>
                    </select>
                    <div class="col-span-1 flex items-center gap-2">
                        <button type="submit" class="p-2 w-full bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </button>
                         <a href="{{ route('spotify.index') }}" class="p-2 w-full bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300" title="Reset Filters">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4 20l16-16" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <table class="w-full whitespace-nowrap">
                <thead class="bg-slate-50 text-left">
                     <tr class="border-b-2 border-slate-200">
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500 tracking-wider text-center">User ID</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500 tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500 tracking-wider text-center">Age</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500 tracking-wider">Country</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500 tracking-wider">Subscription</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500 tracking-wider">Device</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500 tracking-wider">Churned</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($spotifyUsers as $user)
                        <tr class="hover:bg-indigo-50 even:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 text-sm text-slate-700 font-medium text-center">{{ $user->user_id }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $user->gender }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700 text-center">{{ $user->age }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $user->country }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $user->subscription_type }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $user->device_type }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_churned ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $user->is_churned ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <span class="font-semibold">Tidak ada data ditemukan.</span>
                                    <span class="text-sm">Coba sesuaikan filter pencarian Anda.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

    {{-- Paginasi --}}
    <div class="mt-6">
        {{ $spotifyUsers->appends(request()->input())->links() }}
    </div>
@endsection

@section('modal')
    {{-- Modal Preview Anda --}}
    <div id="preview-modal" class="fixed inset-0 bg-slate-900 bg-opacity-60 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-2xl font-semibold text-slate-900">Preview Data Unggahan</h3>
                <button id="close-modal-button" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto">
                <div id="preview-table-container" class="overflow-x-auto"></div>
            </div>
            <div class="p-6 border-t border-slate-200 flex justify-end gap-4 bg-slate-50 rounded-b-lg">
                <button id="cancel-button" class="px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg font-semibold text-sm hover:bg-slate-50 transition">Batal</button>
                <button id="confirm-button" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700 disabled:opacity-50 transition">
                    Konfirmasi & Simpan
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Script untuk upload dan preview Anda --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const uploadForm = document.getElementById('upload-form');
            const fileInput = document.getElementById('file-input');
            const modal = document.getElementById('preview-modal');
            const closeModalButton = document.getElementById('close-modal-button');
            const cancelButton = document.getElementById('cancel-button');
            const confirmButton = document.getElementById('confirm-button');
            const tableContainer = document.getElementById('preview-table-container');
            const notificationContainer = document.getElementById('notification-container');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Listener untuk form upload yang tersembunyi di header
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    // Otomatis submit form untuk preview saat file dipilih
                    uploadForm.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            });

            uploadForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (!fileInput.files.length) {
                    showNotification('Pilih file XLSX terlebih dahulu!', 'error');
                    return;
                }

                const formData = new FormData(this);

                try {
                    const response = await fetch("{{ route('spotify.preview') }}", {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        let errorMsg = result.message || 'Terjadi kesalahan saat memproses file.';
                        if (result.errors && result.errors.file) {
                            errorMsg = result.errors.file[0];
                        }
                        showNotification(errorMsg, 'error');
                        return;
                    }

                    let table = '<table class="w-full whitespace-nowrap"><thead><tr class="text-left">';
                    result.headings.forEach(header => {
                        table += `<th class="px-6 py-3 bg-slate-50 text-xs font-semibold uppercase text-slate-500 tracking-wider">${header.replace(/_/g, ' ')}</th>`;
                    });
                    table += '</tr></thead><tbody class="divide-y divide-slate-200">';
                    result.data.forEach(row => {
                        table += '<tr class="hover:bg-slate-50">';
                        result.headings.forEach(header => {
                            table += `<td class="px-6 py-4 text-sm text-slate-700">${row[header] || ''}</td>`;
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
                    uploadForm.reset();
                }
            });

            confirmButton.addEventListener('click', async function () {
                this.disabled = true;
                this.textContent = 'Menyimpan...';

                try {
                    const response = await fetch("{{ route('spotify.store') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    });

                    if (!response.ok) {
                        const result = await response.json();
                        let errorMsg = 'Gagal menyimpan data.';
                        if (result.validation_errors) {
                            const errorList = '<ul>' + result.validation_errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
                            showNotification(`<strong>Impor Gagal!</strong><br>${errorList}`, 'error');
                        } else if (result.error) {
                            showNotification(result.error, 'error');
                        }
                        throw new Error('Store operation failed');
                    }
                    window.location.reload();
                } catch (error) {
                    console.error('Error during store:', error);
                    this.disabled = false;
                    this.textContent = 'Konfirmasi & Simpan';
                    modal.classList.add('hidden');
                }
            });

            function closePreviewModal() {
                modal.classList.add('hidden');
                tableContainer.innerHTML = '';
            }

            cancelButton.addEventListener('click', closePreviewModal);
            closeModalButton.addEventListener('click', closePreviewModal);

            function showNotification(message, type = 'success') {
                notificationContainer.innerHTML = ''; // Clear previous notifications
                let icon, colors;

                if (type === 'error') {
                    colors = 'bg-red-50 border-red-300 text-red-800';
                    icon = `<svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                } else {
                    colors = 'bg-green-50 border-green-300 text-green-800';
                    icon = `<svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                }

                const notification = `
                    <div class="${colors} flex items-center px-4 py-3 rounded-lg relative" role="alert">
                        ${icon}
                        <span class="block sm:inline">${message}</span>
                    </div>
                `;
                notificationContainer.innerHTML = notification;

                setTimeout(() => {
                    if (notificationContainer.firstChild) {
                        notificationContainer.firstChild.remove();
                    }
                }, 7000);
            }
        });
    </script>
@endpush