<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #1f2937; }
        .sheet {
            width: 100%;
            height: 760px;
            box-sizing: border-box;
            padding: 50px 60px;
            border: 14px solid #f97316;
        }
        .inner {
            border: 2px solid #fdba74;
            height: 100%;
            box-sizing: border-box;
            padding: 40px 50px;
            text-align: center;
        }
        .uni { font-size: 22px; font-weight: bold; letter-spacing: 2px; color: #c2410c; }
        .sub { font-size: 13px; color: #6b7280; margin-top: 4px; letter-spacing: 3px; text-transform: uppercase; }
        .title { font-size: 40px; font-weight: bold; margin: 46px 0 6px; color: #1f2937; }
        .title-rule { width: 160px; border: 0; border-top: 3px solid #f97316; margin: 0 auto 30px; }
        .presented { font-size: 14px; color: #6b7280; }
        .name { font-size: 34px; font-weight: bold; color: #c2410c; margin: 18px 0; }
        .name-rule { width: 360px; border: 0; border-top: 1px solid #d1d5db; margin: 0 auto 26px; }
        .body { font-size: 15px; color: #374151; line-height: 1.7; width: 560px; margin: 0 auto; }
        .points { color: #f97316; font-weight: bold; }
        .footer-table { width: 100%; margin-top: 64px; }
        .footer-table td { width: 50%; vertical-align: bottom; font-size: 12px; color: #6b7280; }
        .sig-line { border-top: 1px solid #9ca3af; width: 200px; padding-top: 6px; }
        .ref { margin-top: 28px; font-size: 11px; color: #9ca3af; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="inner">
            <div class="uni">MAKERERE UNIVERSITY</div>
            <div class="sub">Lost &amp; Found</div>

            <div class="title">Certificate of Appreciation</div>
            <hr class="title-rule">

            <div class="presented">This certificate is proudly presented to</div>
            <div class="name">{{ $user->name }}</div>
            <hr class="name-rule">

            <div class="body">
                In recognition of an outstanding contribution to the Makerere University
                community through the Lost &amp; Found programme, having earned
                <span class="points">{{ $redemption->points_used }} reward points</span>
                by helping reunite lost belongings with their owners.
            </div>

            <table class="footer-table">
                <tr>
                    <td style="text-align: left;">
                        <div class="sig-line">Issued: {{ $issuedOn }}</div>
                    </td>
                    <td style="text-align: right;">
                        <div class="sig-line" style="margin-left: auto;">Lost &amp; Found Administration</div>
                    </td>
                </tr>
            </table>

            <div class="ref">CERTIFICATE REF: {{ $reference }}</div>
        </div>
    </div>
</body>
</html>
