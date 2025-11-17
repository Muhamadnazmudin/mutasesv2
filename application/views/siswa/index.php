<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Data Siswa</h4>
  <div>
    <a href="<?= site_url('siswa/download_template') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-download"></i> Download Template
    </a>
    <a href="<?= site_url('siswa/cetak_semua?kelas=' . $kelas_id . '&search=' . $search) ?>" 
   class="btn btn-dark btn-sm">
    <i class="fas fa-file-archive"></i> Cetak Semua ID Card (ZIP)
</a>
    <a href="<?= site_url('siswa/export_excel') ?>" class="btn btn-success btn-sm">
      <i class="fas fa-file-excel"></i> Export
    </a>
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#importModal">
      <i class="fas fa-upload"></i> Import
    </button>
    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#addModal">
      <i class="fas fa-plus"></i> Tambah
    </button>
  </div>
</div>

<!-- Alert sukses / error -->
<?php if ($this->session->flashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success'); ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error'); ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>


<!-- 🔹 FILTER & PENCARIAN -->
<form method="get" class="row mb-3">
  <div class="col-md-3 col-sm-6 mb-2">
    <select name="kelas" class="form-control form-control-sm">
      <option value="">Semua Kelas</option>
      <?php foreach($kelas as $k): ?>
        <option value="<?= $k->id ?>" <?= ($kelas_id == $k->id ? 'selected' : '') ?>>
          <?= $k->nama ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3 col-sm-6 mb-2">
    <input type="text" name="search" value="<?= $search ?>" class="form-control form-control-sm" placeholder="Cari nama / NIS / NISN">
  </div>

  <div class="col-md-2 col-sm-6 mb-2">
    <select name="limit" class="form-control form-control-sm">
      <option value="10" <?= ($limit == 10 ? 'selected' : '') ?>>10</option>
      <option value="20" <?= ($limit == 20 ? 'selected' : '') ?>>20</option>
      <option value="50" <?= ($limit == 50 ? 'selected' : '') ?>>50</option>
      <option value="100" <?= ($limit == 100 ? 'selected' : '') ?>>100</option>
    </select>
  </div>

  <div class="col-md-2 col-sm-6 mb-2">
    <button class="btn btn-primary btn-sm w-100">
      <i class="fas fa-filter"></i> Terapkan
    </button>
  </div>

  <div class="col-md-2 col-sm-6 mb-2">
    <a href="<?= site_url('siswa') ?>" class="btn btn-secondary btn-sm w-100">
      <i class="fas fa-sync-alt"></i> Reset
    </a>
  </div>
</form>

<table class="table table-bordered table-striped table-responsive-sm">
  <thead class="thead-light">
    <tr>
      <th>No</th>
      <th>NIS</th>
      <th>NISN</th>
      <th>Nama</th>
      <th>Tempat Lahir</th>
      <th>JK</th>
      <th>Agama</th>
      <th>Tanggal Lahir</th>
      <th>Kelas</th>
      <th>Tahun Ajaran</th>
      <th>Status</th>
      <th width="120">Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = $start + 1; foreach ($siswa as $s): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $s->nis ?></td>
        <td><?= $s->nisn ?></td>
        <td><?= $s->nama ?></td>
        <td><?= $s->tempat_lahir ?></td>
        <td><?= $s->jk ?></td>
        <td><?= $s->agama ?></td>
        <td><?= $s->tgl_lahir ?></td>
        <td><?= $s->nama_kelas ?></td>
        <td><?= $s->tahun_ajaran ?></td>
        <td>
          <?php if ($s->status == 'aktif'): ?>
            <span class="badge badge-success">Aktif</span>
          <?php elseif ($s->status == 'mutasi_keluar'): ?>
            <span class="badge badge-danger">Mutasi Keluar</span>
          <?php elseif ($s->status == 'mutasi_masuk'): ?>
            <span class="badge badge-info">Mutasi Masuk</span>
          <?php elseif ($s->status == 'lulus'): ?>
            <span class="badge badge-primary">Lulus</span>
          <?php else: ?>
            <span class="badge badge-secondary">Keluar</span>
          <?php endif; ?>
        </td>
        <td class="text-center">
  <a href="<?= site_url('siswa/edit/'.$s->id) ?>" class="btn btn-warning btn-sm" title="Edit">
    <i class="fas fa-edit"></i>
  </a>

  <a href="<?= site_url('siswa/delete/'.$s->id) ?>" 
     onclick="return confirm('Hapus data ini?')" 
     class="btn btn-danger btn-sm"
     title="Hapus">
    <i class="fas fa-trash"></i>
  </a>

  <a href="<?= site_url('siswa/cetak/'.$s->id) ?>" 
     class="btn btn-info btn-sm" 
     target="_blank"
     title="Cetak Formulir">
    <i class="fas fa-print"></i>
  </a>

  <!-- 🔹 Tombol Cetak ID Card -->
  <a href="<?= site_url('idcard/cetak/'.$s->id) ?>" 
   class="btn btn-primary btn-sm"
   title="Download ID Card JPG">
   <i class="fas fa-id-card"></i>
