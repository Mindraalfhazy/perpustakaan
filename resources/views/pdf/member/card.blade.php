<html>
    <head>
        <style>
            @page {
                size: 54mm 86mm landscape;
                margin: 0;
            }
            body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; }
            .card-shell {
                position: relative;
                width: 86mm;
                height: 54mm;
                overflow: hidden;
                color: #ffffff;
                background:
                    radial-gradient(circle at top right, rgba(255,255,255,0.28), transparent 28%),
                    linear-gradient(135deg, #312E81 0%, #4F46E5 52%, #06B6D4 100%);
            }
            .card-shell:before {
                content: "";
                position: absolute;
                inset: auto -18mm -22mm auto;
                width: 44mm;
                height: 44mm;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.11);
            }
            .card-inner { position: relative; padding: 4.5mm; }
            .brand-table,
            .member-table { width: 100%; border-collapse: collapse; }
            .brand-table td,
            .member-table td { vertical-align: top; }
            .logo {
                width: 11mm;
                height: 11mm;
                object-fit: contain;
                background: rgba(255, 255, 255, 0.12);
                border-radius: 50%;
                padding: 1.5mm;
            }
            .school-name {
                font-size: 10pt;
                font-weight: bold;
                text-transform: uppercase;
                line-height: 1.2;
            }
            .library-name { font-size: 7pt; opacity: 0.95; }
            .card-title {
                display: inline-block;
                margin-top: 2.5mm;
                padding: 1.1mm 2.4mm;
                border-radius: 999px;
                background: rgba(255,255,255,0.16);
                font-size: 6.7pt;
                font-weight: bold;
                letter-spacing: 0.08em;
            }
            .member-name {
                margin-top: 3mm;
                font-size: 11pt;
                font-weight: bold;
                text-transform: uppercase;
                line-height: 1.2;
            }
            .member-meta {
                margin-top: 0.8mm;
                font-size: 6.8pt;
                opacity: 0.95;
            }
            .barcode-wrap {
                position: absolute;
                left: 4.5mm;
                right: 4.5mm;
                bottom: 5mm;
                padding: 2.2mm 2.6mm;
                border-radius: 3mm;
                background: rgba(255,255,255,0.16);
            }
            .barcode {
                width: 100%;
                height: 10mm;
                object-fit: contain;
                display: block;
                background: #ffffff;
                border-radius: 2mm;
                padding: 1mm 1.6mm;
                box-sizing: border-box;
            }
            .footer-note {
                margin-top: 1.6mm;
                font-size: 6.6pt;
                text-align: center;
            }
        </style>
    </head>
    <body>
        @php
            $school = config('perpustakaan.school');
            $logoPath = public_path($school['logo']);
            $logoSource = file_exists($logoPath) ? $logoPath : null;
        @endphp
        <div class="card-shell">
            <div class="card-inner">
                <table class="brand-table">
                    <tr>
                        <td style="width: 13mm;">
                            @if ($logoSource)
                                <img src="{{ $logoSource }}" alt="Logo {{ $school['short_name'] }}" class="logo">
                            @endif
                        </td>
                        <td>
                            <div class="school-name">{{ $school['name'] }}</div>
                            <div class="library-name">{{ $school['library_name'] }}</div>
                        </td>
                    </tr>
                </table>

                <div class="card-title">KARTU ANGGOTA</div>
                <div class="member-name">{{ $cardMemberName }}</div>
                <div class="member-meta">No. Anggota: {{ $cardMemberNo }}</div>
                <div class="member-meta">{{ $cardMemberEmail }}</div>
                <div class="member-meta">Berlaku sampai: {{ $cardMemberExpired }}</div>

                <div class="barcode-wrap">
                    <img src="{{ $barcodeImage }}" alt="Barcode {{ $cardMemberNo }}" class="barcode">
                    <div class="footer-note">{{ $school['short_name'] }} | NPSN {{ $school['npsn'] }}</div>
                </div>
            </div>
        </div>
    </body>
</html>
