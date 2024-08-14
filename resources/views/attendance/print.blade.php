<!DOCTYPE html>
<html>
<head>
    <title>Attendance Print</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
    <style>
        /* Add custom styles for printing here */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<h1>Attendance Report</h1>

<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Date</th>
            <th>Status</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Late</th>
            <th>Early Leaving</th>
            <th>Overtime</th>
            <th>Break Time</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendanceEmployee as $attendance)
        <tr>
            <td>{{ $attendance->employee->name }}</td>
            <td>{{ $attendance->date }}</td>
            <td>{{ $attendance->status }}</td>
            <td>{{ $attendance->clock_in }}</td>
            <td>{{ $attendance->clock_out }}</td>
            <td>{{ $attendance->late }}</td>
            <td>{{ $attendance->early_leaving }}</td>
            <td>{{ $attendance->overtime }}</td>
            <td>{{ $attendance->total_break_duration }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="no-print">
    <button onclick="window.print()">Print</button>
    <button onclick="window.close()">Close</button>
</div>

</body>
</html>