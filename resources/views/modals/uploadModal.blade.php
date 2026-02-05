<!-- New Area Modal -->
<div id="uploadModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full mx-auto">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold">
                    Pakia Faili:
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="uploadModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <form action="{{ $url4 }}" method="post" id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2">
                            <div>
                                <label for="class">Darasa:<span class="text-red-500">*</span></label>
                                <select class="block border border-black rounded-md p-2 w-full" name="class" id="class" required>
                                    <option value="">--- CHAGUA DARASA ---</option>
                                    @if (count($classes)>0)
                                        @foreach ($classes as $class)
                                            <option value="{{ $class['gradeId'] }}">{{ $class['gradeName'] }}</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                                @error('class')
                                    <span class="text-red-500 text-sm italic">{{ $message }}</span>
                                @enderror
                            </div>
    
                            <div>
                                <label for="exam">Mtihani:<span class="text-red-500">*</span></label>
                                <select class="block w-full block p-2 rounded-md border border-black" name="exam" id="exam" required>
                                    <option value="">--- CHAGUA MTIHANI ---</option>
                                    @if (count($exams)>0)
                                        @foreach ($exams as $exam)
                                            <option value="{{ $exam['examId'] }}">{{ $exam['examName'] }}</option>
                                        @endforeach
                                    @else
                                        <option value="" class="text-red-500">No Data Found!</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label for="examDate">Tarehe ya Mtihani:<span class="text-red-500">*</span></label>
                                <input type="date" class="block w-full block p-2 rounded-md border border-black" name="examDate" id="examDate" max="{{ date('Y-m-d') }}" required>
                            </div>

                            <div>
                                <label for="excelFile">Faili:<span class="text-red-500">*</span></label>
                                <input class="block w-full text-sm text-gray-900 border border-black rounded-lg cursor-pointer bg-gray-50 p-2 focus:outline-none" name="excelFile" id="excelFile" type="file">
                                <span class="text-red-500 text-sm italic">Faili la Excel pekee linakubaliwa!</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
          
            <div class="flex justify-end p-6 space-x-2 border-t border-gray-200 rounded-b">
                <button type="submit" form="uploadForm" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Pakia</button>
                <button data-modal-hide="uploadModal" type="button" class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Funga</button>
            </div>
        </div>
    </div>
</div>