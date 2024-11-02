@php
    use App\Enums\TransactionStatus;
    $approved = TransactionStatus::APPROVED;
     $activated = TransactionStatus::ACTIVATED;
    $pending = TransactionStatus::PENDING;
@endphp
<style>
    body {
        margin-top: 20px;
        color: #484b51;
    }

    .text-secondary-d1 {
        color: #728299 !important;
    }

    .page-header {
        margin: 0 0 1rem;
        padding-bottom: 1rem;
        padding-top: .5rem;
        border-bottom: 1px dotted #e2e2e2;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-pack: justify;
        justify-content: space-between;
        -ms-flex-align: center;
        align-items: center;
    }

    .page-title {
        padding: 0;
        margin: 0;
        font-size: 1.75rem;
        font-weight: 300;
    }

    .brc-default-l1 {
        border-color: #dce9f0 !important;
    }

    .ml-n1,
    .mx-n1 {
        margin-left: -.25rem !important;
    }

    .mr-n1,
    .mx-n1 {
        margin-right: -.25rem !important;
    }

    .mb-4,
    .my-4 {
        margin-bottom: 1.5rem !important;
    }

    hr {
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, .1);
    }

    .text-grey-m2 {
        color: #888a8d !important;
    }

    .text-success-m2 {
        color: #86bd68 !important;
    }

    .font-bolder,
    .text-600 {
        font-weight: 600 !important;
    }

    .text-110 {
        font-size: 110% !important;
    }

    .text-blue {
        color: #478fcc !important;
    }

    .pb-25,
    .py-25 {
        padding-bottom: .75rem !important;
    }

    .pt-25,
    .py-25 {
        padding-top: .75rem !important;
    }

    .bgc-default-tp1 {
        background-color: rgba(121, 169, 197, .92) !important;
    }

    .bgc-default-l4,
    .bgc-h-default-l4:hover {
        background-color: #f3f8fa !important;
    }

    .page-header .page-tools {
        -ms-flex-item-align: end;
        align-self: flex-end;
    }

    .btn-light {
        color: #757984;
        background-color: #f5f6f9;
        border-color: #dddfe4;
    }

    .w-2 {
        width: 1rem;
    }

    .text-120 {
        font-size: 120% !important;
    }

    .text-primary-m1 {
        color: #4087d4 !important;
    }

    .text-danger-m1 {
        color: #dd4949 !important;
    }

    .text-blue-m2 {
        color: #68a3d5 !important;
    }

    .text-150 {
        font-size: 150% !important;
    }

    .text-60 {
        font-size: 60% !important;
    }

    .text-grey-m1 {
        color: #7b7d81 !important;
    }

    .align-bottom {
        vertical-align: bottom !important;
    }

