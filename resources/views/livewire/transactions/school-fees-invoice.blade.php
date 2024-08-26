<div>


    <div class="my-4">
        <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 mx-auto sm:flex-0 shrink-0 sm:w-10/12 md:w-8/12">
                @include('flash-messages')

                <form action="{{route('student.invoice')}}" method="POST">
                    @csrf
                    <div
                        class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border sm:my-12">
                        <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 text-center">
                            <div class="flex flex-wrap justify-between -mx-3">
                                <div class="w-full max-w-full px-3 text-left md:flex-0 shrink-0 md:w-4/12">
                                    <img class="block w-1/4 p-2 mb-2 dark:hidden"
                                        src="../../../assets/img/logo-ct-dark.png" alt="Logo" />
                                    <img class="hidden w-1/4 p-2 mb-2 dark:block" src="../../../assets/img/logo-ct.png"
                                        alt="Logo" />
                                    <h6 class="dark:text-white">{{config('app.name')}}, {{config('remita.settings.address')}}, {{config('remita.settings.state')}}
                                    </h6>
                                    <p class="block text-slate-400 dark:text-white dark:opacity-80">email: {{config('remita.settings.email')}}</p>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 mt-12 text-left md:flex-0 shrink-0 md:w-7/12 md:text-right lg:w-3/12">
                                    <h6 class="block mt-2 mb-0 dark:text-white">Billed to: </h6>
                                    <p class="text-slate-400 dark:text-white dark:opacity-80">
                                        {{auth()->user()->full_name}}

                                    </p>
                                    <p class="text-slate-400 dark:text-white dark:opacity-80">
                                        {{auth()->user()->phone}}

                                    </p>
                                </div>
                            </div>
                            <br />
                            @if (auth()->user()->hasInvoice(config('remita.schoolfees.description')))
                            <div class="flex flex-wrap -mx-3 md:justify-between">
                                <div class="w-full max-w-full px-3 mt-auto md:flex-0 shrink-0 md:w-4/12">
                                    <h6 class="mb-0 text-left text-slate-400 dark:text-white dark:opacity-80">Invoice no
                                    </h6>
                                    <h5 class="mb-0 text-left dark:text-white">#0453119</h5>
                                </div>
                                <div class="w-full max-w-full px-3 mt-auto md:flex-0 shrink-0 md:w-7/12 lg:w-5/12">
                                    <div class="flex flex-wrap mt-6 -mx-3 text-left md:mt-12 md:text-right">
                                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-400 dark:text-white dark:opacity-80">Invoice
                                                date:</h6>
                                        </div>
                                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-700 dark:text-white">06/03/2019</h6>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap -mx-3 text-left md:text-right">
                                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-400 dark:text-white dark:opacity-80">Due date:
                                            </h6>
                                        </div>
                                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-700 dark:text-white">11/03/2019</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>

                        <div class="flex flex-col items-center">
                        <div><h5>Payment for {{App\Models\StudentLevel::find($nextLevel)->level}}</h5></div>
                        <div><select wire:model.live='selectedInstallment' class="focus:shadow-soft-primary-outline text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding  
 px-3 py-1 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
    <option value="">Choose payment</option>
    @foreach (config('schoolfees.installments') as $key => $installment)
        <option value={{ $installment / 100 }}>{{ ucfirst($key) }} Installment ({{ $installment }}%)</option>
    @endforeach
    <option value="1">Full Payment</option>
</select></div>

                        </div>



    <div class="mt-3 text-center text-lime-500" wire:loading wire:target="selectedInstallment" >
        Calculating payment...
    </div>


