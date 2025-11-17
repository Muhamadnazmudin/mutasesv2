<!DOCTYPE html>
<html>
<head>
<title>Scan QR Siswa</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://unpkg.com/html5-qrcode"></script>
</head>

<body class="p-4">

<!-- === KUNCI PERANGKAT PETUGAS === -->
<script>
    document.cookie = "petugas_scan=OK; path=/; SameSite=Lax";
</script>

<h3>Scan Kartu Siswa</h3>
<p>Silakan arahkan QR Code kartu siswa ke kamera.</p>

<div id="reader" style="width:100%; max-width:400px;"></div>

<script>
const BASE_URL = "<?= base_url() ?>";

// *** SCAN PROCESS BARU (AMAN) ***
function onScanSuccess(decodedText) {

    // Ambil token dari hasil scan
    let parts = decodedText.split('/');
    let token = parts[parts.length - 1];

    // Kirim ke server untuk diproses (dengan HEADER khusus)
    fetch(BASE_URL + "index.php/izin/scan_process?token=" + token, {
        headers: {
            "X-Scanner": "MUTASES" 
        }
    })
    .then(res => res.text())
    .then(url => {
        if (url === "403") {
            alert("Akses ditolak! Hanya perangkat petugas yang boleh scan.");
        } else {
            window.location.href = url;
        }
    });
}

var html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 }
);
html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
</html>
