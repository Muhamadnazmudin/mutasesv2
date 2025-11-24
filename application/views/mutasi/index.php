<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Data Mutasi Siswa</h4>
  <div>
    <a href="<?= site_url('mutasi/export_excel') ?>" class="btn btn-success btn-sm">
      <i class="fas fa-file-excel"></i> Export
    </a>
    <button class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#importModal">
      <i class="fas fa-upload"></i> Import
    </button>
    <a href="<?= site_url('mutasi/download_template') ?>" class="btn btn-info btn-sm">
      <i class="fas fa-download"></i> Template
    </a>
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
      <i class="fas fa-plus"></i> Tambah
    </button>
  </div>
</div>


<table class="table table-bordered table-striped table-responsive-sm">
    <?php if ($this->session->flashdata('error')): ?>
  <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
<?php elseif ($this->session->flashdata('success')): ?>
  <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

  <thead class="thead-light">
    <tr>
      <th>No</th>
      <th>NIS</th>
      <th>Nama Siswa</th>
      <th>Jenis</th>
      <th>Jenis Keluar</th>
      <th>Alasan</th>
      <th>Kontak Ortu</th>
      <th>Tujuan Sekolah / Kelas</th>
      <th>Tanggal</th>
      <th>Tahun Ajaran</th>
      <th>Berkas</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php $no=1; foreach($mutasi as $m): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= $m->nis ?></td>
      <td><?= $m->nama_siswa ?></td>
      <td><?= ucfirst($m->jenis) ?></td>
      <td><?= ($m->jenis == 'keluar') ? ($m->jenis_keluar ?: '-') : '-' ?></td>
      <td><?= $m->alasan ?: '-' ?></td>
      <td><?= $m->nohp_ortu ?: '-' ?></td>
      <td>
        <?php if($m->jenis == 'keluar'): ?>
          <?= $m->tujuan_sekolah ?: '-' ?>
        <?php else: ?>
          <?= $m->tujuan_kelas_nama ?: '-' ?>
        <?php endif; ?>
      </td>
      <td><?= $m->tanggal ?></td>
      <td><?= $m->tahun_ajaran ?></td>
      <td class="text-center">
        <?php if($m->berkas): ?>
          <a href="<?= base_url('uploads/mutasi/'.$m->berkas) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-file-pdf text-danger"></i> Lihat
          </a>
        <?php else: ?>
          <span class="text-muted">-</span>
        <?php endif; ?>
      </td>
      <td class="text-center">
  <!-- Tombol Edit -->
  <button class="btn btn-warning btn-sm" 
          data-toggle="modal" 
          data-target="#editModal<?= $m->id ?>">
    <i class="fas fa-edit"></i>
  </button>

  <!-- Tombol Hapus -->
  <a href="<?= site_url('mutasi/delete/'.$m->id) ?>" 
     onclick="return confirm('Yakin ingin menghapus data ini?')" 
     class="btn btn-danger btn-sm">
    <i class="fas fa-trash"></i>
  </a>

  <!-- Tombol Batalkan Mutasi -->
  <?php if ($m->jenis == 'keluar'): ?>
    <a href="<?= site_url('mutasi/batalkan/'.$m->id) ?>" 
       onclick="return confirm('Yakin ingin membatalkan mutasi siswa ini dan mengaktifkan kembali?')" 
       class="btn btn-success btn-sm">
      <i class="fas fa-undo"></i> Batalkan
    </a>
  <?php endif; ?>
</td>


    </tr>
    <!-- =================== MODAL EDIT MUTASI =================== -->
<div class="modal fade" id="editModal<?= $m->id ?>">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?= site_url('mutasi/edit/'.$m->id) ?>" enctype="multipart/form-data">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit Mutasi</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <!-- CSRF -->
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
                 value="<?= $this->security->get_csrf_hash(); ?>">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Jenis Mutasi</label>
              <select name="jenis" class="form-control" required>
                <option value="keluar" <?= $m->jenis == 'keluar' ? 'selected' : '' ?>>Mutasi Keluar</option>
                <option value="masuk" <?= $m->jenis == 'masuk' ? 'selected' : '' ?>>Mutasi Masuk</option>
              </select>
            </div>
            <!-- 🔧 TAMBAH DI SINI (setelah jenis mutasi) -->
