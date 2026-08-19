<!doctype html>
<main class="mt-0 transition-all duration-200 ease-soft-in-out">
    <div
        class="min-h-screen p-0 relative overflow-hidden flex items-center bg-cover bg-center bg-[url('../../assets/img/curved-images/curved9.jpg')]">
        <span class="absolute top-0 left-0 w-full h-full bg-center bg-cover opacity-60 bg-gradient-dark-gray"></span>
        <div class="container z-1 ">
            <div class="flex flex-wrap justify-center -mx-3">
                <div class="w-full max-w-full px-3 shrink-0 md:flex-0 md:w-7/12 lg:w-4/12">
                    <div
                        class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-6 text-center lg:px-12 lg:py-12">
                            <div class="mb-6">
                                <div
                                    class="inline-block py-4 mx-auto mb-6 text-center text-black bg-center fill-current bg-gradient-orange shadow-soft-2xl rounded-circle w-25 h-25 stroke-none">
                                    <svg class="mt-3" width="40px" height="35px" viewBox="0 0 40 40" version="1.1"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <title>spaceship</title>
                                        <g id="Basic-Elements" stroke="none" stroke-width="1" fill="none"
                                            fill-rule="evenodd">
                                            <g id="Rounded-Icons" transform="translate(-1720.000000, -592.000000)"
                                                fill="#FFFFFF" fill-rule="nonzero">
                                                <g id="Icons-with-opacity"
                                                    transform="translate(1716.000000, 291.000000)">
                                                    <g id="spaceship" transform="translate(4.000000, 301.000000)">
                                                        <path
                                                            d="M39.3,0.706666667 C38.9660984,0.370464027 38.5048767,0.192278529 38.0316667,0.216666667 C14.6516667,1.43666667 6.015,22.2633333 5.93166667,22.4733333 C5.68236407,23.0926189 5.82664679,23.8009159 6.29833333,24.2733333 L15.7266667,33.7016667 C16.2013871,34.1756798 16.9140329,34.3188658 17.535,34.065 C17.7433333,33.98 38.4583333,25.2466667 39.7816667,1.97666667 C39.8087196,1.50414529 39.6335979,1.04240574 39.3,0.706666667 Z M25.69,19.0233333 C24.7367525,19.9768687 23.3029475,20.2622391 22.0572426,19.7463614 C20.8115377,19.2304837 19.9992882,18.0149658 19.9992882,16.6666667 C19.9992882,15.3183676 20.8115377,14.1028496 22.0572426,13.5869719 C23.3029475,13.0710943 24.7367525,13.3564646 25.69,14.31 C26.9912731,15.6116662 26.9912731,17.7216672 25.69,19.0233333 L25.69,19.0233333 Z"
                                                            id="Shape"></path>
                                                        <path
                                                            d="M1.855,31.4066667 C3.05106558,30.2024182 4.79973884,29.7296005 6.43969145,30.1670277 C8.07964407,30.6044549 9.36054508,31.8853559 9.7979723,33.5253085 C10.2353995,35.1652612 9.76258177,36.9139344 8.55833333,38.11 C6.70666667,39.9616667 0,40 0,40 C0,40 0,33.2566667 1.855,31.4066667 Z"
                                                            id="Path"></path>
                                                        <path
                                                            d="M17.2616667,3.90166667 C12.4943643,3.07192755 7.62174065,4.61673894 4.20333333,8.04166667 C3.31200265,8.94126033 2.53706177,9.94913142 1.89666667,11.0416667 C1.5109569,11.6966059 1.61721591,12.5295394 2.155,13.0666667 L5.47,16.3833333 C8.55036617,11.4946947 12.5559074,7.25476565 17.2616667,3.90166667 L17.2616667,3.90166667 Z"
                                                            id="color-2" opacity="0.598539807"></path>
                                                        <path
                                                            d="M36.0983333,22.7383333 C36.9280725,27.5056357 35.3832611,32.3782594 31.9583333,35.7966667 C31.0587397,36.6879974 30.0508686,37.4629382 28.9583333,38.1033333 C28.3033941,38.4890431 27.4704606,38.3827841 26.9333333,37.845 L23.6166667,34.53 C28.5053053,31.4496338 32.7452344,27.4440926 36.0983333,22.7383333 L36.0983333,22.7383333 Z"
                                                            id="color-3" opacity="0.598539807"></path>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                @include('flash-messages')

                                @if (session()->has('success'))
                                    <div class="p-3 mb-4 text-size-sm font-semibold text-white bg-green-600 rounded-lg shadow-soft-md text-left flex items-start">
                                        <svg class="w-5 h-5 mr-2 inline-block shrink-0 mt-0.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-white">{{ session('success') }}</span>
                                    </div>
                                @endif

                                @if (session()->has('error'))
                                    <div class="p-3 mb-4 text-size-sm font-semibold text-white bg-red-600 rounded-lg shadow-soft-md text-left flex items-start">
                                        <svg class="w-5 h-5 mr-2 inline-block shrink-0 mt-0.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-white">{{ session('error') }}</span>
                                    </div>
                                @endif

                                <div class="mb-6 text-center">
                                    <h3 class="font-bold text-slate-800 dark:text-white text-size-xl mb-2">Enter Verification Code</h3>
                                    <p class="text-size-sm text-slate-600 dark:text-slate-300">A 6-digit verification code has been sent to your email address. Please check your inbox and spam folder.</p>
                                </div>

                                <form wire:submit.prevent="verify" class="w-full">
                                    <div class="mb-4">
                                        <input wire:model.defer="code" type="text" placeholder="######" maxlength="6"
                                            wire:loading.attr="disabled"
                                            class="min-h-unset focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-xl leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid @error('code') border-red-500 focus:border-red-500 @else border-gray-300 focus:border-fuchsia-300 @enderror bg-white bg-clip-padding p-3 font-bold text-center tracking-widest text-gray-800 outline-none transition-all placeholder:text-gray-400 focus:outline-none disabled:opacity-50 disabled:bg-gray-100" required />
                                        @error('code')
                                            <div class="mt-2 p-2 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-md text-left flex items-center">
                                                <svg class="w-4 h-4 mr-1.5 text-red-600 dark:text-red-400 inline shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-size-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</span>
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-full px-6 py-3 mb-2 font-bold text-center text-white uppercase align-middle transition-all bg-teal-700 border-0 rounded-lg cursor-pointer active:opacity-85 hover:scale-102 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 bg-gradient-teal hover:border-teal-800 hover:bg-teal-800 hover:text-white disabled:opacity-60 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="verify">Verify Code</span>
                                            <span wire:loading wire:target="verify" class="inline-flex items-center">
                                                <svg class="w-4 h-4 mr-2 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                Verifying...
                                            </span>
                                        </button>
                                    </div>
                                </form>

                                <div class="w-full mt-4 text-center">
                                    <span class="leading-normal text-slate-600 dark:text-slate-400 text-size-sm">
                                        Haven't received it?
                                        <button wire:click="resend" type="button" wire:loading.attr="disabled"
                                            class="font-bold text-teal-600 dark:text-teal-400 hover:underline disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="resend">Resend code</span>
                                            <span wire:loading wire:target="resend" class="inline-flex items-center text-teal-600 dark:text-teal-400">
                                                <svg class="w-3 h-3 mr-1 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                Sending new code...
                                            </span>
                                        </button>
                                    </span>
                                </div>

                                <form method="POST" action="{{ route('logout') }}" class="w-full mt-2">
                                    @csrf
                                    <button type="submit"
                                        class="inline-block w-full px-6 py-3 font-bold text-center text-white uppercase align-middle transition-all bg-red-700 border-0 rounded-lg cursor-pointer active:opacity-85 hover:scale-102 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 hover:border-red-800 hover:bg-red-800">Logout</button>
                                </form>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
