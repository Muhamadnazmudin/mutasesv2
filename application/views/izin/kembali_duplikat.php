<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Scan Tidak Valid</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
    body {
        background: #f8d7da;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .notif-box {
        background: #fff;
        padding: 25px 35px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        border-left: 8px solid #dc3545;
        max-width: 450px;
        text-align: center;
        animation: fadeIn .4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(.95); }
        to { opacity: 1; transform: scale(1); }
    }

    h3 {
        color: #dc3545;
        font-weight: bold;
        margin-bottom: 12px;
    }

    #timer {
        font-weight: bold;
        color: #b30000;
    }
</style>

</head>
<body>

<div class="notif-box">
    <h3>Scan Tidak Valid!</h3>
    <p>Siswa atas nama <b><?= $izin->nama ?></b> sudah tercatat kembali.</p>

    <p class="mt-2">Menutup otomatis dalam <span id="timer">5</span> detik...</p>

    <button onclick="goBack()" class="btn btn-danger mt-3 w-100">OK</button>
</div>

<script>
// Redirect otomatis 5 detik
let s = 5;
let timer = setInterval(() => {
    s--;
    document.getElementById('timer').innerText = s;
    if (s <= 0) {
        clearInterval(timer);
        goBack();
    }
}, 1000);

function goBack() {
    window.location.href = "<?= base_url('') ?>";
}
</script>

</body>
</html>
