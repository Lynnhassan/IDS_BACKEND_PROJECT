{{--<!DOCTYPE html>--}}
{{--<html>--}}
{{--<head>--}}
{{--    <meta charset="utf-8">--}}
{{--    <title>Certificate of Completion</title>--}}
{{--    <style>--}}
{{--        @page {--}}
{{--            margin: 0;--}}
{{--            size: A4 landscape;--}}
{{--        }--}}

{{--        * {--}}
{{--            margin: 0;--}}
{{--            padding: 0;--}}
{{--            box-sizing: border-box;--}}
{{--        }--}}

{{--        body {--}}
{{--            font-family: 'DejaVu Sans', 'Arial', sans-serif;--}}
{{--            width: 297mm;--}}
{{--            height: 210mm;--}}
{{--            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);--}}
{{--            position: relative;--}}
{{--            overflow: hidden;--}}
{{--        }--}}

{{--        .certificate-border {--}}
{{--            position: absolute;--}}
{{--            top: 15mm;--}}
{{--            left: 15mm;--}}
{{--            right: 15mm;--}}
{{--            bottom: 15mm;--}}
{{--            border: 3px solid #fff;--}}
{{--            box-shadow: inset 0 0 0 8px rgba(255,255,255,0.3);--}}
{{--        }--}}

{{--        .certificate-content {--}}
{{--            position: absolute;--}}
{{--            top: 25mm;--}}
{{--            left: 25mm;--}}
{{--            right: 25mm;--}}
{{--            bottom: 25mm;--}}
{{--            background: white;--}}
{{--            padding: 40px 60px;--}}
{{--            text-align: center;--}}
{{--        }--}}

{{--        .header {--}}
{{--            margin-bottom: 30px;--}}
{{--        }--}}

{{--        .logo {--}}
{{--            font-size: 48px;--}}
{{--            margin-bottom: 10px;--}}
{{--        }--}}

{{--        .title {--}}
{{--            font-size: 42px;--}}
{{--            color: #2d3748;--}}
{{--            font-weight: bold;--}}
{{--            letter-spacing: 3px;--}}
{{--            margin-bottom: 10px;--}}
{{--            text-transform: uppercase;--}}
{{--        }--}}

{{--        .subtitle {--}}
{{--            font-size: 18px;--}}
{{--            color: #718096;--}}
{{--            font-style: italic;--}}
{{--            margin-bottom: 40px;--}}
{{--        }--}}

{{--        .recipient-section {--}}
{{--            margin: 40px 0;--}}
{{--        }--}}

{{--        .awarded-to {--}}
{{--            font-size: 16px;--}}
{{--            color: #718096;--}}
{{--            margin-bottom: 15px;--}}
{{--            text-transform: uppercase;--}}
{{--            letter-spacing: 2px;--}}
{{--        }--}}

{{--        .recipient-name {--}}
{{--            font-size: 48px;--}}
{{--            color: #667eea;--}}
{{--            font-weight: bold;--}}
{{--            margin-bottom: 30px;--}}
{{--            border-bottom: 3px solid #667eea;--}}
{{--            display: inline-block;--}}
{{--            padding: 0 40px 10px;--}}
{{--        }--}}

{{--        .completion-text {--}}
{{--            font-size: 18px;--}}
{{--            color: #4a5568;--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}

{{--        .course-title {--}}
{{--            font-size: 28px;--}}
{{--            color: #2d3748;--}}
{{--            font-weight: bold;--}}
{{--            margin: 20px 0;--}}
{{--        }--}}

{{--        .course-details {--}}
{{--            font-size: 14px;--}}
{{--            color: #718096;--}}
{{--            margin: 30px 0;--}}
{{--        }--}}

{{--        .detail-item {--}}
{{--            display: inline-block;--}}
{{--            margin: 0 20px;--}}
{{--        }--}}

{{--        .detail-label {--}}
{{--            font-weight: bold;--}}
{{--            color: #4a5568;--}}
{{--        }--}}

{{--        .footer {--}}
{{--            margin-top: 50px;--}}
{{--            display: table;--}}
{{--            width: 100%;--}}
{{--        }--}}

{{--        .signature-section {--}}
{{--            display: table-cell;--}}
{{--            text-align: center;--}}
{{--            width: 50%;--}}
{{--            vertical-align: bottom;--}}
{{--        }--}}

{{--        .signature-line {--}}
{{--            border-top: 2px solid #2d3748;--}}
{{--            width: 200px;--}}
{{--            margin: 0 auto 10px;--}}
{{--        }--}}

{{--        .signature-name {--}}
{{--            font-size: 16px;--}}
{{--            color: #2d3748;--}}
{{--            font-weight: bold;--}}
{{--        }--}}

{{--        .signature-title {--}}
{{--            font-size: 14px;--}}
{{--            color: #718096;--}}
{{--        }--}}

{{--        .qr-section {--}}
{{--            display: table-cell;--}}
{{--            text-align: center;--}}
{{--            width: 50%;--}}
{{--            vertical-align: bottom;--}}
{{--        }--}}

{{--        .qr-code {--}}
{{--            width: 100px;--}}
{{--            height: 100px;--}}
{{--            margin-bottom: 10px;--}}
{{--        }--}}

{{--        .verification-code {--}}
{{--            font-size: 11px;--}}
{{--            color: #718096;--}}
{{--            font-family: 'Courier New', monospace;--}}
{{--            word-break: break-all;--}}
{{--        }--}}
{{--    </style>--}}
{{--</head>--}}
{{--<body>--}}
{{--<div class="certificate-border"></div>--}}

{{--<div class="certificate-content">--}}
{{--    <!-- Header -->--}}
{{--    <div class="header">--}}
{{--        <div class="logo">🎓</div>--}}
{{--        <div class="title">Certificate of Completion</div>--}}
{{--        <div class="subtitle">This certifies that</div>--}}
{{--    </div>--}}

{{--    <!-- Recipient -->--}}
{{--    <div class="recipient-section">--}}
{{--        <div class="awarded-to">This is proudly presented to</div>--}}
{{--        <div class="recipient-name">{{ $certificate->user->fullName ?? 'Student Name' }}</div>--}}
{{--    </div>--}}

{{--    <!-- Course Info -->--}}
{{--    <div class="completion-text">--}}
{{--        has successfully completed the course--}}
{{--    </div>--}}

{{--    <div class="course-title">{{ $certificate->course->title ?? 'Course Title' }}</div>--}}

{{--    <div class="course-details">--}}
{{--        <div class="detail-item">--}}
{{--            <span class="detail-label">Category:</span>--}}
{{--            {{ $certificate->course->category ?? 'N/A' }}--}}
{{--        </div>--}}
{{--        <div class="detail-item">--}}
{{--            <span class="detail-label">Difficulty:</span>--}}
{{--            {{ ucfirst($certificate->course->difficulty ?? 'N/A') }}--}}
{{--        </div>--}}
{{--        <div class="detail-item">--}}
{{--            <span class="detail-label">Date Issued:</span>--}}
{{--            {{ $certificate->generatedDate->format('F d, Y') }}--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <!-- Footer with Signature and QR -->--}}
{{--    <div class="footer">--}}
{{--        <div class="signature-section">--}}
{{--            <div class="signature-line"></div>--}}
{{--            <div class="signature-name">--}}
{{--                {{ $certificate->course->instructor->fullName ?? 'Instructor' }}--}}
{{--            </div>--}}
{{--            <div class="signature-title">Course Instructor</div>--}}
{{--        </div>--}}

{{--        @if($qrCode)--}}
{{--            <div class="qr-section">--}}
{{--                <img src="{{ $qrCode }}" alt="QR Code" class="qr-code">--}}
{{--                <div class="verification-code">--}}
{{--                    Verify: {{ $certificate->verificationCode }}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endif--}}
{{--    </div>--}}
{{--</div>--}}
{{--</body>--}}
{{--</html>--}}
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            width: 297mm;
            height: 210mm;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .certificate-border {
            position: absolute;
            top: 15mm;
            left: 15mm;
            right: 15mm;
            bottom: 15mm;
            border: 3px solid #fff;
            box-shadow: inset 0 0 0 8px rgba(255,255,255,0.3);
        }

        .certificate-content {
            position: absolute;
            /*top: 25mm;*/
            left: 25mm;
            right: 25mm;
            bottom: 25mm;
            background: white;
            padding: 40px 60px;
            text-align: center;
        }

        .header {
            margin-bottom: 30px;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 42px;
            color: #2d3748;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 18px;
            color: #718096;
            font-style: italic;
            margin-bottom: 40px;
        }

        .recipient-section {
            margin: 40px 0;
        }

        .awarded-to {
            font-size: 16px;
            color: #718096;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .recipient-name {
            font-size: 48px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            display: inline-block;
            padding: 0 40px 10px;
        }

        .completion-text {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 20px;
        }

        .course-title {
            font-size: 28px;
            color: #2d3748;
            font-weight: bold;
            margin: 20px 0;
        }

        .course-details {
            font-size: 14px;
            color: #718096;
            margin: 30px 0;
        }

        .detail-item {
            display: inline-block;
            margin: 0 20px;
        }

        .detail-label {
            font-weight: bold;
            color: #4a5568;
        }

        .footer {
            margin-top: 50px;
            display: table;
            width: 100%;
        }

        .signature-section {
            display: table-cell;
            text-align: center;
            width: 50%;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 2px solid #2d3748;
            width: 200px;
            margin: 0 auto 10px;
        }

        .signature-name {
            font-size: 16px;
            color: #2d3748;
            font-weight: bold;
        }

        .signature-title {
            font-size: 14px;
            color: #718096;
        }

        .qr-section {
            display: table-cell;
            text-align: center;
            width: 50%;
            vertical-align: bottom;
        }

        .qr-code {
            width: 130px;   /* ✅ Increased from 100px to 130px */
            height: 130px;  /* ✅ Increased from 100px to 130px */
            margin-bottom: 2px;
        }

        .verification-code {
            font-size: 11px;
            color: #718096;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
    </style>
</head>
<body>
<div class="certificate-border"></div>

<div class="certificate-content">
    <!-- Header -->
    <div class="header">
        <div class="logo">🎓</div>
        <div class="title">Certificate of Completion</div>
        <div class="subtitle">This certifies that</div>
    </div>

    <!-- Recipient -->
    <div class="recipient-section">
        <div class="awarded-to">This is proudly presented to</div>
        <div class="recipient-name">{{ $certificate->user->fullName ?? 'Student Name' }}</div>
    </div>

    <!-- Course Info -->
    <div class="completion-text">
        has successfully completed the course
    </div>

    <div class="course-title">{{ $certificate->course->title ?? 'Course Title' }}</div>

    <div class="course-details">
        <div class="detail-item">
            <span class="detail-label">Category:</span>
            {{ $certificate->course->category ?? 'N/A' }}
        </div>
        <div class="detail-item">
            <span class="detail-label">Difficulty:</span>
            {{ ucfirst($certificate->course->difficulty ?? 'N/A') }}
        </div>
        <div class="detail-item">
            <span class="detail-label">Date Issued:</span>
            {{ $certificate->generatedDate->format('F d, Y') }}
        </div>
    </div>

    <!-- Footer with Signature and QR -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-name">
                {{ $certificate->course->instructor->fullName ?? 'Instructor' }}
            </div>
            <div class="signature-title">Course Instructor</div>
        </div>

        @if($qrCode)
            <div class="qr-section">
                <img src="{{ $qrCode }}" alt="QR Code" class="qr-code">
                <div class="verification-code">
                    Verify: {{ $certificate->verificationCode }}
                </div>
            </div>
        @endif
    </div>
</div>
</body>
</html>
