<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Anggota</title>
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif; /* Font standar yang paling aman di DOMPDF */
        }
        
        .card {
            width: 340px;
            height: 214px;
            background-color: #3b0764; /* Ungu gelap pekat (Solid) */
            border-radius: 12px;
            padding: 12px 16px; /* Padding ke dalam supaya isi tidak menyentuh ujung border */
            box-sizing: border-box;
            border: 2px solid #5b21b6;
            color: #ffffff;
            position: relative;
        }

        /* --- HEADER --- */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #f59e0b; /* Garis emas */
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            padding: 2px;
        }
        .header-subtitle {
            font-size: 8px;
            color: #d8b4fe;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #ffffff;
            margin: 0;
        }

        /* --- CONTENT (TEKS & FOTO) --- */
        .content-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .content-table td {
            vertical-align: top;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding-bottom: 6px;
            font-size: 11px;
        }
        .label-col {
            width: 22%;
            color: #fcd34d; /* Warna Kuning Emas biar kontras dan jelas */
            font-weight: bold;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        .colon-col {
            width: 5%;
            color: #fcd34d;
            font-weight: bold;
        }
        .value-col {
            width: 73%;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
        }
        .value-normal {
            font-weight: normal;
            text-transform: none;
        }

        /* --- FOTO PROFIL --- */
        .photo-frame {
            width: 52px;
            height: 68px; /* Dibuat proporsional pasfoto (Agak memanjang) */
            border: 2px solid #ffffff;
            border-radius: 6px;
            background-color: #ffffff;
            overflow: hidden;
            display: inline-block;
        }
        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* --- BARCODE --- */
        .barcode-box {
            background-color: #ffffff;
            border-radius: 6px;
            padding: 5px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            /* Karena box ini di dalam .card yang punya padding, dia nggak akan menabrak border luar */
        }
        .barcode-box img {
            height: 28px; /* Ukuran barcode dipaskan */
            max-width: 100%;
        }
        .barcode-text {
            font-size: 10px;
            font-weight: bold;
            color: #000000;
            margin-top: 2px;
            letter-spacing: 1.5px;
        }

        /* --- FOOTER --- */
        .footer-note {
            width: 340px;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="45">
                    @if (!empty($cardLogoImage))
                        <img src="{{ $cardLogoImage }}" class="logo-img" alt="Logo">
                    @endif
                </td>
                <td>
                    <div class="header-subtitle">Perpustakaan Digital</div>
                    <div class="header-title">MTsN 3 Bone</div>
                </td>
            </tr>
        </table>

        <!-- Content (Info & Foto) -->
        <table class="content-table">
            <tr>
                <!-- Kiri: Info Text -->
                <td width="75%">
                    <table class="info-table">
                        <tr>
                            <td class="label-col">NAMA</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">{{ $cardMemberName }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">NISN</td>
                            <td class="colon-col">:</td>
                            <td class="value-col value-normal">{{ $cardMemberNisn }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">BERLAKU</td>
                            <td class="colon-col">:</td>
                            <td class="value-col value-normal">{{ $cardMemberExpired }}</td>
                        </tr>
                    </table>
                </td>
                
                <!-- Kanan: Foto Profil -->
                <td width="25%" align="right">
                    <div class="photo-frame">
                        @if (!empty($cardMemberPhoto))
                            <img src="{{ $cardMemberPhoto }}" alt="Foto">
                        @else
                            <!-- Fallback inisial kalau ga ada foto -->
                            <div style="width:100%; height:100%; background:#6d28d9; display:table;">
                                <span style="display:table-cell; vertical-align:middle; text-align:center; color:#fff; font-weight:bold; font-size:22px;">
                                    {{ mb_substr($cardMemberName, 0, 1) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Barcode di Dalam Kartu -->
        <div class="barcode-box">
            <img src="{{ $barcodeImage }}" alt="Barcode">
            <div class="barcode-text">{{ $cardMemberNo }}</div>
        </div>

    </div>

    <div class="footer-note">Kartu ini adalah bukti keanggotaan resmi Perpustakaan MTsN 3 Bone.</div>
</body>
</html>