<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RMS || Change Password</title>
    @include('admin.headerScripts')
</head>

<body>
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div id="toast-success"
                class="z-20 flex items-center w-full max-w-xs p-4 mb-4 mx-auto absolute left-[50%] -translate-x-[30%] text-gray-500 bg-red-50 rounded-lg shadow"
                role="alert">
                <div
                    class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-orange-500 bg-orange-100 rounded-lg dark:bg-orange-700 dark:text-orange-200">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z" />
                    </svg>
                    <span class="sr-only">Warning icon</span>
                </div>
                <div class="ml-3 text-sm font-normal">{{ $error }}</div>
                <button type="button" id="successDismiss"
                    class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8"
                    data-dismiss-target="#toast-success" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endforeach
    @endif
    <div class="h-[100vh] flex items-center justify-center bg-blue-50">
        <div class="lg:w-1/3 md:w-1/2 w-11/12 shadow-2xl rounded-xl shadow-green-800 p-5 bg-white">
            <h1 class="font-bold text-center text-lg mb-5">Badilisha Nywila</h1>

            <!-- Display any session messages for success or errors -->
            @if (Session::has('error'))
                <div class="text-center font-bold text-red-500 mb-3">
                    {{ Session::get('error') }}
                </div>
            @endif

            @if (Session::has('success'))
                <div class="text-center font-bold text-green-500 mb-3">
                    {{ Session::get('success') }}
                </div>
            @endif

            <!-- Change Password Form -->
            <form action="{{ route('updatePassword') }}" method="post">
                @csrf
                <div class="my-3">
                    <label class="block" for="newPassword">Nywila Mpya:<span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input class="block w-full rounded-md p-2 border border-gray-200" type="password"
                            name="password" id="newPassword" placeholder="Ingiza Nywila Mpya" required>
                        <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer" id="eyeNewPassword"
                            onclick="togglePasswordVisibility('newPassword', 'eyeNewPassword')">visibility</i>
                    </div>
                </div>

                <div class="my-3">
                    <label class="block" for="confirmPassword">Thibitisha Nywila Mpya:<span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <input class="block w-full rounded-md p-2 border border-gray-200" type="password"
                            name="confirmPassword" id="confirmPassword" placeholder="Thibitisha Nywila Mpya" required>
                        <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer"
                            id="eyeConfirmPassword"
                            onclick="togglePasswordVisibility('confirmPassword', 'eyeConfirmPassword')">visibility</i>
                    </div>
                </div>

                <div class="my-5">
                    <button type="submit"
                        class="text-black bg-blue-300 hover:bg-blue-400 w-full rounded-md py-2 font-bold">Badilisha
                        Nywila</button>
                </div>
            </form>

            <!-- Optional: Link back to login page -->
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-600">Rudi kwenye Ukurasa wa
                    Kuingia</a>
            </div>
        </div>
    </div>

    <!-- Include necessary scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.textContent = "visibility_off";
            } else {
                input.type = "password";
                icon.textContent = "visibility";
            }
        }
        let dismissBtn = document.getElementById("successDismiss");

        if (dismissBtn != null) {
            setTimeout(() => {
                dismissBtn.click();
            }, 2000);
        }

    </script>
</body>

</html>
