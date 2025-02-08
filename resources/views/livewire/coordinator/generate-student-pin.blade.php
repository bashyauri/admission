@use('App\Models\User')
<div>
    <div class="flex flex-wrap -mx-3 mt-6">
        <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
            <div
                class="relative flex flex-col min-w-0 mb-12 overflow-auto overflow-x-hidden break-words bg-white border-0 max-h-70-screen lg:mb-0 bg-white/80 shadow-blur dark:bg-gray-950 dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4">

                    <h6 class="dark:text-white">Student</h6>
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
                    @if ($search !== '')
                        @forelse ($students as $student)

                            <a href="javascript:;" class="block p-2">
                                <div class="flex p-2">
                                    <img src="{{ asset('storage/' . User::find($student->user_id)->picture) }}" alt="Image"
                                        class="inline-flex items-center justify-center w-12 h-12 text-white transition-all duration-200 shadow-soft-2xl text-size-base ease-soft-in-out rounded-xl">
                                    <div class="ml-4">
                                        <h6 class="mb-0 dark:text-white">
                                            {{ User::find($student->user_id)->full_name }}
                                        </h6>
                                        <p class="mb-2 leading-tight text-slate-500 text-size-xs">{{ $student->matric_no }}</p>
                                        <button wire:click.prevent="generatePin({{ $student->id }})" {{-- Added student ID --}}
                                            class="inline-block px-4 py-2 m-0 ml-2 mr-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-lime shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">
                                            Generate Pin
                                        </button>
                                        <div wire:loading wire:target="generatePin({{ $student->id }})"> {{-- Targeted the
                                            specific button --}}
                                            Updating status...
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400">No students found.</p>
                        @endforelse
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>