</style>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <title>{{ config('app.name') }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
      <style type="text/css">
         .button {background-color: #1CA78B;  border: none;  color: white;  padding: 15px 32px;  text-align: center;  text-decoration: none;  display: inline-block;  font-size: 16px;  margin: 4px 2px;  cursor: pointer;  border-radius: 4px;}
         input {  max-width: 30%;}
      </style>
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css" rel="stylesheet" />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>
    <div class="card">
        <div class="card-body">
            @include('flash-messages')

            <div class="container page-content">
                <div class="page-header text-blue-d2">
                    <h1 class="page-title text-secondary-d1">
                        Remita:
                        <small class="page-info">
                            <i class="fa fa-angle-double-right text-80"></i>
                            {{ $RRR }}
                        </small>
                    </h1>


                </div>

                <div class="container px-0">
                    <div class="mt-4 row">
                        <div class="col-12 col-lg-10 offset-lg-1">
                            <div class="row">
                                <div class="col-12">
                                    <div class="text-center text-150">
                                        <img alt="Image placeholder" src="{{ asset('assets') }}/img/logo-ct.png">

                                    </div>
                                </div>
                            </div>
                            <!-- .row -->
                          <form action="https://login.remita.net/remita/ecomm/finalize.reg" name="SubmitRemitaForm" method="POST">
                                 <input name="merchantId" value="{{config('remita.settings.merchantid')}}" type="hidden">

                            <input name="hash" value="{{$apiHash}}" type="hidden">

                            <input name="rrr" value="{{$RRR}}" type="hidden">

                            <input name="responseurl" value="{{config('remita.admission.payment_redirect')}}" type="hidden">


                                @csrf
                                <hr class="mb-4 row brc-default-l1 mx-n1" />

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div>
                                            <span class="text-sm align-middle text-grey-m2">To:</span>
                                            <span
                                                class="align-middle text-600 text-110 text-blue">
                                                {{ auth()->user()->surname.' '.auth()->user()->firstname. ' '.auth()->user()->middlename }}</span>
                                        </div>
                                        <div class="text-grey-m2">
                                            <div class="my-1">
                                                <i class="fas fa-envelope"></i>
                                                <b class="text-600">{{ auth()->user()->email }}</b>
                                            </div>

                                            <div class="my-1"><i class="fas fa-phone"></i> <b
                                                    class="text-600">{{ auth()->user()->phone }}</b></div>
                                        </div>
                                    </div>
                                    <!-- /.col -->

                                    <div class="text-95 col-sm-6 align-self-start d-sm-flex justify-content-end">
                                        <hr class="d-sm-none" />
                                        <div class="text-grey-m2">
                                            <div class="mt-1 mb-2 text-secondary-m1 text-600 text-125">
                                            @php


                                            @endphp
                                                @if ($status === $approved->toString())
                                                Reciept
                                            @else
                                            Invoice
                                            @endif

                                            </div>

                                            <div class="my-2"><i
                                                    class="mr-1 text-xs fa fa-circle text-blue-m2"></i> <span
                                                    class="text-600 text-90">ID:</span> #{{ $transaction_id }}</div>

                                            <div class="my-2"><i
                                                    class="mr-1 text-xs fa fa-circle text-blue-m2"></i> <span
                                                    class="text-600 text-90">Issue Date:{{$date}}</span>
                                            </div>

                                            <div class="my-2"><i
                                                    class="mr-1 text-xs fa fa-circle text-blue-m2"></i> <span
                                                    class="text-600 text-90">Status:{{$status}}</span>
                                                @if ($status === config('remita.status.approved'))

                                                <span class="badge badge-success">Success</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col -->
                                </div>

                                <div class="mt-4">
                                    <div class="text-white row text-600 bgc-default-tp1 py-25">

                                        <div class="col-md-6 col-sm-5">Payment Type</div>
                                        <div class="d-none d-sm-block col-4 col-sm-2">RRR</div>
                                        <div class="d-none d-sm-block col-sm-2">Phone</div>
                                        <div class="col-2">Amount</div>
                                    </div>

                                    <div class="text-95 text-secondary-d3">
                                        <div class="mb-2 row mb-sm-0 py-25">

                                            <div class="col-md-6 col-sm-5">{{ $resource }}</div>

                                            <div class="d-none d-sm-block col-2 text-95">{{ $RRR }}</div>
                                            <div class="col-2 text-secondary-d2">{{ auth()->user()->phone }}</div>
                                            <div class="col-2 text-secondary-d2">{{ $amount }}</div>

                                        </div>
                                        <div class="border-b-2 row brc-default-l2"></div>

                                        <div class="row ">
                                            <div class="mt-2 col-12 col-sm-7 text-grey-d2 text-95 mt-lg-0">
                                                Wufpbk  Services
                                            </div>

                                            <div class="order-first col-12 col-sm-5 text-grey text-90 order-sm-last">
                                                <div class="my-2 row">
                                                    <div class="text-right col-7">
                                                        Total
                                                    </div>
                                                    <div class="col-5">
                                                        <span class="text-120 text-secondary-d1">&#8358
                                                            {{ $amount }}</span>
                                                    </div>
                                                </div>




                                                <hr />

                                                <div>

                                                    @if ($status === $activated->toString() || $status === $approved->toString())
                                                    <a href="{{ route('analytics') }}" class="button">Back</a>
                                                  @else

                                                    <input type="submit"  value="Pay" button class="button"/>
                                                    <a href="{{ route('analytics') }}" class="button">Back</a>
                                                  @endif
                                                </div>
                                            </div>
                                              </form>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
                <!-- Core -->
                <script src="../assets/js/core/popper.min.js"></script>
                <script src="../assets/js/core/bootstrap.min.js"></script>

                <!-- Theme JS -->
                <script src="../assets/js/soft-ui-dashboard.min.js"></script>

      <script type="text/javascript" src="https://login.remita.net/payment/v1/remita-pay-inline.bundle.js"></script>
      <script>
        function makePayment() {
          return new Promise((resolve, reject) => {
            var randomnumber = Math.floor(Math.random() * 1101233);
            var form = document.querySelector("#payment-form");
            var rrr = document.querySelector('input[name="rrr"]').value;
            var transaction_id = document.querySelector('input[name="transaction_id"]').value;

            var paymentEngine = RmPaymentEngine.init({
                key:"V1VGUHw0MjA2NDE1M3w1M2UxN2QwYmVhODA5NTc4YjVlOWJiZWNiYjBjYzJhNjZlM2EwNTM1ZTgyNzUzZWQ5MjlkNWViZTk2OGYwMTUzNDhlMzc3NzQ3NmQzNmEwM2JjYTk4ZTIzMmE1MTA4NGEwMDljMjBhYWQyNjk5MTliNjkxNTJiZDczYjlhNTFmOQ==",
                processRrr: true,
                // transactionId: randomnumber,

                extendedData: {
                    customFields: [
                        {
                            name: "rrr",
                            value: rrr
                        }
                     ]
                },
                onSuccess: function (response) {
                    console.log('callback Successful Response', response);
                    resolve(response);
                },
                onError: function (response) {
                    console.log('callback Error Response', response);
                    reject(response);
                },
                onClose: function () {
                    console.log("closed");
                    reject("Payment window closed.");
                }
            });
            paymentEngine.showPaymentWidget();
          });
        }
        </script>
