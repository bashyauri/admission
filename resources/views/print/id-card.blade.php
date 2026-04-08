<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card – {{ $student->surname }}</title>
    <style>
        /* CR80 PVC card: 3.375in x 2.125in */
        @page {
            size: 85.725mm 53.975mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
        }

        body {
            background: #e2e8f0;
            display: flex;
            justify-content: center;
            padding: 4mm;
        }

        .sheet {
            display: flex;
            flex-direction: column;
            gap: 4mm;
            align-items: center;
        }

        .card {
            width: 85.725mm;
            height: 53.975mm;
            position: relative;
            display: flex;
            flex-direction: column;
            border: 0.35mm solid #0f2f57;
            border-radius: 2mm;
            overflow: hidden;
            background: #ffffff;
        }

        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(37, 99, 235, 0.20), transparent 38%),
                radial-gradient(circle at 85% 85%, rgba(14, 165, 233, 0.20), transparent 40%),
                linear-gradient(165deg, #f8fbff 0%, #e9f2ff 60%, #ddeaff 100%);
            z-index: 0;
        }

        .card::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(
                -35deg,
                rgba(15, 47, 87, 0.06) 0,
                rgba(15, 47, 87, 0.06) 1mm,
                transparent 1mm,
                transparent 4mm
            );
            z-index: 0;
        }

        .card-front-content,
        .card-back-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-header {
            background: linear-gradient(120deg, #0f2f57 0%, #1d4d88 55%, #2f6fbb 100%);
            color: #fff;
            text-align: center;
            padding: 1.5mm 2mm;
            flex-shrink: 0;
        }

        .card-header .uni-name {
            font-size: 4.4pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.25mm;
            line-height: 1.2;
        }

        .card-header .id-label {
            font-size: 3.3pt;
            letter-spacing: 0.45mm;
            text-transform: uppercase;
            opacity: 0.9;
            margin-top: 0.4mm;
        }

        .card-body {
            display: flex;
            flex: 1;
            padding: 1.8mm 2mm;
            gap: 2.2mm;
        }

        .card-photo {
            flex-shrink: 0;
            width: 20mm;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .card-photo img {
            width: 20mm;
            height: 24mm;
            object-fit: cover;
            border: 0.45mm solid #0f2f57;
            border-radius: 1mm;
            background: #ffffff;
            box-shadow: 0 0.2mm 0.8mm rgba(0, 0, 0, 0.2);
        }

        .card-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 0.9mm;
            overflow: hidden;
        }

        .card-info .name {
            font-size: 5.2pt;
            font-weight: 800;
            color: #0f2f57;
            text-transform: uppercase;
            line-height: 1.2;
            word-break: break-word;
        }

        .card-info .row {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .card-info .label {
            font-size: 3.1pt;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.18mm;
        }

        .card-info .value {
            font-size: 4.4pt;
            color: #0f172a;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-footer {
            background: #0f2f57;
            color: #fff;
            text-align: center;
            padding: 1mm 2mm;
            font-size: 3.25pt;
            letter-spacing: 0.22mm;
            flex-shrink: 0;
        }

        .back-top {
            padding: 3mm 3.2mm 1.2mm;
            border-bottom: 0.3mm solid rgba(15, 47, 87, 0.35);
            text-align: center;
        }

        .back-title {
            font-size: 6.5pt;
            font-weight: 900;
            color: #0f2f57;
            letter-spacing: 0.15mm;
            text-transform: uppercase;
        }

        .back-subtitle {
            margin-top: 0.8mm;
            font-size: 3.5pt;
            color: #334155;
        }

        .back-body {
            padding: 2.2mm 3.2mm;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .back-rules {
            font-size: 3.55pt;
            line-height: 1.45;
            color: #0f172a;
        }

        .verification-block {
            display: flex;
            align-items: center;
            gap: 2.2mm;
            margin-top: 1.8mm;
            padding: 1.2mm;
            border: 0.25mm solid rgba(15, 47, 87, 0.25);
            border-radius: 1mm;
            background: rgba(255, 255, 255, 0.55);
        }

        .qr-box {
            width: 11mm;
            height: 11mm;
            border: 0.25mm solid #0f2f57;
            border-radius: 0.6mm;
            overflow: hidden;
            background: #fff;
            flex-shrink: 0;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .verify-meta {
            min-width: 0;
            flex: 1;
        }

        .verify-label {
            font-size: 2.9pt;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.15mm;
            margin-bottom: 0.4mm;
        }

        .barcode-strip {
            font-size: 4.2pt;
            font-weight: 800;
            letter-spacing: 0.34mm;
            color: #0f2f57;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .signature-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 4mm;
        }

        .signature {
            width: 33mm;
        }

        .signature-line {
            border-top: 0.3mm solid #0f2f57;
            margin-bottom: 0.6mm;
        }

        .signature-label {
            font-size: 3.2pt;
            color: #334155;
            text-transform: uppercase;
        }

        .back-contact {
            text-align: right;
            font-size: 3.3pt;
            color: #0f2f57;
            font-weight: 700;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .sheet {
                gap: 0;
            }

            .card {
                border-radius: 0;
                border: 0;
                page-break-after: always;
                break-after: page;
            }

            .card:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="card card-front">
            <div class="card-front-content">
                <div class="card-header">
                    <div class="uni-name">{{ config('app.name') }}</div>
                    <div class="id-label">Student Identity Card</div>
                </div>

                <div class="card-body">
                    <div class="card-photo">
                        <img src="{{ $photo }}" alt="Photo">
                    </div>

                    <div class="card-info">
                        <div class="name">
                            {{ $student->surname }}, {{ $student->firstname }}
                            @if($student->m_name) {{ $student->m_name }} @endif
                        </div>

                        <div class="row">
                            <span class="label">Matric No</span>
                            <span class="value">{{ $matric }}</span>
                        </div>

                        <div class="row">
                            <span class="label">Department</span>
                            <span class="value" title="{{ $dept }}">{{ $dept }}</span>
                        </div>

                        <div class="row">
                            <span class="label">Phone</span>
                            <span class="value">{{ $student->phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    If found, please return to the Registrar's Office
                </div>
            </div>
        </div>

        <div class="card card-back">
            <div class="card-back-content">
                <div class="back-top">
                    <div class="back-title">{{ config('app.name') }}</div>
                    <div class="back-subtitle">This card remains the property of the institution.</div>
                </div>

                <div class="back-body">
                    <div class="back-rules">
                        <div>1. Carry this card at all times while on campus.</div>
                        <div>2. This card is non-transferable.</div>
                        <div>3. Report loss immediately to Student Affairs.</div>
                        <div>4. Defaced or altered cards are invalid.</div>

                        <div class="verification-block">
                            <div class="qr-box">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrPayload) }}" alt="QR Code">
                            </div>
                            <div class="verify-meta">
                                <div class="verify-label">Verification Code</div>
                                <div class="barcode-strip">{{ str_replace('-', '', $matric) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="signature-wrap">
                        <div class="signature">
                            <div class="signature-line"></div>
                            <div class="signature-label">Student Signature</div>
                        </div>
                        <div class="back-contact">Registrar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
        window.onafterprint = function () {
            window.close();
        };
    </script>
</body>
</html>
