@extends('layouts.admin')
@section('page-title')
    {{__('Tax Summary')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Tax Summary')}}</li>
@endsection
@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        var year = '{{$currentYear}}';

        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A2'}
            };
            html2pdf().set(opt).from(element).save();
        }
        $(document).ready(function() {
        var today = new Date().toISOString().split('T')[0];

        const urlParams = new URLSearchParams(window.location.search);
        var startDate = urlParams.get('start_date') || today;
        var endDate = urlParams.get('end_date') || today;
        $('#start_date').val(startDate);
        $('#end_date').val(endDate);

        $('#daterange').daterangepicker({
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')]
            },
            startDate:moment(startDate).format("MM/DD/YYYY"),
            endDate:moment(endDate).format("MM/DD/YYYY")
        }, function(start, end, label) {
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        });
    });

    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}

        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()"data-bs-toggle="tooltip" title="{{__('Download')}}" data-original-title="{{__('Download')}}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>

    </div>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">


                                    {{-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div> --}}
                                    {{-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div> --}}
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        {{ Form::open(array('route' => array('report.tax.summary'),'method' => 'GET','id'=>'report_tax_summary_date')) }}
                                        <div class="btn-box">
                                            {{ Form::label('daterange', __('Date'),['class'=>'form-label'])}}
                                            <input type="text" name="" id="daterange" class = "form-control"/>

                                            <input type="text" name="start_date" id="start_date" hidden>
                                            <input type="text" name="end_date" id="end_date" hidden>
                                            {{-- {{ Form::text('daterange', ['class' => 'form-control', 'placeholder' => __('Enter Description'),'id' => 'daterange']) }} --}}

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box mt-2">
                                            <div class="row">
                                                <div class="col-auto mt-4">

                                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_tax_summary_date').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                                    </a>

                                                    <a href="{{route('report.tax.summary')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                                    </a>


                                                </div>

                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        {{ Form::open(array('route' => array('report.tax.summary'),'method' => 'GET','id'=>'report_tax_summary_year')) }}
                                        <div class="btn-box">
                                            {{ Form::label('year', __('Year'),['class'=>'form-label'])}}
                                            {{ Form::select('year',$yearList,isset($_GET['year'])?$_GET['year']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box mt-2">
                                            <div class="row">
                                                <div class="col-auto mt-4">

                                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_tax_summary_year').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                                    </a>

                                                    <a href="{{route('report.tax.summary')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                                    </a>


                                                </div>

                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                {{-- <div class="row">
                                    <div class="col-auto mt-4">

                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_tax_summary').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="{{route('report.tax.summary')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>


                                    </div>

                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="printableArea">
        <div class="row mt-3">
            <div class="col">
                <input type="hidden" value="{{__('Tax Summary').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Report')}} :</h7>
                    <h6 class="report-text mb-0">{{__('Tax Summary')}}</h6>
                </div>
            </div>
            <div class="col">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Duration')}} :</h7>
                    <h6 class="report-text mb-0">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</h6>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="col-sm-12">
                            <h5>{{__('Income')}}</h5>
                            <div class="table-responsive mt-3 mb-3">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>{{__('Tax')}}</th>
                                        @foreach($monthList as $month)
                                            <th>{{$month}}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse(array_keys($incomes) as $k=> $taxName)
                                        <tr>
                                            <td>{{$taxName}}</td>
                                            @foreach(array_values($incomes)[$k] as $price)
                                                <td>{{\Auth::user()->priceFormat($price)}}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center">{{__('Income tax not found')}}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <h5>{{__('Expense')}}</h5>
                            <div class="table-responsive mt-4">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>{{__('Tax')}}</th>
                                        @foreach($monthList as $month)
                                            <th>{{$month}}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse(array_keys($expenses) as $k=> $taxName)
                                        <tr>
                                            <td>{{$taxName}}</td>
                                            @foreach(array_values($expenses)[$k] as $price)
                                                <td>{{\Auth::user()->priceFormat($price)}}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center">{{__('Expense tax not found')}}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


