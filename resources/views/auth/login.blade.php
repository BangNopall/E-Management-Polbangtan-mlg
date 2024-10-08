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
    <meta name="author" content="Noxvall, Tegar">
    <meta name="keywords" content="absen, e-absen, polbangtan, malang, politeknik, elektronik, mahasiswa, kampus">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Management Asrama Polbangtan-mlg</title>
    <link rel="icon" href="{{ asset('img/logo-asrama.png') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    @vite('resources/css/app.css')

</head>

<body>
    <div class=" bg-gray-200 px-6 h-screen">
        <div class="container ml-0 md:ml-5 flex justify-between">
            <!-- Logo dan Nama -->
            <div class="mt-4 flex items-center">
                <a href="" class="flex items-center">
                    <img src="{{ asset('img/logo-asrama.png') }}" alt="Logo" class="h-10 w-10 mr-2 rounded-xl">
                    <div class="text-gray-700 text-xl font-bold">Management Asrama Polbangtan-mlg</div>
                </a>
            </div>
        </div>
        <div class="flex justify-center items-center mt-[10%]">
            <div class="p-6 max-w-sm w-full bg-white shadow-md rounded-md">
                <div class="flex justify-center items-center">
                    <img src="{{ asset('img/logo-asrama.png') }}" alt="Logo" class="h-10 w-10 mr-2 rounded">
                    <span class="text-gray-700 text-center font-semibold text-2xl">Dashboard Management</span>
                </div>

                @if (session()->has('success'))
                    <div id="alert-3" class="flex my-2 items-center p-4 text-green-800 rounded-lg bg-green-200"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <span class="sr-only">Info</span>
                        <div class="ml-3 text-sm font-medium">
                            {{ session('success') }}
                        </div>
                        <button type="button"
                            class="ml-auto -mx-1.5 -my-1.5 bg-green-200 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8"
                            data-dismiss-target="#alert-3" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div id="alert-2" class="flex my-2 items-center p-4 text-red-800 rounded-lg bg-red-200" role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <span class="sr-only">Info</span>
                        <div class="ml-3 text-sm font-medium">
                            {{ session('error') }}
                        </div>
                        <button type="button"
                            class="ml-auto -mx-1.5 -my-1.5 bg-red-200 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8"
                            data-dismiss-target="#alert-2" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                @endif
                <form action="{{ route('auth.login') }}" method="POST">
                    @csrf
                    <label class="block">
                        <span class="text-gray-700 text-sm">Email</span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="form-input mt-1 block w-full p-1 rounded-md border-2 shadow border-utama">
                        @error('email')
                            <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="block mt-3">
                        <span class="text-gray-700 text-sm">Password</span>
                        <div class="relative">
                            <input type="password" id="passwordInput" name="password" value="{{ old('password') }}"
                                class="form-input mt-1 block w-full p-1 rounded-md border-2 shadow border-utama">
                            <button id="togglePassword" type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-500 cursor-pointer">
                                <i id="showEye" class="ri-eye-line text-lg text-black"></i>
                        </div>
                        @error('password')
                            <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
                        @enderror
                    </label>
                    <div class="flex items-center mt-4">
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="remember" name="remember" class="form-checkbox text-utama" checked required>
                                <span class="mx-2 text-gray-600 text-sm">Remember me</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button
                            class="py-2 px-4 text-center bg-utama rounded-md w-full text-white text-sm hover:bg-teal-800 transition-all"
                            type="submit">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</body>
<script src="{{ asset('js/login.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js"></script>

</html>