<div class="form-group">
  <label>Jenis Keluar</label>
  <select name="jenis_keluar" class="form-control">
    <option value="">-- Pilih Jenis Keluar --</option>
    <option value="mutasi" <?= $m->jenis_keluar == 'mutasi' ? 'selected' : '' ?>>Mutasi ke Sekolah Lain</option>
    <option value="mengundurkan diri" <?= $m->jenis_keluar == 'mengundurkan diri' ? 'selected' : '' ?>>Mengundurkan Diri</option>
    <option value="meninggal" <?= $m->jenis_keluar == 'meninggal' ? 'selected' : '' ?>>Meninggal Dunia</option>
    <option value="dikeluarkan" <?= $m->jenis_keluar == 'dikeluarkan' ? 'selected' : '' ?>>Dikeluarkan Sekolah</option>
    <option value="lainnya" <?= $m->jenis_keluar == 'lainnya' ? 'selected' : '' ?>>Lain-lain</option>
  </select>
</div>

            <div class="form-group col-md-6">
              <label>Tanggal</label>
              <input type="date" name="tanggal" value="<?= $m->tanggal ?>" class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label>Alasan</label>
            <textarea name="alasan" class="form-control" rows="2"><?= $m->alasan ?></textarea>
          </div>
          <div class="form-group">
  <label>Nomor HP Orang Tua</label>
  <input type="text" name="nohp_ortu" value="<?= $m->nohp_ortu ?>" class="form-control" placeholder="Contoh: 081234567890">
</div>

          <div class="form-group">
            <label>Tujuan Sekolah / Kelas</label>
            <input type="text" name="tujuan" value="<?= $m->tujuan_sekolah ?: $m->tujuan_kelas_nama ?>" class="form-control">
          </div>

          <div class="form-group">
            <label>Tahun Ajaran</label>
            <select name="tahun_id" class="form-control">
              <?php foreach($tahun as $t): ?>
                <option value="<?= $t->id ?>" <?= $t->id == $m->tahun_id ? 'selected' : '' ?>><?= $t->tahun ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Ganti Berkas (Opsional)</label>
            <input type="file" name="berkas" class="form-control-file" accept=".pdf">
            <?php if($m->berkas): ?>
              <small class="text-muted d-block mt-1">
                File saat ini: <a href="<?= base_url('uploads/mutasi/'.$m->berkas) ?>" target="_blank"><?= $m->berkas ?></a>
              </small>
            <?php endif; ?>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

    <?php endforeach; ?>
  </tbody>
</table>

<?= $pagination ?>

<!-- =================== MODAL TAMBAH MUTASI =================== -->
<div class="modal fade" id="addModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?= site_url('mutasi/add') ?>" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Mutasi</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <!-- CSRF -->
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
                 value="<?= $this->security->get_csrf_hash(); ?>">

          <!-- ---------------------------------------------
               JENIS MUTASI
          ---------------------------------------------- -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Jenis Mutasi</label>
              <select name="jenis" id="jenis" class="form-control" required onchange="toggleFields()">
                <option value="">-- Pilih --</option>
                <option value="keluar">Mutasi Keluar</option>
                <option value="masuk">Mutasi Masuk</option>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Tanggal</label>
              <input type="date" name="tanggal" class="form-control" required>
            </div>
          </div>

          <!-- ---------------------------------------------
               CARI SISWA (HANYA UNTUK KELUAR)
          ---------------------------------------------- -->
          <div class="form-group keluar-only" style="display:none;">
            <label>Cari Siswa</label>
            <input type="text" id="search_siswa" class="form-control" placeholder="Ketik nama atau NIS siswa...">
            <input type="hidden" name="siswa_id" id="siswa_id">
            <div id="siswa_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
          </div>

          <!-- ---------------------------------------------
               INPUT MANUAL UNTUK MUTASI MASUK
          ---------------------------------------------- -->
          <div class="masuk-only" style="display:none;">
    <div class="form-group">
        <label>Nama Siswa</label>
        <input type="text" name="nama_baru" class="form-control" placeholder="Nama lengkap siswa baru">
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>NIS</label>
            <input type="text" name="nis_baru" class="form-control" placeholder="NIS siswa">
        </div>

        <div class="form-group col-md-6">
            <label>NISN</label>
            <input type="text" name="nisn_baru" class="form-control" placeholder="NISN siswa">
        </div>
    </div>

    <div class="form-group">
        <label>Jenis Kelamin</label>
        <select name="jk_baru" class="form-control">
            <option value="L">Laki-Laki</option>
            <option value="P">Perempuan</option>
        </select>
    </div>

    <div class="form-group">
        <label>Sekolah Asal</label>
        <input type="text" name="asal_sekolah_baru" class="form-control" placeholder="Nama sekolah asal siswa">
    </div>
