<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RMS || Sign In</title>
    @include('admin.headerScripts')
</head>
<body>
    <div class="h-[100vh] relative">
        <div class="h-full">
            <div class="bg-blue-50 h-full">
                <div class="flex items-center h-full">
                    <div class="w-full">
                        <div class="text-center mb-5">
                            <div>
                                <div class="font-bold mb-2">MFUMO WA USIMAMIZI WA MATOKEO</div>
                                <img class="w-[100px] mx-auto" src="{{ asset('img/logo.png') }}" alt="logo">
                            </div>
                        </div>

                        <div class="lg:w-3/12 md:w-1/2 w-11/12 shadow-2xl rounded-xl shadow-green-800 p-5 mx-auto bg-white">
                            <h1 class="font-bold text-center text-md mb-3">Tafadhali Andika E-mail Na Nywila Ili Kuingia</h1>

                            @if (Session::has('accessDenied'))
                                <div class="text-center font-bold text-red-500">
                                    {{ Session::get('accessDenied') }}
                                </div>
                            @endif

                            @if (Session::has('success'))
                                <div class="text-center font-bold text-green-500">
                                    {{ Session::get('success') }}
                                </div>
                            @endif

                            <form action="{{ url('/signIn') }}" method="post">
                                @csrf
                                <div>
                                    <div class="my-3">
                                        <label class="block" for="email">Barua Pepe:<span class="text-red-500">*</span></label>
                                        <input class="block w-full rounded-md p-2 border border-gray-200" type="text" name="email" id="email" placeholder="Ingiza Barua Pepe" value="{{ Cookie::get('email') }}" required>
                                    </div>

                                    <div class="my-3">
                                        <label class="block" for="password">Nywila:<span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input class="block w-full rounded-md p-2 border border-gray-200" type="password" name="password" id="password" placeholder="Ingiza Nywila" value="{{ Cookie::get('password') }}" required>
                                            <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer" id="eye" onclick="showText('password', 'eye')">visibility</i>
                                        </div>
                                    </div>

                                    <div class="flex justify-between">
                                        <div>
                                            <input type="checkbox" name="rememberMe" id="rememberMe" class="rounded-sm">
                                            <label for="rememberMe">Nikumbuke</label>
                                        </div>

                                        <div>
                                            <a href="{{ url('/forgotPassword') }}" class="text-blue-500 hover:text-blue-600">Nimesahau Nywila?</a>
                                        </div>
                                    </div>

                                    <div class="my-3">
                                        <button type="submit" class="text-black bg-blue-300 hover:bg-blue-400 w-full rounded-md py-2 font-bold">Ingia</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 py-3 text-center font-bold w-full absolute bottom-0">
            <a href="https://rmstechnology.co.tz/" target="blank" class="text-blue-500 hover:text-blue-600 underline">RMS Technology</a> | Haki zote zimehifadhiwa © 2024
        </div>
    </div>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    function showText(inputName, divName){
        let inputType=$(`#${inputName}`).attr('type');
        let inputText=$(`#${divName}`).text();

        let changeType=(inputType=='text')?"password":"text";
        let changeText=(inputText=='visibility')?"visibility_off":"visibility";

        $(`#${inputName}`).attr('type', changeType);
        $(`#${divName}`).text(changeText);
    }
</script>

</html>
