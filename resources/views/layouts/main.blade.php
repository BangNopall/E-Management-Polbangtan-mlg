<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="Management Asrama Polbangtan-mlg merupakan aplikasi menejemen asrama Politeknik Pembangunan dan pertanian malang">
    <link rel="canonical" href="https://asramapolbangtan-mlg.com"/>
    <meta property="og:site_name" content="Management Asrama Polbangtan-mlg">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://asramapolbangtan-mlg.com">
    <meta property="og:title" content="Management Asrama Polbangtan-mlg">
    <meta property="og:description" content="Management Asrama Polbangtan-mlg merupakan aplikasi menejemen asrama Politeknik Pembangunan dan pertanian malang">
    <meta property="og:image" content="{{ asset('img/logo-asrama.png') }}">
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@Polbangtanmlg" />
    <meta name="twitter:title" content="Management Asrama Polbangtan-mlg " />
    <meta name="twitter:description" content="Management Asrama Polbangtan-mlg merupakan aplikasi menejemen asrama Politeknik Pembangunan dan pertanian malang" />
    <meta name="twitter:creator" content="@Polbangtanmlg" />
    <meta name="author" content="Noxvall, OmTegar">
    <meta name="keywords" content="absen, e-absen, management, menejemen, system, sistem, polbangtan, malang, politeknik, elektronik, mahasiswa, kampus">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard - Management Asrama</title>
    <link rel="shortcut icon" href="{{ asset('img/logo-asrama.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link href="{{ asset('css/library/flowbite.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/remixIcon/fonts/remixicon.css') }}">
    @vite('resources/css/app.css')
</head>
<body class="text-gray-800 font-inter transit">
    {{-- peringatan keamanan sesi --}}
    <div class="fixed inset-0 flex items-center bg-black/60 justify-center z-50 transition-all hidden">
        <div class="bg-white shadow-xl rounded p-5 w-[90%] md:w-[500px]">
            <h1 class="text-xl font-semibold text-center">Peringatan Keamanan</h1>
            <p class="mt-2 text-center text-sm">Sesi aplikasi anda telah habis, harap perbaruhi sesi dengan menekan tombol dibawah</p>
            <div class="flex flex-col md:flex-row text-center justify-center mt-2 gap-2">
                <a href="" class="py-1 px-2 bg-green-500 rounded text-white">Perbarui sesi</a>
                <a href="" class="py-1 px-2 bg-red-500 rounded text-white">Logout</a>
            </div>
        </div>
    </div>
    <div id="preloader" class="fixed inset-0 flex items-center bg-gray-200 justify-center z-50 transition-all">
        <div role="status">
            <svg aria-hidden="true" class="w-12 h-12 mr-2 text-gray-500 animate-spin fill-utama" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <main class="w-full md:w-[calc(100%-256px)] md:ml-64 bg-gray-50 min-h-screen transition-all main active">
        @include('partials.headnav')
        @include('partials.nav')
        @yield('container')
    </main>
</body>
<script src="{{ asset('js/library/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/library/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/library/flowbite.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/library/axios.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/script.js') }}"></script>
</html>