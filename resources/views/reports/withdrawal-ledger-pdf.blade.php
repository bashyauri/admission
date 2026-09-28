<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #25324b; font-size: 9px; }
        h1 { font-size: 18px; margin: 0 0 5px; }
        .meta { color: #64748b; margin-bottom: 14px; }
        .stats { margin: 10px 0 16px; }
        .stats span { display: inline-block; margin: 0 12px 5px 0; padding: 6px 8px; background: #f1f5f9; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        th { background: #e2e8f0; font-size: 8px; text-transform: uppercase; }
        .footer { margin-top: 12px; color: #64748b; font-size: 8px; }
    </style>
</head>
<body>
    <h1>{{ $report_title }}</h1>
    <div class="meta">Undergraduate student status records · Generated {{ now()->format('d M Y, H:i') }}</div>
    <div class="stats">
        @foreach($summary as $label => $value)
            <span>{{ str($label)->replace('_', ' ')->title() }}: <strong>{{ $value }}</strong></span>
        @endforeach
    </div>

    @if($report === 'departmental')
        <table>
            <thead><tr><th>Department</th><th>Total</th><th>Academic</th><th>Voluntary</th><th>Medical</th><th>Pending</th><th>Reinstated</th></tr></thead>
            <tbody>@foreach($department_summary as $row)<tr><td>{{ $row['department'] }}</td><td>{{ $row['total'] }}</td><td>{{ $row['academic'] }}</td><td>{{ $row['voluntary'] }}</td><td>{{ $row['medical'] }}</td><td>{{ $row['senate_pending'] }}</td><td>{{ $row['reinstated'] }}</td></tr>@endforeach</tbody>
        </table>
    @else
        <table>
            <thead><tr><th>Matric No</th><th>Student</th><th>Programme</th><th>Department</th><th>Type</th><th>Session</th><th>Effective</th><th>Senate Ref</th><th>Decision</th><th>Reinstatement</th></tr></thead>
            <tbody>@foreach($rows as $row)<tr>
                <td>{{ $row['matric_no'] }}</td><td>{{ $row['student_name'] }}</td><td>{{ $row['programme'] }}</td><td>{{ $row['department'] }}</td>
                <td>{{ $row['withdrawal_type'] }}</td><td>{{ $row['academic_session'] }}</td><td>{{ $row['effective_date'] ?? '—' }}</td>
                <td>{{ $row['senate_reference'] ?? '—' }}</td><td>{{ str_replace('_', ' ', $row['senate_decision'] ?: '—') }}</td><td>{{ $row['reinstatement_status'] }}</td>
            </tr>@endforeach</tbody>
        </table>
    @endif
    <div class="stats">
        @foreach(['By academic session' => $by_session, 'By withdrawal type' => $by_type, 'By department' => $by_department] as $heading => $counts)
            <h2>{{ $heading }}</h2>
            @foreach($counts as $category => $count)
                <span>{{ $category }}: <strong>{{ $count }}</strong></span>
            @endforeach
        @endforeach
    </div>
    <div class="footer">Official administrative report. Handle according to institutional data access policy.</div>
</body>
</html>
