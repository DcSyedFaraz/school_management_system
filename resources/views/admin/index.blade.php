<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RMS || Sign In</title>
    @include('admin.headerScripts')
    <style>
        .contact-info {
            position: absolute;
            top: 10px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .contact-item {
            margin-right: 20px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .contact-item i {
            margin-right: 5px;
            font-size: 18px;
            color: #f1c40f;
        }

        .phone-section {
            background-color: #ff9800;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }


        .phone-section i {
            margin-right: 5px;
        }

        .phone-section a {
            color: #fff;
            text-decoration: none;
        }

        .phone-section a:hover {
            color: #007bff;
        }

        /* WhatsApp Button Styles */
        .whatsapp-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #25D366;
            color: white;
            border-radius: 50px;
            padding: 10px 15px;
            font-size: 18px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            z-index: 1000;
            text-decoration: none;
        }

        .whatsapp-button i {
            margin-right: 10px;
        }

        .whatsapp-button:hover {
            background-color: #128C7E;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body>
    <div class="h-[100vh] relative">
        <div class="contact-info">
            <div class="contact-item">
                <i class="material-symbols-outlined">place</i>
                <span>Dodoma, Tanzania</span>
            </div>
            <div class="contact-item">
                <i class="material-symbols-outlined">email</i>
                <span><a href="mailto:info@rmstechnology.co.tz">info@rmstechnology.co.tz</a></span>
            </div>
            <div class="phone-section">
                <i class="material-symbols-outlined">call</i>
                <span>Tupigie: <a href="tel:+255786283282">0786 283 282</a></span>
            </div>
        </div>

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

                        <div
                            class="lg:w-3/12 md:w-1/2 w-11/12 shadow-2xl rounded-xl shadow-green-800 p-5 mx-auto bg-white">
                            <h1 class="font-bold text-center text-md mb-3">Tafadhali Andika E-mail Na Nywila Ili Kuingia
                            </h1>

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
                                        <label class="block" for="email">Jina la mtumiaje/Email:<span
                                                class="text-red-500">*</span></label>
                                        <input class="block w-full rounded-md p-2 border border-gray-200" type="text"
                                            name="email" id="email" placeholder="Ingiza Barua Pepe"
                                            value="{{ Cookie::get('email') }}" required>
                                    </div>

                                    <div class="my-3">
                                        <label class="block" for="password">Nywila:<span
                                                class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input class="block w-full rounded-md p-2 border border-gray-200"
                                                type="password" name="password" id="password"
                                                placeholder="Ingiza Nywila" value="{{ Cookie::get('password') }}"
                                                required>
                                            <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer"
                                                id="eye" onclick="showText('password', 'eye')">visibility</i>
                                        </div>
                                    </div>

                                    <div class="flex justify-between">
                                        <div>
                                            <input type="checkbox" name="rememberMe" id="rememberMe" class="rounded-sm">
                                            <label for="rememberMe">Nikumbuke</label>
                                        </div>

                                        <div>
                                            <a href="{{ url('/forgotPassword') }}"
                                                class="text-blue-500 hover:text-blue-600">Nimesahau Nywila?</a>
                                        </div>
                                    </div>

                                    <div class="my-3">
                                        <button type="submit"
                                            class="text-black bg-blue-300 hover:bg-blue-400 w-full rounded-md py-2 font-bold">Ingia</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 py-3 text-center font-bold w-full absolute bottom-0">
            <a href="https://rmstechnology.co.tz/" target="blank"
                class="text-blue-500 hover:text-blue-600 underline">RMS Technology</a> | Haki zote zimehifadhiwa © 2024
        </div>
    </div>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/255786283282" class="whatsapp-button" target="_blank">
        <i class="fa-brands fa-whatsapp text-2xl"></i>

        Chat na sisi kupitia WhatsApp
    </a>

</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    function showText(inputName, divName) {
        let inputType = $(`#${inputName}`).attr('type');
        let inputText = $(`#${divName}`).text();

        let changeType = (inputType == 'text') ? "password" : "text";
        let changeText = (inputText == 'visibility') ? "visibility_off" : "visibility";

        $(`#${inputName}`).attr('type', changeType);
        $(`#${divName}`).text(changeText);
    }
</script>

</html>
