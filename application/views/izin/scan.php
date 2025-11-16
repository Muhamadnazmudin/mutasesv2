<!DOCTYPE html>
<html>
<head>
<title>Scan QR Siswa</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://unpkg.com/html5-qrcode"></script>
</head>

<body class="p-4">

<h3>Scan Kartu Siswa</h3>
<p>Silakan arahkan QR Code kartu siswa ke kamera.</p>

<div id="reader" style="width:100%; max-width:400px;"></div>

<script>
const BASE_URL = "<?= base_url() ?>";

function onScanSuccess(decodedText) {

    // Pecah hasil scan berdasarkan "/"
    let parts = decodedText.split('/');
    let token = parts[parts.length - 1]; // ambil elemen terakhir, contoh: kembali_xxxx atau qr_xxxx

    // === Jika token adalah token kembali ===
    if (token.startsWith("kembali_")) {
        window.location.href = BASE_URL + "index.php/izin/kembali/" + token;
        return;
    }

    // === Jika token adalah token kartu siswa ===
    window.location.href = BASE_URL + "index.php/izin/scan/" + token;
}

var html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 }
);
html5QrcodeScanner.render(onScanSuccess);
</script>


</body>
</html>
