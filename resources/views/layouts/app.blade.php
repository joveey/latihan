<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Ambil judul dari halaman spesifik, dengan fallback --}}
    <title>@yield('title', 'Spotify Analytics')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Tambahkan style kustom jika ada --}}
    @stack('styles')
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        /* Style untuk link navigasi yang aktif */
        .nav-link-active {
            background-color: #4f46e5; /* indigo-600 */
            color: white;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div id="app">
        {{-- Header dan Navigasi --}}
        <header class="bg-white shadow-md sticky top-0 z-40">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                {{-- ========================================================= --}}
                {{-- PERUBAHAN STRUKTUR FLEXBOX DI SINI --}}
                {{-- ========================================================= --}}
                <div class="flex items-center justify-between h-16">
                    
                    {{-- Bagian Kiri: Hanya Logo --}}
                    <div>
                        <a href="{{ route('spotify.index') }}" class="flex-shrink-0 font-bold text-xl text-indigo-600">
                            🎵 Spotify Analytics
                        </a>
                    </div>

                    {{-- Bagian Tengah: Navigasi Halaman --}}
                    <nav class="hidden md:flex md:space-x-4">
                        <a href="{{ route('spotify.index') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('spotify.index') ? 'nav-link-active' : 'text-slate-700 hover:bg-slate-100' }}">
                            Tabel Data
                        </a>
                        <a href="{{ route('spotify.dashboard') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('spotify.dashboard') ? 'nav-link-active' : 'text-slate-700 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                    </nav>

                    {{-- Bagian Kanan: Tombol Aksi (Unggah & Unduh) --}}
                    <div class="flex items-center space-x-2">
                        <button onclick="document.getElementById('file-input').click()" class="hidden sm:inline-flex items-center px-3 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Unggah Data
                        </button>
                        {{-- Form tersembunyi untuk upload --}}
                        <form id="upload-form" action="{{ route('spotify.preview') }}" method="POST" enctype="multipart/form-data" class="hidden">
                            @csrf
                            <input type="file" name="file" id="file-input" required accept=".xlsx,.xls,.csv">
                        </form>

                        <a href="{{ route('spotify.export', request()->query()) }}" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-green-700 transition">
                             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{-- Konten Utama Halaman --}}
        <main class="container mx-auto p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    {{-- Modal Preview (jika ada di halaman) --}}
    @yield('modal')

    {{-- Memuat script dari halaman spesifik --}}
    @stack('scripts')
</body>
</html>