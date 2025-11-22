<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Semua Biodata Siswa</title>

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 13px;
        margin: 20px;
    }
    h3 {
        text-align: center;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    .page-break {
        page-break-after: always;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    td {
        padding: 6px 8px;
        vertical-align: top;
    }
    .label {
        width: 180px;
        font-weight: bold;
    }
    .bordered td {
        border: 1px solid #000;
    }
</style>
</head>

<body>

<?php foreach ($siswa as $s): ?>

<h3>FORMULIR BIODATA SISWA</h3>
<p style="text-align:center; margin-top: -10px;">
    Kelas: <strong><?= $s->nama_kelas ?></strong>
</p>

<table class="bordered">
    <tr><td class="label">NIS</td><td><?= $s->nis ?></td></tr>
    <tr><td class="label">NISN</td><td><?= $s->nisn ?></td></tr>
    <tr><td class="label">Nama Lengkap</td><td><?= $s->nama ?></td></tr>
    <tr><td class="label">Jenis Kelamin</td><td><?= ($s->jk=='L'?'Laki-laki':'Perempuan') ?></td></tr>
    <tr><td class="label">Tempat Lahir</td><td><?= $s->tempat_lahir ?></td></tr>
    <tr><td class="label">Tanggal Lahir</td><td><?= $s->tgl_lahir ?></td></tr>
    <tr><td class="label">Agama</td><td><?= $s->agama ?></td></tr>
    <tr><td class="label">Alamat</td><td><?= $s->alamat ?></td></tr>
    <tr><td class="label">Kelas</td><td><?= $s->nama_kelas ?></td></tr>
    <tr><td class="label">Tahun Ajaran</td><td><?= $s->tahun_ajaran ?></td></tr>
    <tr><td class="label">Status</td><td><?= ucfirst($s->status) ?></td></tr>
</table>

<br><br>

<table style="margin-top:40px;">
<tr>
    <td style="text-align:center;">
        Mengetahui,<br>
        Wali Kelas<br><br><br><br>
        ___________________________
    </td>

    <td style="text-align:center;">
        Kuningan, <?= date('d-m-Y') ?><br>
        Orang Tua / Wali<br><br><br><br>
        ___________________________
    </td>
</tr>
</table>

<div class="page-break"></div>

<?php endforeach; ?>

<script>
    window.print();
</script>

</body>
</html>