@if ($selectedInstallment)
<div class="flex-auto p-6 payments"    wire:loading.attr="hidden">
                            <div class="flex flex-wrap -mx-3">
                                <div class="w-full max-w-full px-3 flex-0">
                                    <div class="overflow-x-auto">
                                        <table
                                            class="w-full table-auto sm:table-auto md:table-auto lg:table-fixed">

                                            <tbody class="border-t-2">
                                                <tr>
                                                    <th scope="col"
                                                    class="px-2 py-3 font-semibold text-left capitalize bg-transparent border-b border-solid shadow-none tracking-none whitespace-nowrap border-b-gray-200 dark:border-white/40 dark:text-white">
                                                    Transaction ID:</th>
                                                    <td
                                                        class="p-2 text-left border-b whitespace-nowrap dark:border-white/40 dark:text-white/60">
                                                        {{$transactionId ?? 'Not Available'}}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="col"
                                                    class="px-2 py-3 font-semibold text-left capitalize bg-transparent border-b border-solid shadow-none tracking-none whitespace-nowrap border-b-gray-200 dark:border-white/40 dark:text-white">
                                                    RRR:</th>
                                                    <td
                                                        class="p-2 text-left border-b whitespace-nowrap dark:border-white/40 dark:text-white/60">
                                                        Not Available</td>
                                                </tr>
                                                <tr>
                                                    <th scope="col"
                                                    class="px-2 py-3 font-semibold text-left capitalize bg-transparent border-b border-solid shadow-none tracking-none whitespace-nowrap border-b-gray-200 dark:border-white/40 dark:text-white">
                                                    Description:</th>
                                                    <td class="p-2 pl-6 border-b whitespace-nowrap dark:border-white/40 dark:text-white/60"
                                                    >{{$description}}</td>

                                                </tr>
                                                <tr>
                                                    <th scope="col"
                                                    class="px-2 py-3 font-semibold text-left capitalize bg-transparent border-b border-solid shadow-none tracking-none whitespace-nowrap border-b-gray-200 dark:border-white/40 dark:text-white">
                                                    Amount:</th>
                                                    <td class="p-2 pl-6 border-b whitespace-nowrap dark:border-white/40 dark:text-white/60"
                                                    >{{config('remita.currency')}}
                                                    {{$amount}}</td>

                                                </tr>




                                                </tr>
                                                <input name="payerPhone" value="{{auth()->user()->phone}}" type="hidden" />
                                                <input name="description" value="{{config('remita.schoolfees.description')}}" type="hidden" />
                                                <input id="payerEmail" name="payerEmail" value="{{auth()->user()->email}}"
                                                type="hidden" />
                                                <input id="payerName" name="payerName"
                                                value="{{auth()->user()->full_name}}"
                                                type="hidden" />
                                                <input name="amount" value="{{$amount}}" type="hidden" />
                                                 <input name="student_level_id" value="{{$nextLevel}}" type="hidden" />
                                                <input name="service"
                                                value="{{$description}}" type="hidden" />
                                                <input id="transactionId" name="transactionId" value="{{$transactionId}}"
                                    type="hidden" />

                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

@endif


                        <div class="border-black/12.5 mt-6 rounded-b-2xl border-t-0 border-solid p-6 md:mt-12">
                            <div class="flex flex-wrap -mx-3">
                                <div class="w-full max-w-full px-3 text-left lg:flex-0 shrink-0 lg:w-5/12">
                                    <h5 class="dark:text-white">Thank you!</h5>
                                    <p
                                        class="leading-normal text-size-sm text-slate-400 dark:text-white dark:opacity-80">
                                        If you encounter any issues related to the invoice you can contact us at:</p>
                                    <h6 class="mb-0 text-slate-400 dark:text-white dark:opacity-80">
                                        email:
                                        <span class="text-slate-700 dark:text-white">{{config('remita.settings.email')}}</span>
                                    </h6>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 mt-4 lg:flex-0 shrink-0 md:mt-0 md:text-right lg:w-7/12">
                                    @if (auth()->user()->hasPaid(config('remita.schoolfees.description')))
                                    <button onclick="window.print()" type="button"
                                        class="inline-block px-6 py-3 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-cyan leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 lg:mt-24">Print</button>
                                    @else
                                    <button  type="submit"
                                        class="inline-block px-6 py-3 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-cyan leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 lg:mt-24">Generate Invoice</button>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
