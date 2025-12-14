<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Data E-Ijazah</title>

    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 520px;
            margin: 60px auto;
            background: #fff;
            padding: 30px 35px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
            text-align: center;
            font-weight: 600;
            color: #333;
            font-size: 22px;
        }

        .error {
            background: #ffe1e1;
            border-left: 4px solid #d90000;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #b30000;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        input[type="file"] {
            padding: 12px;
            border: 1px solid #ccc;
            width: 100%;
            border-radius: 6px;
            background: #fafafa;
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #0069d9;
        }

        .info {
            margin-top: 25px;
            font-size: 13px;
            color: #666;
            text-align: center;
            line-height: 1.5;
        }

        hr {
            margin: 25px 0;
            border: none;
            border-bottom: 1px solid #eee;
        }
        .back-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: #fff;
    padding: 10px 18px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 999;
}

.back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.25);
    background: linear-gradient(135deg, #5a6268, #343a40);
}

.back-btn i {
    font-size: 14px;
}
.back-btn-inline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: #fff;
    padding: 10px 22px;
    border-radius: 25px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: 0.25s;
}

.back-btn-inline:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.25);
    background: linear-gradient(135deg, #5a6268, #343a40);
}

.back-wrapper {
    text-align: center;
}
.table-wrapper {
    margin-top: 10px;
    border-radius: 8px;
    overflow-x: auto;
}

table.school-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    min-width: 420px;
}

.school-table th,
.school-table td {
    padding: 8px 6px;
    border: 1px solid #e1e1e1;
    text-align: left;
    vertical-align: middle;
}

.school-table th {
    background: #f5f7fa;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    color: #555;
}

.school-table td:nth-child(1) {
    text-align: center;
    width: 40px;
}

.school-table td:nth-child(2) {
    width: 70px;
    text-align: center;
}

.school-table td:nth-child(4) {
    font-family: monospace;
    font-size: 12px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 14px;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 13px;
    text-decoration: none;
    background: #f1f3f5;
    color: #333;
}

.pagination span.active {
    background: #007bff;
    color: #fff;
    font-weight: 600;
}
.school-section {
    width: 60%;
    margin: 0 auto 40px auto; /* tengah */
}

@media (max-width: 768px) {
    .school-section {
        width: 95%; /* HP full biar kebaca */
    }
}
.refresh-wrapper {
    display: flex;
    justify-content: flex-end; /* ⬅️ ke kanan */
    margin-bottom: 10px;
}


.refresh-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #121313ff;
    border: 1px solid #dee2e6;
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 13px;
    cursor: pointer;
    transition: all .2s ease;
    width: 14%;
    
}

.refresh-btn:hover {
    background: #0c75ddff;
}


    </style>
</head>

<body>

<div class="container">

    <h2>Scrap Data Ijazah (PDF → Excel)</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= nl2br(htmlspecialchars($error)) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('scrapijazah/process') ?>" enctype="multipart/form-data">

        <input type="hidden"
               name="<?= $this->security->get_csrf_token_name(); ?>"
               value="<?= $this->security->get_csrf_hash(); ?>">

        <div class="input-group">
            <label style="font-size:14px;color:#555;margin-bottom:5px;display:block;">
                Pilih File Ijazah (PDF)
            </label>
            <input type="file" name="pdf_file" accept="application/pdf" required>
        </div>

        <button type="submit">Proses & Download Excel</button>

    </form>

   <hr>

<div class="info">
    Tips: Kalo gabisa, yaudah tinggalin aja., mending ngopii.,..
</div>

<div class="back-wrapper">
    <a href="<?= base_url('index.php/dashboard') ?>" class="back-btn-inline">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>
  
</div>
<hr>

<div class="school-section">

    <h3 style="font-size:15px;margin-bottom:10px;color:#333;text-align:center;">
        Sekolah yang menggunakan fitur ini
    </h3>
    <div class="refresh-wrapper">
    <button type="button" class="refresh-btn" onclick="window.location.reload()">
        🔄 Refresh Data
    </button>
</div>


    <?php if (!empty($schools)): ?>
    <div class="table-wrapper">
        <table class="school-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenjang</th>
                    <th>Nama Sekolah</th>
                    <th>Jumlah Siswa</th>
                    <!-- <th>NPSN</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schools as $i => $s): ?>
                <tr>
                    <td><?= $start_no + $i ?></td>
                    <td><?= htmlspecialchars($s->jenjang) ?></td>
                    <td><?= htmlspecialchars($s->nama_sekolah) ?></td>
                    <td style="text-align:left;">
    <?= number_format($s->jumlah_siswa, 0, ',', '.') ?>
</td>
                    <!-- <td><?= htmlspecialchars($s->npsn) ?></td> -->
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= $pagination ?>

    <?php else: ?>
        <div style="font-size:13px;color:#777;text-align:center;">
            Belum ada sekolah yang menggunakan fitur ini.
        </div>
    <?php endif; ?>

</div>

<?php if ($this->session->flashdata('auto_refresh')): ?>
<script>
    // tunggu sebentar biar download mulai
    setTimeout(function () {
        window.location.reload();
    }, 1200);
</script>
<?php endif; ?>

</body>
</html>