</a>

</td>

      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?= $pagination ?>


<!-- Modal Tambah -->
<div class="modal fade" id="addModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?= site_url('siswa/add') ?>">
        <div class="modal-header"><h5 class="modal-title">Tambah Siswa</h5></div>
        <div class="modal-body">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
                 value="<?= $this->security->get_csrf_hash(); ?>">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>NIS</label>
              <input type="text" name="nis" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>NISN</label>
              <input type="text" name="nisn" class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Nama</label>
              <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>Tempat Lahir</label>
              <input type="text" name="tempat_lahir" class="form-control">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Jenis Kelamin</label>
              <select name="jk" class="form-control">
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Agama</label>
              <select name="agama" class="form-control">
                <option value="">-- Pilih Agama --</option>
                <option value="Islam">Islam</option>
                <option value="Kristen">Kristen</option>
                <option value="Katolik">Katolik</option>
                <option value="Hindu">Hindu</option>
                <option value="Budha">Budha</option>
                <option value="Konghucu">Konghucu</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Tanggal Lahir</label>
            <input type="date" name="tgl_lahir" class="form-control">
          </div>

          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"></textarea>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Kelas</label>
              <select name="id_kelas" class="form-control">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach($kelas as $k): ?>
                  <option value="<?= $k->id ?>"><?= $k->nama ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Tahun Ajaran</label>
              <select name="tahun_id" class="form-control" required>
                <?php foreach($tahun as $t): ?>
                  <option value="<?= $t->id ?>"><?= $t->tahun ?></option>
                <?php endforeach; ?>
              </select>
            </div>
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


<!-- Modal Import -->
<div class="modal fade" id="importModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?= site_url('siswa/import_excel') ?>" enctype="multipart/form-data">
        <div class="modal-header"><h5>Import Data Siswa</h5></div>
        <div class="modal-body">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
                 value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="form-group">
            <label>Pilih File Excel</label>
            <input type="file" name="file" accept=".xls,.xlsx" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Import</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
  <style>
/* === Fix tampilan modal tambah siswa agar teks lebih jelas === */

/* Warna label & teks form */
#addModal label,
#addModal .form-control,
#addModal select,
#addModal textarea {
  color: #212529 !important;          /* teks hitam pekat */
  background-color: #ffffff !important; /* background putih */
  border-color: #ced4da !important;
  font-weight: 500;
}

/* Placeholder terlihat jelas */
#addModal input::placeholder,
#addModal textarea::placeholder {
  color: #6c757d !important;
  opacity: 1;
}

/* Header modal lebih tegas */
#addModal .modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

#addModal .modal-title {
  color: #212529 !important;
  font-weight: 700;
}

/* Tombol bawah tetap kontras */
#addModal .modal-footer .btn-primary {
  background-color: #3b82f6;
  border-color: #3b82f6;
  font-weight: 600;
}

#addModal .modal-footer .btn-primary:hover {
  background-color: #2563eb;
  border-color: #2563eb;
}

#addModal .modal-footer .btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
  font-weight: 600;
}
form.row.mb-3 select,
form.row.mb-3 input {
  border-radius: 6px;
}
.pagination .page-item .page-link {
  color: #007bff;
  border: 1px solid #dee2e6;
  margin: 0 2px;
  border-radius: 6px;
  transition: all 0.2s ease-in-out;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
  color: #fff;
}

.pagination .page-item .page-link:hover {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
}

body.dark-mode .pagination .page-item .page-link {
  background-color: #1f1f1f;
  color: #ccc;
  border-color: #333;
}

body.dark-mode .pagination .page-item.active .page-link {
  background-color: #0d6efd;
  color: #fff;
}

</style>

</div>
