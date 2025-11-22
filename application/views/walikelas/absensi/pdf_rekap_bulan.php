<?php
$headBg = "#e9ecef";
?>

<style>
table { 
    border-collapse: collapse; 
    font-size:9px; 
    table-layout: fixed; 
    width: 100%;
}
th, td { 
    border:1px solid #000; 
    padding:2px; 
    text-align:center; 
    word-wrap: break-word;
}
.nama { text-align:left; padding-left:2px; }
</style>

<div style="text-align:center; margin-top:5px; margin-bottom:0px;">

    <!-- Logo lebih kecil -->
    <img src="<?= FCPATH.'assets/img/logobonti.png' ?>" width="35" style="margin-bottom:2px;">

    <!-- Judul Sekolah -->
    <div style="font-size:11px; font-weight:bold; margin:0; padding:0;">
        SMKN 1 CILIMUS
    </div>

    <!-- INFO LAPORAN (3 Kolom Sejajar) -->
<table width="100%" style="font-size:10px; margin-top:2px; margin-bottom:5px; border:none;">
    <tr style="border:none;">
        <!-- Kelas (Kiri) -->
        <td style="text-align:left; border:none; width:33%;">
            Kelas: <b><?= $kelas_nama ?></b>
        </td>

        <!-- Judul (Tengah) -->
        <td style="text-align:center; border:none; font-weight:bold; width:34%;">
            LAPORAN ABSENSI QR SISWA
        </td>

        <!-- Bulan & Tahun (Kanan) -->
        <td style="text-align:right; border:none; width:33%;">
            Bulan: <b><?= $bulan_label ?></b>
        </td>
    </tr>
</table>



<table width="100%">
    <tr>
       <th style="background:<?= $headBg ?>; width:180px;">Nama Siswa</th>


        <?php foreach($tanggal as $tgl): ?>
            <?php
                $hari = date('N', strtotime($tgl)); 
                $is_libur = in_array($tgl, $tanggalMerah);

                $bg = "";
                if ($hari == 6 || $hari == 7 || $is_libur) {
                    $bg = "background-color:#ffb3b3;";
                }
            ?>
            <th style="<?= $bg ?> width:22px;">
                <?= date('d', strtotime($tgl)) ?>
            </th>
        <?php endforeach; ?>

        <th width="18">H</th>
        <th width="18">S</th>
        <th width="18">I</th>
        <th width="18">A</th>
    </tr>
    

    <?php foreach($siswa as $s): ?>
        <tr>
            <td class="nama"><?= strtoupper($s->nama) ?></td>

            <?php
                $countH = $countS = $countI = $countA = 0;
            ?>

            <?php foreach ($tanggal as $tgl): ?>

                <?php
                    // ===============================
                    // Ambil KODE kehadiran
                    // ===============================
                    if (isset($rekap[$s->nis][$tgl])) {
                        $kode = strtoupper($rekap[$s->nis][$tgl]);
                    } else {
                        // Weekend / tanggal merah
                        $hari = date('N', strtotime($tgl));
                        $is_libur = in_array($tgl, $tanggalMerah);

                        if ($hari == 6 || $hari == 7 || $is_libur) {
                            $kode = 'L';
                        } else {
                            $kode = '-';
                        }
                    }

                    // Hitung rekap
                    if ($kode == 'H') $countH++;
                    if ($kode == 'S') $countS++;
                    if ($kode == 'I') $countI++;
                    if ($kode == 'A') $countA++;

                    // Warna libur
                    $hari2 = date('N', strtotime($tgl));
                    $is_libur2 = in_array($tgl, $tanggalMerah);
                    $bg = ($kode == 'L' || $hari2 == 6 || $hari2 == 7 || $is_libur2)
                        ? "background-color:#ffb3b3;" : "";
                ?>

                <td style="<?= $bg ?>"><?= $kode ?></td>

            <?php endforeach; ?>

            <td><?= $countH ?></td>
            <td><?= $countS ?></td>
            <td><?= $countI ?></td>
            <td><?= $countA ?></td>
        </tr>
    <?php endforeach; ?>
    <br>

    <tr>
    <!-- Kolom kiri berisi total rekap kelas -->
    <td width="70%" style="font-size:10px; padding-left:5px; text-align:left; border:none;">

        <?php
            // Hitung total keseluruhan kelas
            $totalH = $totalS = $totalI = $totalA = 0;
            foreach($siswa as $s) {
                $nis = $s->nis;
                foreach($tanggal as $tgl) {
                    if (isset($rekap[$nis][$tgl])) {
                        $kode = strtoupper($rekap[$nis][$tgl]);

                        if ($kode == 'H') $totalH++;
                        if ($kode == 'S') $totalS++;
                        if ($kode == 'I') $totalI++;
                        if ($kode == 'A') $totalA++;
                    }
                }
            }
        ?>

        <b>Rekapitulasi Kehadiran:</b><br>
        Hadir : <?= $totalH ?><br>
        Sakit : <?= $totalS ?><br>
        Izin  : <?= $totalI ?><br>
        Alpha : <?= $totalA ?><br>
    </td>

    <!-- Kolom kanan tetap Wali Kelas -->
    <td width="30%" style="text-align:center; font-size:11px; border:none;">

        <?= $tanggal_ttd ?><br>
        Wali Kelas<br><br><br>

        <b><?= $walikelas->nama ?></b><br>
        NIP. <?= $walikelas->nip ?>

    </td>
</tr>


</table>

