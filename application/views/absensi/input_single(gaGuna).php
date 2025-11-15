<div class="container-fluid">
  <h3>Input Data Absen</h3>

  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('index.php/Absensi/Absensi/save_single') ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
           value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="form-group">
      <label>Tanggal Absen</label>
      <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="form-group">
      <label>Kelas</label>
      <select name="id_kelas" class="form-control" required>
        <option value="">-- Pilih Kelas --</option>
        <?php foreach ($kelas as $k): ?>
          <option value="<?= $k->id ?>"><?= $k->nama ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Nama Siswa</label>
      <input list="listSiswa" name="id_siswa" class="form-control" placeholder="Ketik nama atau NIS" required>
      <datalist id="listSiswa">
        <?php foreach ($siswa_all as $s): ?>
          <!-- value di sini saya gabungkan id dan nama karena datalist hanya menyediakan value -->
          <option data-id="<?= $s->id_siswa ?>" value="<?= $s->id_siswa ?> - <?= $s->nama_siswa ?>"></option>
        <?php endforeach; ?>
      </datalist>
      <small class="form-text text-muted">Pilih dari daftar (format: id - nama). Jika mau, nanti kita ganti jadi ajax autocomplete.</small>
    </div>

    <div class="form-group">
      <label>Keterangan</label>
      <select name="status" id="statusSelect" class="form-control" required>
        <option value="">-- Pilih --</option>
        <option value="sakit">Sakit</option>
        <option value="izin">Izin</option>
        <option value="alpa">Alpa</option>
      </select>
    </div>

    <div class="form-group">
      <label>Alasan</label>
      <textarea name="keterangan" id="keterangan" class="form-control" placeholder="Alasan/ket"></textarea>
    </div>

    <div class="form-group">
      <label>Tahun Pelajaran</label>
      <input type="text" name="tahun_pelajaran" class="form-control" value="<?= date('Y') . '/' . (date('Y')+1) ?>">
    </div>

    <button class="btn btn-primary">Simpan</button>
  </form>
</div>

<script>
  // autofill alasan berdasarkan keterangan
  (function(){
    var sel = document.getElementById('statusSelect');
    var ket = document.getElementById('keterangan');

    sel.addEventListener('change', function(){
      var v = this.value;
      if (v === 'sakit') {
        ket.value = 'Sakit (silakan isi keterangan singkat)';
      } else if (v === 'izin') {
        ket.value = 'Izin (oleh orang tua/wali)';
      } else if (v === 'alpa') {
        ket.value = 'Alpa (tanpa keterangan)';
      } else {
        ket.value = '';
      }
    });
  })();
</script>
