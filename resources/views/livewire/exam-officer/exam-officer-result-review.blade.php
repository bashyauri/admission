<div>
    <div class="flex flex-wrap -mx-3 mb-5">
        <div class="w-full max-w-full px-3 mb-6 mx-auto">
            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
                
                {{-- Header --}}
                <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h6 class="dark:text-white font-bold text-lg">
                            🎓 Exam Officer Result Auditing & Release
                        </h6>
                        <p class="text-sm text-slate-500">
                            @if($inspectingCourse)
                                Auditing results for <strong class="text-slate-800">{{ $inspectingCourse->studentCourse->code ?? '' }} - {{ $inspectingCourse->studentCourse->title ?? '' }}</strong> ({{ $inspectingCourse->department->name ?? '' }})
                            @else
                                Review HOD-approved departmental score sheets, verify grades, and publish/release official semester results.
                            @endif
                        </p>
                    </div>

                    @if($inspectingCourse)
                        <div class="flex items-center gap-2">
                            <button wire:click="closeInspection" class="px-4 py-2 text-xs font-bold text-slate-700 uppercase bg-slate-100 rounded-lg shadow hover:bg-slate-200 transition">
                                ⬅ Back to Courses
                            </button>
                            <a href="{{ route('exam-officer.course-score-sheet', ['departmentCourse' => $inspectingCourse->id, 'session' => str_replace('/', '-', $selectedSession), 'semester' => $selectedSemester]) }}"
                               target="_blank"
                               class="px-4 py-2 text-xs font-bold text-slate-700 uppercase bg-slate-100 rounded-lg shadow hover:bg-slate-200 transition inline-flex items-center gap-1">
                                🖨️ Score Sheet
                            </a>
                            <button wire:click="openRejectModal" class="px-4 py-2 text-xs font-bold text-white uppercase bg-amber-600 rounded-lg shadow hover:bg-amber-500 transition">
                                ↩ Return to Coordinator
                            </button>
                            <button wire:click="releaseCourseResults"
                                onclick="confirm('Release and publish these results to students? This will recalculate student GPAs and process carry-overs.') || event.stopImmediatePropagation()"
                                class="px-4 py-2 text-xs font-bold text-white uppercase bg-green-600 rounded-lg shadow hover:bg-green-500 transition">
                                🚀 Release to Students
                            </button>
                        </div>
                    @else
                        @if($selectedDepartmentId !== 'all')
                            <div class="flex items-center gap-2">
                                <a href="{{ route('exam-officer.senate-broadsheet', ['department' => $selectedDepartmentId, 'session' => str_replace('/', '-', $selectedSession), 'semester' => $selectedSemester]) }}"
                                   target="_blank"
                                   class="px-4 py-2 text-xs font-bold text-white uppercase bg-slate-900 rounded-lg shadow hover:bg-slate-800 transition inline-flex items-center gap-1.5">
                                    📄 Senate Broadsheet
                                </a>
                            </div>
                        @endif
                    @endif
                </div>


                {{-- Filters --}}
                <div class="px-6 py-4 border-b bg-slate-50 flex flex-wrap items-center gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Department</label>
                        <select wire:model="selectedDepartmentId" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400">
                            <option value="all">All Departments</option>
                            @foreach($availableDepartments as $dept)
                                <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Academic Session</label>
                        <select wire:model="selectedSession" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400">
                            @foreach($availableSessions as $sess)
                                <option value="{{ $sess }}">{{ $sess }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Semester</label>
                        <select wire:model="selectedSemester" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400">
                            <option value="first">Harmattan (First)</option>
                            <option value="second">Rain (Second)</option>
                        </select>
                    </div>

                    @if(!$inspectingCourse)
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Search Courses</label>
                            <input type="text" wire:model.debounce.300ms="searchQuery" placeholder="Search by course code or title..."
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Status Filter</label>
                            <select wire:model="statusFilter" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400">
                                <option value="all">All Courses</option>
                                <option value="hod_approved">Ready for Release (HOD Approved)</option>
                                <option value="released">Released to Students</option>
                                <option value="submitted">Awaiting HOD Approval</option>
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Summary Metrics Cards --}}
                @if(!$inspectingCourse)
                    <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-semibold text-slate-500 uppercase">Total Courses</span>
                            <h4 class="text-2xl font-bold text-slate-800 mt-1">{{ $totalCourses }}</h4>
                        </div>
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <span class="text-xs font-semibold text-blue-700 uppercase">Ready for Release (HOD Approved)</span>
                            <h4 class="text-2xl font-bold text-blue-800 mt-1">{{ $awaitingRelease }}</h4>
                        </div>
                        <div class="p-4 bg-green-50 rounded-xl border border-green-200">
                            <span class="text-xs font-semibold text-green-700 uppercase">Officially Released</span>
                            <h4 class="text-2xl font-bold text-green-800 mt-1">{{ $releasedCount }}</h4>
                        </div>
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                            <span class="text-xs font-semibold text-amber-700 uppercase">Awaiting HOD Review</span>
                            <h4 class="text-2xl font-bold text-amber-800 mt-1">{{ $pendingHodCount }}</h4>
                        </div>
                    </div>
                @endif

                {{-- Main Table View --}}
                <div class="flex-auto px-0 pt-0 pb-2">
                    @if(!$inspectingCourse)
                        {{-- COURSE LIST --}}
                        <div class="p-0 overflow-x-auto">
                            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                                <thead class="align-bottom">
                                    <tr>
                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Course</th>
                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Department</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Units</th>
                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Lecturer</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Result Status</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departmentCourses as $course)
                                        <tr class="hover:bg-slate-50 transition border-b">
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <div class="flex px-2 py-1">
                                                    <div class="flex flex-col justify-center">
                                                        <h6 class="mb-0 text-sm font-bold text-slate-800">
                                                            {{ $course->studentCourse->code ?? 'N/A' }}
                                                        </h6>
                                                        <p class="mb-0 text-xs text-slate-500">
                                                            {{ $course->studentCourse->title ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-xs font-semibold text-slate-700">{{ $course->department->name ?? 'N/A' }}</span>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-xs font-bold text-slate-700">{{ $course->units }} Unit(s)</span>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-xs font-medium text-slate-700">{{ $course->allocated_lecturer }}</span>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                    @if($course->hod_approved_count > 0)
                                                        <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-blue-100 text-blue-800 animate-pulse">
                                                            {{ $course->hod_approved_count }} Ready to Release
                                                        </span>
                                                    @endif
                                                    @if($course->released_count > 0)
                                                        <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-green-100 text-green-800">
                                                            {{ $course->released_count }} Released
                                                        </span>
                                                    @endif
                                                    @if($course->submitted_count > 0)
                                                        <span class="px-2 py-0.5 text-xxs font-bold uppercase rounded bg-amber-100 text-amber-800">
                                                            {{ $course->submitted_count }} With HOD
                                                        </span>
                                                    @endif
                                                    @if($course->pending_count > 0 || ($course->total_registered > 0 && ($course->submitted_count + $course->hod_approved_count + $course->released_count) === 0))
                                                        <span class="px-2 py-0.5 text-xxs font-bold uppercase rounded bg-gray-100 text-gray-700">
                                                            {{ $course->pending_count }} Pending
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button wire:click="inspectCourse({{ $course->id }})"
                                                        class="text-xs font-bold text-slate-800 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-none border">
                                                        🔍 Inspect
                                                    </button>
                                                    @if($course->hod_approved_count > 0)
                                                        <button wire:click="releaseCourseResults({{ $course->id }})"
                                                            onclick="confirm('Release {{ $course->studentCourse->code ?? '' }} results to students now?') || event.stopImmediatePropagation()"
                                                            class="text-xs font-bold text-white bg-green-600 hover:bg-green-500 px-3 py-1.5 rounded-lg transition shadow">
                                                            🚀 Release
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="p-8 text-center align-middle text-slate-400">
                                                No courses found matching selected filters for <strong>{{ $selectedSession }}</strong>.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- STUDENT SCORES INSPECTION TABLE --}}
                        <div class="p-0 overflow-x-auto">
                            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                                <thead class="align-bottom">
                                    <tr>
                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Student</th>
                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Matric No</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">CA Score (40)</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Exam Score (60)</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Total (100)</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Grade</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">GP</th>
                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($studentsWithResults as $row)
                                        <tr class="hover:bg-slate-50 transition border-b">
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <h6 class="mb-0 text-sm font-semibold text-slate-800">{{ $row['name'] }}</h6>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <p class="mb-0 text-xs font-mono font-semibold text-slate-600">{{ $row['matric_no'] }}</p>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-sm font-bold text-slate-700">{{ $row['ca_score'] !== null ? number_format((float)$row['ca_score'], 1) : '-' }}</span>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-sm font-bold text-slate-700">{{ $row['exam_score'] !== null ? number_format((float)$row['exam_score'], 1) : '-' }}</span>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-sm font-bold text-slate-900">{{ $row['total_score'] !== null ? number_format((float)$row['total_score'], 1) : '-' }}</span>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-sm font-bold {{ $row['grade'] === 'A' ? 'text-green-600' : ($row['grade'] === 'F' ? 'text-red-600' : 'text-slate-800') }}">
                                                    {{ $row['grade'] }}
                                                </span>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-xs font-bold text-slate-700">{{ $row['grade_point'] }}</span>
                                            </td>
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">
                                                <span class="text-xxs font-bold uppercase px-2.5 py-1 rounded-full
                                                    @if($row['status'] === 'hod_approved') bg-blue-100 text-blue-800
                                                    @elseif($row['status'] === 'released') bg-green-100 text-green-800
                                                    @elseif($row['status'] === 'submitted') bg-amber-100 text-amber-800
                                                    @else bg-gray-100 text-gray-700 @endif">
                                                    {{ $row['status'] }}
                                                </span>
                                                @if(!empty($row['remarks']))
                                                    <p class="text-xxs text-amber-600 italic mt-0.5">{{ $row['remarks'] }}</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="p-8 text-center align-middle text-slate-400">
                                                No students registered for this course in this academic session.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- RETURN TO HOD MODAL --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-999 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 m-4 relative animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b mb-4">
                    <h5 class="text-lg font-bold text-slate-800">Return Results to Head of Department</h5>
                    <button wire:click="closeRejectModal" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                
                <p class="text-sm text-slate-600 mb-4">
                    Please provide an audit comment or reason for returning these course results to the HOD for review.
                </p>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Reason / Feedback</label>
                    <textarea wire:model.defer="rejectionReason" rows="4" placeholder="E.g. Discrepancy noted in CA vs Exam weightings, please cross-check..."
                        class="w-full text-sm border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-amber-400"></textarea>
                    @error('rejectionReason')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button wire:click="closeRejectModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg uppercase transition">
                        Cancel
                    </button>
                    <button wire:click="rejectCourseResults" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg uppercase shadow transition">
                        Confirm Return to HOD
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
