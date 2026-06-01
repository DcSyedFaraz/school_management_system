<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMS || Sign In</title>

    @include('admin.headerScripts')

    <style>
        /* WhatsApp pulse animation */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.6);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 15px 4px rgba(37, 211, 102, 0.4);
            }
        }
        .whatsapp-animate {
            animation: pulse 2s infinite;
        }

        /* Mobile menu animation (smooth & stable) */
        #mobile-menu {
            height: 0;
            overflow: hidden;
            transition: height 0.35s ease-in-out;
        }
        
        .animate-bounce {
        animation: bounce 1.3s infinite;
    }
    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-6px);
        }
    }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-100 via-blue-200 to-blue-300 min-h-screen flex flex-col justify-between">

<!-- HEADER -->
<header class="w-full bg-white/80 backdrop-blur-sm shadow-sm py-2 px-4">
    <div class="flex justify-between items-center">

        <!-- Left contacts -->
        <div class="flex items-center gap-6 text-sm text-gray-700">
        
            <div class="flex items-center gap-1">
                <i class="material-symbols-outlined text-blue-500 text-[20px] leading-none flex items-center">place</i>
                <span class="flex items-center">Dodoma, Tanzania</span>
            </div>
        
            <div class="hidden sm:flex items-center gap-1">
                <i class="material-symbols-outlined text-blue-500 text-[20px] leading-none flex items-center">email</i>
                <a href="mailto:info@rmstechnology.co.tz" class="hover:text-blue-600 flex items-center">
                    info@rmstechnology.co.tz
                </a>
            </div>
        
        </div>

        <!-- Hamburger button -->
        <button id="menu-toggle"
            class="sm:hidden flex items-center justify-center text-gray-700 p-2 rounded-md hover:bg-gray-200 transition">
            <i id="menu-icon"
               class="material-symbols-outlined text-[32px] leading-none">
               menu
            </i>
        </button>
        
        <!-- Desktop menu -->
        <nav class="hidden sm:flex items-center gap-3 text-sm">

            <!-- Phone -->
            <div class="flex items-center gap-2 font-bold text-white text-sm bg-teal-500 px-3 py-1.5 rounded-lg shadow hover:bg-teal-600 transition cursor-pointer whitespace-nowrap">
            <i class="material-symbols-outlined text-teal-700 text-[18px] p-1 rounded-full bg-white">call</i>
            Tupigie:
            <a href="tel:+255786283282" class="hover:underline">0786 283 282</a>
            </div>

            <!-- Menu Items -->
            <div class="flex items-center gap-2 font-bold text-white text-sm bg-teal-500 px-3 py-1.5 rounded-lg shadow hover:bg-blue-600 transition cursor-pointer whitespace-nowrap">
                <a href="{{ url('/index') }}" class="menu-btn flex items-center gap-2">
                    <i class="material-symbols-outlined text-blue-700 text-[18px] p-1 rounded-full bg-blue-100 shadow-sm">home</i>
                    Nyumbani
                </a>
            </div>

            <div class="flex items-center gap-2 font-bold text-white text-sm bg-teal-500 px-3 py-1.5 rounded-lg shadow hover:bg-amber-600 transition cursor-pointer whitespace-nowrap">
                <a href="{{ url('/results') }}" class="menu-btn flex items-center gap-2">
                    <i class="material-symbols-outlined text-amber-700 text-[18px] p-1 rounded-full bg-amber-100 shadow-sm">school</i>
                    Matokeo NECTA
                </a>
            </div>

            <div class="flex items-center gap-2 font-bold text-white text-sm bg-teal-500 px-3 py-1.5 rounded-lg shadow hover:bg-purple-600 transition cursor-pointer whitespace-nowrap">
                <a href="{{ url('/help') }}" class="menu-btn flex items-center gap-2">
                    <i class="material-symbols-outlined text-purple-700 text-[18px] p-1 rounded-full bg-purple-100 shadow-sm">tips_and_updates</i>
                    Msaada
                </a>
            </div>

            <div class="flex items-center gap-2 font-bold text-white text-sm bg-teal-500 px-3 py-1.5 rounded-lg shadow hover:bg-green-600 transition cursor-pointer whitespace-nowrap">
                <a href="{{ url('/mwongozo') }}" class="menu-btn flex items-center gap-2">
                    <i class="material-symbols-outlined text-green-700 text-[18px] p-1 rounded-full bg-green-100 shadow-sm">menu_book</i>
                    Mwongozo
                </a>
            </div>

            <div class="flex items-center gap-2 font-bold text-white text-sm bg-teal-500 px-3 py-1.5 rounded-lg shadow hover:bg-red-600 transition cursor-pointer whitespace-nowrap">
                <a href="{{ url('/index') }}" class="menu-btn flex items-center gap-2">
                    <i class="material-symbols-outlined text-red-700 text-[18px] p-1 rounded-full bg-red-100 shadow-sm">login</i>
                    Ingia
                </a>
            </div>

        </nav>

    </div>

    <!-- MOBILE DROPDOWN MENU -->
    <nav id="mobile-menu" class="sm:hidden w-full flex flex-col gap-2 mt-3 bg-white/90 backdrop-blur-md rounded-lg shadow p-3">

        <a href="tel:+255786283282" class="flex items-center gap-2 text-[16px]">
          <i class="material-symbols-outlined text-teal-700 text-[20px] p-1 rounded-full bg-teal-100 shadow-sm">call</i>
          <span class="whitespace-nowrap">0786 283 282</span>
        </a>

        <a href="{{ url('/index') }}" class="menu-btn-mobile flex items-center gap-2">
            <i class="material-symbols-outlined text-blue-700 text-[20px] p-1.5 rounded-full bg-blue-100 shadow-sm">home</i>
            Nyumbani
        </a>

        <a href="{{ url('/results') }}" class="menu-btn-mobile flex items-center gap-2">
            <i class="material-symbols-outlined text-amber-700 text-[20px] p-1.5 rounded-full bg-amber-100 shadow-sm">school</i>
            Matokeo NECTA
        </a>

        <a href="{{ url('/help') }}" class="menu-btn-mobile flex items-center gap-2">
            <i class="material-symbols-outlined text-purple-700 text-[20px] p-1.5 rounded-full bg-purple-100 shadow-sm">tips_and_updates</i>
            Msaada
        </a>

        <a href="{{ url('/mwongozo') }}" class="menu-btn-mobile flex items-center gap-2">
            <i class="material-symbols-outlined text-green-700 text-[20px] p-1.5 rounded-full bg-green-100 shadow-sm">menu_book</i>
            Mwongozo
        </a>

        <a href="{{ url('/index') }}" class="menu-btn-mobile flex items-center gap-2">
            <i class="material-symbols-outlined text-red-700 text-[20px] p-1.5 rounded-full bg-red-100 shadow-sm">login</i>
            Ingia
        </a>

    </nav>

