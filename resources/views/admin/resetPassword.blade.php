<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RMS || Reset Password</title>
    @include('admin.headerScripts')
</head>
<body>
    <div class="bg-blue-50 h-[100vh]">
        <div class="flex items-center h-full">
            <div class="w-full">
                <div class="lg:w-3/12 md:w-1/2 w-11/12 shadow-2xl rounded-md p-5 mx-auto mt-3 bg-white">
                    <h1 class="font-bold text-center text-2xl mb-3">Reset Password:</h1>
        
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

                    <form action="{{ url('/resetPassword') }}" method="post">
                        @csrf

                        <input type="hidden" name="userToken" id="userToken" value="{{ $userToken }}" required>
                        <div>
                            <div class="my-3">
                                <label class="block" for="password">New Password:<span class="text-red-500">*</span></label>
                                <input class="block w-full rounded-md p-2 border border-gray-200" type="password" name="password" id="password" placeholder="Enter New Password" required>
                                @error('password')
                                    <span class="text-red-500 capitalize">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label class="block" for="password_confirmation">Confirm Password:<span class="text-red-500">*</span></label>
                                <input class="block w-full rounded-md p-2 border border-gray-200" type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" required>
                                @error('password_confirmation')
                                    <span class="text-red-500 capitalize">{{ $message }}</span>
                                @enderror
                            </div>
            
                            <div class="my-3">
                                <button type="submit" class="text-white bg-green-500 hover:bg-green-600 w-full rounded-md py-2 font-bold">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>