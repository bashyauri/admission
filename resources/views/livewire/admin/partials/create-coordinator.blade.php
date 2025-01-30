<div class="relative flex flex-col min-w-0 mt-6 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border"
    id="coordinator">
    <div class="p-6 mb-0 rounded-t-2xl">
        <h5 class="dark:text-white">Create Coordinator</h5>
    </div>
    <form wire:submit.prevent='createCordinator'>
        <div class="flex-auto p-6 pt-0">
            <div class="flex flex-wrap -mx-3">
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="First Name">First Name</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input type="text" wire:model='form.surName' placeholder="Last Name"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                </div>
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="Last Name">Last Name</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input type="text" wire:model='form.firstName' placeholder="Other Names"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap -mx-3">
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="Email">Email</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input type="email" wire:model='form.email' placeholder="Institution Email Address"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                </div>
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="Confirmation Email">Confirmation Email</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input type="email" wire:model='form.confirmationEmail' placeholder="Confirmation email"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap -mx-3">
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="Email">Phone</label>
                    <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                        <input type="text" wire:model='form.phone' placeholder="Phone Number"
                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                    </div>
                </div>
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="department">Department</label>
                    <div wire:ignore x-data x-init="
                                      choices = new Choices($refs.roles, {
                                          searchEnabled: false
                                      });
                                      $refs.roles.addEventListener('change', function (event) {
                                          values = event.detail.value;
                                          @this.set('form.department_id', values);
                                      })">
                        <select wire:model="form.department_id" choice name="choices-department" id="choices-gender"
                            x-ref="roles">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)

                                <option value="{{$department->id}}">{{$department->name}}</option>

                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


            <div class="flex justify-end mt-6 mb-4">
                <button type="button"
                    class="inline-block px-6 py-3 m-0 font-bold text-center uppercase align-middle transition-all bg-gray-200 border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">Cancel</button>
                <button type="submit"
                    class="inline-block px-6 py-3 m-0 ml-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-fuchsia shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Save</button>
            </div>
        </div>
    </form>
</div>