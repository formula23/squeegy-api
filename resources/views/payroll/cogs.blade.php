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
        <th>Total Pay</th>
        <th>Referral Program</th>
        <th>Training</th>
        <th>Kit Rental</th>
        <th>COGs</th>
        <th>Promotional</th>
    </tr>

    @foreach($cogs as $cog)

        <tr>
            <td>{{ $cog[0] }}</td>
            <td>{{ date("m/d/Y") }}</td>
            <td>${{ number_format($cog[4], 2) }}</td>
            <td></td>
            <td></td>
            <td>${{ number_format($cog[3], 2) }}</td>
            <td>${{ number_format($cog[1], 2) }}</td>
            <td>${{ number_format($cog[2], 2) }}</td>
        </tr>

    @endforeach

</table>

</body>
</html>