</div>


          <!-- ---------------------------------------------
               KHUSUS MUTASI KELUAR
          ---------------------------------------------- -->
          <div class="form-group keluar-only" style="display:none;">
            <label>Jenis Keluar</label>
            <select name="jenis_keluar" id="jenis_keluar" class="form-control" onchange="toggleKeluarFields()">
              <option value="">-- Pilih Jenis Keluar --</option>
              <option value="mutasi">Mutasi ke Sekolah Lain</option>
              <option value="mengundurkan diri">Mengundurkan Diri</option>
              <option value="meninggal">Meninggal Dunia</option>
            </select>
          </div>

          <div class="form-group keluar-only" id="tujuanSekolahField" style="display:none;">
            <label>Tujuan Sekolah</label>
            <input type="text" name="tujuan_sekolah" class="form-control">
          </div>

          <div class="form-group keluar-only" id="alasanField" style="display:none;">
            <label>Alasan</label>
            <textarea name="alasan" class="form-control" rows="2"></textarea>
          </div>

          <div class="form-group keluar-only" id="nohpOrtuField" style="display:none;">
            <label>Nomor HP Orang Tua</label>
            <input type="text" name="nohp_ortu" class="form-control" placeholder="Contoh: 081234567890">
          </div>

          <!-- ---------------------------------------------
               MUTASI MASUK → PILIH KELAS
          ---------------------------------------------- -->
          <div class="form-group masuk-only" id="tujuanKelasField" style="display:none;">
            <label>Tujuan Kelas (untuk Mutasi Masuk)</label>
            <select name="tujuan_kelas_id" class="form-control">
              <option value="">-- Pilih Kelas --</option>
              <?php foreach($kelas as $k): ?>
                <option value="<?= $k->id ?>"><?= $k->nama ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- ---------------------------------------------
               TAHUN AJARAN
          ---------------------------------------------- -->
          <div class="form-group">
            <label>Tahun Ajaran</label>
            <select name="tahun_id" class="form-control">
              <?php foreach($tahun as $t): ?>
                <option value="<?= $t->id ?>"><?= $t->tahun ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- ---------------------------------------------
               FILE PDF
          ---------------------------------------------- -->
          <div class="form-group">
            <label>Berkas Mutasi (PDF, maks 500 KB)</label>
            <input type="file" name="berkas" id="berkas" class="form-control-file" accept=".pdf">
            <small class="text-muted d-block">Hanya file PDF &lt; 500 KB.</small>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- =================== MODAL IMPORT EXCEL =================== -->
<div class="modal fade" id="importModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?= site_url('mutasi/import_excel') ?>" enctype="multipart/form-data">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title">Import Data Mutasi (Excel)</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <!-- CSRF -->
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
                 value="<?= $this->security->get_csrf_hash(); ?>">

          <div class="form-group">
  <label for="file_excel">Pilih File Excel (.xlsx)</label>
  <div class="custom-file">
    <input type="file" name="file_excel" id="file_excel" class="custom-file-input" accept=".xlsx" required>
    <label class="custom-file-label" for="file_excel">Pilih file...</label>
  </div>
  <small class="text-muted d-block mt-1">Pastikan format sesuai template.</small>
