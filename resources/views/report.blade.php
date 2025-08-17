<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
        th { background-color: #f8f8f8; }
    </style>
</head>
<body>
<h1>Monthly Report</h1>

<table>
    <thead>
        <tr>
            <th>Month</th>
            <th>Gross Value</th>
            <th>Inflows</th>
            <th>MoM %</th>
            <th>YTD</th>
        </tr>
    </thead>
    <tbody>
    @foreach($snapshots as $s)
        <tr>
            <td>{{ $s['month'] }}</td>
            <td>{{ $s['gross_value'] }}</td>
            <td>{{ $s['inflows'] }}</td>
            <td>{{ $s['mom_pct'] }}</td>
            <td>{{ $s['ytd'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>AI TL;DR</h2>
<p>Placeholder for AI-generated summary.</p>

</body>
</html>
