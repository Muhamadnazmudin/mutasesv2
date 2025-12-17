<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Siswa - SimSGTK</title>
  
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

    .login-card {
      background: #1e1e2f;
      border: none;
      border-radius: 1rem;
      box-shadow: 0 0 20px rgba(0,0,0,0.6);
      overflow: hidden;
      animation: fadeIn 0.8s ease-out;
    }

    .card-header {
      background: linear-gradient(90deg, #4b6cb7, #182848);
      text-align: center;
      padding: 1.5rem;
    }

    .card-header h4 {
      margin: 0;
      font-weight: 600;
      color: #fff;
    }

    .form-label {
      font-weight: 500;
      color: #ccc;
    }

    .form-control {
      background-color: #2b2b3c;
      border: 1px solid #444;
      color: #eee;
      border-radius: 8px;
      height: 50px;
      font-size: 16px;
    }

    .form-control:focus {
      background-color: #34344a;
      border-color: #4b6cb7;
      box-shadow: 0 0 0 0.2rem rgba(75,108,183,0.25);
      color: #fff;
    }

    .btn-primary {
      background: linear-gradient(90deg, #4b6cb7, #182848);
      border: none;
      border-radius: 8px;
      padding: 0.7rem;
      font-weight: 500;
      transition: all 0.2s;
    }

    .btn-primary:hover {
      background: linear-gradient(90deg, #5d7de2, #243b55);
      transform: scale(1.02);
    }

    .footer-text {
      margin-top: 1.5rem;
      text-align: center;
      color: #aaa;
      font-size: 0.9rem;
    }

    .footer-text a {
      color: #4b6cb7;
      text-decoration: none;
    }

    /* 👁️ Icon toggle password */
    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      top: 60%;
      right: 15px;
      cursor: pointer;
      color: #aaa;
    }

    .toggle-password:hover {
      color: #fff;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-6 col-lg-4">

        <div class="card login-card">
          <div class="card-header">
            <h4><i class="fas fa-user-graduate me-2"></i>Login Siswa</h4>
          </div>

          <div class="card-body p-4">

            <?php if ($this->session->flashdata('error')): ?>
              <div class="alert alert-danger text-center">
                <?= $this->session->flashdata('error') ?>
              </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('SiswaAuth/cek_login') ?>">
              <input type="hidden" 
                     name="<?= $this->security->get_csrf_token_name(); ?>" 
                     value="<?= $this->security->get_csrf_hash(); ?>">

              <div class="mb-3">
                <label class="form-label">NISN</label>
                <input type="text" name="nisn" class="form-control" placeholder="Masukkan NISN" required>
              </div>

              <div class="mb-3 password-wrapper">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="Masukkan password" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
              </div>

              <button type="submit" class="btn btn-primary w-100 mt-2">
                <i class="fas fa-sign-in-alt me-1"></i> Login
              </button>
            </form>

          </div>
        </div>

        <div class="footer-text">
          © <?= date('Y') ?> <a href="#">SimSGTK</a> — Sistem Informasi Siswa dan GTK
        </div>

      </div>
    </div>
  </div>

  <script src="<?= base_url('assets/sbadmin2/vendor/jquery/jquery.min.js') ?>"></script>
  <script src="<?= base_url('assets/sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

  <script>
    // 👁️ toggle show/hide password
    document.getElementById('togglePassword').addEventListener('click', function () {
      const input = document.getElementById('password');
      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';

      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });
  </script>

</body>
</html>
