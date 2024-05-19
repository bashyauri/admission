<div>
<div class="w-full max-w-full px-3 lg:flex-0 shrink-0">

    <form wire:submit.prevent="save" enctype="multipart/form-data">

        <div class="relative flex flex-col flex-auto min-w-0 p-4 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border"
            id="profile">
            <div class="flex flex-wrap items-center justify-center -mx-3">
                <div class="w-4/12 max-w-full px-3 flex flex-col sm:w-auto">

                    <div class="w-full max-w-full flex">
                        <div
                            class="inline-flex items-center justify-center w-19 relative text-white transition-all duration-200 text-size-base ease-soft-in-out rounded-xl">
                            @if($form->picture)
                            <img src="{{ $form->picture->temporaryUrl() }}"
                                class="w-19 shadow-soft-sm rounded-xl" alt="Profile Photo">
                            @elseif (auth()->user()->picture)

                            <img src="{{ asset('storage/' . auth()->user()->picture) }}" alt="avatar"
                                class="w-19 shadow-soft-sm rounded-xl">
                            @else
                            <img src="{{ asset('assets') }}/img/avatar-default.jpg" alt="avatar"
                                class="w-19 shadow-soft-sm rounded-xl">
                            @endif

                            <label for="file-input"
                                class="inline-block w-6 h-6 p-1.2 right-0 bottom-0 absolute -mb-2 -mr-2 font-bold text-center uppercase align-middle transition-all bg-gradient-gray text-slate-800 border-0 border-transparent border-solid rounded-lg cursor-pointer leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 active:opacity-85">
                                <i class="top-0 fa fa-pen text-size-3xs"></i>
                            </label>
                            <input wire:model='form.picture' type="file" id="file-input" style="display: none">


                        </div>

                    </div>
                    <div cla wire:loading wire:target="form.picture" class="text-teal-500">
                        uploading...
                       </div>

                </div>

                <div class="w-8/12 max-w-full px-3 my-auto flex-0 sm:w-auto">
                    <div class="h-full">
                        <h5 class="mb-1 font-bold dark:text-white">{{ auth()->user()->name }}</h5>
                        <p class="mb-0 font-semibold leading-normal text-size-sm">CEO / Co-Founder</p>
                    </div>
                </div>
                <div class="flex max-w-full px-3 mt-4 sm:flex-0 shrink-0 sm:mt-0 sm:ml-auto sm:w-auto">
                    <label profile-visibility-toggle-label for="profile-visibility-toggle"
                        class="inline-block mb-0 ml-1 font-normal cursor-pointer text-size-sm text-slate-700 dark:text-white/80">
                        <small>Switch to invisible</small>
                    </label>
                    <div class="min-h-6 ml-2 mb-0.5 block pl-12">
                        <input checked profile-visibility-toggle
                            class="rounded-10 duration-250 ease-soft-in-out after:rounded-circle after:shadow-soft-2xl after:duration-250 checked:after:translate-x-5.3 h-5-em mt-0.5-em relative float-left -ml-12 w-10 cursor-pointer appearance-none border border-solid border-gray-200 bg-slate-800/10 bg-none bg-contain bg-left bg-no-repeat align-top transition-all after:absolute after:top-px after:h-4 after:w-4 after:translate-x-px after:bg-white after:content-[''] checked:border-slate-800/95 checked:bg-slate-800/95 checked:bg-none checked:bg-right"
                            type="checkbox" />
                    </div>
                </div>

            </div>

            @error('form.picture')
            <p class="mt-4 text-size-sm text-red-500">{{ $message }} </p>
            @enderror

        </div>

        <div class="relative flex flex-col min-w-0 mt-6 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border"
            id="basic-info">
            <div class="p-6 mb-0 rounded-t-2xl">
                <h5 class="dark:text-white">Profile</h5>
            </div>

            <div class="flex-auto p-6 pt-0">

                @if (Session::has('status'))
                <div class="fixed bottom-1/100 right-1/100 z-2 mb-16 pb-4 mr-1.25">
                    <div id="alert"
                        class="w-85 text-size-sm shadow-soft-2xl pointer-events-auto max-w-full rounded-lg border-0 bg-white bg-clip-padding p-2 transition-opacity ease-linear">
                        <div class="flex items-center p-3 rounded-t-lg bg-clip-padding text-slate-700">
                            <i class="mr-2 ni ni-check-bold text-lime-500"></i>
                            <span class="mr-auto font-semibold">Soft UI Dashboard</span>
                            <small class="text-slate-500">Now</small>
                            <button type="button" onclick="alertClose()">
                                <i class="ml-4 cursor-pointer fas fa-times"></i>
                            </button>
                        </div>
                        <hr
                            class="h-px m-0 bg-transparent border-0 opacity-25 bg-gradient-horizontal-dark dark:bg-gradient-horizontal-light" />
                        <div class="p-3 break-words">{{ Session::get('status') }}</div>
                    </div>
                </div>
                @endif
                <div class="flex flex-wrap -mx-3">
                    <div class="w-4/12 max-w-full px-3 flex-0">

                        <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="Name">Birthday</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <input datetimepicker  wire:model="form.birthday"
                                             class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" type="text" placeholder="Please select a date" />


                            @error('form.birthday')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                    <div class="w-4/12 max-w-full px-3 flex-0">

                        <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="gender">Gender</label>
                        <div className="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <select choices-select="" name="choices" wire:model="form.gender"  class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" >
                                <option value="">Select Gender</option>
                              <option value="male">Male</option>
                              <option value="female">Female</option>

                            </select>
                            @error('form.gender')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                    <div class="w-4/12 max-w-full px-3 flex-0">

                        <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="Email">Marital Sattus</label>
                        <div className="w-4/5">
                            <select choices-select="" wire:model="form.maritalStatus" name="choices"  class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" >
                                <option value="">Status</option>
                              <option value="married">Married</option>
                              <option value="single">Single</option>
                              <option value="divorced">Divorced</option>
                              <option value="widowed">Widowed</option>

                            </select>
                            @error('form.maritalStatus')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>

                </div>
                <div class="flex flex-wrap -mx-3">
                    <div class="w-6/12 max-w-full px-3 flex-0">

                        <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="stateID">State</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <select  wire:model.live="form.stateID" class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"  >
                                <option value="">Select</option>
                                @foreach ($this->states as $state)
                                            <option value="{{ $state->id }}">{{$state->name}}</option>
                                @endforeach

                            </select>

                            @error('form.stateID')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                    <div class="w-6/12 max-w-full px-3 flex-0">

                        <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="lgaID">LGA</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <select wire:model.live="form.lgaID" class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">Select</option>
                                @foreach ($this->lgas as $lga)
                                            <option value="{{$lga->id}}">{{$lga->name}}</option>
                                            @endforeach

                            </select>
                            @error('form.lgaID')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>


                </div>


                <div class="flex flex-wrap -mx-3">
                    <div class="w-6/12 max-w-full px-3 flex-0">

                        <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="homeAddress">Home Address</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <input wire:model="form.homeAddress" type="text" name="Location"
                                placeholder="Home Address"
                                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            @error('form.homeAddress')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                    <div class="w-6/12 max-w-full px-3 flex-0">

                        <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="corAddress">Correspondent Address</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <input wire:model="form.corAddress" type="text" name="Phone" placeholder="Correspondent Address"
                                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            @error('form.corAddress')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3">
                    <div class="w-6/12 max-w-full px-3 flex-0">

                        <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="Location">Next of Kin Name</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <input wire:model="form.kinName" type="text"
                                placeholder="Next of Kin Name"
                                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            @error('form.kinName')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                    <div class="w-6/12 max-w-full px-3 flex-0">

                        <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="kinAddress">Next of kin Address</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <input wire:model="form.kinAddress" type="text"  placeholder="Next of kin Address"
                                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            @error('form.kinAddress')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3">

                    <div class="w-6/12 max-w-full px-3 flex-0">

                        <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="kinPhone">Next of Kin Phone</label>
                        <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <input wire:model="form.kinPhone" type="text" placeholder="0723456789"
                                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            @error('form.kinPhone')
                            <p class="text-size-sm text-red-500">{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap -mx-3">
                    <div class="flex-auto p-6 pt-0">
                        <button type="submit"
                            class="inline-block float-right px-8 py-2 mt-16 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">Save
                            Changes</button>
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3">
                    <a href="{{route('school-attended')}}" class="inline-block float-right px-8 py-2 mt-2 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">
                        to school attended
                    </a>
                </div>




                </div>
            </div>

        </div>


    </form>



</div>
</div>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
<script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
<script>
    function alertClose() {
        document.getElementById("alert").style.display = "none";
    }
</script>

@endpush