</header>


<!-- MENU BUTTON CSS -->
<style>
    .menu-btn {
        @apply inline-flex items-center gap-1 font-bold text-white bg-gradient-to-r from-amber-400 to-amber-500 px-4 py-2 rounded-lg transition-all hover:from-blue-500 hover:to-blue-600 active:scale-95;
    }
    .menu-btn-mobile {
        @apply inline-flex items-center gap-2 font-bold text-gray-700 bg-gray-100 px-4 py-2 rounded-lg transition-all hover:bg-gray-200 active:scale-95;
    }
</style>



<!-- MOBILE MENU JS -->
<script>
    const toggleBtn = document.getElementById("menu-toggle");
    const mobileMenu = document.getElementById("mobile-menu");
    const menuIcon = document.getElementById("menu-icon");

    toggleBtn.addEventListener("click", () => {

        // If open → close
        if (mobileMenu.style.height && mobileMenu.style.height !== "0px") {
            mobileMenu.style.height = "0px";
            menuIcon.textContent = "menu";
        }

        // If closed → open
        else {
            mobileMenu.style.height = mobileMenu.scrollHeight + "px";
            menuIcon.textContent = "close";
        }
    });
</script>


<!-- SUBJECTS INTEGRITY BANNER -->
@if (!empty(cache('subjects_integrity_violations', [])))
<div class="w-full max-w-md mx-auto mt-4 px-4">
    <div class="rounded-xl border border-red-400 bg-red-50 shadow p-4 flex items-start gap-3">
        <svg class="h-6 w-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-red-700">Hitilafu ya Mfumo Imegunduliwa</p>
            <p class="text-sm text-red-600 mt-0.5">
                Mfumo una tatizo la usanidi. Tafadhali wasiliana na msaada wa kiufundi:
            </p>
            <a href="https://wa.me/255786283282" target="_blank"
               class="inline-flex items-center gap-1.5 mt-2 text-sm font-semibold text-green-700 hover:text-green-800">
                <i class="fa-brands fa-whatsapp text-lg"></i> +255 786 283 282
            </a>
        </div>
    </div>
