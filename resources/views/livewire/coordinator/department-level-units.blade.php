<div>
    <div class="mb-12 w-8/12 mx-auto">
        <div class="relative flex flex-col min-w-0 mt-12 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border"
            id="add-course">
            <div class="p-6 mb-0 rounded-t-2xl justify-center items-center">
                <h5 class="dark:text-white">Add max unit</h5>
            </div>
            <form wire:submit.prevent='createCourse'>
                <div class="flex-auto p-6 pt-0 mr-5">

                    <div class="flex flex-wrap -mx-3">

                        <div class="w-6/12 max-w-full px-3 flex-0">
                            <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                for="level">Level</label>
                            <div wire:ignore x-data x-init="
                                                                      choices = new Choices($refs.levels, {
                                                                          searchEnabled: false
                                                                      });
                                                                      $refs.roles.addEventListener('change', function (event) {
                                                                          values = event.detail.value;
                                                                          @this.set('form.level_id', values);
                                                                      })">
                                <select wire:model="form.level_id" choice name="choices-level" id="choices-level"
                                    x-ref="levels">
                                    <option value="">Select Level</option>
                                    @foreach ($levels as $level)

                                        <option value="{{ $level['value'] }}">{{ $level['name'] }}</option>

                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="w-4/12 max-w-full px-3 flex-0">
                            <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                for="Email">Max Credit
                                Unit</label>
                            <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                                <input type="number" wire:model='courseForm.units' placeholder="Credit Unit"
                                    class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            </div>
                        </div>

                    </div>
                    <div class="flex justify-end mt-6 mb-4">
                        <button type="button"
                            class="inline-block px-6 py-3 m-0 font-bold text-center uppercase align-middle transition-all bg-gray-200 border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">Cancel</button>
                        <button type="submit" wire:target="addMaxUnit" wire:loading.attr="disabled"
                            wire:loading.class="bg-gradient-gray text-slate-800"
                            wire:loading.class.remove="bg-gradient-lime text-slate-800"
                            class="inline-block px-6 py-3 m-0 ml-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-lime shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">
                            <span wire:loading.remove wire:target="addMaxUnit">Add</span>
                            <span wire:loading wire:target="addMaxUnit">Loading...</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
    @push('js')
        <script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
    @endpush