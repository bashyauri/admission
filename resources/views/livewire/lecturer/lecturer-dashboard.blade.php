<div>
    <span class="sr-only">Lecturer Dashboard</span>

    <div class="flex flex-wrap -mx-3 mb-5">
        <div class="w-full max-w-full px-3 mb-6 mx-auto">

            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">

                {{-- Header --}}
                <div
                    class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl
                           flex flex-wrap justify-between items-center gap-3"
                >

                    {{-- Title --}}
                    <div>
                        <h6 class="font-bold dark:text-white">
                            My Allocated Courses
                        </h6>

                        <p class="text-sm text-slate-500">
                            Manage results for your allocated courses.
                        </p>
                    </div>

                    {{-- Academic Session --}}
                    <div>
                        <label
                            for="academic-session"
                            class="block text-xs font-bold text-slate-500 uppercase mb-1"
                        >
                            Academic Session
                        </label>

                        <div class="flex items-center gap-2">
                            <select
                                id="academic-session"
                                wire:model.live="selectedSession"
                                class="text-sm border border-gray-300 rounded-lg px-3 py-1.5
                                       focus:ring-2 focus:ring-fuchsia-400
                                       focus:border-fuchsia-400"
                            >
                                @foreach($availableSessions as $session)
                                    <option value="{{ $session }}">
                                        {{ $session }}
                                    </option>
                                @endforeach
                            </select>

                            <span class="text-xs text-slate-400 italic">
                                auto-detected
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="flex-auto px-0 pt-0 pb-2">
                    <div class="p-0 overflow-x-auto">

                        <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">

                            {{-- Table Header --}}
                            <thead class="align-bottom">
                                <tr>

                                    <th class="table-header">
                                        Course
                                    </th>

                                    <th class="table-header">
                                        Department
                                    </th>

                                    <th class="table-header">
                                        Academic Session
                                    </th>

                                    <th class="table-header">
                                        Semester
                                    </th>

                                    <th class="table-header">
                                        Units
                                    </th>

                                    <th class="table-header text-center">
                                        Action
                                    </th>

                                </tr>
                            </thead>

                            {{-- Table Body --}}
                            <tbody>

                                @forelse($allocations as $allocation)

                                    <tr>

                                        {{-- Course --}}
                                        <td class="table-cell">
                                            <div class="flex px-2 py-1">
                                                <div class="flex flex-col justify-center">

                                                    <h6 class="mb-0 text-sm leading-normal">
                                                        {{ $allocation->departmentCourse->studentCourse->code ?? 'N/A' }}
                                                    </h6>

                                                    <p class="mb-0 text-xs leading-tight text-slate-400">
                                                        {{
                                                            $allocation->departmentCourse->studentCourse->title
                                                            ?? $allocation->departmentCourse->studentCourse->name
                                                            ?? 'N/A'
                                                        }}
                                                    </p>

                                                </div>
                                            </div>
                                        </td>

                                        {{-- Department --}}
                                        <td class="table-cell">
                                            <p class="mb-0 text-xs font-semibold leading-tight">
                                                {{ $allocation->department->name ?? 'N/A' }}
                                            </p>
                                        </td>

                                        {{-- Academic Session --}}
                                        <td class="table-cell">
                                            <p class="mb-0 text-xs font-semibold leading-tight">
                                                {{ $allocation->academic_session ?? 'N/A' }}
                                            </p>
                                        </td>

                                        {{-- Semester --}}
                                        <td class="table-cell">
                                            <p class="mb-0 text-xs font-semibold leading-tight capitalize">
                                                {{ $allocation->semester ?? 'N/A' }}
                                            </p>
                                        </td>

                                        {{-- Units --}}
                                        <td class="table-cell">
                                            <p class="mb-0 text-xs font-semibold leading-tight">
                                                {{ $allocation->assigned_units
                                                    ?? $allocation->departmentCourse->units
                                                    ?? 0 }}
                                            </p>
                                        </td>

                                        {{-- Action --}}
                                        <td class="table-cell text-center">
                                            <a
                                                href="{{ route('lecturer.result-entry', $allocation->id) }}"
                                                class="text-xs font-semibold leading-tight
                                                       text-fuchsia-500
                                                       hover:text-fuchsia-800"
                                            >
                                                Enter Results
                                            </a>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td
                                            colspan="6"
                                            class="p-6 text-center align-middle bg-transparent
                                                   border-b whitespace-nowrap shadow-transparent"
                                        >
                                            <p class="mb-0 text-sm font-semibold leading-tight text-slate-400">
                                                No courses allocated for this session.
                                            </p>
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
