<div class="relative flex flex-col min-w-0 mt-6 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border"
    id="add-course">
    <div class="p-6 mb-0 rounded-t-2xl">
        <h5 class="dark:text-white">Create Course</h5>
        <p class="mb-0 text-sm text-slate-500 dark:text-white/60">The course catalog is shared. Course codes and titles can only be added once; use an existing course when assigning it elsewhere.</p>
    </div>
    <form wire:submit.prevent='createCourse'>
        <div class="flex-auto p-6 pt-0">
            <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
                    <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="course-code">Course Code</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input id="course-code" type="text" wire:model='courseForm.code' placeholder="e.g. CSC101" autocomplete="off" aria-describedby="course-code-help"
                            aria-invalid="{{ $errors->has('courseForm.code') ? 'true' : 'false' }}"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid {{ $errors->has('courseForm.code') ? 'border-red-500' : 'border-gray-300' }} bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                    <p id="course-code-help" class="mt-1 text-xs text-slate-500">Use the official code. Letter case and extra spaces are normalized automatically.</p>
                    @error('courseForm.code') <p class="mt-1 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="w-full max-w-full px-3 md:w-8/12 flex-0">
                    <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="course-title">Course Title</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input id="course-title" type="text" wire:model='courseForm.title' placeholder="e.g. Introduction to Computer Science" autocomplete="off"
                            aria-describedby="course-title-help"
                            aria-invalid="{{ $errors->has('courseForm.title') ? 'true' : 'false' }}"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid {{ $errors->has('courseForm.title') ? 'border-red-500' : 'border-gray-300' }} bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                    <p id="course-title-help" class="mt-1 text-xs text-slate-500">Use the official title. Extra spaces are cleaned up automatically.</p>
                    @error('courseForm.title') <p class="mt-1 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
                    <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="course-units">Credit Units</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input id="course-units" type="number" min="1" step="1" wire:model='courseForm.units' placeholder="e.g. 3"
                            aria-invalid="{{ $errors->has('courseForm.units') ? 'true' : 'false' }}"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid {{ $errors->has('courseForm.units') ? 'border-red-500' : 'border-gray-300' }} bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                    @error('courseForm.units') <p class="mt-1 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="w-full max-w-full px-3 md:w-6/12 flex-0">
                    <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="course-level">Level</label>
                    <div wire:ignore x-data x-init="
                                                                      choices = new Choices($refs.levels, {
                                                                          searchEnabled: false
                                                                      });
                                                                      $refs.levels.addEventListener('change', function (event) {
                                                                          values = event.target.value;
                                                                          @this.set('courseForm.level_id', values);
                                                                      })">
                        <select wire:model="courseForm.level_id" choice name="course-level" id="course-level"
                            x-ref="levels">
                            <option value="">Select level</option>
                            @foreach ($levels as $level)

                                <option value="{{ $level['value'] }}">{{ $level['name'] }}</option>

                            @endforeach
                        </select>
                    </div>
                    @error('courseForm.level_id') <p class="mt-1 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p> @enderror
                </div>

            </div>





            <div class="flex justify-end mt-6 mb-4">
                <button type="submit" wire:target="createCourse" wire:loading.attr="disabled"
                    wire:loading.class="bg-gradient-gray text-slate-800"
                    wire:loading.class.remove="bg-gradient-fuchsia text-slate-800"
                    class="inline-block px-6 py-3 m-0 ml-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-fuchsia shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">
                    <span wire:loading.remove wire:target="createCourse">Add Course</span>
                    <span wire:loading wire:target="createCourse">Checking and saving...</span>
                </button>
            </div>

        </div>
    </form>
</div>
