<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 text-center flex-0">
        <h3 class="mt-12">Build Your Profile</h3>
        <h5 class="font-normal dark:text-white text-slate-400">This information will let us know more about you.
        </h5>
        <div multisteps-form class="mb-12">
            <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 mx-auto my-12 flex-0 lg:w-8/12">
                    <div class="grid grid-cols-3">
                        <button aria-controls="about" type="button"
                            class="before:w-3.4 before:h-3.4 before:rounded-circle before:scale-120 rounded-0 -indent-330 relative m-0 cursor-pointer border-none bg-transparent px-1.5 pb-0.5 pt-5 text-slate-700 outline-none transition-all ease-linear before:absolute before:top-0 before:left-1/2 before:z-30 before:box-border before:block before:-translate-x-1/2 before:border-2 before:border-solid before:border-current before:bg-current before:transition-all before:ease-linear before:content-[''] sm:indent-0"
                            title="About"><span class="text-slate-400">About</span></button>
                        {{-- <button aria-controls="account" type="button"
                            class="before:w-3.4 before:h-3.4 before:rounded-circle after:top-1.25 rounded-0 -indent-330 relative m-0 cursor-pointer border-none bg-transparent px-1.5 pb-0.5 pt-5 text-slate-100 outline-none transition-all ease-linear before:absolute before:top-0 before:left-1/2 before:z-30 before:box-border before:block before:-translate-x-1/2 before:border-2 before:border-solid before:border-current before:bg-white before:transition-all before:ease-linear before:content-[''] after:absolute after:left-[calc(-50%-13px/2)] after:z-10 after:block after:h-0.5 after:w-full after:bg-current after:transition-all after:ease-linear after:content-[''] sm:indent-0"
                            title="Account">Account</button> --}}
                        <button aria-controls="address" type="button"
                            class="before:w-3.4 before:h-3.4 before:rounded-circle after:top-1.25 rounded-0 -indent-330 relative m-0 cursor-pointer border-none bg-transparent px-1.5 pb-0.5 pt-5 text-slate-100 outline-none transition-all ease-linear before:absolute before:top-0 before:left-1/2 before:z-30 before:box-border before:block before:-translate-x-1/2 before:border-2 before:border-solid before:border-current before:bg-white before:transition-all before:ease-linear before:content-[''] after:absolute after:left-[calc(-50%-13px/2)] after:z-10 after:block after:h-0.5 after:w-full after:bg-current after:transition-all after:ease-linear after:content-[''] sm:indent-0"
                            title="Address">Address</button>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 m-auto flex-0 lg:w-8/12">
                    <form wire:submit="store" enctype="multipart/form-data" class="relative mb-32">
                        <div active form="about"
                            class="absolute top-0 left-0 flex flex-col visible w-full h-auto min-w-0 p-4 break-words bg-white border-0 opacity-100 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">

                            <div class="flex flex-wrap -mx-3 text-center">
                                <div class="w-10/12 max-w-full px-3 mx-auto flex-0">
                                    <h5 class="font-normal dark:text-white">Let's start with the basic
                                        information</h5>
                                    <p>Let us know your name and email address. Use an address you don't mind
                                        other users contacting you at</p>
                                </div>
                            </div>

                                <div class="flex flex-wrap mt-4 -mx-3">
                                    <div class="w-full max-w-full px-3 flex-0 sm:w-4/12">

                                        <div

                                            class="inline-flex items-center justify-center w-28 h-28 relative text-white transition-all duration-200 text-size-base ease-soft-in-out rounded-xl">
                                            @if($picture)
                                            <img src="{{ $picture->temporaryUrl() }}"
                                                class="w-full rounded-lg" alt="Profile Photo">
                                            @else
                                            <img src="{{ asset('assets') }}/img/avatar-default.jpg"
                                                class="w-full rounded-lg">
                                            @endif

                                            <label for="file-input"
                                                class="inline-block w-6 h-6 p-1.2 right-0 bottom-0 absolute -mb-2 -mr-2 font-bold text-center uppercase align-middle transition-all bg-gradient-gray text-slate-800 border-0 border-transparent border-solid rounded-lg cursor-pointer leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 active:opacity-85">
                                                <i class="top-0 fa fa-pen text-size-3xs"></i>
                                            </label>
                                                        <input wire:model='picture' type="file" id="file-input" >
                                        </div>
                                        @error('picture')
                                        <p class="text-size-sm text-red-500">{{ $message }} </p>
                                        @enderror
                                    </div>

                                    <div class="w-full max-w-full px-3 mt-6 text-left flex-0 sm:w-8/12 sm:mt-0">
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="Date of Birth">Date of Birth</label>
                                            <input datetimepicker  wire:model="birthday"
                                             class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" type="text" placeholder="Please select a date" />

                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="gender">Gender</label>
                                            <div className="w-4/5">
                                                <select choices-select="" wire:model="gender"  class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" >
                                                    <option value="">Select Gender</option>
                                                  <option value="male">Male</option>
                                                  <option value="female">Female</option>

                                                </select>
                                              </div>
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="Email Address">Marital Status</label>
                                            <div className="w-4/5">
                                                <select choices-select="" wire:model="maritalStatus" name="choices"  class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" >
                                                    <option value="">Status</option>
                                                  <option value="married">Married</option>
                                                  <option value="single">Single</option>
                                                  <option value="divorced">Divorced</option>
                                                  <option value="widowed">Widowed</option>

                                                </select>
                                              </div>
                                    </div>

                                </div>
                                <div class="flex mt-6">
                                    <button type="button" aria-controls="address" next-form-btn href="javascript:;"
                                        class="inline-block px-6 py-3 mb-0 ml-auto font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">Next</button>
                                </div>
                            </div>




                        <div form="address"
                            class="absolute top-0 left-0 flex flex-col invisible w-full h-0 min-w-0 p-4 break-words bg-white border-0 opacity-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">

                            <div class="flex flex-wrap -mx-3 text-center">
                                <div class="w-10/12 max-w-full px-3 mx-auto flex-0">
                                    <h5 class="font-normal dark:text-white">Are you living in a nice area?</h5>
                                    <p>One thing I love about the later sunsets is the chance to go for a walk
                                        through the neighborhood woods before dinner</p>
                                </div>
                            </div>

                            <div>
                                <div class="flex flex-wrap -mx-3 text-left">
                                    <div class="w-full max-w-full px-3 mt-4 ml-auto flex-0 md:w-7/12">
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="State">State</label>

                                        <select choice
                                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                                            wire:model="state" id="choices-country">
                                            <option value="Argentina">Argentina</option>
                                            <option value="Albania">Albania</option>
                                            <option value="Algeria">Algeria</option>
                                            <option value="Andorra">Andorra</option>
                                            <option value="Angola">Angola</option>
                                            <option value="Brasil">Brasil</option>
                                        </select>
                                    </div>
                                    <div class="w-full max-w-full px-3 mt-4 ml-auto flex-0 md:w-5/12">
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="lga">Lga</label>
                                        <select choice
                                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                                            wire:model="lga" id="choices-lga">
                                            <option value="Argentina">Argentina</option>
                                            <option value="Albania">Albania</option>
                                            <option value="Algeria">Algeria</option>
                                            <option value="Andorra">Andorra</option>
                                            <option value="Angola">Angola</option>
                                            <option value="Brasil">Brasil</option>
                                        </select>
                                    </div>
                                    <div class="w-full max-w-full px-3 mt-4 ml-auto flex-0 md:w-6/12">
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="Street Name">Home Address</label>
                                        <input type="text" wire:model="homeAddress" placeholder="Home Address"
                                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                                    </div>
                                    <div class="w-full max-w-full px-3 mt-4 ml-auto flex-0 md:w-6/12">
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="Street No">Correspondence Address</label>
                                        <input type="text" wire:model="corAddress" placeholder="Correspondence Address"
                                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                                    </div>
                                    <div class="w-full max-w-full px-3 mt-4 ml-auto flex-0 md:w-8/12">
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="Street Name">Next of Kin Address</label>
                                        <input type="text" wire:model="kinAddress" placeholder="Next of Kin Address"
                                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                                    </div>
                                    <div class="w-full max-w-full px-3 mt-4 ml-auto flex-0 md:w-4/12">
                                        <label
                                            class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                            for="Street No">Next of Kin Name</label>
                                        <input type="text" wire:model="kinName"  placeholder="Next of Kin Name"
                                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                                    </div>


                                </div>
                                <div class="flex flex-wrap -mx-3">
                                    <div class="flex w-full max-w-full px-3 mt-6 flex-0">
                                        <button type="button" aria-controls="about" prev-form-btn href="javascript:;"
                                            class="inline-block px-6 py-3 mb-0 font-bold text-right uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">Prev</button>
                                        <button type="submit" send-form-btn href="javascript:;"
                                            class="inline-block px-6 py-3 mb-0 ml-auto font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">Send</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
<script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>

@endpush
