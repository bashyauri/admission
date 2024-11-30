
<div class="flex-auto px-0 pt-0 pb-2">
    <div class="p-0 overflow-x-auto">
      <table class="items-center mb-0 align-top border-gray-200 w-96 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="px-3 py-3 text-sm leading-normal">Credentials</th>
            <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70"></th>
          </tr>
        </thead>
        <tbody>
            @forelse ($this->uploadedCertificates as $certificate)
            <div wire:key="{{$certificate->id}}">

                <tr>
                  <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                    <div class="flex px-2 py-1">

                      <div class="flex flex-col justify-center">
                        <h6 class="mb-0 text-sm leading-normal">{{ucwords($certificate->name)}}</h6>

                      </div>
                    </div>
                  </td>
                  <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">


                      <p class="mb-0 text-xs font-semibold leading-tight">
                          <a target="_blank" href="{{ asset('storage/' . $certificate->path) }}" aria-label="View Certificate">
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                              </svg>
                          </a>
                      </p>


                  </td>


                  <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                      <button wire:click="delete({{$certificate->id}})" class="mr-1 text-sm font-semibold text-red-500 rounded hover:text-teal-800">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                              stroke="currentColor" class="w-4 h-4">
                              <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                          </svg>
                      </button>
                  </td>
                </tr>
              </div>
            @empty
            <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
            <p class="mb-0 text-xs font-semibold leading-tight">
                No Record(s)
            </p>
            </td>
            @endforelse




        </tbody>
      </table>
    </div>
  </div>
