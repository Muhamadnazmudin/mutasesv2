<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <h1 class="h4 mb-4">Verval Data Siswa</h1>

    <div class="card shadow">
        <div class="card-body table-responsive">

            <!-- FILTER -->
            <form method="get" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="small">Kelas</label>
                        <select name="kelas" class="form-control form-control-sm">
                            <option value="">Semua Kelas</option>
                            <?php foreach ($kelas as $k): ?>
                                <option value="<?= $k->id ?>" <?= $this->input->get('kelas')==$k->id?'selected':'' ?>>
                                    <?= htmlspecialchars($k->nama) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="small">Cari Nama / NISN</label>
                        <input type="text" name="q" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($this->input->get('q')) ?>"
                               placeholder="Ketik nama atau NISN">
                    </div>

                    <div class="col-md-2">
                        <label class="small">Per Halaman</label>
                        <select name="limit" class="form-control form-control-sm">
                            <?php foreach ([10,20,50,100] as $l): ?>
                                <option value="<?= $l ?>" <?= $this->input->get('limit')==$l?'selected':'' ?>>
                                    <?= $l ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                        <a href="<?= site_url('verval/siswa') ?>" class="btn btn-sm btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- TABEL -->
            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="4%">No</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th width="14%" class="text-center">Status</th>
                        <th width="25%">Catatan</th>
                        <th width="20%" class="text-center">Aksi</th>

                    </tr>
                </thead>
                <tbody>
                <?php
                $offset = (int) $this->input->get('per_page');
                $no = $offset + 1;
                foreach ($siswa as $s):
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($s->nisn) ?></td>
                        <td><?= htmlspecialchars($s->nama) ?></td>
                        <td><?= htmlspecialchars($s->nama_kelas) ?></td>
                        <td class="text-center">
                            <?php if ($s->status_verval == 1): ?>
                                <span class="badge badge-success px-3">Valid</span>
                            <?php elseif ($s->status_verval == 2): ?>
                                <span class="badge badge-warning px-3" title="<?= htmlspecialchars($s->catatan_verval) ?>">Perbaikan</span>
                            <?php else: ?>
                                <span class="badge badge-secondary px-3">Belum</span>
                            <?php endif; ?>
                        </td>
                        <td>
    <?php if ($s->status_verval == 2 && !empty($s->catatan_verval)): ?>
        <span class="text-truncate d-inline-block"
              style="max-width: 220px;"
              title="<?= htmlspecialchars($s->catatan_verval) ?>">
            <?= htmlspecialchars($s->catatan_verval) ?>
        </span>
    <?php else: ?>
        <span class="text-muted">-</span>
    <?php endif; ?>
</td>

                       <td class="text-center">
    <?php $qs = $_SERVER['QUERY_STRING']; ?>

<a href="<?= site_url('verval/valid/'.$s->id.'?'.$qs) ?>"
   class="btn btn-success btn-sm"
   title="Valid">
    <i class="fas fa-check"></i>
</a>

    <button class="btn btn-warning btn-sm" title="Perbaikan" data-id="<?= $s->id ?>" onclick="openPerbaikan(this)">
        <i class="fas fa-edit"></i>
    </button>
    <?php if ($s->status_verval != 0): ?>
    <a href="<?= site_url('verval/reset/'.$s->id.'?'.$qs) ?>"
   class="btn btn-secondary btn-sm"
   title="Reset">
    <i class="fas fa-undo"></i>
</a>

    <?php endif; ?>
</td>

                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">Total data: <?= $total_rows ?></small>
                <?= $pagination ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PERBAIKAN -->
<div class="modal fade" id="modalPerbaikan" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= site_url('verval/perbaikan') ?>">

    <!-- CSRF -->
    <input type="hidden"
           name="<?= $this->security->get_csrf_token_name(); ?>"
           value="<?= $this->security->get_csrf_hash(); ?>">

    <!-- ID SISWA (INI WAJIB) -->
    <input type="hidden" name="id_siswa" id="id_siswa">
    <input type="hidden" name="redirect_url"
           value="<?= current_url().'?'.$_SERVER['QUERY_STRING'] ?>">

    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Catatan Perbaikan</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <label>Catatan Perbaikan</label>
                <textarea name="catatan"
                          class="form-control"
                          rows="4"
                          required></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                Batal
            </button>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</form>

  </div>
</div>

<script>
function openPerbaikan(btn) {
    document.getElementById('id_siswa').value = btn.getAttribute('data-id');
    $('#modalPerbaikan').modal('show');
}
</script>
