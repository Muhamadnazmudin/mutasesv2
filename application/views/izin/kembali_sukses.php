<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Siswa Sudah Kembali</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
    body {
        background: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .notif-box {
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        text-align: center;
        max-width: 400px;
    }

    .notif-box h3 {
        font-weight: bold;
        margin-bottom: 10px;
        color: #198754;
    }

    .countdown {
        margin-top: 15px;
        font-size: 14px;
        color: #777;
    }
</style>

</head>
<body>

<div class="notif-box">
    <h3>✔ Siswa sudah kembali ke sekolah</h3>
    <p>Data sudah dicatat dalam sistem.</p>

    <button class="btn btn-success w-100" onclick="goBack()">OK</button>

    <div class="countdown">
        Mengalihkan halaman dalam <span id="timer">5</span> detik...
    </div>
</div>

<script>
function goBack() {
    window.location.href = "<?= base_url('') ?>";
}

let timeLeft = 5;
let interval = setInterval(() => {
    timeLeft--;
    document.getElementById('timer').innerText = timeLeft;

    if (timeLeft <= 0) {
        clearInterval(interval);
        goBack();
    }
}, 1000);
</script>

</body>
</html>
