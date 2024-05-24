<div class="flex flex-col items-center">
    <div class="w-full max-w-2xl p-4 bg-white rounded-lg shadow-md mb-4">
      <div class="flex flex-col space-y-4">
        <div class="flex items-center">
          {{-- <h1 class="text-xl font-bold text-gray-800 dark:text-white">{{$description}}</h1> --}}
          <hr class="w-full mt-2 mb-4 border-gray-200 dark:border-gray-700" />
        </div>

        @if($errors->any())
          <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
            <span class="alert-text text-white">
              {{$errors->first()}}
            </span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
              <i class="fa fa-close" aria-hidden="true"></i>
            </button>
          </div>
        @endif
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show m-3" id="alert-success" role="alert">
            <span class="text-white">
              {{ session('success') }}
            </span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
              <i class="fa fa-close" aria-hidden="true"></i>
            </button>
          </div>
        @endif

        <form action="" method="POST" class="space-y-4">
          @csrf
          <table class="table w-full table-auto border-collapse">
            <thead>
              </thead>
            <tbody>
              <tr>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                  <div class="flex items-center">
                    <div class="mr-2">Transaction ID:</div>
                  </div>
                </td>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                  {{-- <p class="text-sm font-bold mb-0">{{$transactionId}}</p>
                  <input id="transactionId" name="transactionId" value="{{$transactionId}}" type="hidden" /> --}}
                </td>
              </tr>
              <tr>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                  <div class="flex items-center">
                    <div class="mr-2">Service:</div>
                  </div>
                </td>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                  {{-- <p class="text-sm font-bold mb-0">{{$description}}</p>
                  <input name="service" value="{{$description}}" type="hidden" /> --}}
                </td>
              </tr>
              <tr>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                  <div class="flex items-center">
                    <div class="mr-2">Amount:</div>
                  </div>
                </td>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                  {{-- <p class="text-sm font-bold mb-0">
                    {{config('remita.currency'). $amount}}
                  </p>
                  <input name="amount" value="{{$amount}}" type="hidden" /> --}}
                </td>
              </tr>
              <tr>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                  <div class="flex items-center">
                    <div class="mr-2">Name:</div>
                  </div>
                </td>
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:
                <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <p class="text-sm font-bold mb-0">
                      {{auth()->user()->surname . ' ' . auth()->user()->firstname . ' ' . auth()->user()->middlename}}
                    </p>
                    <input id="payerName" name="payerName" value="{{auth()->user()->surname . ' ' . auth()->user()->firstname . ' ' . auth()->user()->middlename}}" type="hidden" />
                  </td>
                </tr>
                <tr>
                  <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <div class="flex items-center">
                      <div class="mr-2">Email:</div>
                    </div>
                  </td>
                  <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <p class="text-sm font-bold mb-0">{{auth()->user()->email}}</p>
                    <input id="payerEmail" name="payerEmail" value="{{auth()->user()->email}}" type="hidden" />
                  </td>
                </tr>
                <tr>
                  <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <div class="flex items-center">
                      <div class="mr-2">Phone:</div>
                    </div>
                  </td>
                  <td class="px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <p class="text-sm font-bold mb-0">{{auth()->user()->phone}}</p>
                    <input name="payerPhone" value="{{auth()->user()->phone}}" type="hidden" />
                    <input name="description" value="{{config('remita.admission.description')}}" type="hidden" />
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="text-center py-2">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-md text-white">Proceed to Remita</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
