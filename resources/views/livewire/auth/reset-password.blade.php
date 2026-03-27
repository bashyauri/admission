<main class="mt-0 transition-all duration-200 ease-soft-in-out">
    <section class="min-h-75-screen ">
        <div class="container">
            <div class="flex flex-wrap justify-center -mx-3">
                <div class="w-full max-w-full px-3 mx-auto shrink-0 md:flex-0 md:w-7/12 lg:w-5/12">
                    <div
                        class="relative z-0 flex flex-col min-w-0 mt-40 mb-6 break-words bg-white border-0 sm:mt-64 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                        <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 text-center pt-6 pb-1">
                            @if (Session::has('email'))
                            <div class="fixed mb-4 bottom-1/100 right-1/100 z-2">
                                <div id="alert"
                                    class="max-w-full p-2 transition-opacity ease-linear bg-white border-0 rounded-lg pointer-events-auto w-85 text-size-sm shadow-soft-2xl bg-clip-padding">
                                    <div class="flex items-center p-3 rounded-t-lg bg-clip-padding text-slate-700">
                                        <i class="mr-2 text-transparent ni ni-notification-70 bg-gradient-red bg-clip-text"></i>
                                        <span class="mr-auto font-semibold text-transparent bg-gradient-red bg-clip-text">Soft UI
                                            Dashboard</span>
                                        <small class="text-slate-500">Now</small>
                                        <button type="button" onclick="alertClose()">
                                            <i class="ml-4 cursor-pointer fas fa-times"></i>
                                        </button>
                                    </div>
                                    <hr
                                        class="h-px m-0 bg-transparent border-0 opacity-25 bg-gradient-horizontal-dark dark:bg-gradient-horizontal-light" />
                                    <div class="p-3 break-words">{{ Session::get('email') }}</div>
                                </div>
                            </div>
                            @endif
                            <h4 class="mb-1 font-bold">Reset password</h4>
                            <p class="mb-0">Here you can reset your password! <br />Enter your email and your new
                                password below!</p>
                        </div>

                        <div class="flex-auto p-6 text-center">

                            <form wire:submit="resetPassword" role="form">

                                <div class="mb-4">
                                    <input wire:model="email" type="email"
                                        class="
                                        focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                                        placeholder="Email" aria-label="Email" aria-describedby="email-addon" required
                                        />
                                    @error('email')
                                    <p class="text-red-500 text-size-sm">{{ $message }}</p>
                                    @enderror
                                    </div>

                                <div class="mb-4">
                                    <div x-data="{ show: false }" class="relative">
                                        <input wire:model="password" :type="show ? 'text' : 'password'" id="password"
                                            class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none pr-10"
                                            placeholder="New password" aria-label="Password"
                                            aria-describedby="password-addon" required />
                                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 focus:outline-none" tabindex="-1">
                                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95m3.671-2.568A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.293 5.03M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('password')
                                    <p class="text-red-500 text-size-sm">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                        <input wire:model="password_confirmation" :type="show ? 'text' : 'password'" id="password_confirmation"
                                            class="
                                            focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none pr-10"
                                            placeholder="Confirm password" aria-label="Confirm Password"
                                            aria-describedby="password-confirmation-addon" required />
                                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 focus:outline-none" tabindex="-1">
                                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95m3.671-2.568A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.293 5.03M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                            </svg>
                                        </button>
                                        @error('password_confirmation')
                                        <p class="text-red-500 text-size-sm">{{ $message }}</p>
                                        @enderror
                                </div>

                                <div class="text-center">
                                    <button type="submit"
                                        class="inline-block px-16 py-3.5 mb-0 mt-4 font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-sm ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">
                                        Reset</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
</main>

@push('js')
<script>
    function alertClose() {
        document.getElementById("alert").style.display = "none";
    }
</script>
@endpush
