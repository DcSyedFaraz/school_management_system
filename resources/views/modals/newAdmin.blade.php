<!-- New Area Modal -->
<div id="newAdminModal" tabindex="-1" aria-hidden="true"
    class="fixed top-0 left-0 bg-black/25 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[100vh] max-h-full">
    <div class="relative w-full max-w-4xl max-h-full mx-auto">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold">
                    New {{ substr(Session::get('pageTitle'), 0, -1) }}:
                </h3>
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-hide="newAdminModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div>
                    <form action="{{ $url1 }}" method="post" id="adminForm">
                        @csrf

                        <div class="grid lg:grid-cols-3 md:grid-cols-3 grid-cols-1 gap-x-5">
                            <div class="my-3">
                                <label for="username" class="block">Name:<span class="text-red-500">*</span></label>
                                <input type="text" class="p-2 rounded-md w-full border border-black" name="username"
                                    id="username" placeholder="Enter Username" value="{{ old('username') }}" required>
                                @error('username')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="email" class="block">Email Id:<span
                                        class="text-red-500">*</span></label>
                                <input type="email" class="p-2 rounded-md w-full border border-black" name="email"
                                    id="email" placeholder="Enter Email Id" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="contactNumber" class="block">Contact Number:<span
                                        class="text-red-500">*</span></label>
                                <input type="number" min="1" class="p-2 rounded-md w-full border border-black"
                                    name="contactNumber" id="contactNumber" placeholder="Enter Contact Number"
                                    value="{{ old('contactNumber') }}" required>
                                @error('mobileNumber')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="my-3">
                                <label for="user_name" class="block">User Name:<span
                                        class="text-red-500">*</span></label>
                                <input type="text" min="1" class="p-2 rounded-md w-full border border-black"
                                    name="user_name" id="user_name" placeholder="Enter User Name"
                                    value="{{ old('user_name') }}" required>
                                @error('user_name')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="my-3">
                                <label for="otp" class="block">One Time Password: <span
                                        class="text-red-500">*</span></label>
                                <input type="text" min="1" class="p-2 rounded-md w-full border border-black"
                                    name="otp" id="otp" placeholder="Enter One Time Password"
                                    value="{{ old('otp') }}" required>
                                @error('otp')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="region" class="block">Region:<span class="text-red-500">*</span></label>
                                <select class="p-2 rounded-md w-full border border-black" name="region" id="region"
                                    required>
                                    <option value="">--- SELECT REGION ---</option>
                                    @if (count($regions) > 0)
                                        @foreach ($regions as $region)
                                            <option value="{{ $region['regionId'] }}" @selected(old('region') == $region['regionId'])>
                                                {{ $region['regionName'] }} ({{ $region['regionCode'] }})</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                                @error('region')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="district" class="block">District:<span
                                        class="text-red-500">*</span></label>
                                <select class="p-2 rounded-md w-full border border-black" name="district"
                                    id="district" required>
                                    <option value="">--- SELECT DISTRICT ---</option>
                                    @if (count($districts) > 0)
                                        @foreach ($districts as $district)
                                            <option value="{{ $district['districtId'] }}"
                                                @selected(old('district') == $district['districtId'])>{{ $district['districtName'] }}
                                                ({{ $district['districtCode'] }})</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                                @error('district')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="ward" class="block">Ward:<span class="text-red-500">*</span></label>
                                <select class="p-2 rounded-md w-full border border-black" name="ward"
                                    id="ward" required>
                                    <option value="">--- SELECT WARD ---</option>
                                    @if (count($wards) > 0)
                                        @foreach ($wards as $ward)
                                            <option value="{{ $ward['wardId'] }}" @selected(old('ward') == $ward['wardId'])>
                                                {{ $ward['wardName'] }} ({{ $ward['wardCode'] }})</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                                @error('ward')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            @if (Session::get('pageTitle') == 'Admins')
                                <input type="hidden" name="userType" id="userType" value="A">
                            @else
                                <input type="hidden" name="userType" id="userType" value="T">
                            @endif
                        </div>

                        @if (Session::get('pageTitle') == 'Teachers')
                            <div class="my-3">
                                <div class="my-3">
                                    <label for="school" class="block">School:<span
                                            class="text-red-500">*</span></label>
                                    <select class="p-2 rounded-md w-full border border-black" name="school"
                                        id="school" required>
                                        <option value="">--- SELECT SCHOOL ---</option>
                                        @if (count($schools) > 0)
                                            @foreach ($schools as $school)
                                                <option value="{{ $school['schoolId'] }}"
                                                    @selected(old('school') == $school['schoolId'])>{{ $school['schoolName'] }}</option>
                                            @endforeach
                                        @else
                                            <option value="" class="text-red-500">No Data Found!</option>
                                        @endif
                                    </select>
                                    @error('school')
                                        <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-x-5">
                            <div class="my-3">
                                <label for="password" class="block">Password:<span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="password" class="p-2 rounded-md w-full border border-black"
                                        name="password" id="password" placeholder="Enter Password" required>
                                    <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer"
                                        id="eye" onclick="showText('password', 'eye')">visibility</i>
                                </div>
                                @error('password')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="password_confirmation" class="block">Confirm Password:<span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="password" class="p-2 rounded-md w-full border border-black"
                                        name="password_confirmation" id="password_confirmation2"
                                        placeholder="Confirm Password" required>
                                    <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer"
                                        id="cFEye"
                                        onclick="showText('password_confirmation2', 'cFEye')">visibility</i>
                                </div>
                                @error('password_confirmation')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="flex justify-end p-6 space-x-2 border-t border-gray-200 rounded-b">
                <button type="submit" form="adminForm"
                    class="text-white bg-green-500 hover:bg-green-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save</button>
                <button data-modal-hide="newAdminModal" type="button"
                    class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Close</button>
            </div>
        </div>
    </div>
</div>
