<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Squeegy Payroll</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">--}}
</head>

<body>
<h3>Squeegy Payroll for {{ $washer_info['washer']['name'] }} - Week of {{$week_of}}</h3>

<h4>Number of washes this week: {{ $washer_info['job_count'] }}</h4>

<table class="table table-condensed">

    <thead>
    <tr>
        {{--<th></th>--}}
        <th>Order Date</th>
        <th>Year</th>
        <th>Make</th>
        <th>Model</th>
        <th>Wash Type</th>
        <th>Wash Time</th>
        <th>ETC</th>
        <th>Rating</th>
        <th>Pay</th>
    </tr>
    </thead>

    @foreach($washer_info['jobs']['days'] as $date=>$day)

        <tr>
            <td colspan="9"><strong>{{ $date }}</strong></td>
        </tr>

        @foreach($day['orders'] as $idx=>$job)

            <tr>
                {{--<td><img width="150" src="https://s3-us-west-1.amazonaws.com/com.octanela.squeegy/orders/{{ $job['id'] }}.jpg" /></td>--}}
                <td>{{ $job['time']  }}</td>
                <td>{{ $job['vehicle']['year']  }}</td>
                <td>{{ $job['vehicle']['make']  }}</td>
                <td>{{ $job['vehicle']['model']  }}</td>
                <td>{{ $job['wash_type']  }}</td>
                <td>{{ $job['wash_time']  }}</td>
                <td>{{ $job['etc']  }}</td>
                <td>{{ $job['rating']  }}</td>
                <td>${{ number_format($job['pay'], 2)  }}</td>
            </tr>

        @endforeach

        <tr>
            <td colspan="8" class="text-right"><strong>Subtotal:</strong></td>
            <td>${{ number_format($day['pay'], 2)  }}</td>

        </tr>

        <tr>
            <td colspan="9">&nbsp;</td>
        </tr>

    @endforeach

    @if($washer_info['minimum'])
    <tr>
        <td colspan="8" class="text-right"><strong>Supplement weekly min. ($500):</strong></td>
        <td>${{ number_format($washer_info['minimum'], 2) }}</td>
    </tr>
    @endif

    @if($washer_info['rental'])
    <tr>
        <td colspan="8" class="text-right"><strong>Equipment Rental:</strong></td>
        <td>- ${{ number_format($washer_info['rental'], 2) }}</td>
    </tr>
    @endif

    <tr>
        <td colspan="8" class="text-right"><strong>Total:</strong></td>
        <td><strong>${{ number_format((($washer_info['jobs']['total'] + $washer_info['minimum']) - $washer_info['rental']), 2) }}</strong></td>
    </tr>

</table>

</body>
</html>
