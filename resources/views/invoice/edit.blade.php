@extends('layouts.admin')
@section('page-title')
    {{__('Invoice Edit')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('invoice.index')}}">{{__('Invoice')}}</a></li>
    <li class="breadcrumb-item">{{__('Invoice Edit')}}</li>
@endsection
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script>
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: true,
                defaultValues: {
                    'status': 1
                },
                show: function () {
                    $(this).slideDown();
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: 2048
                        });
                    }
                    // $('.select2').select2();
                },
                hide: function (deleteElement) {


                    $(this).slideUp(deleteElement);
                    $(this).remove();
                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }
                    $('.subTotal').html(subTotal.toFixed(2));
                    $('.totalAmount').html(subTotal.toFixed(2));

                },
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');

            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
                for (var i = 0; i < value.length; i++) {
                    var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                    tr.find('.item').val(value[i].product_id);
                    changeItem(tr.find('.item'));
                }
            }

        }

        $(document).on('change', '#customer', function () {
            $('#customer_detail').removeClass('d-none');
            $('#customer_detail').addClass('d-block');
            $('#customer-box').removeClass('d-block');
            $('#customer-box').addClass('d-none');
            var id = $(this).val();
            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': id
                },
                cache: false,
                success: function (data) {
                    if (data != '') {
                        $('#customer_detail').html(data);
                    } else {
                        $('#customer-box').removeClass('d-none');
                        $('#customer-box').addClass('d-block');
                        $('#customer_detail').removeClass('d-block');
                        $('#customer_detail').addClass('d-none');
                    }
                },

            });
        });

        $(document).on('click', '#remove', function () {
            $('#customer-box').removeClass('d-none');
            $('#customer-box').addClass('d-block');
            $('#customer_detail').removeClass('d-block');
            $('#customer_detail').addClass('d-none');
        })
        $(document).on('change', '.tax-select', function () {
            var el = $(this).parent().parent().parent().parent().parent();
            // console.log(el);
            var totalItemTaxRate = $(this).find('option:selected').attr('data-taxrate');
            var taxid = $(this).find('option:selected').attr('value');

            $(el.find('.itemTaxRate')).val(parseFloat(totalItemTaxRate).toFixed(2));

            var tax = [];
            tax.push(taxid);
            $(el.find('.tax')).val(tax);

            var quantity = $(el.find('.quantity')).val();
            var price = $(el.find('.price')).val();
            var discount = $(el.find('.discount')).val();

            if(discount.length <= 0)
            {
                discount = 0 ;
            }

            var totalItemPrice = (quantity * price) - discount;
            var amount = (totalItemPrice);

            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html((parseFloat(itemTaxPrice)+parseFloat(amount)).toFixed(2));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }

            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

        });
        $(document).on('change', '.item', function () {
            changeItem($(this));
        });

        var invoice_id = '{{$invoice->id}}';

        function changeItem(element) {

            var iteams_id = element.val();
            var url = element.data('url');
            var el = element;
            var currency_code = $("#currency_code").val();
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': iteams_id,
                    'currency_code': currency_code
                },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);
                    if (item.hasOwnProperty('status') && item.status == 0) {
                        show_toastr('danger', item.message);
                    } else {
                        if(item.exchange_rate != 1)
                        {
                            $("#conversion_rate_span").html(` (1 ${ $("#currency_code").val() } = ${ item.exchange_rate } {{ \Auth::user()->currencyCode() }}  )`);
                        } else {
                            $("#conversion_rate_span").html(``);
                        }
                        $("#exchange_rate").val(item.exchange_rate);
                        $.ajax({
                                url: '{{route('invoice.items')}}',
                            type: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': jQuery('#token').val()
                            },
                            data: {
                                'invoice_id': invoice_id,
                                'product_id': iteams_id,
                            },
                            cache: false,
                            success: function (data) {
                                var invoiceItems = JSON.parse(data);
                                if (invoiceItems != null) {

                                    var amount = (invoiceItems.price * invoiceItems.quantity);

                                    $(el.parent().parent().find('.quantity')).val(invoiceItems.quantity);
                                    $(el.parent().parent().find('.price')).val(invoiceItems.price);
                                    $(el.parent().parent().find('.discount')).val(invoiceItems.discount);
                                    $(el.parent().parent().parent().find('.pro_description')).val(invoiceItems.description);
                                    // $('.pro_description').text(invoiceItems.description);
                                    $(el.parent().parent().find('.taxes')).val(invoiceItems.tax);

                                    // alert(invoiceItems.tax);

                                } else {

                                    $(el.parent().parent().find('.quantity')).val(1);
                                    $(el.parent().parent().find('.price')).val(item.product.sale_price);
                                    $(el.parent().parent().find('.discount')).val(0);
                                    // $(el.parent().parent().find('.pro_description')).val(item.product.description);
                                    $(el.parent().parent().parent().find('.pro_description')).val(item.product.description);
                                    // $('.pro_description').text(item.product.description);
                                }

                                var taxes = '';
                                var tax = [];

                                var totalItemTaxRate = 0;
                                taxes += `<select class='form-control tax-select'><option value=''>--</option>`;
                                var selected_tax = $(el.parent().parent().find('.taxes')).val();

                                for (var i = 0; i < item.taxes.length; i++) {
                                    if(selected_tax==item.taxes[i].id) {
                                        taxes += `<option data-taxrate='${item.taxes[i].rate}' selected value='${item.taxes[i].id}'>${item.taxes[i].name} (${item.taxes[i].rate}%)</option>`;
                                        totalItemTaxRate += parseFloat(item.taxes[i].rate);
                                        tax.push(item.taxes[i].id);
                                    } else {
                                        taxes += `<option data-taxrate='${item.taxes[i].rate}' value='${item.taxes[i].id}'>${item.taxes[i].name} (${item.taxes[i].rate}%)</option>`;
                                    }
                                    // taxes += '<span class="p-2 px-3 mt-1 mr-1 rounded badge bg-primary">' + item.taxes[i].name + ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                                }
                                taxes += '</select>';

                                var discount=$(el.parent().parent().find('.discount')).val();


                                if (invoiceItems != null) {
                                    var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((invoiceItems.price * invoiceItems.quantity)-discount);
                                } else {
                                    var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((item.product.sale_price * 1)-discount);
                                }

                                $(el.parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
                                $(el.parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(2));
                                $(el.parent().parent().find('.taxes')).html(taxes);
                                $(el.parent().parent().find('.tax')).val(tax);
                                $(el.parent().parent().find('.unit')).html(item.unit);
                                // $(el.parent().parent().find('.discount')).val(item.discount);


                                var inputs = $(".amount");
                                var subTotal = 0;
                                for (var i = 0; i < inputs.length; i++) {
                                    subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                                }

                                var totalItemPrice = 0;
                                var inputs_quantity = $(".quantity");

                                var priceInput = $('.price');
                                for (var j = 0; j < priceInput.length; j++) {
                                    totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
                                }


                                var totalItemTaxPrice = 0;
                                var itemTaxPriceInput = $('.itemTaxPrice');
                                for (var j = 0; j < itemTaxPriceInput.length; j++) {
                                    totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                                    if (invoiceItems != null) {
                                        $(el.parent().parent().find('.amount')).html((parseFloat(amount)+parseFloat(itemTaxPrice)-parseFloat(discount)).toFixed(2));
                                    } else {
                                        $(el.parent().parent().find('.amount')).html((parseFloat(item.totalAmount)+parseFloat(itemTaxPrice)).toFixed(2));
                                    }

                                }

                                var totalItemDiscountPrice = 0;
                                var itemDiscountPriceInput = $('.discount');

                                for (var k = 0; k < itemDiscountPriceInput.length; k++) {
                                    totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
                                }


                                $('.subTotal').html(totalItemPrice.toFixed(2));
                                $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                                $('.totalAmount').html((parseFloat(totalItemPrice) - parseFloat(totalItemDiscountPrice) + parseFloat(totalItemTaxPrice)).toFixed(2));
                                $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));

                            }
                        });
                    }

                },
            });
        }

        $(document).on('keyup', '.quantity', function () {
            var quntityTotalTaxPrice = 0;

            var el = $(this).parent().parent().parent().parent();

            var quantity = $(this).val();
            var price = $(el.find('.price')).val();
            var discount = $(el.find('.discount')).val();
            if(discount.length <= 0)
            {
                discount = 0 ;
            }

            var totalItemPrice = (quantity * price) - discount;

            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html((parseFloat(itemTaxPrice)+parseFloat(amount)).toFixed(2));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

        })

        $(document).on('keyup change', '.price', function () {

            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();
            var discount = $(el.find('.discount')).val();
            if(discount.length <= 0)
            {
                discount = 0 ;
            }


            var totalItemPrice = (quantity * price)-discount;

            var amount = (totalItemPrice);

            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html((parseFloat(itemTaxPrice)+parseFloat(amount)).toFixed(2));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

        })

        $(document).on('keyup change', '.discount', function () {
            var el = $(this).parent().parent().parent();
            var discount = $(this).val();
            if(discount.length <= 0)
            {
                discount = 0 ;
            }
            var price = $(el.find('.price')).val();

            var quantity = $(el.find('.quantity')).val();
            var totalItemPrice = (quantity * price) - discount;

            var amount = (totalItemPrice);



            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html((parseFloat(itemTaxPrice)+parseFloat(amount)).toFixed(2));


            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }


            var totalItemDiscountPrice = 0;
            var itemDiscountPriceInput = $('.discount');

            for (var k = 0; k < itemDiscountPriceInput.length; k++) {

                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));


        })

        $(document).on('click', '[data-repeater-create]', function () {
            $('.item :selected').each(function () {
                var id = $(this).val();
                $(".item option[value=" + id + "]").prop("disabled", true);
            });
        })

        $(document).on('click', '[data-repeater-delete]', function () {
        // $('.delete_item').click(function () {
            if (confirm('Are you sure you want to delete this element?')) {
                var el = $(this).parent().parent();
                var id = $(el.find('.id')).val();
                var amount = $(el.find('.amount')).html();

                $.ajax({
                    url: '{{route('invoice.product.destroy')}}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('#token').val()
                    },
                    data: {
                        'id': id,
                        'amount': amount,
                    },
                    cache: false,
                    success: function (data) {

                    },
                });

            }
        });
    </script>
    <script>
        $(document).on('click', '[data-repeater-delete]', function () {
            $(".price").change();
            $(".discount").change();
        });

        $(document).ready(function() {
            $('#due_date').attr('min', new Date().toISOString().split('T')[0]);

            // Event listener for issue date change
            $('#issue_date').on('change', function() {
                const issueDate = new Date($(this).val());
                const dueDateInput = $('#due_date');

                if ($(this).val()) {
                    dueDateInput.attr('min', issueDate.toISOString().split('T')[0]);
                } else {
                    dueDateInput.attr('min', new Date().toISOString().split('T')[0]);
                }
            });

            // Event listener for clicking due date
            $(document).on('click', '#due_date', function () {
                const issueDate = new Date($('#issue_date').val());
                if (issueDate) {
                    $(this).attr('min', issueDate.toISOString().split('T')[0]);
                }
            });
        });
    </script>
