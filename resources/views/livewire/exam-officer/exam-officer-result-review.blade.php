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
                                Auditing results for
                                <strong class="text-slate-800">
                                    {{ $inspectingCourse->studentCourse->code ?? '' }}
                                    -
                                    {{ $inspectingCourse->studentCourse->title ?? '' }}
                                </strong>
                                ({{ $inspectingCourse->department->name ?? '' }})
                            @else
                                Review Coordinator-approved departmental score sheets,
                                verify grades, and publish official semester results.
                            @endif
                        </p>
                    </div>

                    @if($inspectingCourse)

                        <div class="flex items-center gap-2">

                            <button
                                wire:click="closeInspection"
                                class="px-4 py-2 text-xs font-bold text-slate-700 uppercase bg-slate-100 rounded-lg shadow hover:bg-slate-200 transition"
                            >
                                ⬅ Back to Courses
                            </button>

                            <a
                                href="{{ route('exam-officer.course-score-sheet', [
                                    'departmentCourse' => $inspectingCourse->id,
                                    'session' => str_replace('/', '-', $selectedSession),
                                    'semester' => $selectedSemester
                                ]) }}"
                                target="_blank"
                                class="px-4 py-2 text-xs font-bold text-slate-700 uppercase bg-slate-100 rounded-lg shadow hover:bg-slate-200 transition inline-flex items-center gap-1"
                            >
                                🖨️ Score Sheet
                            </a>

                            <button
                                wire:click="openRejectModal"
                                class="px-4 py-2 text-xs font-bold text-white uppercase bg-amber-600 rounded-lg shadow hover:bg-amber-500 transition"
                            >
                                ↩ Return to Coordinator
                            </button>

                            <button
                                wire:click="releaseCourseResults"
                                onclick="confirm('Release and publish these results to students? This will recalculate student GPAs and process carry-overs.') || event.stopImmediatePropagation()"
                                class="px-4 py-2 text-xs font-bold text-white uppercase bg-green-600 rounded-lg shadow hover:bg-green-500 transition"
                            >
                                🚀 Release to Students
                            </button>

                        </div>

                    @else

                        @if($selectedDepartmentId !== 'all')
                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('exam-officer.senate-broadsheet', [
                                        'department' => $selectedDepartmentId,
                                        'session' => str_replace('/', '-', $selectedSession),
                                        'semester' => $selectedSemester
                                    ]) }}"
                                    target="_blank"
                                    class="px-4 py-2 text-xs font-bold text-white uppercase bg-slate-900 rounded-lg shadow hover:bg-slate-800 transition inline-flex items-center gap-1.5"
                                >
                                    📄 Senate Broadsheet
                                </a>
                            </div>
                        @endif

                    @endif
                </div>


                {{-- Filters --}}
                <div class="px-6 py-4 border-b bg-slate-50 flex flex-wrap items-center gap-6">

                    {{-- Department --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Department
                        </label>

                        <select
                            wire:model.live="selectedDepartmentId"
                            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400"
                        >
                            <option value="all">All Departments</option>

                            @foreach($availableDepartments as $dept)
                                <option value="{{ $dept['id'] }}">
                                    {{ $dept['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Academic Session --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Academic Session
                        </label>

                        <select
                            wire:model.live="selectedSession"
                            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400"
                        >
                            @forelse($availableSessions as $sess)
                                <option value="{{ $sess }}">
                                    {{ $sess }}
                                </option>
                            @empty
                                <option value="">
                                    No registered sessions found
                                </option>
                            @endforelse
                        </select>
                    </div>


                    {{-- Semester --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Semester
                        </label>

                        <select
                            wire:model.live="selectedSemester"
                            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400"
                        >
                            <option value="first">
                                Harmattan (First)
                            </option>

                            <option value="second">
                                Rain (Second)
                            </option>
                        </select>
                    </div>


                    @if(!$inspectingCourse)

                        {{-- Search --}}
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                                Search Courses
                            </label>

                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchQuery"
                                placeholder="Search by course code or title..."
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400"
                            >
                        </div>


                        {{-- Status Filter --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                                Status Filter
                            </label>

                            <select
                                wire:model.live="statusFilter"
                                class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-fuchsia-400"
                            >
                                <option value="all">
                                    All (Ready &amp; Released)
                                </option>

                                <option value="coordinator_approved">
                                    Ready for Release (Coordinator Approved)
                                </option>

                                <option value="released">
                                    Released to Students
                                </option>
                            </select>
                        </div>

                    @endif
                </div>


                {{-- Summary Metrics --}}
                @if(!$inspectingCourse)

                    <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Total Courses --}}
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-semibold text-slate-500 uppercase">
                                Total Approved Courses
                            </span>

                            <h4 class="text-2xl font-bold text-slate-800 mt-1">
                                {{ $totalCourses }}
                            </h4>
                        </div>


                        {{-- Coordinator Approved --}}
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <span class="text-xs font-semibold text-blue-700 uppercase">
                                Ready for Release (Coordinator Approved)
                            </span>

                            <h4 class="text-2xl font-bold text-blue-800 mt-1">
                                {{ $awaitingRelease }}
                            </h4>
                        </div>


                        {{-- Released --}}
                        <div class="p-4 bg-green-50 rounded-xl border border-green-200">
                            <span class="text-xs font-semibold text-green-700 uppercase">
                                Officially Released to Students
                            </span>

                            <h4 class="text-2xl font-bold text-green-800 mt-1">
                                {{ $releasedCount }}
                            </h4>
                        </div>

                    </div>

                @endif


                {{-- Main Content --}}
                <div class="flex-auto px-0 pt-0 pb-2">

                    @if(!$inspectingCourse)

                        {{-- ============================================================
                             COURSE LIST
                             ============================================================ --}}
                        <div class="p-0 overflow-x-auto">

                            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">

                                <thead class="align-bottom">
                                    <tr>

                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Course
                                        </th>

                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Department
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Units
                                        </th>

                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Lecturer
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Result Status
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Action
                                        </th>

                                    </tr>
                                </thead>


                                <tbody>

                                    @forelse($departmentCourses as $course)

                                        <tr class="hover:bg-slate-50 transition border-b">

                                            {{-- Course --}}
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


                                            {{-- Department --}}
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-xs font-semibold text-slate-700">
                                                    {{ $course->department->name ?? 'N/A' }}
                                                </span>

                                            </td>


                                            {{-- Units --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-xs font-bold text-slate-700">
                                                    {{ $course->units }} Unit(s)
                                                </span>

                                            </td>


                                            {{-- Lecturer --}}
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-xs font-medium text-slate-700">
                                                    {{ $course->allocated_lecturer ?? 'Not Assigned' }}
                                                </span>

                                            </td>


                                            {{-- Result Status --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <div class="flex items-center justify-center gap-1.5 flex-wrap">

                                                    {{-- Coordinator Approved --}}
                                                    @if($course->coordinator_approved_count > 0)

                                                        <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-blue-100 text-blue-800 animate-pulse">
                                                            {{ $course->coordinator_approved_count }}
                                                            Ready to Release
                                                        </span>

                                                    @endif


                                                    {{-- Released --}}
                                                    @if($course->released_count > 0)

                                                        <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-green-100 text-green-800">
                                                            {{ $course->released_count }}
                                                            Released
                                                        </span>

                                                    @endif

                                                </div>

                                            </td>


                                            {{-- Actions --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <div class="flex items-center justify-center gap-2">

                                                    <button
                                                        wire:click="inspectCourse({{ $course->id }})"
                                                        class="text-xs font-bold text-slate-800 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-none border"
                                                    >
                                                        🔍 Inspect
                                                    </button>


                                                    @if($course->coordinator_approved_count > 0)

                                                        <button
                                                            wire:click="releaseCourseResults({{ $course->id }})"
                                                            onclick="confirm('Release {{ $course->studentCourse->code ?? '' }} results to students now?') || event.stopImmediatePropagation()"
                                                            class="text-xs font-bold text-white bg-green-600 hover:bg-green-500 px-3 py-1.5 rounded-lg transition shadow"
                                                        >
                                                            🚀 Release
                                                        </button>

                                                    @endif

                                                </div>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="6"
                                                class="p-8 text-center align-middle text-slate-400"
                                            >
                                                No Coordinator-approved results found for
                                                <strong>{{ $selectedSession }}</strong>
                                                in the
                                                <strong>{{ ucfirst($selectedSemester) }}</strong>
                                                semester.
                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>


                    @else

                        {{-- ============================================================
                             STUDENT RESULT INSPECTION
                             ============================================================ --}}
                        <div class="p-0 overflow-x-auto">

                            <div class="px-6 py-4 bg-slate-50 border-b">

                                <div class="flex flex-wrap items-center justify-between gap-3">

                                    <div>

                                        <h5 class="text-sm font-bold text-slate-800">
                                            Student Results
                                        </h5>

                                        <p class="text-xs text-slate-500 mt-1">
                                            Academic Session:
                                            <strong>{{ $selectedSession }}</strong>

                                            <span class="mx-1">•</span>

                                            Semester:
                                            <strong>{{ ucfirst($selectedSemester) }}</strong>

                                            <span class="mx-1">•</span>

                                            Students registered for this session only
                                        </p>

                                    </div>


                                    <div class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-lg text-xs font-bold">
                                        Coordinator Approved / Released
                                    </div>

                                </div>

                            </div>


                            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">

                                <thead class="align-bottom">

                                    <tr>

                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Student
                                        </th>

                                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Matric No
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            CA Score (40)
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Exam Score (60)
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Total (100)
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Grade
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            GP
                                        </th>

                                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                            Status
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse($studentsWithResults as $row)

                                        <tr class="hover:bg-slate-50 transition border-b">

                                            {{-- Student --}}
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <h6 class="mb-0 text-sm font-semibold text-slate-800">
                                                    {{ $row['name'] }}
                                                </h6>

                                            </td>


                                            {{-- Matric Number --}}
                                            <td class="p-4 align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <p class="mb-0 text-xs font-mono font-semibold text-slate-600">
                                                    {{ $row['matric_no'] }}
                                                </p>

                                            </td>


                                            {{-- CA --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-sm font-bold text-slate-700">
                                                    {{ $row['ca_score'] !== null
                                                        ? number_format((float) $row['ca_score'], 1)
                                                        : '-' }}
                                                </span>

                                            </td>


                                            {{-- Exam --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-sm font-bold text-slate-700">
                                                    {{ $row['exam_score'] !== null
                                                        ? number_format((float) $row['exam_score'], 1)
                                                        : '-' }}
                                                </span>

                                            </td>


                                            {{-- Total --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-sm font-bold text-slate-900">
                                                    {{ $row['total_score'] !== null
                                                        ? number_format((float) $row['total_score'], 1)
                                                        : '-' }}
                                                </span>

                                            </td>


                                            {{-- Grade --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-sm font-bold
                                                    {{ $row['grade'] === 'A'
                                                        ? 'text-green-600'
                                                        : ($row['grade'] === 'F'
                                                            ? 'text-red-600'
                                                            : 'text-slate-800') }}"
                                                >
                                                    {{ $row['grade'] }}
                                                </span>

                                            </td>


                                            {{-- Grade Point --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                <span class="text-xs font-bold text-slate-700">
                                                    {{ $row['grade_point'] }}
                                                </span>

                                            </td>


                                            {{-- Status --}}
                                            <td class="p-4 text-center align-middle bg-transparent whitespace-nowrap shadow-transparent">

                                                @php
                                                    $status = $row['status'] ?? '';

                                                    $statusClasses = match ($status) {
                                                        'exam_officer_approved' =>
                                                            'bg-blue-100 text-blue-800',

                                                        'released' =>
                                                            'bg-green-100 text-green-800',

                                                        default =>
                                                            'bg-gray-100 text-gray-700',
                                                    };

                                                    $statusLabel = match ($status) {
                                                        'exam_officer_approved' =>
                                                            'Coordinator Approved',

                                                        'released' =>
                                                            'Released',

                                                        default =>
                                                            ucwords(str_replace('_', ' ', $status)),
                                                    };
                                                @endphp

                                                <span class="text-xxs font-bold uppercase px-2.5 py-1 rounded-full {{ $statusClasses }}">
                                                    {{ $statusLabel }}
                                                </span>


                                                @if(!empty($row['remarks']))

                                                    <p class="text-xxs text-amber-600 italic mt-0.5">
                                                        {{ $row['remarks'] }}
                                                    </p>

                                                @endif

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="8"
                                                class="p-8 text-center align-middle text-slate-400"
                                            >
                                                <div class="flex flex-col items-center justify-center py-6">

                                                    <div class="text-4xl mb-3">
                                                        📋
                                                    </div>

                                                    <p class="font-semibold text-slate-600">
                                                        No approved results found
                                                    </p>

                                                    <p class="text-xs text-slate-400 mt-1">
                                                        No Coordinator-approved or released results
                                                        exist for this course in
                                                        <strong>{{ $selectedSession }}</strong>
                                                        /
                                                        <strong>{{ ucfirst($selectedSemester) }}</strong>.
                                                    </p>

                                                </div>
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


    {{-- ================================================================
         RETURN TO COORDINATOR MODAL
         ================================================================ --}}
    @if($showRejectModal)

        <div class="fixed inset-0 z-999 flex items-center justify-center overflow-auto bg-black bg-opacity-50">

            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 m-4 relative animate-fade-up">

                {{-- Modal Header --}}
                <div class="flex justify-between items-center pb-3 border-b mb-4">

                    <h5 class="text-lg font-bold text-slate-800">
                        Return Results to Coordinator
                    </h5>

                    <button
                        wire:click="closeRejectModal"
                        class="text-slate-400 hover:text-slate-600 text-xl font-bold"
                    >
                        &times;
                    </button>

                </div>


                {{-- Modal Description --}}
                <p class="text-sm text-slate-600 mb-4">

                    Please provide an audit comment or reason for returning these
                    course results to the Coordinator for further review.

                </p>


                {{-- Reason --}}
                <div class="mb-4">

                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Reason / Feedback
                    </label>

                    <textarea
                        wire:model.defer="rejectionReason"
                        rows="4"
                        placeholder="E.g. Discrepancy noted in CA vs Exam weightings, please cross-check..."
                        class="w-full text-sm border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-amber-400"
                    ></textarea>

                    @error('rejectionReason')

                        <span class="text-xs text-red-600 mt-1 block">
                            {{ $message }}
                        </span>

                    @enderror

                </div>


                {{-- Modal Actions --}}
                <div class="flex justify-end gap-3 pt-3 border-t">

                    <button
                        wire:click="closeRejectModal"
                        class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg uppercase transition"
                    >
                        Cancel
                    </button>

                    <button
                        wire:click="rejectCourseResults"
                        class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg uppercase shadow transition"
                    >
                        Confirm Return to Coordinator
                    </button>

                </div>

            </div>

        </div>

    @endif

</div>

