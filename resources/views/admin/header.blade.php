<div class="w-full p-3 flex justify-between shadow-md bg-white">
    <div class="flex justify-start">
        <div class="text-center">
            <button class="inline-flex items-center p-1 mr-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200" type="button" data-drawer-target="drawer-example" data-drawer-show="drawer-example" aria-controls="drawer-example">
                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                </svg>
            </button>
        </div>

        @if (Session::has('pageTitle'))
            <h1 class="font-bold text-2xl">
                @if (Session::get('pageTitle')=='Admins')
                    <span>Wasimamizi:</span>
                @elseif(Session::get('pageTitle')=='Teachers')
                    <span>Walimu:</span>
                @elseif(Session::get('pageTitle')=='Regions')
                    <span>Mkoa:</span>
                @elseif(Session::get('pageTitle')=='Districts')
                    <span>Wilaya:</span>
                @elseif(Session::get('pageTitle')=='Schools')
                    <span>Shule:</span>
                @elseif(Session::get('pageTitle')=='Wards')
                    <span>Kata:</span>
                @else
                    {{ Session::get('pageTitle') }}:
                @endif
            </h1>
        @endif
    </div>

    <div class="flex justify-end items-center">
        {{-- <div class="flex justify-start mr-3 translate-y-1">
            @if (Session::has('langSelected') && Session::get('langSelected')=='sw')
                @php
                    $checkStatus="checked";
                @endphp
            @else
                @php
                    $checkStatus="";
                @endphp
            @endif

            <div>EN</div>
    
            <div class="mx-2">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" name="langCheckbox" id="langCheckbox" {{ $checkStatus }} onclick="changeLang()">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </label>
            </div>
    
            <div>SW</div>
        </div> --}}
        
        <div class="relative">
            <button id="dropdownAvatarNameButton" data-dropdown-toggle="dropdownAvatarName" class="flex items-center text-sm font-medium text-gray-900 rounded-full hover:text-blue-600 dark:hover:text-blue-500 md:mr-0 focus:ring-4 focus:ring-gray-100" type="button">
                <span class="material-symbols-outlined text-3xl">
                    account_circle
                </span>
            </button>
    
            <!-- Dropdown menu -->
            <div id="dropdownAvatarName" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                <div class="px-4 py-3 text-sm">
                    <div class="font-medium">{{ Session::get('userName') }}</div>
                    <div class="truncate">{{ Session::get('userEmail') }}</div>
                </div>
                <ul class="py-2 text-sm" aria-labelledby="dropdownInformdropdownAvatarNameButtonationButton">
                    <li>
                        <button data-modal-target="changePasswordModal" data-modal-toggle="changePasswordModal" class="block px-4 py-2 w-full text-left">Change Password</button>
                    </li>
                </ul>
                <div class="py-2">
                    <form action="{{ url('/logout') }}" method="post">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-500 font-bold">Sign Out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>