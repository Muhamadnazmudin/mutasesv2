<!DOCTYPE html>
<html>
<head>
<title>Ajukan Izin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">

<h3>Ajukan Izin</h3>

<form method="POST" action="<?= base_url('index.php/izin/ajukan/' . $token_qr) ?>">

    <!-- Nama -->
    <div class="mb-3">
        <label>Nama</label>
        <input class="form-control" value="<?= htmlspecialchars($siswa->nama, ENT_QUOTES, 'UTF-8') ?>" disabled>
    </div>

    <!-- Kelas -->
    <div class="mb-3">
        <label>Kelas</label>
        <input class="form-control"
               value="<?= isset($siswa->kelas_nama) ? htmlspecialchars($siswa->kelas_nama, ENT_QUOTES, 'UTF-8') : '-' ?>"
               disabled>
    </div>

    <!-- Jenis Izin -->
    <div class="mb-3">
        <label>Jenis Izin</label>
        <select name="jenis" id="jenisIzin" class="form-control" required>
            <option value="">-- Pilih Jenis Izin --</option>
            <option value="keluar">Izin Keluar</option>
            <option value="pulang">Izin Pulang</option>
        </select>
    </div>

    <!-- Keperluan -->
    <div class="mb-3">
        <label>Keperluan</label>
        <textarea name="keperluan" class="form-control" required></textarea>
    </div>

    <!-- Estimasi (hanya izin keluar) -->
    <div class="mb-3" id="estimasiBox" style="display:none;">
        <label>Estimasi Waktu (Menit)</label>
        <input type="number" name="estimasi" class="form-control" min="0">
    </div>

    <!-- Guru Mapel -->
    <div class="mb-3" id="guruMapelBox" style="display:none;">
        <label>Guru Mata Pelajaran</label>
        <select name="id_guru_mapel" class="form-control">
            <option value="">-- Pilih Guru Mapel --</option>
            <?php if (!empty($guru_list)): ?>
                <?php foreach ($guru_list as $g): ?>
                    <option value="<?= $g->id ?>">
                        <?= htmlspecialchars($g->nama, ENT_QUOTES, 'UTF-8') ?> 
                        (NIP: <?= htmlspecialchars($g->nip, ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <!-- Petugas Piket -->
    <div class="mb-3" id="piketBox" style="display:none;">
        <label>Petugas Piket</label>
        <select name="id_piket" class="form-control">
            <option value="">-- Pilih Petugas Piket --</option>
            <?php if (!empty($guru_list)): ?>
                <?php foreach ($guru_list as $g): ?>
                    <option value="<?= $g->id ?>">
                        <?= htmlspecialchars($g->nama, ENT_QUOTES, 'UTF-8') ?>
                        (NIP: <?= htmlspecialchars($g->nip, ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <!-- Yang Ditujukan (keluar) -->
    <div class="mb-3" id="yangDitujukanBox" style="display:none;">
        <label>Yang Ditujukan</label>
        <input type="text" name="ditujukan" class="form-control">
    </div>

    <!-- Wali Kelas (pulang) -->
    <div class="mb-3" id="waliKelasBox" style="display:none;">
        <label>Wali Kelas</label>

        <?php if (!empty($walikelas) && is_object($walikelas)): ?>
            <input class="form-control"
                   value="<?= htmlspecialchars($walikelas->nama, ENT_QUOTES, 'UTF-8') ?>
                   (NIP: <?= htmlspecialchars($walikelas->nip, ENT_QUOTES, 'UTF-8') ?>)"
                   disabled>

            <input type="hidden" name="id_walikelas" value="<?= $walikelas->id ?>">

        <?php else: ?>
            <input class="form-control" value="Wali kelas tidak ditemukan" disabled>
            <input type="hidden" name="id_walikelas" value="">
        <?php endif; ?>

    </div>

    <button class="btn btn-primary">Simpan Izin</button>

</form>

<script>
const jenis = document.getElementById('jenisIzin');

jenis.addEventListener('change', function() {

    let v = this.value;

    document.getElementById('estimasiBox').style.display      = (v === 'keluar') ? 'block' : 'none';
    document.getElementById('yangDitujukanBox').style.display = (v === 'keluar') ? 'block' : 'none';

    document.getElementById('waliKelasBox').style.display     = (v === 'pulang') ? 'block' : 'none';

    const showBase = (v === 'keluar' || v === 'pulang');
    document.getElementById('guruMapelBox').style.display     = showBase ? 'block' : 'none';
    document.getElementById('piketBox').style.display         = showBase ? 'block' : 'none';

});
</script>

</body>
</html>
