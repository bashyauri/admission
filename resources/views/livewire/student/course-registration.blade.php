@use('App\Models\StudentCourse')

<div>
    <!-- Pin Section -->
    <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
        <div class="flex p-4 rounded-xl bg-gray-50 dark:bg-gray-600">
            <div class="flex justify-center items-center" wire:loading wire:target="usePin">
                <span class="ml-2 text-lime-500 dark:text-fuchsia-300">please wait...</span>
            </div>

            @if(!$student->approval?->isPinUsed())
                <h6 class="my-auto dark:text-white text-fuchsia-500">
                    <span class="mr-1 leading-bold text-sm text-slate-400 dark:text-white/80">Pin:</span>
                    {{$student->approval?->pin ?? 'Not Generated ! Ask your Coordinator.'}}
                </h6>

                @if ($student->approval?->pin)
                    <a href="javascript:;" wire:click.prevent="usePin"
                        class="inline-block px-3 py-2 mb-0 ml-auto font-bold text-center align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer leading-pro text-xs ease-soft-in tracking-tight-soft active:opacity-85 active:shadow-soft-xs hover:scale-102 border-fuchsia-500 text-fuchsia-500 hover:border-slate-700 hover:bg-transparent hover:opacity-75 active:border-slate-700 active:bg-slate-700 active:text-white">
                        Apply
                    </a>
                @endif
            @endif
        </div>
    </div>

    <div class="flex flex-wrap -mx-3 mt-6">
        @if ($student->approval?->isPinUsed())
            <!-- Search Header Section -->
            <div class="w-full max-w-full px-3 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex flex-col lg:flex-row gap-6 justify-between items-start lg:items-center">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Course Registration</h2>
                            <p class="text-gray-600 dark:text-gray-400">Search and register for your courses</p>
                        </div>

                        <!-- Global Search -->
                        <div class="w-full lg:w-80" x-data="{ focused: false }">
                            <div class="relative" :class="{ 'ring-2 ring-fuchsia-500 rounded-lg': focused }">
                                <input type="text" wire:model.live="searchCourse" placeholder="Press / to search courses..."
                                    x-ref="searchInput" @focus="focused = true" @blur="focused = false"
                                    @keydown.window="if (event.key === '/' && !focused) { event.preventDefault(); $refs.searchInput.focus(); }"
                                    class="w-full pl-11 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none bg-white dark:bg-gray-700 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                @if($searchCourse)
                                    <button wire:click="clearSearch('available')"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Courses Section -->
            <div class="w-full max-w-full px-3 lg:w-5/12 flex-0">
                <div
                    class="relative flex flex-col min-w-0 mb-12 overflow-auto overflow-x-hidden break-words bg-white border-0 max-h-70-screen lg:mb-0 bg-white/80 shadow-blur dark:bg-gray-950 dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                    <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="dark:text-white text-lg font-semibold">Available Courses</h6>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Click on a course to add it</p>
                            </div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $courses->count() }} courses
                            </span>
                        </div>
                    </div>

                    <!-- Search Results Info -->
                    @if($searchCourse)
                        <div class="px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800">
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Showing {{ $courses->count() }} result(s) for "<span
                                        class="font-medium">"{{ $searchCourse }}"</span>"
                                </p>
                                <button wire:click="clearSearch('available')"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium">
                                    Clear
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-center items-center p-4" wire:loading wire:target="addCourse">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 border-2 border-fuchsia-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            <span class="text-lime-500 dark:text-fuchsia-300">Adding course...</span>
                        </div>
                    </div>

                    <div class="flex-auto p-6">
                        @if($courses->isNotEmpty())
                            <div class="grid gap-4">
                                @foreach ($courses as $course)
                                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all duration-200 cursor-pointer group
                                                            {{ $isActive ? 'opacity-50 cursor-not-allowed' : 'hover:border-fuchsia-300 dark:hover:border-fuchsia-600' }}"
                                        wire:click="addCourse({{ $course->id }})" wire:loading.attr="disabled"
                                        wire:target="addCourse">

                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">{{ $course->code }}
                                                </h4>
                                                <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-2 leading-relaxed">
                                                    {{ $course->title }}</p>
                                            </div>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ml-3 flex-shrink-0">
                                                {{ $course->units }} unit{{ $course->units > 1 ? 's' : '' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center space-x-4">
                                                <span class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Level {{ $course->student_level_id }}00
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Semester {{ $course->semester }}
                                                </span>
                                            </div>
                                            <div
                                                class="flex items-center space-x-1 text-fuchsia-600 dark:text-fuchsia-400 font-medium">
                                                <span>Add</span>
                                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                @if($searchCourse)
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No courses found</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">No courses match "<span
                                            class="font-medium">"{{ $searchCourse }}"</span>"</p>
                                    <button wire:click="clearSearch('available')"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-fuchsia-700 bg-fuchsia-100 hover:bg-fuchsia-200 dark:bg-fuchsia-900 dark:text-fuchsia-200 transition-colors">
                                        Clear search
                                    </button>
                                @else
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No courses available</h3>
                                    <p class="text-gray-600 dark:text-gray-400">All courses for your level have been registered.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Registered Courses Section -->
            <div class="w-full max-w-full px-3 flex-0 lg:w-7/12">
                <div
                    class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6">
                        <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center mb-4">
                            <div>
                                <h6 class="dark:text-white text-lg font-semibold mb-1">Registered Courses</h6>

                                <!-- Unit Progress Bar -->
                                <div class="w-full lg:w-80">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Unit Usage</span>
                                        <span
                                            class="text-sm font-bold {{ $registeredCourses->sum('units') > $maxUnits ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $registeredCourses->sum('units') }} / {{ $maxUnits }} units
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        @php
                                            $percentage = min(($registeredCourses->sum('units') / $maxUnits) * 100, 100);
                                            $progressColor = $percentage >= 90 ? 'bg-red-500' : ($percentage >= 75 ? 'bg-amber-500' : 'bg-green-500');
                                        @endphp
                                        <div class="h-2.5 rounded-full transition-all duration-500 ease-out {{ $progressColor }}"
                                            style="width: {{ $percentage }}%"></div>
                                    </div>
                                    @if($percentage >= 90)
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            You've reached the unit limit
                                        </p>
                                    @elseif($percentage >= 75)
                                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Approaching unit limit
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Registered Courses Search -->
                            <div class="w-full lg:w-64">
                                <div class="relative">
                                    <input type="text" wire:model.live="searchRegistered"
                                        placeholder="Search registered courses..."
                                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-fuchsia-500 dark:bg-gray-700 dark:text-white">
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    @if($searchRegistered)
                                        <button wire:click="clearSearch('registered')"
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Print Button -->
                        @if ($registeredCourses->count() && $student->approval?->isPinUsed())
                            <div class="flex justify-center mb-2">
                                <a href="{{ route('student.print-course-form', ['user' => $student->user_id]) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-fuchsia-500 rounded-lg hover:bg-fuchsia-600 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 focus:ring-offset-2 transition-colors duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                    </svg>
                                    Print Course Form
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-center items-center p-4" wire:loading wire:target="deleteCourse">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 border-2 border-red-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            <span class="text-red-500 dark:text-red-400">Removing course...</span>
                        </div>
                    </div>

                    <div class="flex-auto p-6">
                        @if ($student->approval?->isPinUsed())
                            @if($registeredCourses->isNotEmpty())
                                <div class="grid gap-4">
                                    @foreach ($registeredCourses as $pickedCourse)
                                        <div wire:key="course-{{ $pickedCourse->id }}"
                                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all duration-200">

                                            <div class="flex justify-between items-start mb-3">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">
                                                        {{ $pickedCourse->departmentCourse->studentCourse->code }}
                                                    </h4>
                                                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                                                        {{ $pickedCourse->departmentCourse->studentCourse->title }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center space-x-3 ml-4">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        {{ $pickedCourse->units }} unit{{ $pickedCourse->units > 1 ? 's' : '' }}
                                                    </span>
                                                    <button wire:click="deleteCourse({{ $pickedCourse->id }})"
                                                        wire:confirm="Are you sure you want to remove {{ $pickedCourse->departmentCourse->studentCourse->code }} from your registered courses?"
                                                        wire:loading.attr="disabled"
                                                        class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200"
                                                        title="Remove course">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="flex items-center space-x-6 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Level {{ $pickedCourse->student_level_id }}00
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Semester {{ $pickedCourse->departmentCourse->studentCourse->semester }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Empty Registered Courses State -->
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    @if($searchRegistered)
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No registered courses found
                                        </h3>
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">No registered courses match "<span
                                                class="font-medium">"{{ $searchRegistered }}"</span>"</p>
                                        <button wire:click="clearSearch('registered')"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-fuchsia-700 bg-fuchsia-100 hover:bg-fuchsia-200 dark:bg-fuchsia-900 dark:text-fuchsia-200 transition-colors">
                                            Clear search
                                        </button>
                                    @else
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No courses registered yet
                                        </h3>
                                        <p class="text-gray-600 dark:text-gray-400">Start by adding courses from the available courses
                                            list.</p>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- JavaScript for Enhanced Interactions -->
<script>
    document.addEventListener('livewire:initialized', () => {
        // Keyboard shortcut for search
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && !e.target.matches('input, textarea, [contenteditable]')) {
                e.preventDefault();
                const searchInput = document.querySelector('[x-ref="searchInput"]');
                if (searchInput) searchInput.focus();
            }
        });

        // Smooth scrolling for better UX
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            respond(() => {
                // Scroll to top after actions for better mobile experience
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    });
</script>
