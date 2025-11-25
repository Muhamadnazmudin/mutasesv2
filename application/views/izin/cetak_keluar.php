<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Izin Keluar</title>

<style>
@page {
    size: 80mm auto;
    margin: 0;
}
body {
    width: 80mm;
    margin: 0;
    padding: 6px;
    font-family: Arial, sans-serif;
    font-size: 11px;
}
.border-box {
    border: 2px solid #000;
    padding: 6px;
    
}
.header {
    text-align: center;
}
.header img {
    width: 40px;
    margin-bottom: 3px;
}
.header .title-school {
    font-size: 12px;
    font-weight: bold;
}
.address {
    font-size: 9px;
}

/* JUDUL */
.surat-title {
    text-align: center;
    font-weight: bold;
    margin-top: 5px;
    padding: 3px 0;
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
}

/* TABEL */
table { width: 100%; }
td.label { width: 28mm; vertical-align: top; }

/* QR */
.qr-box { text-align: center; margin-top: 8px; }
.qr-box img { width: 120px; }

/* TTD AREA */
.ttd-wrapper {
    width: 100%;
    margin-top: 15px;
}

.ttd-left, .ttd-right {
    width: 49%;
    display: inline-block;
    font-size: 10.5px;
    vertical-align: top;
}

.ttd-left { text-align: left; }
.ttd-right { text-align: right; }

.yg-dituju {
    text-align: center;
    margin-top: 35px;
    font-size: 11px;
}

.yg-dituju .line {
    margin-top: 25px;
    border-bottom: 1px solid #000;
    width: 70%;
    margin-left: auto;
    margin-right: auto;
}

</style>
</head>

<body onload="window.print()">

<?php 
$qr_url = base_url('index.php/izin/kembali/' . $izin->token_kembali);
$qr_image = 'https://quickchart.io/qr?text=' . urlencode($qr_url)
    . '&size=260&margin=12&ecLevel=H';
?>


<div class="border-box">

    <!-- HEADER -->
<div style="
    background:#0000; 
    border-bottom:2px solid #000; 
    border-top:2px solid #000;
    padding:5px 2px; 
    width:100%;
    display:flex;
    align-items:center;
">

    <!-- LOGO -->
    <div style="width:45px; text-align:center;">
        <img src="<?= base_url('assets/img/logobonti.png') ?>" style="width:45px;">
    </div>

    <!-- TEKS HEADER -->
    <div style="flex:1; text-align:center; font-size:10px; line-height:1.15;">
        <div style="font-weight:bold;">PEMERINTAH DAERAH PROVINSI JAWA BARAT</div>
        <div style="font-weight:bold;">DINAS PENDIDIKAN</div>
        <div style="font-weight:bold;">CABANG DINAS PENDIDIKAN X</div>

        <div style="font-size:12px; font-weight:bold; margin-top:2px;">
            SMK NEGERI 1 CILIMUS
        </div>

        <div style="font-size:9px; margin-top:3px;">
            Jalan Baru Lingkar Caracas Cilimus<br>
            Telp. (0232) 8910145, Email: smkn_1cilimus@yahoo.com<br>
            Kabupaten Kuningan 45556
        </div>
    </div>

</div>


    <!-- JUDUL -->
    <div class="surat-title">SURAT IZIN KELUAR</div>

    <!-- DATA -->
    <table>
        <tr><td class="label">Nama Siswa</td><td>: <?= $izin->nama ?></td></tr>
        <tr><td class="label">Kelas</td><td>: <?= $izin->kelas_nama ?></td></tr>
        <tr><td class="label">Jam Keluar</td><td>: <?= $izin->jam_keluar ?></td></tr>
        <tr><td class="label">Alasan</td><td>: <?= $izin->keperluan ?></td></tr>
        <!-- <tr><td class="label">Jam Kembali</td><td>: <?= $izin->jam_masuk ?: '...........................' ?></td></tr> -->
        <tr><td class="label">Guru Mapel</td><td>: <?= $guru_mapel->nama ?></td></tr>
    </table>

    <!-- QR CODE -->
    <div class="qr-box">
        <img src="<?= $qr_image ?>"><br>
        <div style="font-size:9px; color:#666;">Scan untuk menandai siswa sudah kembali</div>
    </div>

    <!-- TTD BLOK -->
<div class="ttd-wrapper">

    <!-- KIRI: GURU MAPEL -->
    <div class="ttd-left"><br>
        Guru Mata Pelajaran,<br><br><br><br><br>
        <b><?= $guru_mapel->nama ?></b><br>
        NIP. <?= $guru_mapel->nip ?>
    </div>

    <!-- KANAN: PETUGAS PIKET -->
    <div class="ttd-right">

        <!-- TANGGAL DI ATAS PETUGAS PIKET -->
        Kuningan, <?= date('d-m-Y') ?><br>

        <!-- LABEL SEJAJAR DENGAN GURU MAPEL -->
        Petugas Piket,<br><br><br><br><br>

        <b><?= $piket->nama ?></b><br>
        NIP. <?= $piket->nip ?>
    </div>

</div>

    <div class="yg-dituju">
    Yang dituju:<br>
    <b><?= $izin->ditujukan ?></b><br><br>
    <div class="line"></div>
</div>


</div>

</body>
</html>
