<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RMS || Forgot Password</title>
    @include('admin.headerScripts')
</head>
<body>
    <div class="bg-blue-50 h-[100vh]">
        <div class="flex items-center h-full">
            <div class="w-full">
                <div class="lg:w-3/12 md:w-1/2 w-11/12 shadow-2xl rounded-md p-5 mx-auto mt-3 bg-white">
                    <h1 class="font-bold text-center text-2xl mb-3">Nimesahau Nywila:</h1>
        
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

                    <form action="{{ url('/forgotPassword') }}" method="post">
                        @csrf
                        <div>
                            <div class="my-3">
                                <label class="block" for="email">Barua Pepe:<span class="text-red-500">*</span></label>
                                <input class="block w-full rounded-md p-2 border border-gray-200" type="email" name="email" id="email" placeholder="Ingiza Barua Pepe" value="{{ Cookie::get('email') }}" required>
                            </div>
            
                            <div class="my-3">
                                <button type="submit" class="text-white bg-green-500 hover:bg-green-600 w-full rounded-md py-2 font-bold">Send Email</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>