<main class="mt-0 transition-all duration-200 ease-soft-in-out">
    <div
        class="pb-56 pt-12 m-4 min-h-50-screen items-start rounded-xl p-0 relative overflow-hidden flex bg-cover bg-center bg-[url('../../assets/img/curved-images/curved9.jpg')]">
        <span class="absolute top-0 left-0 w-full h-full bg-center bg-cover opacity-60 bg-gradient-dark-gray"></span>
        <div class="container z-1">
            <div class="flex flex-wrap justify-center -mx-3">
                <div class="w-full max-w-full px-3 mx-auto text-center shrink-0 lg:flex-0 lg:w-5/12">
                    <h1 class="mt-12 mb-2 text-white">Welcome</h1>
          <p class="text-white ">{{config('app.name')}} Portal</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="flex flex-wrap justify-center -mx-3 -mt-48 lg:-mt-48 md:-mt-56">
            <div class="w-full max-w-full px-3 mx-auto shrink-0 md:flex-0 md:w-7/12 lg:w-5/12 xl:w-4/12">
                <div
                    class="relative z-0 flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="text-center border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6">
                        @if (Session::has('status'))
                        <div class="fixed bottom-1/100 right-1/100 z-2 mb-4">
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
                        <h5>Sign in</h5>
                    </div>


                    <div class="flex-auto p-6 text-center">

                        @include('flash-messages')
                        <form wire:submit.prevent="login" role="form text-left">
                            <div class="mb-4">
                                <input wire:model.lazy="email" type="email"
                                    class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                                    placeholder="email" aria-label="email" aria-describedby="email-addon" required
                                    />
                                @error('email')
                                <p class="text-size-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <input wire:model.lazy="password" type="password"
                                    class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                                    placeholder="Password" aria-label="Password" aria-describedby="password-addon"
                                    required />
                                @error('password')
                                <p class="text-size-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="text-center">
                                <button type="submit"
                                    class="inline-block w-full px-6 py-3 mt-6 mb-2 font-bold text-center text-white uppercase align-middle transition-all  border-0 rounded-lg cursor-pointer active:opacity-85 hover:scale-102 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 bg-teal-300 hover:bg-teal-500 hover:text-white">Sign
                                    in</button>
                            </div>
                            <div class="relative w-full max-w-full px-3 mb-2 text-center shrink-0">
                                <p
                                    class="inline mb-2 px-4 text-slate-400 bg-white z-2 text-size-sm leading-normal font-semibold before:bg-gradient-horizontal-duo-before before:right-2-em before:-ml-1/2 before:content-[''] before:inline-block before:w-3/10 before:h-px before:relative before:align-middle after:left-2 after:-mr-1/2 after:bg-gradient-horizontal-duo-after after:content-[''] after:inline-block after:w-3/10 after:h-px after:relative after:align-middle">
                                    Don't have an account?</p>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('register') }}"
                                    class="inline-block w-full px-6 py-3 mt-6 mb-2 font-bold text-center text-white uppercase align-middle transition-all bg-teal-700 border-0 rounded-lg cursor-pointer active:opacity-85 hover:scale-102 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 bg-gradient-dark-cyan hover:border-teal-800 hover:bg-teal-800 hover:text-white">Sign
                                    up</a>
                                <p class="mx-auto mb-6 leading-normal text-size-sm">
                                    Forgot your password? Reset your password
                                    <a href="{{ route('forgot-password') }}"
                                        class="relative z-10 font-semibold text-transparent bg-gradient-cyan bg-clip-text">here</a>.
                                </p>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

@push('js')
<script>
    function alertClose() {
        document.getElementById("alert").style.display = "none";
    }
</script>
@endpush
