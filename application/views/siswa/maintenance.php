<!doctype html>

<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maintenance Sistem - SimSGTK</title>

  <!-- Bootstrap -->

  <link href="<?= base_url('assets/sbadmin2/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="<?= base_url('assets/sbadmin2/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">

  <style>
    body {
      background: radial-gradient(circle at top left, #1f1f2e, #111121);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
      color: #eee;
      padding: 10px;
    }

    .maintenance-card {
      background: #1e1e2f;
      border: none;
      border-radius: 1rem;
      box-shadow: 0 0 20px rgba(0,0,0,0.6);
      overflow: hidden;
      animation: fadeIn 0.8s ease-out;
    }

    .card-header {
      background: linear-gradient(90deg, #b76c4b, #482818);
      text-align: center;
      padding: 1.5rem;
    }

    .card-header h4 {
      margin: 0;
      font-weight: 600;
      color: #fff;
    }

    .card-body {
      text-align: center;
      padding: 2rem 1.5rem;
    }

    .maintenance-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
      color: #f1c40f;
      animation: spin 4s linear infinite;
    }

    .maintenance-text {
      color: #ccc;
      font-size: 1rem;
      line-height: 1.6;
    }

    .info-text {
      margin-top: 1rem;
      font-size: 0.9rem;
      color: #aaa;
    }

    .footer-text {
      margin-top: 1.5rem;
      text-align: center;
      color: #aaa;
      font-size: 0.9rem;
    }

    .footer-text a {
      color: #f1c40f;
      text-decoration: none;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }
  </style>

</head>

<body>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-6 col-lg-4">

```
    <div class="card maintenance-card">
      <div class="card-header">
        <h4><i class="fas fa-tools me-2"></i>Maintenance Sistem</h4>
      </div>

      <div class="card-body">
        <div class="maintenance-icon">
          <i class="fas fa-cog"></i>
        </div>

        <h5 class="mb-3">Sistem Sedang Dalam Perbaikan</h5>

        <p class="maintenance-text">
          Mohon maaf, saat ini <strong>Sistem Informasi Siswa dan GTK (SimSGTK)</strong><br>
          sedang dilakukan pemeliharaan untuk peningkatan layanan.
        </p>

        <p class="info-text">
          Silakan coba kembali beberapa saat lagi.<br>
          Terima kasih atas pengertiannya.
        </p>
      </div>
    </div>

    <div class="footer-text">
      © <?= date('Y') ?> <a href="#">SimSGTK</a> — Sistem Informasi Siswa dan GTK
    </div>

  </div>
</div>
```

  </div>

</body>
</html>
