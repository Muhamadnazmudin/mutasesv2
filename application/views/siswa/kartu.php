<style>

/* ======================================
   KARTU SISWA – DESAIN GRADIENT BIRU
   ====================================== */
#kartu-container {
    width: 340px;
    min-height: 220px;
    border-radius: 12px;
    padding: 15px;
    margin: auto;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);

    /* GRADIENT BIRU */
    background: linear-gradient(135deg, #0D6EFD, #5CAEFF);
    color: #fff;
    font-family: "Segoe UI", sans-serif;
}

#kartu-header {
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    letter-spacing: 1px;
}

#kartu-sub {
    text-align: center;
    font-size: 12px;
    margin-top: -4px;
    opacity: 0.9;
}

#foto-siswa {
    width: 90px;
    height: 110px;
    border-radius: 6px;
    border: 2px solid rgba(255,255,255,0.8);
    object-fit: cover;
    background: #fff;
}

#info-siswa td {
    padding: 2px 5px;
    font-size: 13px;
}

#qr-area img {
    width: 115px;
    background: #fff;
    padding: 6px;
    border-radius: 6px;
}

#qr-area {
    text-align: center;
    margin-top: 10px;
}


/* ======================================
   CETAK HANYA KARTU
   ====================================== */
@media print {

    @page {
        size: auto;
        margin: 0;
    }

    body * {
        visibility: hidden !important;
    }

    #print-area, #print-area * {
        visibility: visible !important;
    }

    #print-area {
        position: absolute;
        inset: 0;
        margin: auto;
        width: 340px;
    }
}

</style>


<div class="text-center mb-3">
    <button onclick="window.print()" class="btn btn-primary btn-sm">
        <i class="fas fa-print"></i> Cetak Kartu
    </button>
</div>


<!-- AREA CETAK -->
<div id="print-area">
    <div id="kartu-container">

        <div id="kartu-header">KARTU SISWA</div>
        <div id="kartu-sub"><?= $siswa->nama_kelas ?></div>

        <table width="100%">
            <tr>
                <td width="40%" class="text-center">

                    <?php if (!empty($siswa->foto) && file_exists(FCPATH . 'uploads/siswa/' . $siswa->foto)): ?>
                        <img id="foto-siswa" src="<?= base_url('uploads/siswa/' . $siswa->foto) ?>">
                    <?php else: ?>
                        <img id="foto-siswa"
                             src="https://ui-avatars.com/api/?name=<?= urlencode($siswa->nama) ?>&size=200&background=0D6EFD&color=fff">
                    <?php endif; ?>

                </td>

                <td width="60%">
                    <table id="info-siswa">
                        <tr><td><b>Nama</b></td><td>:</td><td><?= $siswa->nama ?></td></tr>
                        <tr><td><b>NISN</b></td><td>:</td><td><?= $siswa->nisn ?></td></tr>
                        <tr><td><b>NIS</b></td><td>:</td><td><?= $siswa->nis ?></td></tr>
                        <tr><td><b>Kelas</b></td><td>:</td><td><?= $siswa->nama_kelas ?></td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <div id="qr-area">
            <img src="<?= $qr_file ?>">
            <div style="font-size: 10px;">Scan untuk Izin Keluar</div>
        </div>

    </div>
</div>