</div>
@endif

<!-- MAIN SECTION -->
<main class="flex-1 flex flex-col items-center justify-center px-4">
    <div class="text-center mb-6">
        <h1 class="font-extrabold text-xl md:text-2xl text-gray-800 leading-snug">
            MFUMO WA USIMAMIZI WA MATOKEO <br> (MUM)
        </h1>
        <div class="relative w-fit mx-auto mt-3">

            <!-- LOGO -->
            <img src="{{ asset('img/logo.png') }}" alt="logo" class="w-28 mx-auto drop-shadow-md">
        
        </div>

    </div>

    <div class="w-full max-w-md bg-white/90 backdrop-blur-md rounded-2xl shadow-xl p-6">

        <h2 class="text-center font-bold text-gray-700 mb-4">
            Tafadhali andika Jina la Mtumiaji na Nywila ili kuingia
        </h2>

        @if (Session::has('accessDenied'))
        <div class="alert alert-danger text-center text-red-500 font-bold mb-3">
        {!! nl2br(e(Session::get('accessDenied'))) !!}
        </div>
        @endif


        @if (Session::has('success'))
            <p class="text-center font-semibold text-green-600 mb-3">{{ Session::get('success') }}</p>
        @endif

        <form action="{{ url('/signIn') }}" method="post" class="space-y-4">
            @csrf

            <!-- USERNAME -->
            <div>
                <label for="email" class="block font-medium text-gray-600">
                    Jina la mtumiaji/Email: <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <input type="text" id="email" name="email"
                        value="{{ Cookie::get('email') }}"
                        required placeholder="Ingiza Jina la Mtumiaji/barua pepe"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-400
                               focus:border-blue-400 p-2 pr-8">
                </div>
            </div>

            <!-- PASSWORD -->
            <div>
                <label for="password" class="block font-medium text-gray-600">
                    Nywila: <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <input type="password" id="password" name="password"
                        value="{{ Cookie::get('password') }}"
                        required placeholder="Ingiza Nywila"
                        class="block w-full rounded-md p-2 border border-gray-200">

                    <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer"
                       id="eye"
                       onclick="togglePassword()">visibility</i>
                </div>
            </div>

            <script>
                function togglePassword() {
                    const pwd = document.getElementById("password");
                    const icon = document.getElementById("eye");

                    if (pwd.type === "password") {
                        pwd.type = "text";
                        icon.textContent = "visibility_off";
                    } else {
                        pwd.type = "password";
                        icon.textContent = "visibility";
                    }
                }
            </script>

            <!-- REMEMBER + FORGOT -->
            <div class="flex justify-between items-center text-sm">
                <label class="flex items-center gap-1">
                    <input type="checkbox" name="rememberMe" id="rememberMe" class="rounded-sm">
                    Nikumbuke
                </label>

                <a href="{{ url('/forgotPassword') }}" class="text-blue-500 hover:underline">
                    Nimesahau nywila?
                </a>
            </div>

            <!-- SUBMIT -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500
                       hover:to-blue-600 text-white py-2 rounded-lg font-bold shadow-md transition-all">
                Ingia
            </button>

        </form>
    </div>
</main>

<!-- FOOTER -->
<footer class="footer bg-white/80 backdrop-blur-sm text-sm font-bold shadow-inner
              flex justify-between items-center px-6 h-12">

    <div>Haki zote zimehifadhiwa &copy; 2023 - {{ date('Y') }}</div>

    <div class="flex items-center gap-2 whatsapp-animate">
        <a href="https://wa.me/255786283282" target="_blank"
            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white
                   px-4 py-1.5 rounded-full shadow-md text-sm font-semibold transition-all">
            <i class="fa-brands fa-whatsapp text-xl"></i> Chati Nasi
        </a>
    </div>

</footer>

</body>
</html>
