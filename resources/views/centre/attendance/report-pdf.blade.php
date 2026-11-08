<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Recorded attendance</title>
    <style>
        body { font: 10pt DejaVu Sans, sans-serif; color: #182235; }
        table { width: 100%; border-collapse: collapse; }
        caption { text-align: left; font-weight: bold; margin-bottom: 6pt; }
        thead { display: table-header-group; }
        th, td { padding: 6pt; border: 1px solid #b2bed0; text-align: left; }
        th { background: #e7edf5; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Recorded attendance</h1>
    <p>{{ $centre->centre_name }} · {{ $startDate }} to {{ $endDate }}</p>
    <p>Saved marks only. Unmarked opportunities are not included.</p>
    @include('centre.attendance.report-table')
</body>
</html>
