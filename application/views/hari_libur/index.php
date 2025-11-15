<div class="card">
    <div class="card-header bg-primary text-white">
        <h4><i class="fa fa-calendar"></i> Hari Libur</h4>
    </div>

    <div class="card-body">

        <!-- Notifikasi -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalTambah">
            <i class="fa fa-plus"></i> Tambah Hari Libur
        </button>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="80">#</th>
                    <th>Tanggal</th>
                    <th>Nama Hari Libur</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php $no=1; foreach($libur as $l): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $l->start ?></td>
                    <td><?= $l->nama ?></td>
                    <td>
                        <a href="<?= site_url('HariLibur/hapus/'.$l->id) ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Hapus hari libur ini?')">
                           <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>

    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog">
    <div class="modal-content">

      <form action="<?= site_url('HariLibur/tambah') ?>" method="post">

        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Tambah Hari Libur</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

            <!-- CSRF -->
            <input type="hidden"
                name="<?= $this->security->get_csrf_token_name(); ?>"
                value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="form-group">
                <label>Nama Hari Libur</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="start" class="form-control" required>
            </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>

      </form>

    </div>
  </div>
</div>
