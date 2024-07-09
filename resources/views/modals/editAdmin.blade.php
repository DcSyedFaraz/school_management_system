<!-- New Area Modal -->
<div id="editAdminModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 bg-black/25 border border-black right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[100vh] max-h-full">
    <div class="relative w-full max-w-4xl max-h-full mx-auto">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold">
                    Edit {{ substr(Session::get('pageTitle'), 0, -1) }}:
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="editAdminModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <form action="{{ $url2 }}" method="post" id="adminForm2">
                        @csrf
                        <input type="hidden" name="adminId" id="adminId">
                        <div class="grid lg:grid-cols-3 md:grid-cols-3 grid-cols-1 gap-x-5">
                            <div class="my-3">
                                <label for="username" class="block">Username:<span class="text-red-500">*</span></label>
                                <input type="text" class="p-2 rounded-md w-full border border-black" name="updatedUsername" id="username2" placeholder="Enter Username" value="{{ old('updatedUsername') }}" required>
                                @error('updatedUsername')
                                    <span class="text-red-500 capitalize text-sm italic">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="email" class="block">Email Id:<span class="text-red-500">*</span></label>
                                <input type="email" class="p-2 rounded-md w-full border border-black" name="updatedEmail" id="email2" placeholder="Enter Email Id" value="{{ old('updatedEmail') }}" required>
                                @error('updatedEmail')
                                    <span class="text-red-500 capitalize text-sm italic">{{ $message }}</span>
                                @enderror
                            </div>
                
                            <div class="my-3">
                                <label for="contactNumber" class="block">Contact Number:<span class="text-red-500">*</span></label>
                                <input type="number" min="1" class="p-2 rounded-md w-full border border-black" name="updatedContactNumber" id="contactNumber2" placeholder="Enter Contact Number" value="{{ old('updatedContactNumber') }}" required>
                                @error('updatedContactNumber')
                                    <span class="text-red-500 capitalize text-sm italic">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="region" class="block">Region:<span class="text-red-500">*</span></label>
                                <select class="p-2 rounded-md w-full border border-black" name="updatedRegion" id="region2" disabled required>
                                    <option value="">--- SELECT REGION ---</option>
                                    @if (count($regions)>0)
                                        @foreach ($regions as $region)
                                            <option value="{{ $region['regionId'] }}" @selected(old('updatedRegion')==$region['regionId'])>{{ $region['regionName'] }} ({{ $region['regionCode'] }})</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                                @error('updatedRegion')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="district" class="block">District:<span class="text-red-500">*</span></label>
                                <select class="p-2 rounded-md w-full border border-black" name="updatedDistrict" id="district2" disabled required>
                                    <option value="">--- SELECT DISTRICT ---</option>
                                    @if (count($districts)>0)
                                        @foreach ($districts as $district)
                                            <option value="{{ $district['districtId'] }}" @selected(old('updatedDistrict')==$district['districtId'])>{{ $district['districtName'] }} ({{ $district['districtCode'] }})</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                                @error('updatedDistrict')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="my-3">
                                <label for="ward" class="block">Ward:<span class="text-red-500">*</span></label>
                                <select class="p-2 rounded-md w-full border border-black" name="updatedWard" id="ward2" disabled required>
                                    <option value="">--- SELECT WARD ---</option>
                                    @if (count($wards)>0)
                                        @foreach ($wards as $ward)
                                            <option value="{{ $ward['wardId'] }}" @selected(old('updatedWard')==$ward['wardId'])>{{ $ward['wardName'] }} ({{ $ward['wardCode'] }})</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                                @error('updatedWard')
                                    <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        @if (Session::get('pageTitle')=='Teachers')
                            <div class="my-3">
                                <div class="my-3">
                                    <label for="school" class="block">School:<span class="text-red-500">*</span></label>
                                    <select class="p-2 rounded-md w-full border border-black" name="updatedSchool" id="school2" disabled required>
                                        <option value="">--- SELECT SCHOOL ---</option>
                                        @if (count($schools)>0)
                                            @foreach ($schools as $school)
                                                <option value="{{ $school['schoolId'] }}" @selected(old('updatedSchool')==$school['schoolId'])>{{ $school['schoolName'] }}</option>
                                            @endforeach
                                        @else
                                            <option value="" class="text-red-500">No Data Found!</option>
                                        @endif
                                    </select>
                                    @error('updatedSchool')
                                        <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        @if (Session::get('pageTitle')=='Admins')
                            <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-x-5">
                                <div class="my-3">
                                    <label for="updatedPassword" class="block">Password:</label>
                                    <div class="relative">
                                        <input type="password" class="p-2 rounded-md w-full border border-black" name="updatedPassword" id="updatedPassword" placeholder="Enter Password">
                                        <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer" id="uEye" onclick="showText('updatedPassword', 'uEye')">visibility</i>
                                    </div>
                                    @error('updatedPassword')
                                        <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                    
                                <div class="my-3">
                                    <label for="updatedConfirmPassword" class="block">Confirm Password:</label>
                                    <div class="relative">
                                        <input type="password" class="p-2 rounded-md w-full border border-black" name="updatedConfirmPassword" id="updatedConfirmPassword" placeholder="Confirm Password">
                                        <i class="material-symbols-outlined absolute right-2 top-2.5 cursor-pointer" id="ufEye" onclick="showText('updatedConfirmPassword', 'ufEye')">visibility</i>
                                    </div>
                                    @error('updatedConfirmPassword')
                                        <span class="text-red-500 capitalize italic text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
          
            <div class="flex justify-end p-6 space-x-2 border-t border-gray-200 rounded-b">
                <button type="submit" form="adminForm2" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Update</button>
                <button data-modal-hide="editAdminModal" type="button" class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Close</button>
            </div>
        </div>
    </div>
</div>