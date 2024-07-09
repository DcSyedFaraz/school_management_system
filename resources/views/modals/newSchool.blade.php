<!-- New Area Modal -->
<div id="newEntryModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full mx-auto">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold">
                    Add School:
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="newEntryModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <form action="{{ $url1 }}" method="post" id="newEntryForm">
                        @csrf
                        
                        <div class="my-3">
                            <label class="block" for="schoolName">School Name:<span class="text-red-500">*</span></label>
                            <input type="text" class="block border border-black rounded-md p-2 w-full" name="schoolName" id="schoolName" placeholder="Enter School Name" required/>
                            @error('schoolName')
                                <span class="text-red-500 text-sm italic">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
          
            <div class="flex justify-end p-6 space-x-2 border-t border-gray-200 rounded-b">
                <button type="submit" form="newEntryForm" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save</button>
                <button data-modal-hide="newEntryModal" type="button" class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Close</button>
            </div>
        </div>
    </div>
</div>