</div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-secondary">Import</button>
          <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- =================== SCRIPT =================== -->
<script>
function toggleFields() {
  const jenis = document.getElementById('jenis').value;

  // Show/hide untuk mutasi keluar
  document.querySelectorAll('.keluar-only').forEach(el =>
    el.style.display = (jenis === 'keluar') ? 'block' : 'none'
  );

  // Show/hide untuk mutasi masuk
  document.querySelectorAll('.masuk-only').forEach(el =>
    el.style.display = (jenis === 'masuk') ? 'block' : 'none'
  );

  // Jika jenis = masuk → kosongkan field pencarian
  if (jenis === 'masuk') {
    document.getElementById('siswa_id').value = "";
    document.getElementById('search_siswa').value = "";
  }
}

function toggleKeluarFields() {
  const jenisKeluar = document.getElementById('jenis_keluar').value;

  document.getElementById('tujuanSekolahField').style.display = 
      (jenisKeluar === 'mutasi') ? 'block' : 'none';

  document.getElementById('alasanField').style.display = 
      (jenisKeluar === 'mengundurkan diri' || jenisKeluar === 'dikeluarkan' || jenisKeluar === 'lainnya') 
      ? 'block' : 'none';

  document.getElementById('nohpOrtuField').style.display =
      (jenisKeluar !== 'meninggal') ? 'block' : 'none';
}


// ========== Preview File Upload & Validasi ==========
const fileInput = document.getElementById('berkas');
const preview = document.getElementById('previewFile');
const fileName = document.getElementById('fileName');

fileInput.addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    if (file.type !== 'application/pdf') {
      alert('Hanya file PDF yang diperbolehkan.');
      e.target.value = '';
      preview.style.display = 'none';
      return;
    }
    if (file.size > 512000) {
      alert('Ukuran file melebihi 500 KB. Silakan pilih file yang lebih kecil.');
      e.target.value = '';
      preview.style.display = 'none';
      return;
    }
    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
});
</script>
<script>
// ========== Tampilkan Nama File di Input Import ==========
document.addEventListener('DOMContentLoaded', function() {
  const importFileInput = document.getElementById('file_excel');
  if (importFileInput) {
    importFileInput.addEventListener('change', function(e) {
      const fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
      const label = importFileInput.nextElementSibling;
      if (label) label.textContent = fileName;
    });
  }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const input = document.getElementById('search_siswa');
  const hidden = document.getElementById('siswa_id');
  const list = document.getElementById('siswa_suggestions');
  const jenisSelect = document.getElementById('jenis');

  let timer;

  input.addEventListener('keyup', function() {
    clearTimeout(timer);
    const keyword = this.value.trim();
    const jenis = jenisSelect.value || 'keluar'; // default keluar

    if (keyword.length < 2) {
      list.innerHTML = '';
      return;
    }

    timer = setTimeout(() => {
      fetch('<?= site_url('mutasi/search_siswa') ?>?term=' + encodeURIComponent(keyword) + '&jenis=' + encodeURIComponent(jenis))
        .then(res => res.json())
        .then(data => {
          list.innerHTML = '';
          if (data.length > 0) {
            data.forEach(item => {
              const a = document.createElement('a');
              a.href = "#";
              a.classList.add('list-group-item', 'list-group-item-action');
              a.textContent = item.label;
              a.onclick = function(e) {
                e.preventDefault();
                input.value = item.value;
                hidden.value = item.id;
                list.innerHTML = '';
              };
              list.appendChild(a);
            });
          } else {
            const empty = document.createElement('div');
            empty.classList.add('list-group-item', 'text-muted');
            empty.textContent = 'Tidak ditemukan';
            list.appendChild(empty);
          }
        })
        .catch(() => {
          list.innerHTML = '<div class="list-group-item text-danger">Gagal memuat data</div>';
        });
    }, 300);
  });

  // Klik di luar suggestions -> hilang
  document.addEventListener('click', function(e) {
    if (!list.contains(e.target) && e.target !== input) {
      list.innerHTML = '';
    }
  });
});
</script>


