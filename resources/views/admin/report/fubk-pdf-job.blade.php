<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generating FUBK PDF</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; }
        .wrap { max-width: 720px; margin: 72px auto; padding: 0 20px; }
        .card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
        .title { margin: 0 0 10px; font-size: 20px; }
        .status { margin: 12px 0; color: #334155; }
        .note { margin-top: 8px; color: #64748b; font-size: 14px; }
        .ok { color: #166534; font-weight: 700; }
        .bad { color: #b91c1c; font-weight: 700; }
        .btn {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #0f172a;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1 class="title">Your PDF is being prepared</h1>
            <p class="status" id="statusText">Queued. Waiting for worker...</p>
            <p class="note">You can keep this tab open. Download will appear automatically when ready.</p>
            <a id="downloadBtn" href="{{ $downloadUrl }}" class="btn" style="display:none;">Download PDF</a>
        </div>
    </div>

    <script>
        (function () {
            const statusUrl = @json($statusUrl);
            const statusEl = document.getElementById('statusText');
            const btn = document.getElementById('downloadBtn');

            const timer = setInterval(async () => {
                try {
                    const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();

                    if (!response.ok) {
                        statusEl.textContent = data.message || 'Unable to read job status.';
                        statusEl.className = 'status bad';
                        clearInterval(timer);
                        return;
                    }

                    statusEl.textContent = data.message || 'Processing...';

                    if (data.status === 'completed') {
                        statusEl.className = 'status ok';
                        btn.style.display = 'inline-block';
                        btn.click();
                        clearInterval(timer);
                        return;
                    }

                    if (data.status === 'failed') {
                        statusEl.className = 'status bad';
                        clearInterval(timer);
                    }
                } catch (error) {
                    statusEl.textContent = 'Network error while checking job status.';
                    statusEl.className = 'status bad';
                    clearInterval(timer);
                }
            }, 2500);
        })();
    </script>
</body>
</html>
