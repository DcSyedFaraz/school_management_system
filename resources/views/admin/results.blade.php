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


    <!-- Main Section -->
    <section class="flex-grow flex flex-col items-center justify-center px-4 py-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Matokeo ya NECTA</h2>

        <div class="grid md:grid-cols-2 gap-8 result-link-container max-w-4xl w-full">
            

            <!-- Darasa la Nne (IV) -->
<div class="bg-white shadow-lg rounded-2xl p-8 text-center">
    <h3 class="text-2xl font-bold text-gray-700 mb-4">Matokeo Darasa la Nne (IV)</h3>
    
    <!-- Link ya matokeo ya miaka mitatu -->
    <div class="flex flex-col space-y-3 mt-4">
        <a href="https://matokeo.necta.go.tz/results/2025/sfna/sfna.htm" 
           class="block bg-blue-100 hover:bg-orange-500 text-orange-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2025
        </a>
        <a href="https://onlinesys.necta.go.tz/results/2024/sfna/sfna.htm" 
           class="block bg-blue-100 hover:bg-blue-500 text-blue-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2024
        </a>
        <a href="https://onlinesys.necta.go.tz/results/2023/sfna/sfna.htm" 
           class="block bg-green-100 hover:bg-green-500 text-green-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2023
        </a>
        <a href="https://onlinesys.necta.go.tz/results/2022/sfna/sfna.htm" 
           class="block bg-yellow-100 hover:bg-yellow-500 text-yellow-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2022
        </a>
    </div>
</div>


            
<div class="bg-white shadow-lg rounded-2xl p-8 text-center">
    <h3 class="text-2xl font-bold text-gray-700 mb-4">Matokeo Darasa la Saba (VII)</h3>
    
    <!-- Link ya matokeo ya miaka mitatu -->
    <div class="flex flex-col space-y-3 mt-4">
        <a href="https://matokeo.necta.go.tz/results/2025/psle/psle.htm" 
           class="block bg-blue-100 hover:bg-orange-500 text-orange-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2025
        </a>
        <a href="https://onlinesys.necta.go.tz/results/2024/psle/psle.htm" 
           class="block bg-blue-100 hover:bg-blue-500 text-blue-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2024
        </a>
        <a href="https://onlinesys.necta.go.tz/results/2023/psle/psle.htm" 
           class="block bg-green-100 hover:bg-green-500 text-green-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2023
        </a>
        <a href="https://onlinesys.necta.go.tz/results/2022/psle/psle.htm" 
           class="block bg-yellow-100 hover:bg-yellow-500 text-yellow-800 hover:text-white rounded-md p-3 transition font-semibold">
           Matokeo 2022
        </a>
    </div>
</div>

            </a>
        </div>
    </section>

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
