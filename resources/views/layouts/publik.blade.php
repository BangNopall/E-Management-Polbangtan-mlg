<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Verifikasi Keabsahan Surat Perizinan Asrama')</title>
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans min-h-screen flex flex-col justify-between antialiased">
    <!-- Header Publik Ringkas -->
    <header class="bg-teal-800 text-white py-3 px-4 shadow-sm">
        <div class="max-w-xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <img src="{{ asset('img/logo-asrama2.jpeg') }}" alt="Logo" class="w-8 h-8 rounded object-cover">
                <div>
                    <h1 class="text-sm font-bold leading-tight">Management Asrama</h1>
                    <p class="text-[10px] text-teal-200">Politeknik Pembangunan Pertanian Malang</p>
                </div>
            </div>
            <span class="text-[10px] bg-teal-900 text-teal-200 px-2 py-0.5 rounded font-semibold border border-teal-700">
                Sistem Verifikasi Publik
            </span>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 max-w-xl w-full mx-auto p-4">
        @yield('container')
    </main>

    <!-- Footer Publik Ringkas -->
    <footer class="bg-white border-t border-gray-200 py-3 text-center text-xs text-gray-500">
        <div class="max-w-xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} Polbangtan Malang. Hak Cipta Dilindungi Undang-Undang.</p>
        </div>
    </footer>
</body>
</html>
