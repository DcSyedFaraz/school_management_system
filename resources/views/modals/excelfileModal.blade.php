<!-- Add a modal for downloading excel files -->
<div id="excelfile"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full justify-center items-center flex">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative bg-white rounded-lg shadow-md">
            <div class="p-6 text-lg">
                <h5 class="text-lg font-medium leading-relaxed text-gray-800 ">Pakua Kiolezo</h5>
                <p class="text-sm text-gray-500 ">Chagua darasa la kupakua Kiolezo:</p>
                <ul class="list-none mb-0">
                    <li>
                        <a href="{{ asset('excel/class_one.xlsx') }}" target="_blank" download
                            class="flex items-center justify-between py-2 px-4 rounded-md text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <span>Darasa la Kwanza</span>
                            <i class="material-symbols-outlined text-sm text-gray-400">file_download</i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ asset('excel/class_two.xlsx') }}" target="_blank" download
                            class="flex items-center justify-between py-2 px-4 rounded-md text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <span>Darasa la Pili</span>
                            <i class="material-symbols-outlined text-sm text-gray-400">file_download</i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ asset('excel/class_three.xlsx') }}" target="_blank" download
                            class="flex items-center justify-between py-2 px-4 rounded-md text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <span>Darasa la Tatu</span>
                            <i class="material-symbols-outlined text-sm text-gray-400">file_download</i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ asset('excel/class_four_and_five.xlsx') }}" target="_blank" download
                            class="flex items-center justify-between py-2 px-4 rounded-md text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <span>Darasa la Nne na la Tano</span>
                            <i class="material-symbols-outlined text-sm text-gray-400">file_download</i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ asset('excel/class_six.xlsx') }}" target="_blank" download
                            class="flex items-center justify-between py-2 px-4 rounded-md text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <span>Darasa la Sita</span>
                            <i class="material-symbols-outlined text-sm text-gray-400">file_download</i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ asset('excel/class_seven.xlsx') }}" target="_blank" download
                            class="flex items-center justify-between py-2 px-4 rounded-md text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <span>Darasa la Saba</span>
                            <i class="material-symbols-outlined text-sm text-gray-400">file_download</i>
                        </a>
                    </li>
                </ul>
            </div>
            <button type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center -600 "
                data-modal-toggle="excelfile">
                <i class="material-symbols-outlined text-sm">close</i>
            </button>
        </div>
    </div>
</div>
