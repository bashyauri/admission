@use('App\Models\StudentCourse')

<div>
<div class="w-full max-w-full px-3 md:w-4/12 flex-0">
<div class="flex p-4 rounded-xl bg-gray-50 dark:bg-gray-600">
    @if (!$student->approval?->isPinUsed())
        
   
    <h6 class="my-auto dark:text-white text-fuchsia-500"><span class="mr-1 leading-bold text-sm text-slate-400 dark:text-white/80">Pin:</span>{{$student->approval?->pin ?? 'Not Generated ! Ask your Coordinator.'}}</h6>
   @if ($student->approval?->pin)
     <a href="javascript:;" wire:click.prevent="usePin"  class="inline-block px-3 py-2 mb-0 ml-auto font-bold text-center  align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer leading-pro text-xs ease-soft-in tracking-tight-soft active:opacity-85 active:shadow-soft-xs hover:scale-102 border-fuchsia-500 text-fuchsia-500 hover:border-slate-700 hover:bg-transparent hover:opacity-75 active:border-slate-700 active:bg-slate-700 active:text-white">Apply</a>  
   @endif
    @endif
    
</div>

</div>

    <div class="flex flex-wrap -mx-3 mt-6" wire:poll wire:target="usePin" >
       
                
        @if ($student->approval?->isPinUsed())
              <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
            <div
                class="relative flex flex-col min-w-0 mb-12 overflow-auto overflow-x-hidden break-words bg-white border-0 max-h-70-screen lg:mb-0 bg-white/80 shadow-blur dark:bg-gray-950 dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4">
                    <h6 class="dark:text-white">Course List</h6>

                </div>
                 <div class="flex justify-center items-center p-4" >
                <span class="inline-block px-4 py-2 rounded-md bg-cyan-500 mb-4 text-white dark:text-fuchsia-300">
  Click on the title to select the course
</span>
                 </div>
                 
               <div class="flex justify-center items-center p-4" wire:loading wire:target="addCourse">
                        
                        <span class="ml-2 text-lime-500 dark:text-fuchsia-300">please wait...</span>
                    </div>
                <div class="flex-auto p-4 pt-0">
                    <ul class="flex flex-col pl-0 mb-0 rounded-none">
                    
                        @foreach ($courses as $course)
                                                                                                                                                    @php

    $lastDigit = substr($course->code, -1);
    $semester = $lastDigit % 2 === 0 ? 2 : 1;

                                                                                                                                                    @endphp

                                                                                                                                                    <li
                                                                                                                                                        class="border-black/12.5 rounded-t-inherit relative mb-4 block flex-col items-center border-0 border-solid px-4 py-0 pl-0 text-inherit">
                                                                                                                                                        <div
                                                                                                                                                            class="before:w-0.75 before:rounded-1 ml-4 pl-2 before:absolute before:top-0 before:left-0 before:h-full before:bg-fuchsia-500 before:content-['']">                                                                                                        <div class="flex items-center space-x-3">
                                                                                                                                                                <div class="relative" 
                                                     >

                                                                                                                                                                </div>
                                                                                                                                                            <h6 class="text-sm font-semibold text-gray-700 dark:text-white cursor-pointer" @if(!$isActive)
                                                                                                                                                                wire:click="addCourse({{ $course->id }})" @endif wire:target="addCourse"
                                                                                                                                                                wire:loading.class="cursor-not-allowed opacity-50" wire:loading.attr="disabled"
                                                                                                                                                                wire:loading.class.remove="cursor-pointer" @if($isActive) class="cursor-not-allowed opacity-50" @endif>
                                                                                                                                                            
                                                                                                                                                                {{ $course->code . ' ' . $course->title }}
                                                                                                                                                            </h6>
                                                                                                                                                            </div>

                                                                                                                                                            <div class="flex items-center pl-1 mt-4 ml-6">
                                                                                                                                                                <div>
                                                                                                                                                                    <p
                                                                                                                                                                        class="mb-0 font-semibold leading-tight text-size-xs text-slate-400 dark:text-white/80">
                                                                                                                                                                        Level</p>
                                                                                                                                                                    <span class="font-bold leading-tight text-size-xs">{{
        number_format($course->student_level_id) . '00' }}</span>
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
                                                                                                                                                                    <span class="font-bold leading-tight text-size-xs">{{$course->units}}</span>
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
        @endif
      
        <div class="w-full max-w-full px-3 flex-0 lg:w-6/12">
             
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4">
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                            <h6 class="mb-0 dark:text-white">Registered Courses</h6>
                        </div>
                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12 text-right">
                            <p class="mb-2 font-semibold leading-tight text-size-sm text-slate-700 dark:text-white">
                                Units Selected: <span class="font-bold text-lime-500 dark:text-fuchsia-300">{{ $this->totalRegisteredUnits }}</span>
                            </p>
                            <p class="mb-0 font-semibold leading-tight text-size-sm text-slate-700 dark:text-white">
                                Units Allowed: <span class="font-bold text-lime-500 dark:text-fuchsia-300">{{ $maxUnits }}</span>
                            </p>
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


                        @foreach ($registeredCourses as $pickedCourse)

                                                <li wire:key="course-{{ $course->id }}"
                                                    class="border-black/12.5 rounded-t-inherit relative mb-4 block flex-col items-center border-0 border-solid px-4 py-0 pl-0 text-inherit">
                                                    <div
                                                        class="before:w-0.75 before:rounded-1 ml-4 pl-2 before:absolute before:top-0 before:left-0 before:h-full before:bg-slate-700 before:content-['']">
                                                        <div class="flex items-center">

                                                            <h6
                                                                class="mb-0 font-semibold leading-normal text-size-sm text-slate-700 dark:text-white">
                                                                {{$pickedCourse->departmentCourse->StudentCourse->code . ' ' . $pickedCourse->departmentCourse->StudentCourse->title}}
                                                            </h6>

                                                        </div>
                                                        <div class="flex items-center pl-1 mt-4 ml-6">
                                                            <div>
                                                                <p
                                                                    class="mb-0 font-semibold leading-tight text-size-xs text-slate-400 dark:text-white/80">
                                                                    Level</p>
                                                                <span class="font-bold leading-tight text-size-xs">{{
        number_format($pickedCourse->student_level_id) . '00' }}</span>
                                                            </div>
                                                            <div class="ml-auto">
                                                                <p
                                                                    class="mb-0 font-semibold leading-tight text-size-xs text-slate-400 dark:text-white/80">
                                                                    Semester
                                                                </p>
                                                                <span class="font-bold leading-tight text-size-xs">{{$pickedCourse->departmentCourse->StudentCourse->semester }}</span>
                                                            </div>
                                                            <div class="mx-auto">
                                                                <p
                                                                    class="mb-0 font-semibold leading-tight text-size-xs text-slate-400 dark:text-white/80">
                                                                    Units
                                                                </p>
                                                              

                                                                    <span
                                                                        class="font-bold leading-tight text-size-xs">{{$pickedCourse->units}}</span>
                                                              

                                                            </div>
                                                            <div class="flex items-center space-x-2">
                                                            
                                                                
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