<div class="my-4">
    <div class="flex flex-wrap -mx-3">
        <div class="w-full max-w-full px-3 mx-auto sm:flex-0 shrink-0 sm:w-10/12 md:w-8/12">
            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border sm:my-12">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 text-center">
                    <h6 class="text-xl font-bold">Add Matriculation Number</h6>
                </div>
                <div class="flex-auto p-6">
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full max-w-full px-3 flex-0">
                               <form wire:submit.prevent="addRegistrationNumber" wire:poll>
                            <div class="overflow-x-auto">
                                
                                <div class="mb-4">
                                    <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <p id="fullName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $user->full_name }}</p>
                                </div>
                                <div class="mb-4">
                                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                                    <p id="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $user->proposedCourse->department->name }}</p>
                                </div>
                                <div class="mb-4">
                                    <label for="course" class="block text-sm font-medium text-gray-700">Course</label>
                                    <p id="course" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $user->proposedCourse->course->name }}</p>
                                </div>
                                <div class="mb-4">
                                    <label for="registrationNumber" class="block text-sm font-medium text-gray-700">Registration Number</label>
                                    <p id="registrationNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $user->academicDetail->matric_no ?? "Not Available" }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 <div class="flex justify-end mt-6 mb-4">
                    <a href="{{route('cit.first-school-fees')}}"
                        class="inline-block px-6 py-3 m-0 font-bold text-center uppercase align-middle transition-all bg-gray-200 border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">Back</a>
                        @if($user->academicDetail?->matric_no)
                        <div class="text-center text-green-600 font-bold text-xs m-4 bg-gradient-lime text-white">
                                        Matric number has already been issued.
                                    </div>
                       
                        @else
                          <button type="submit"
                        class="inline-block px-6 py-3 m-0 ml-2 mr-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-lime  shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Generate</button>
                            <div wire:loading wire:target="addRegistrationNumber">
                                updating status...
                            </div>
                        
                            
                        @endif
                   
                </div>
            </div>
        </div>
    </form>
    </div>
</div>