<div class="w-full max-w-full px-3 lg:flex-0 shrink-0">
<div class="relative flex flex-col min-w-0 mt-6 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border lg:w-9/12"
            id="basic-info">
            <div class="p-6 mb-0 rounded-t-2xl">
                <h5 class="dark:text-white">Course</h5>
            </div>

            <form wire:submit.prevent="save">

            <div class="flex-auto p-6 pt-0">
                <div wire:loading>
                    <i class="fas fa-spinner fa-spin"></i> wait...
                </div>
                <div class="flex flex-wrap -mx-3">
                    <div class="w-full md:w-6/12 max-w-full px-3 flex-0">
                        <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80" for="Name">Department</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <select  wire:model.live="form.departmentID"
                                    class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">Select an Option</option>
                                @foreach ($this->departments as $department)
                                <option value="{{$department->id}}">{{$department->name}}</option>

                                @endforeach

                        </select>


                            @error('form.department')
                            <p class="text-size-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="w-full md:w-6/12 max-w-full px-3 flex-0">
                        <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80" for="course">Course of Study</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <select choices-select=""  wire:model.live="form.courseID"
                                    class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">Select an Option</option>
                                @foreach ($this->courses as $course)
                                <option value="{{$course->id}}">{{$course->name}}</option>

                                @endforeach

                            </select>
                            @error('form.courseID')
                            <p class="text-size-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                </div>
                <div class="flex justify-end mt-6 mb-4">
                    <a href="{{route('upload-certificate')}}"
                        class="inline-block px-6 py-3 m-0 font-bold text-center uppercase align-middle transition-all bg-gray-200 border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">Back</a>
                    <button type="submit"
                        class="inline-block px-6 py-3 m-0 ml-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-fuchsia shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Save</button>
                </div>

                <div class="flex flex-wrap -mx-3">
                    <div class="flex-auto p-6 pt-0">
                        <a href="{{route('print-form')}}"
                                class="inline-block float-right px-8 py-2 mt-16 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">
                            Next
                        </a>
                    </div>
                </div>
            </form>


            </div>


        </div>
    </div>
