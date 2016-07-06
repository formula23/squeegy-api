<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Squeegy Payroll</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>

<body>
<h3>Squeegy COGs - Week of {{$week_of}}</h3>

<table class="table table-condensed">

    <tr>
        <th>Washer Name</th>
        <th>Pay Date</th>
        <th>COGs</th>
        <th>Promotional</th>
        <th>Jobs Pay</th>
        <th>Weekly Min. Pay</th>
        <th>Daily Min. Pay</th>
        <th>Referral Program</th>
        <th>Referral Code</th>
        <th>Training</th>
        <th>Washer Training</th>
        <th>Bonus</th>
        <th>Tip</th>
        <th>Kit Rental</th>
        <th>Total Pay</th>
    </tr>

    @foreach($orders_by_worker as $worker)

        <tr>
            <td>{{ $worker["washer"]["name"] }}</td>
            <td>{{ date("m/d/Y") }}</td>
            <td>${{ @number_format($worker['jobs']['total_cog'], 2) }}</td>
            <td>${{ @number_format($worker['jobs']['total_promotional'], 2) }}</td>
            <td>${{ @number_format($worker['jobs']['total_cog']+$worker['jobs']['total_promotional'], 2) }}</td>
            <td>${{ @number_format($worker['minimum'], 2) }}</td>
            <td>${{ @number_format($worker['daily_min_pay'] + $worker['total_bonus'], 2) }}</td>
            <td>${{ @number_format($worker['referral_program'], 2) }}</td>
            <td>${{ @number_format($worker['referral_code'], 2) }}</td>
            <td>${{ @number_format($worker['training'], 2) }}</td>
            <td>${{ @number_format($worker['total_washer_training'], 2) }}</td>
            <td>${{ @number_format($worker['bonus'], 2) }}</td>
            <td>${{ @number_format(array_sum((array)$worker['tip']), 2) }}</td>
            <td>${{ @number_format($worker['rental'], 2) }}</td>
            <td>${{ @number_format($worker['total_pay'], 2) }}</td>
        </tr>

    @endforeach

</table>

</body>
</html>