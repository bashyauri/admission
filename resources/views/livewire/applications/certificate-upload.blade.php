<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto flex-0 lg:w-9/12">
        <div
            class="relative flex flex-col flex-auto min-w-0 p-4 mt-6 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">

            <hr
                class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-horizontal-dark dark:bg-gradient-horizontal-light" />
                <div wire:loading>
                    <i class="fas fa-spinner fa-spin"></i> wait...
                </div>
                <div wire:offline>
                    This device is currently offline.
                </div>
        <form wire:submit="save">

            <div class="flex flex-wrap -mx-3">
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="inline-block mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="Start Date">Qualification</label>

                    <select wire:model="name" choices
                        class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                        data-input >
                        <option value="">Select Certificate</option>
                        @foreach ($this->certificates as $certificate)


                        <option value="{{strtolower($certificate->certificate_name)}}">{{ucwords($certificate->certificate_name)}}</option>

                        @endforeach
                    </select>
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="w-6/12 max-w-full px-3 flex-0">
                    <label class="inline-block mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                        for="End Date">upload file:(pdf)</label>
                    <input wire:model="certificate"
                        class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                        type="file" accept=".pdf" placeholder="Please select end date"  />
                        @error('certificate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end mt-6 mb-4">
                <button type="button"
                    class="inline-block px-6 py-3 m-0 font-bold text-center uppercase align-middle transition-all bg-gray-200 border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">Cancel</button>
                <button type="submit"
                    class="inline-block px-6 py-3 m-0 ml-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-fuchsia shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Save</button>
            </div>
        </form>
        @include('livewire.applications.includes.certificate-upload-table')
        <div class="flex justify-end mt-6 mb-4">

            <a href ="{{route('proposed-course')}}"
            class="inline-block float-right px-8 py-2 mt-2 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">Next</a>
        </div>
        </div>


    </div>

</div>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
<script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
<script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>

@endpush