@endpush

@section('content')
    {{--    @dd($invoice)--}}
    <div class="row">
        {{ Form::model($invoice, array('route' => array('invoice.update', $invoice->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="customer-box">
                                {{ Form::label('customer_id', __('Customer'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                {{ Form::select('customer_id', $customers,null, array('class' => 'form-control select ','id'=>'customer','data-url'=>route('invoice.customer'),'required'=>'required')) }}
                            </div>
                            <div id="customer_detail" class="d-none">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        <div class="form-icon-user">
                                            {{Form::date('issue_date',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('due_date', __('Due Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        <div class="form-icon-user">
                                            {{Form::date('due_date',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('invoice_number', __('Invoice Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        <div class="form-icon-user">
                                            <input type="text" name="invoice_number" class="form-control" value="{{$invoice_number}}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                    {{ Form::select('category_id', $category,null, array('class' => 'form-control select','required'=>'required')) }}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('ref_number', __('Ref Number'),['class'=>'form-label']) }}
                                        <div class="form-icon-user">
                                            <span><i class="ti ti-joint"></i></span>
                                            {{ Form::text('ref_number', null, array('class' => 'form-control')) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('currency_code', __('Currency'),['class'=>'form-label']) }} <span id="conversion_rate_span"> (1 {{ \Auth::user()->currencyCode() }} = {{ $invoice->exchange_rate }} {{ $invoice->currency_code }}  ) </span>
                                        {{ Form::text('currency_code', null, array('class' => 'form-control', 'id' => 'currency_code', 'readonly' => 'readonly')) }}
                                        <input type="hidden" name="currency_symbol" id="currency_symbol" value="{{ $invoice->currency_symbol }}">
                                        <input type="hidden" name="exchange_rate" id="exchange_rate" value="{{ $invoice->exchange_rate }}">
                                        {{-- {{ Form::select('currency', $currency, null, array('class' => 'form-control select','id'=>'currency', 'required'=>'required')) }} --}}
                                    </div>
                                </div>
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="mt-4 form-check custom-checkbox">--}}
{{--                                        <input class="form-check-input" type="checkbox" name="discount_apply" id="discount_apply" {{$invoice->discount_apply==1?'checked':''}}>--}}
{{--                                        <label class="form-check-label" for="discount_apply">{{__('Discount Apply')}}</label>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        {{Form::label('sku',__('SKU')) }}--}}
{{--                                        {!!Form::text('sku', null,array('class' => 'form-control','required'=>'required')) !!}--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                @if(!$customFields->isEmpty())
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include('customFields.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class="mb-4 d-inline-block">{{__('Product & Services')}}</h5>
            <div class="card repeater" data-value='{!! json_encode($invoice->items) !!}'>
                <div class="py-2 item-section">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal" data-target="#add-bank">
                                    <i class="ti ti-plus"></i> {{__('Add item')}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Items')}}<span class="text-danger">*</span></th>
                                <th>{{__('Quantity')}}</th>
                                <th>{{__('Price')}} </th>
                                <th>{{__('Discount')}}</th>
                                <th>{{__('Tax')}}</th>
                                <th class="text-end">{{__('Amount')}} </th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                {{ Form::hidden('id',null, array('class' => 'form-control id')) }}
                                <td width="25%" class="pt-0 form-group">
                                    {{ Form::select('item', $product_services,null, array('class' => 'form-control item select','data-url'=>route('invoice.product'))) }}

                                </td>
                                <td>

                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('quantity',null, array('class' => 'form-control quantity','required'=>'required','placeholder'=>__('Qty'),'required'=>'required')) }}
                                        <span class="bg-transparent unit input-group-text"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('price',null, array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
                                        <span class="bg-transparent input-group-text"><span class="my_currency_symbol">{{ $invoice->currency_symbol }}</span></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('discount',null, array('class' => 'form-control discount','required'=>'required','placeholder'=>__('Discount'))) }}
                                        <span class="bg-transparent input-group-text"><span class="my_currency_symbol">{{ $invoice->currency_symbol }}</span></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <div class="input-group colorpickerinput">
                                            <div class="taxes"></div>
                                            {{ Form::hidden('tax','', array('class' => 'form-control tax')) }}
                                            {{ Form::hidden('itemTaxPrice','', array('class' => 'form-control itemTaxPrice')) }}
                                            {{ Form::hidden('itemTaxRate','', array('class' => 'form-control itemTaxRate')) }}
                                        </div>
                                    </div>
                                </td>

                                <td class="text-end amount">0.00</td>

                                <td>
{{--                                    @can('delete invoice product')--}}
{{--                                        <a href="#" class="text-white ti ti-trash text-danger delete_item" data-repeater-delete></a>--}}
{{--                                    @endcan--}}

                                        <a href="#" class="text-white ti ti-trash repeater-action-btn bg-danger ms-2 delete_item" data-repeater-delete></a>

                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="form-group">
                                        {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                                    </div>
                                </td>
                                <td colspan="5"></td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Sub Total')}} (<span class="my_currency_symbol">{{ $invoice->currency_symbol }}</span>)</strong></td>
                                <td class="text-end subTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Discount')}} (<span class="my_currency_symbol">{{ $invoice->currency_symbol }}</span>)</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Tax')}} (<span class="my_currency_symbol">{{ $invoice->currency_symbol }}</span>)</strong></td>
                                <td class="text-end totalTax">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="blue-text"><strong>{{__('Total Amount')}} (<span class="my_currency_symbol">{{ $invoice->currency_symbol }}</span>)</strong></td>
                                <td class="text-end totalAmount blue-text">0.00</td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("invoice.index")}}';" class="btn btn-light me-3">
            <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
        </div>
        {{ Form::close() }}
    </div>
@endsection

