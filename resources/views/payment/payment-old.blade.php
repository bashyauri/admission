<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
</head>
<style>
body{
    margin-top:20px;
    color: #484b51;
}
.text-secondary-d1 {
    color: #728299!important;
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
    border-color: #dce9f0!important;
}

.ml-n1, .mx-n1 {
    margin-left: -.25rem!important;
}
.mr-n1, .mx-n1 {
    margin-right: -.25rem!important;
}
.mb-4, .my-4 {
    margin-bottom: 1.5rem!important;
}

hr {
    margin-top: 1rem;
    margin-bottom: 1rem;
    border: 0;
    border-top: 1px solid rgba(0,0,0,.1);
}

.text-grey-m2 {
    color: #888a8d!important;
}

.text-success-m2 {
    color: #86bd68!important;
}

.font-bolder, .text-600 {
    font-weight: 600!important;
}

.text-110 {
    font-size: 110%!important;
}
.text-blue {
    color: #478fcc!important;
}
.pb-25, .py-25 {
    padding-bottom: .75rem!important;
}

.pt-25, .py-25 {
    padding-top: .75rem!important;
}
.bgc-default-tp1 {
    background-color: rgba(121,169,197,.92)!important;
}
.bgc-default-l4, .bgc-h-default-l4:hover {
    background-color: #f3f8fa!important;
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
    font-size: 120%!important;
}
.text-primary-m1 {
    color: #4087d4!important;
}

.text-danger-m1 {
    color: #dd4949!important;
}
.text-blue-m2 {
    color: #68a3d5!important;
}
.text-150 {
    font-size: 150%!important;
}
.text-60 {
    font-size: 60%!important;
}
.text-grey-m1 {
    color: #7b7d81!important;
}
.align-bottom {
    vertical-align: bottom!important;
}




</style>



















<body>
<div class="container page-content">
    <div class="page-header text-blue-d2">
        <h1 class="page-title text-secondary-d1">
            Remita:
            <small class="page-info">
                <i class="fa fa-angle-double-right text-80"></i>
                {{$RRR}}
            </small>
        </h1>

        <div class="page-tools">
            <div class="action-buttons">

                <a  onclick="javascript:window.print();" class="bg-white btn btn-light mx-1px text-95" href="#" data-title="Print">
                    <i class="w-2 mr-1 fa fa-print text-primary-m1 text-120"></i>
                    Print
                </a>
                <a class="bg-white btn btn-light mx-1px text-95" href="#" data-title="PDF">
                    <i class="w-2 mr-1 fa fa-file-pdf-o text-danger-m1 text-120"></i>
                    Export
                </a>
            </div>
        </div>
    </div>

    <div class="container px-0">
        <div class="mt-4 row">
            <div class="col-12 col-lg-10 offset-lg-1">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center text-150">
                            <i class="mr-1 fa fa-book fa-2x text-success-m2"></i>
                            <span class="text-default-d3">Bootdey.com</span>
                        </div>
                    </div>
                </div>
                <!-- .row -->
                <form action="https://login.remita.net/remita/ecomm/finalize.reg" name="SubmitRemitaForm" method="POST">
                <hr class="mb-4 row brc-default-l1 mx-n1" />
                    @csrf
                <div class="row">
                    <div class="col-sm-6">
                        <div>
                            <span class="text-sm align-middle text-grey-m2">To:</span>
                            <span class="align-middle text-600 text-110 text-blue">{{auth()->user()->fullName}}</span>
                        </div>
                        <div class="text-grey-m2">
                            <div class="my-1">
                                <i class="fa fa-envelope-square"></i>
                                <b class="text-600">{{auth()->user()->email}}</b>
                            </div>

                            <div class="my-1"><i class="fa fa-phone fa-flip-horizontal text-secondary"></i> <b class="text-600">{{auth()->user()->phone}}</b></div>
                        </div>
                    </div>
                    <!-- /.col -->

                    <div class="text-95 col-sm-6 align-self-start d-sm-flex justify-content-end">
                        <hr class="d-sm-none" />
                        <div class="text-grey-m2">
                            <div class="mt-1 mb-2 text-secondary-m1 text-600 text-125">
                                Invoice
                            </div>

                            <div class="my-2"><i class="mr-1 text-xs fa fa-circle text-blue-m2"></i> <span class="text-600 text-90">ID:</span> #{{$transaction_id}}</div>

                            <div class="my-2"><i class="mr-1 text-xs fa fa-circle text-blue-m2"></i> <span class="text-600 text-90">Issue Date:</span> {{$date}}</div>

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

                        <div class="col-9 col-sm-5">Payment Type</div>
                        <div class="d-none d-sm-block col-4 col-sm-2">RRR</div>
                        <div class="d-none d-sm-block col-sm-2">Phone</div>
                        <div class="col-2">Amount</div>
                    </div>

                    <div class="text-95 text-secondary-d3">
                        <div class="mb-2 row mb-sm-0 py-25">

                            <div class="col-9 col-sm-5">{{$resource}}</div>

                            <div class="d-none d-sm-block col-2 text-95">{{$RRR}}</div>
                            <div class="col-2 text-secondary-d2">{{auth()->user()->phone}}</div>
                            <div class="col-2 text-secondary-d2">{{$amount}}</div>

                        </div>







                    <div class="border-b-2 row brc-default-l2"></div>

                    <!-- or use a table instead -->
                    <!--
            <div class="table-responsive">
                <table class="table border-0 border-b-2 table-striped table-borderless brc-default-l1">
                    <thead class="bg-none bgc-default-tp1">
                        <tr class="text-white">
                            <th class="opacity-2">#</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th width="140">Amount</th>
                        </tr>
                    </thead>

                    <tbody class="text-95 text-secondary-d3">
                        <tr></tr>
                        <tr>
                            <td>1</td>
                            <td>Domain registration</td>
                            <td>2</td>
                            <td class="text-95">$10</td>
                            <td class="text-secondary-d2">$20</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            -->

                    <div class="mt-3 row">
                        <div class="mt-2 col-12 col-sm-7 text-grey-d2 text-95 mt-lg-0">
                            Wufpbk Online Services
                        </div>

                        <div class="order-first col-12 col-sm-5 text-grey text-90 order-sm-last">
                            <div class="my-2 row">
                                <div class="text-right col-7">
                                    Total
                                </div>
                                <div class="col-5">
                                    <span class="text-120 text-secondary-d1">&#8358 {{$amount}}</span>
                                </div>
                            </div>
                            <input name="merchantId" value="{{config('remita.settings.merchantid')}}" type="hidden">

                            <input name="hash" value="{{$apiHash}}" type="hidden">

                            <input name="rrr" value="{{$RRR}}" type="hidden">

                            <input name="responseurl" value="{{config('remita.admission.payment_redirect')}}" type="hidden">



                    <hr />

                    <div>
                        <span class="text-secondary-d1 text-105">Thank you for your business</span>
                        @if ($status === '01' || $status == '00')
                        <button type="submit" class="float-right px-4 mt-3 btn btn-info btn-bold mt-lg-0">Pay Now</button>
                        @else
                        <button type="submit" class="float-right px-4 mt-3 btn btn-info btn-bold mt-lg-0">Pay Now</button>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
