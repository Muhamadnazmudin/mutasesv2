<!DOCTYPE html>
<html>
<head>
<title>Ajukan Izin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">

<h3>Ajukan Izin</h3>

<form method="POST" action="<?= base_url('index.php/izin/ajukan/' . $token_qr) ?>">

    <div class="mb-3">
        <label>Nama</label>
        <input class="form-control" value="<?= $siswa->nama ?>" disabled>
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

    <!-- Estimasi (muncul hanya jika pilih izin keluar) -->
    <div class="mb-3" id="estimasiBox" style="display:none;">
        <label>Estimasi Waktu (Menit)</label>
        <input type="number" name="estimasi" class="form-control">
    </div>

    <button class="btn btn-primary">Simpan Izin</button>

</form>

<script>
// Tampilkan / sembunyikan estimasi berdasarkan pilihan
document.getElementById('jenisIzin').addEventListener('change', function() {
    if (this.value === 'keluar') {
        document.getElementById('estimasiBox').style.display = 'block';
    } else {
        document.getElementById('estimasiBox').style.display = 'none';
    }
});
</script>

</body>
</html>
