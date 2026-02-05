<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RMS</title>
    @include('admin.headerScripts')
</head>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }
</style>

<body>
    <div class="h-[100vh]">
        <div class="flex justify-start min-h-screen">
            @include('admin.sidebar')

            <div class="w-full bg-[#f0f0f0]">
                @include('admin.header')

                @if (Session::has('success'))
                    <div id="toast-success"
                        class="z-20 flex items-center w-full max-w-xs p-4 mb-4 mx-auto absolute left-[50%] -translate-x-[30%] text-gray-500 bg-green-50 rounded-lg shadow"
                        role="alert">
                        <div
                            class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                            </svg>
                            <span class="sr-only">Check icon</span>
                        </div>
                        <div class="ml-3 text-sm font-normal">{{ Session::get('success') }}</div>
                        <button type="button" id="successDismiss"
                            class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8"
                            data-dismiss-target="#toast-success" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if (Session::has('error'))
                    <div id="toast-success"
                        class="z-20 flex items-center w-full max-w-xs p-4 mb-4 mx-auto absolute left-[50%] -translate-x-[30%] text-gray-500 bg-red-50 rounded-lg shadow"
                        role="alert">
                        <div
                            class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-orange-500 bg-orange-100 rounded-lg dark:bg-orange-700 dark:text-orange-200">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z" />
                            </svg>
                            <span class="sr-only">Warning icon</span>
                        </div>
                        <div class="ml-3 text-sm font-normal">{{ Session::get('error') }}</div>
                        <button type="button" id="successDismiss"
                            class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8"
                            data-dismiss-target="#toast-success" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                @endif

                <div>
                    @yield('content')
                </div>
            </div>
        </div>

        @include('modals.changePasswordModal')
    </div>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.7.0/flowbite.min.js"></script>

<script>
    $(document).ready(function() {
        $('.myTable').DataTable({
            "lengthMenu": [
                [10, 25, 50, 100, 250, 500],
                [10, 25, 50, 100, 250, 500]
            ]
        });
    });

    function changeLang() {
        var lang = ($('#langCheckbox').prop('checked')) ? "sw" : "en";

        $.ajax({
            type: "GET",
            url: `{{ url('/changeLang') }}/${lang}`,
            success: function($response) {
                if ($response.status == 200) {
                    location.reload();
                }
            }
        });
    }

    let dismissBtn = document.getElementById("successDismiss");

    if (dismissBtn != null) {
        setTimeout(() => {
            dismissBtn.click();
        }, 2000);
    }

    function showText(inputName, divName) {
        let inputType = $(`#${inputName}`).attr('type');
        let inputText = $(`#${divName}`).text();

        let changeType = (inputType == 'text') ? "password" : "text";
        let changeText = (inputText == 'visibility') ? "visibility_off" : "visibility";

        $(`#${inputName}`).attr('type', changeType);
        $(`#${divName}`).text(changeText);
    }
</script>
@yield('scripts')
</html>
