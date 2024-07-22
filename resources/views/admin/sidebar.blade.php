<div id="drawer-example" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-[300px]" tabindex="-1" aria-labelledby="drawer-label">
    <h5 id="drawer-label" class="inline-flex items-center mb-3 text-base font-semibold">
        <div>
            <img class="w-[50px]" src="{{ asset('img/logo.png') }}" alt="logo">
        </div>

        <div class="ml-3">
            <b class="text-blue-500 text-3xl">RMS</b>
        </div>
    </h5>

    <button type="button" data-drawer-hide="drawer-example" aria-controls="drawer-example" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 flex items-center justify-center" >
       <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
       </svg>
       <span class="sr-only">Close menu</span>
    </button>

    @if (Session::get('userType')=='A')
        <div>
            <a href="{{ url('/admin-dashboard') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    speed
                </span>

                <p class="inline-block -translate-y-[2px]">Ubao</p>
            </a>

            <a href="{{ url('/admin-dashboard/reports') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    lab_profile
                </span>

                <p class="inline-block -translate-y-[2px]">Matokeo</p>
            </a>

            <a href="{{ url('/admin-dashboard/student-data') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    lab_profile
                </span>

                <p class="inline-block -translate-y-[2px]">Matokeo Kiwanafunzi</p>
            </a>

            <a href="{{ url('/dashboard/detailed-report') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    lab_profile
                </span>

                <p class="inline-block -translate-y-[2px]">PSLE/SFNA Ripoti</p>
            </a>

            <a href="{{ url('/admin-dashboard/subject-report') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    lab_profile
                </span>

                <p class="inline-block -translate-y-[2px]">Kimasomo Ripoti</p>
            </a>

            <button id="dropdownDefaultButton" data-dropdown-toggle="dropdown" class="hover:bg-blue-50 w-full focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg px-5 py-2.5 flex items-center justify-between" type="button">
                <p>
                    <span class="material-symbols-outlined inline-block -translate-x-2">
                        group
                    </span>
                    <span class="font-bold -translate-y-1 inline-block">Watumiaji</span>
                </p>

                <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownDefaultButton">
                    <li>
                        <a href="{{ url('/admin-dashboard/admins') }}" class="block px-4 py-2 hover:bg-gray-100">Wasimamizi</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin-dashboard/teachers') }}" class="block px-4 py-2 hover:bg-gray-100">Walimu</a>
                    </li>
                </ul>
            </div>

            <button id="dropdownDefaultButton" data-dropdown-toggle="masterData" class="hover:bg-blue-50 w-full focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg px-5 py-2.5 flex items-center justify-between" type="button">
                <p>
                    <span class="material-symbols-outlined inline-block -translate-x-2">
                        folder_open
                    </span>
                    <span class="font-bold -translate-y-1 inline-block">Taarifa</span>
                </p>

                <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div id="masterData" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownDefaultButton">
                    <li>
                        <a href="{{ url('/admin-dashboard/regions') }}" class="block px-4 py-2 hover:bg-gray-100">Mkoa</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin-dashboard/districts') }}" class="block px-4 py-2 hover:bg-gray-100">Wilaya</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin-dashboard/wards') }}" class="block px-4 py-2 hover:bg-gray-100">Kata</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin-dashboard/schools') }}" class="block px-4 py-2 hover:bg-gray-100">Shule</a>
                    </li>
                </ul>
            </div>
        </div>
    @else
        <div>
            <a href="{{ url('/dashboard') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    speed
                </span>

                <p class="inline-block -translate-y-[2px]">Ubao</p>
            </a>

            <a href="{{ url('/dashboard/reports') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    lab_profile
                </span>

                <p class="inline-block -translate-y-[2px]">Matokeo</p>
            </a>

            <a href="{{ url('/dashboard/teacher-detailed-report') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    lab_profile
                </span>

                <p class="inline-block -translate-y-[2px]">PSLE/SFNA Ripoti</p>
            </a>

            <a href="{{ url('/dashboard/teacher-subject-report') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    lab_profile
                </span>

                <p class="inline-block -translate-y-[2px]">Kimasomo Ripoti</p>
            </a>

            <a href="{{ url('/dashboard/uploads') }}" class="rounded-2xl hover:bg-blue-50 p-3 w-full font-bold my-2 block">
                <span class="material-symbols-outlined mr-1 translate-y-1">
                    upload
                </span>

                <p class="inline-block -translate-y-[2px]">Pandisha Faili</p>
            </a>
        </div>
    @endif

    <div class="absolute bottom-0 py-3">
        <div>
            <b>Kwa Msaada Piga:</b>
            <a class="text-blue-500 hover:text-blue-600 hover:underline italic block" href="tel:+255 736 102 030">+255 736 102 030</a>
            <a class="text-blue-500 hover:text-blue-600 hover:underline italic block" href="tel:+255 786 283 282">+255 786 283 282</a>
        </div>

        <div class="mt-2">
            <b>Muda:</b>
            <p class="italic">02:00 asb - 11:00 jion</p>
        </div>
        <div class="mt-2">
            <p class=" text-blue-500 hover:text-blue-600 italic"><b>Hati Miliki © 2024</b></p>
        </div>
    </div>
</div>
