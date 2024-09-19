@php
    $settings_data = \App\Models\Utility::settingsById($invoice->created_by);
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ $settings_data['SITE_RTL'] == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap" rel="stylesheet">

    <style type="text/css">
        :root {
            --theme-color: #4e8fb8;
            --white: #ffffff;
            --black: #000000;
        }

        body {
            font-family: 'Lato', sans-serif;
            margin: 0;
            padding: 0;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background-image: url('{{ asset('invoice/invoice.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 115px 60px; /* Adjusted padding to align content with background */
            position: relative;
            z-index: 1;
            
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: var(--theme-color);
            text-align: center;
            margin-top: 70px; /* Increased from 20px to 40px for more space */
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .info {
            width: 48%;
            line-height: 1.6;
        }

        .info strong {
            font-weight: bold;
            color: #4e8fb8;
        }

        .right-info {
            text-align: right;
        }

        .table-container {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 8px;
            text-align: left;
        }

        table thead th {
        background-color: #dbe8eb; /* Set background color for the header */
        padding: 12px;
        text-align: left;
        text-decoration: underline; /* Add underline to the text */
        text-underline-offset: 3px; 
    }

    /* Table Body Styling */
    table tbody td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px dashed #000; /* Dotted border for table content */
    }

        .totals {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .totals table {
            width: 300px;
            border-collapse: collapse;
        }

        .totals table th, .totals table td {
            padding: 8px;
            text-align: right;
        }

        .totals table tr {
            border-bottom: 1px dashed #000; /* Dotted line for totals */
        }

        .bank-info {
            margin-top: 30px;
            line-height: 1.6;
        }

        .bank-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .bank-info th, .bank-info td {
            padding: 8px;
            text-align: left;
        }

        .bank-info th {
            border-bottom: 1px solid #000; /* Dotted line for bank details */
        }

        .footer-content {
        display: flex;
        align-items: center;
        margin-top: 50px;
        padding-top: 20px;
}
        .footer {
            text-align: center;
            margin-top: 50px; /* Adjust to align with the design reference */
            padding-top: 10px;
        }

        .underline {
        text-decoration: underline;
        text-underline-offset: 2px; /* Adjusts the space between the text and underline */
    }

    .info-section .info {
        line-height: 1.8; /* Adjusts the line spacing */
    }
    
    @media print {
        body {
            font-size: 12px; /* Reduce the font size*/
        }

        .invoice-container {
            max-width: 100%;
            max-height:auto;
            margin: 0;
            padding: 140px 60px; 
            background-size: contain; 
            background-position: center;
        }

        .title {
            font-size: 20px; /* Decrease title font size */
            margin-top: 20px; /* Adjust margin */
        }

        .info-section {
            margin-top: 10px; /* Decrease space between sections */
        }

        .info {
            width: 48%;
            line-height: 1.4; /* Decrease line spacing */
        }

        .table-container {
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 5px; /* Reduce padding */
        }

        .totals {
            margin-top: 10px;
        }

        .totals table th, .totals table td {
            padding: 5px;
        }

        .bank-info {
            margin-top: 10px;
            line-height: 1.4; /* Decrease line spacing */
        }

        .footer-content {
            margin-top: 60px;
            padding-top: 10px;
        }

        /* Hide unnecessary elements when printing */
        .no-print {
            display: none;
        } 
    }
    </style>

    @if ($settings_data['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
    @endif
</head>

<body>
<div class="invoice-container">
    <!-- Title Section -->
    <div class="title">{{ __('Tax Invoice') }}</div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info">
            <strong>{{ __('Bill To') }}:</strong><br>
            {{ $customer->billing_name ?? 'Static Name' }}<br>
            {{ $customer->billing_address ?? 'Static Address' }}<br>
            {{ $customer->billing_city ?? 'Static City' }}, {{ $customer->billing_state ?? 'Static State' }} - {{ $customer->billing_zip ?? 'Zip Code' }}<br>
            {{ $customer->billing_country ?? 'Country' }}<br>
            TRN: {{ $customer->trn ?? 'N/A' }}
        </div>
        <div class="info right-info">
            <strong>{{ __('Invoice Number') }}:</strong> {{ Utility::invoiceNumberFormat($settings, $invoice->invoice_id) ?? '#INV0001' }}<br>
            <strong>{{ __('Invoice Date') }}:</strong> {{ Utility::dateFormat($settings, $invoice->issue_date) ?? 'Date' }}<br>
            <strong>{{ __('Due Date') }}:</strong> {{ Utility::dateFormat($settings, $invoice->due_date) ?? 'Date' }}<br>
            TRN: {{ $settings['tax_number'] ?? 'N/A' }}
        </div>
    </div>

    <!-- Billed By Section -->
    <div class="info-section">
        <div class="info">
            <strong>{{ __('Billed By') }}:</strong><br>
            <span class="underline">Vendor Name:</span>&nbsp; Zero Gravity Advertisement Gifts<br>
            <span class="underline">Address:</span>&nbsp; Unit 802, Guardian Tower, Al Mustarhim St, Abu Dhabi<br>
            <span class="underline">PO Box:</span>&nbsp; 107018
        </div>
    </div>

    <div class="table-container">
        <table class="section">
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Quantity') }}</th>
                    <th>{{ __('Rate') }}</th>
                    <th>{{ __('VAT') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('VAT Amount') }}</th>
                    <th>{{ __('Delivery Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->itemData ?? [] as $item)
                    <tr>
                        <td>{{ $item->name ?? 'Item Name' }}<br>{{ $item->description ?? '' }}</td>
                        <td>{{ $item->quantity ?? '1' }}</td>
                        <td>{{ Utility::priceFormat($settings, $item->price ?? 0) }}</td>
                        <td>{{ $item->tax ?? '5%' }}</td>
                        <td>{{ Utility::priceFormat($settings, $item->amount ?? 0) }}</td>
                        <td>{{ Utility::priceFormat($settings, $item->tax_amount ?? 0) }}</td>
                        <td>{{ Utility::dateFormat($settings, $item->delivery_date ?? 'N/A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals Section -->
    <div class="totals">
        <table>
            <tr>
                <th>{{ __('Subtotal') }}</th>
                <td>{{ Utility::priceFormat($settings, $invoice->subtotal ?? 0) }}</td>
            </tr>
            <tr>
                <th>{{ __('VAT 5% Total') }}</th>
                <td>{{ Utility::priceFormat($settings, $invoice->total_vat ?? 0) }}</td>
            </tr>
            <tr>
                <th>{{ __('Total') }}</th>
                <td>{{ Utility::priceFormat($settings, $invoice->total_amount ?? 0) }}</td>
            </tr>
            <tr>
                <th>{{ __('Amount Received') }}</th>
                <td>{{ Utility::priceFormat($settings, $invoice->amount_received ?? 0) }}</td>
            </tr>
            <tr>
                <th>{{ __('Balance Due') }}</th>
                <td style="border-bottom: 1px dotted #000;">{{ Utility::priceFormat($settings, $invoice->balance_due ?? 0) }}</td>
            </tr>
        </table>
    </div>

    <!-- Bank Information -->
    <div class="bank-info">
        <table style="width: 50%; border-collapse: collapse;">
            <tr>
                <th style="border-bottom: 1px solid #000;">{{ __('Bank Name') }}</th>
                <td style="border-bottom: 1px solid #000;">{{ $settings['bank_name'] ?? 'Bank Name' }}</td>
            </tr>
            <tr>
                <th style="border-bottom: 1px solid #000;">{{ __('Account Name') }}</th>
                <td style="border-bottom: 1px solid #000;">{{ $settings['account_name'] ?? 'Account Name' }}</td>
            </tr>
            <tr>
                <th style="border-bottom: 1px solid #000;">{{ __('Account Number') }}</th>
                <td style="border-bottom: 1px solid #000;">{{ $settings['account_number'] ?? '0000000000' }}</td>
            </tr>
            <tr>
                <th style="border-bottom: 1px solid #000;">{{ __('IBAN Number') }}</th>
                <td style="border-bottom: 1px solid #000;">{{ $settings['iban_number'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th style="border-bottom: 1px solid #000;">{{ __('Swift Code') }}</th>
                <td style="border-bottom: 1px solid #000;">{{ $settings['swift_code'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th style="border-bottom: 1px solid #000;">{{ __('Branch Name') }}</th>
                <td style="border-bottom: 1px solid #000;">{{ $settings['branch'] ?? 'Branch Name' }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer Section -->
    <div class="footer-content">
        <div class="footer-left">
            <strong>Zero Gravity Advertisement Gifts</strong>
        </div>
        <div class="footer-right" style="margin-left:200px;">
            <strong>Client Acknowledgement</strong>
        </div>
    </div>
</div>

@if (!isset($preview))
    @include('invoice.script')
@endif