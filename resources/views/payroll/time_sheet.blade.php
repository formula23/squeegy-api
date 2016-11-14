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
        {{--<th>Wash Time</th>--}}
        {{--<th>ETC</th>--}}
        <th>Rating</th>
        @if(@$washer_info['comp_type']=='comm')
            <th>Net Receipt</th>
            <th>Commission</th>
            <th>Txn Fee</th>
            <th>Addons</th>
        @endif
        <th>Net Pay</th>
    </tr>
    </thead>
@if(isset($washer_info['jobs']))
    @foreach($washer_info['jobs']['days'] as $date=>$day)

        @if( (isset($day['pay']) && $day['pay'] > 0) || (isset($day['min']) && $day['min'] > 0) || (isset($day['onsite']) && $day['onsite'] > 0) || count($day['orders']) )

        <tr>
            <td colspan="{{ $colspan + 1 }}"><strong>{{ $date }}</strong></td>
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

                @if(@$washer_info['comp_type']=='comm')
                    <td>${{ number_format($job['price'], 2)  }}</td>
                    <td>${{ number_format($job['price'] - $job['squeegy'], 2)  }}</td>
                    <td>(${{ number_format($job['txn'], 2)  }})</td>
                    <td>${{ number_format($job['addons'], 2)  }}</td>
                @endif

                <td>${{ number_format($job['pay'], 2)  }}</td>
            </tr>

        @endforeach

        @if(isset($day['tip']))
            <tr>
                <td colspan="{{ $colspan }}" class="text-right"><strong>Tips:</strong></td>
                <td>${{ number_format($day['tip'], 2) }}</td>
            </tr>
        @endif

        @if(isset($day['onsite']))
            <tr>
                <td colspan="{{ $colspan }}" class="text-right"><strong>On-site:</strong></td>
                <td>${{ number_format($day['onsite'], 2) }}</td>
            </tr>
        @endif

        @if(isset($day['onsite_tip']))
            <tr>
                <td colspan="{{ $colspan }}" class="text-right"><strong>On-site Tips:</strong></td>
                <td>${{ number_format($day['onsite_tip'], 2) }}</td>
            </tr>
        @endif

        @if(isset($day['min']) && $day['min'] > 0)
            <tr>
                <td colspan="{{ $colspan }}" class="text-right"><strong>Minimum:</strong></td>
                <td>${{ number_format($day['min'], 2) }}</td>
            </tr>
        @endif

        @if(isset($day['bonus']))
            <tr>
                <td colspan="{{ $colspan }}" class="text-right"><strong>Bonus:</strong></td>
                <td>${{ number_format($day['bonus'], 2) }}</td>
            </tr>
        @endif

        @if(isset($day['deduction']))
            <tr>
                <td colspan="{{ $colspan }}" class="text-right"><strong>Deduction:</strong></td>
                <td>$-{{ number_format($day['deduction'], 2) }}</td>
            </tr>
        @endif

            <tr>
                <td colspan="{{ $colspan }}" class="text-right"><strong>Subtotal:</strong></td>
                <td>${{ number_format($day['pay'] + @$day['min'] + @$day['onsite'] + @$day['bonus'] + @$day['tip'] + @$day['onsite_tip'] - @$day['deduction'], 2) }}</td>
            </tr>

            <tr>
                <td colspan="{{ $colspan + 1 }}">&nbsp;</td>
            </tr>

        @endif

    @endforeach
@endif

    @if($washer_info['minimum'])
    <tr>
        <td colspan="{{ $colspan }}" class="text-right"><strong>Supplement weekly min. (${{ $weekly_min }}):</strong></td>
        <td>${{ number_format($washer_info['minimum'], 2) }}</td>
    </tr>
    @endif

    @if($washer_info['rental'])
    <tr>
        <td colspan="{{ $colspan }}" class="text-right"><strong>Equipment Rental:</strong></td>
        <td>$-{{ number_format($washer_info['rental'], 2) }}</td>
    </tr>
    @endif

    @if(@$washer_info['training'])
        <tr>
            <td colspan="{{ $colspan }}" class="text-right"><strong>Training:</strong></td>
            <td>${{ number_format($washer_info['training'], 2) }}</td>
        </tr>
    @endif

    @if(@$washer_info['total_washer_training'])
        <tr>
            <td colspan="{{ $colspan }}" class="text-right"><strong>Washer Training:</strong></td>
            <td>${{ number_format($washer_info['total_washer_training'], 2) }}</td>
        </tr>
    @endif

    @if(@$washer_info['bonus'])
        <tr>
            <td colspan="{{ $colspan }}" class="text-right"><strong>Bonus:</strong></td>
            <td>${{ number_format($washer_info['bonus'], 2) }}</td>
        </tr>
    @endif

    @if(@$washer_info['referral_code'])
        <tr>
            <td colspan="{{ $colspan }}" class="text-right"><strong>Referral Code:</strong></td>
            <td>${{ number_format($washer_info['referral_code'], 2) }}</td>
        </tr>
    @endif

    @if(@$washer_info['referral_program'])
        <tr>
            <td colspan="{{ $colspan }}" class="text-right"><strong>Referral Program:</strong></td>
            <td>${{ number_format($washer_info['referral_program'], 2) }}</td>
        </tr>
    @endif

    @if(@$washer_info['extra_tip'])
        <tr>
            <td colspan="{{ $colspan }}" class="text-right" style="font-size: 18px"><strong>Extra Tips:</strong></td>
            <td style="font-size: 18px">${{ number_format($washer_info['extra_tip'], 2) }}</td>
        </tr>
    @endif

    @if(isset($washer_info['tip']))
        <tr>
            <td colspan="{{ $colspan }}" class="text-right" style="font-size: 18px"><strong>Tips ({{ count($washer_info['tip']) }} Washes):</strong></td>
            <td style="font-size: 18px">${{ number_format(array_sum($washer_info['tip']), 2) }}</td>
        </tr>
    @endif

    <tr>
        <td colspan="{{ $colspan }}" class="text-right" style="font-size: 22px"><strong>Total:</strong></td>
        <td style="font-size: 22px"><strong>${{ number_format($washer_info['total_pay'], 2) }}</strong></td>
    </tr>

</table>

</body>
</html>
