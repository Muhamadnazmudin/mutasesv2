<?php
// SETUP
$border = "1px solid #000";
$headBg = "#e9ecef";
?>

<style>
    table { border-collapse: collapse; font-size:9px; }
    th, td { border:1px solid #000; padding:2px; text-align:center; }
</style>

<div style="text-align:center; margin-bottom:10px;">
    <?php if (file_exists(FCPATH.'assets/img/logobonti.png')): ?>
        <img src="<?= FCPATH.'assets/img/logobonti.png' ?>" width="65" />
    <?php endif; ?>
    
    <div style="font-size:15px; font-weight:bold; margin-top:4px;">SMKN 1 CILIMUS</div>
    <div style="font-size:12px; margin-top:2px;">Laporan Absensi Siswa</div>

    <div style="font-size:11px; margin-top:4px;">
        Bulan: <b><?= $bulan_label ?></b>, 
        Tahun Ajaran: <b><?= $tahun ?></b>
    </div>

    <div style="font-size:11px; margin-top:2px;">
        Kelas: <b><?= $nama_kelas ?></b>
    </div>
</div>


<table width="100%">
    <tr>
        <th style="background:<?= $headBg ?>; width:150px;">Nama Siswa</th>

        <?php foreach($tanggal as $tgl): ?>
            <?php
                $hari = date('N', strtotime($tgl)); 
                $is_libur = in_array($tgl, $tanggalMerah);
                $bg = "";

                if ($hari == 6 || $hari == 7 || $is_libur) {
                    $bg = "background-color:#ffb3b3;";   // MERAH LEMBUT
                }
            ?>
            <th style="<?= $bg ?> width:18px;">
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
            <td style="text-align:left; padding-left:4px;">
                <?= strtoupper($s->nama) ?>
            </td>

            <?php
                $countH = $countS = $countI = $countA = 0;
            ?>

            <?php foreach ($tanggal as $tgl): ?>

                <?php
                    // Tentukan kode final
                    if (isset($absen[$s->id]) && isset($absen[$s->id][$tgl])) {
                        $kode = $absen[$s->id][$tgl];
                    } else {
                        // weekend atau tanggal merah = Libur
                        $hari = date('N', strtotime($tgl));
                        $is_libur = in_array($tgl, $tanggalMerah);

                        if ($hari == 6 || $hari == 7 || $is_libur) {
                            $kode = 'L';
                        } else {
                            $kode = 'H';
                        }
                    }

                    // Hitung summary
                    if ($kode == 'H') $countH++;
                    if ($kode == 'S') $countS++;
                    if ($kode == 'I') $countI++;
                    if ($kode == 'A') $countA++;

                    // Warna L
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
</table>
