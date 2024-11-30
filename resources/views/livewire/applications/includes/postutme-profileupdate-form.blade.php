    @php

$parts = explode(' ', $result->name);


[$surname, $firstname, $middlename] = array_pad($parts, 3, null);
    @endphp
   <form wire:submit.prevent="updateProfile" class="p-6 space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <!-- Jamb Number -->
        <div>
            <label for="jambNumber" class="block text-sm font-medium text-gray-700">Jamb Number</label>
            <input type="text" value="{{$result->jamb_no}}" id="jambNumber"
                class="block w-full px-3 py-2 mt-1 text-sm italic text-gray-600 bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500"
               readonly >

        </div>

        <!-- Surname -->
        <div>
            <label for="surname" class="block text-sm font-medium text-gray-700">Surname</label>
            <input type="text" value="{{$surname}}" id="surname"
                class="block w-full px-3 py-2 mt-1 text-sm italic text-gray-600 bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500"
                readonly>

        </div>

        <!-- Firstname -->
        <div>
            <label for="firstname" class="block text-sm font-medium text-gray-700">Firstname</label>
            <input type="text"  value="{{$firstname}}" id="firstname"
                class="block w-full px-3 py-2 mt-1 text-sm italic text-gray-600 bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500"
                readonly>

        </div>

        <!-- Middlename -->
        <div>
            <label for="middlename" class="block text-sm font-medium text-gray-700">Middlename</label>
            <input type="text"  value="{{$middlename}}" id="middlename"
                class="block w-full px-3 py-2 mt-1 text-sm italic text-gray-600 bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500"
                readonly>

        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email"  value="{{auth()->user()->email}}" id="email"
                class="block w-full px-3 py-2 mt-1 text-sm italic text-gray-600 bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500"
                readonly>

        </div>

        <!-- Phone Number -->
        <div>
            <label for="phoneNumber" class="block text-sm font-medium text-gray-700">Phone Number</label>
            <input type="text" wire:model="phoneNumber"
                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
            @error('phoneNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- Generate Invoice Button -->
    <div class="flex justify-end mt-4">
        <button type="submit"
            class="flex items-center px-6 py-2 text-sm font-medium text-white bg-teal-500 rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Update Profile</span>
            <svg wire:loading class="w-5 h-5 ml-2 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0a12 12 0 00-9 21.92l3-3.92A7.93 7.93 0 014 12z">
                </path>
            </svg>
        </button>
    </div>
</form>



