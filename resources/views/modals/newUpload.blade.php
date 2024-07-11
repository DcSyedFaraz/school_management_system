<!-- New Area Modal -->
<div id="newEntryModal" tabindex="-1" aria-hidden="true"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full mx-auto">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold">
                    Add Marks:
                </h3>
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-hide="newEntryModal">
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
                    <form action="{{ $url1 }}" method="post" id="newEntryForm">
                        @csrf

                        <div>
                            <h2 class="font-bold text-2xl">Student Details:</h2>

                            <div class="mb-3">
                                <label class="block" for="studentName">Student Name:<span
                                        class="text-red-500">*</span></label>
                                <input type="text" class="block border border-black rounded-md p-2 w-full"
                                    name="studentName" id="studentName" placeholder="Enter Student Name" required />
                                @error('studentName')
                                    <span class="text-red-500 text-sm italic">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2">
                                <div>
                                    <label class="block" for="gender">Gender:<span
                                            class="text-red-500">*</span></label>
                                    <select class="block border border-black rounded-md p-2 w-full" name="gender"
                                        id="gender" required>
                                        <option value="">--- SELECT GENDER ---</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                    @error('gender')
                                        <span class="text-red-500 text-sm italic">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block" for="selectedclass">Class:</label>
                                    <select class="block w-full block p-2 rounded-md border border-black" name="class"
                                        id="selectedclass" required onchange="loadSubjects()">
                                        <option value="">--- SELECT CLASS ---</option>
                                        @if (count($classes) > 0)
                                            @foreach ($classes as $class)
                                                <option value="{{ $class['gradeId'] }}">{{ $class['gradeName'] }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" class="text-red-500">No Data Found!</option>
                                        @endif
                                    </select>
                                </div>

                                <div>
                                    <label for="exam">Exam:</label>
                                    <select class="block w-full block p-2 rounded-md border border-black" name="exam"
                                        id="exam" required>
                                        <option value="">--- SELECT EXAM ---</option>
                                        @if (count($exams) > 0)
                                            @foreach ($exams as $exam)
                                                <option value="{{ $exam['examId'] }}">{{ $exam['examName'] }}</option>
                                            @endforeach
                                        @else
                                            <option value="" class="text-red-500">No Data Found!</option>
                                        @endif
                                    </select>
                                </div>

                                <div>
                                    <label for="firstGrade">Joined First Grade in 2017?</label>
                                    <select class="block w-full block p-2 rounded-md border border-black"
                                        name="firstGrade" id="firstGrade" required>
                                        <option value="">--- SELECT OPTION ---</option>
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h2 class="font-bold text-2xl">Subject Details:</h2>

                            <div class="mb-3">
                                <label class="block" for="examDate">Exam Date:<span
                                        class="text-red-500">*</span></label>
                                <input type="date" class="block border border-black rounded-md p-2 w-full"
                                    name="examDate" id="examDate" placeholder="Enter Exam Date"
                                    max="{{ date('Y-m-d') }}" required />
                                @error('examDate')
                                    <span class="text-red-500 text-sm italic">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="subject-container" class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-2">
                                <!-- Subject fields will be inserted here dynamically -->
                            </div>
                        </div>
                    </form>
                </div>
            </div>



            <div class="flex justify-end p-6 space-x-2 border-t border-gray-200 rounded-b">
                <button type="submit" form="newEntryForm"
                    class="text-white bg-green-500 hover:bg-green-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save</button>
                <button data-modal-hide="newEntryModal" type="button"
                    class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const subjectsByClass = {
        1: ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo'],
        2: ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'utamaduni'],
        3: ['hisabati', 'kiswahili', 'sayansi', 'english', 'maadili', 'jiographia', 'smichezo'],
        default: ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili']
    };

    function loadSubjects() {
        const classId = document.getElementById('selectedclass').value;
        const subjects = subjectsByClass[classId] || subjectsByClass.default;
        const container = document.getElementById('subject-container');

        // console.log(subjects, subjectsByClass[classId], classId);

        container.innerHTML = '';
        subjects.forEach(subject => {
            const subjectLabel = subject.charAt(0).toUpperCase() + subject.slice(1);
            container.innerHTML += `
                <div>
                    <label class="block" for="${subject}Marks">${subjectLabel}:<span class="text-red-500">*</span></label>
                    <input type="number" min="0" max="50" class="block border border-black rounded-md p-2 w-full" name="${subject}Marks" id="${subject}Marks" placeholder="Enter ${subjectLabel} Marks" required/>
                    @error('${subject}Marks')
                        <span class="text-red-500 text-sm italic">{{ $message }}</span>
                    @enderror
                </div>
            `;
        });
    }
</script>
