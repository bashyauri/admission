<main class="mt-0 transition-all duration-200 ease-soft-in-out">
  <div
    class="pb-56 pt-12 m-4 min-h-50-screen items-start rounded-xl p-0 relative overflow-hidden flex bg-cover bg-center bg-[url('../../assets/img/curved-images/curved6.jpg')]">
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
          class="relative z-0 flex flex-col min-w-0 break-words bg-white border-0 lg:py-4 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
          <div class="text-center border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6">
            <h5>Register</h5>
          </div>

          <div class="flex-auto p-6 text-center">
            <form wire:submit.prevent="register" role="form text-left">
              <div class="mb-4">
                <input wire:model.lazy="surname" type="text"
                  class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                  placeholder="Surname" aria-label="Surname" aria-describedby="surname-addon" required />
                @error('surname')
                <p class="text-size-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4">
                <input wire:model.lazy="firstName" type="text"
                  class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                  placeholder="FirstName" aria-label="First Name" aria-describedby="firstname-addon" required />
                @error('firstName')
                <p class="text-size-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4">
                <input wire:model.lazy="middleName" type="text"
                  class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                  placeholder="Middle Name" aria-label="Middle Name" aria-describedby="middlename-addon"/>
                @error('middleName')
                <p class="text-size-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4">
                <input wire:model.lazy="email" type="email"
                  class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                  placeholder="Email" aria-label="Email" aria-describedby="email-addon" required />
                @error('email')
                <p class="text-size-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4">
                <input wire:model.lazy="phone" type="text"
                  class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                  placeholder="Phone" aria-label="Phone" aria-describedby="phone-addon" required />
                @error('phone')
                <p class="text-size-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4">
                <input wire:model.lazy="password" type="password"
                  class="text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow"
                  placeholder="Password" aria-label="Password" aria-describedby="password-addon" required />
                @error('password')
                <p class="text-size-sm text-red-500">{{ $message }}</p>
                @enderror
              </div>

              <div class="mb-4">
                <div wire:ignore x-data x-init="
                choices = new Choices($refs.roles, {
                    searchEnabled: false
                });
                $refs.roles.addEventListener('change', function (event) {
                    values = event.detail.value;
                    @this.set('programme_id', values);
                })">
                  <select choice wire:model="programme_id" x-ref="roles">
                    <option value="">Select Programme</option>
                    @foreach($programmes as $programme)
                    <option value="{{ $programme->id}}">{{ $programme->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              @error('programme_id')
              <p class='text-size-sm text-red-500'>{{ $message }} </p>
              @enderror



              <div class="text-center">
                <button type="submit"
                  class="inline-block w-full px-6 py-3 mt-6 mb-2 font-bold text-center text-white uppercase align-middle transition-all  border-0 rounded-lg cursor-pointer active:opacity-85 hover:scale-102 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 bg-teal-700 hover:border-teal-800 hover:bg-teal-800 hover:text-white">Sign
                  up</button>
              </div>
              <p class="mt-4 mb-0 leading-normal text-size-sm">Already have an account? <a href="{{ route('login') }}"
                  class="font-bold text-teal-700">Sign in</a></p>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

</main>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
@endpush
