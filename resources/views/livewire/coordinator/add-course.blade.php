@use('App\Models\StudentCourse')
<div>
   

    <div class="flex flex-wrap -mx-3 mt-6">
     
        <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
            <div
                class="relative flex flex-col min-w-0 mb-12 overflow-auto overflow-x-hidden break-words bg-white border-0 max-h-70-screen lg:mb-0 bg-white/80 shadow-blur dark:bg-gray-950 dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4">
                    <h6 class="dark:text-white">Courses</h6>
                    <input type="text" placeholder="Search Contact"
                        class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                        wire:model.live="search" />
                </div>
                <div class="flex justify-center items-center p-4" wire:loading wire:target="search">
                    <div
                        class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-fuchsia-500 dark:border-fuchsia-300">
                    </div>
                    <span class="ml-2 text-fuchsia-500 dark:text-fuchsia-300">Searching...</span>
                </div>
                <div class="flex-auto p-2">

                    @forelse ($courses as $course)

                        <div href="javascript:;" class="cursor-pointer block p-2 bg-gradient-lime rounded-xl mb-2">
                            <div class="flex p-2">

                                <div wire:click="addCourse({{ $course->id }})" class="ml-4">
                                    <div class="items-center justify-between">
                                        <h6 class="mb-0 text-white dark:text-white">
                                            {{$course->code}}
                                        </h6>
                                        <p class="mb-0 leading-normal text-white text-size-sm">{{$course->title}}</p>
                                        <input type="hidden" wire:model="form.code" value="{{$course->code}}">
                                        <input type="hidden" wire:model="form.title" value="{{$course->title}}">
                                        <input type="hidden" wire:model="form.course_id" value="{{$course->id}}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <p>No courses found</p>
                    @endforelse
                    
                </div>
            </div>

        </div>
        <div class="w-full max-w-full px-3 flex-0 lg:w-8/12">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4">
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                            <h6 class="mb-0 dark:text-white">Courses List</h6>
                        </div>
                        <div class="flex justify-center items-center p-4" wire:loading wire:target="addCourse">
                            <div
                                class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-fuchsia-500 dark:border-fuchsia-300">
                            </div>
                            <span class="ml-2 text-fuchsia-500 dark:text-fuchsia-300">Adding Course</span>
                        </div>

                    </div>
                    <hr
                        class="h-px mx-0 my-4 mb-0 bg-transparent border-0 opacity-25 bg-gradient-horizontal-dark dark:bg-gradient-horizontal-light" />
                </div>

                <div class="flex-auto p-4 pt-0">
                    <div class="flex justify-center items-center p-4" wire:loading wire:target="deleteCourse">
                        <div
                            class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-fuchsia-500 dark:border-fuchsia-300">
                        </div>
                        <span class="ml-2 text-fuchsia-500 dark:text-fuchsia-300">Deleting...</span>
                    </div>
                    <ul class="flex flex-col pl-0 mb-0 rounded-none" wire:poll.visible wire:target="addCourse">


                        @foreach ($selectedCourses as $pickedCourse)
                                                @php
    $course = StudentCourse::find($pickedCourse->student_course_id);
    $lastDigit = substr($course->code, -1);
    $semester = $lastDigit % 2 === 0 ? 2 : 1;

                                                @endphp
                                                <li
                                                    class="border-black/12.5 rounded-t-inherit relative mb-4 block flex-col items-center border-0 border-solid px-4 py-0 pl-0 text-inherit">
                                                    <div
                                                        class="before:w-0.75 before:rounded-1 ml-4 pl-2 before:absolute before:top-0 before:left-0 before:h-full before:bg-slate-700 before:content-['']">
                                                        <div class="flex items-center">

                                                            <h6
                                                                class="mb-0 font-semibold leading-normal text-size-sm text-slate-700 dark:text-white">
                                                                {{$course->code . '  ' . $course->title}}
                                                            </h6>

                                                        </div>
                                                        <div class="flex items-center pl-1 mt-4 ml-6">
                                                            <div>
                                                                <p
                                                                    class="mb-0 font-semibold leading-tight text-size-xs text-slate-400 dark:text-white/80">
                                                                    Level</p>
                                                                <span
                                                                    class="font-bold leading-tight text-size-xs">{{ number_format($course->student_level_id) . '00' }}</span>
                                                            </div>
                                                            <div class="ml-auto">
                                                                <p
                                                                    class="mb-0 font-semibold leading-tight text-size-xs text-slate-400 dark:text-white/80">
                                                                    Semester
                                                                </p>
                                                                <span class="font-bold leading-tight text-size-xs">{{ $semester }}</span>
                                                            </div>
                                                            <div class="mx-auto">
                                                                <p
                                                                    class="mb-0 font-semibold leading-tight text-size-xs text-slate-400 dark:text-white/80">
                                                                    Units
                                                                </p>
                                                                @if ($editingCourseId === $pickedCourse->id)
                                                                    <div class="flex justify-center items-center p-4" wire:loading
                                                                        wire:target="editCourse">
                                                                        <div
                                                                            class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-fuchsia-500 dark:border-fuchsia-300">
                                                                        </div>
                                                                        <span class="ml-2 text-fuchsia-500 dark:text-fuchsia-300">Deleting...</span>
                                                                    </div>
                                                                    <div class="relative flex-auto p-4">
                                                                        <form>
                                                                            <input wire:model="form.unit" type="text"
                                                                                class="dark:bg-gray-950 mb-2 focus:shadow-soft-success-outline dark:placeholder:text-white/80 dark:text-white/80 text-xs leading-5 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-2 py-1 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-teal-300 focus:outline-none"
                                                                                placeholder="units...">
                                                                            @error('form.unit')
                                                                                <p class="text-xs text-red-500">{{ $message }} </p>
                                                                            @enderror

                                                                            <button wire:click.prevent="saveUnit"
                                                                                class="mt-3 text-teal-500 hover:text-teal-600">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                                    class="w-5 h-5">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                                        d="M5 13l4 4L19 7" />
                                                                                </svg>
                                                                            </button>
                                                                            <button wire:click="cancelEdit"
                                                                                class="mt-3 text-red-500 hover:text-red-600">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                                    class="w-5 h-5">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                                        d="M6 18L18 6M6 6l12 12" />
                                                                                </svg>
                                                                            </button>
                                                                        </form>

                                                                    </div>


                                                                @else

                                                                    <span
                                                                        class="font-bold leading-tight text-size-xs">{{$pickedCourse->units}}</span>
                                                                @endif

                                                            </div>
                                                            <div class="flex items-center space-x-2">
                                                                <button wire:click="editCourse({{$pickedCourse->id}})"
                                                                    class="text-sm text-teal-500 font-semibold rounded hover:text-teal-800">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                                    </svg>
                                                                </button>
                                                                <button wire:click="deleteCourse({{$pickedCourse->id}})"
                                                                    class="text-sm text-red-500 font-semibold rounded hover:text-teal-800 mr-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <hr
                                                        class="h-px mx-0 my-6 mb-0 bg-transparent border-0 opacity-25 bg-gradient-horizontal-dark dark:bg-gradient-horizontal-light" />
                                                </li>

                        @endforeach


                    </ul>
                </div>
            </div>
        </div>
    </div>

    
</div>