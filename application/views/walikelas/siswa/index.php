<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Data Siswa Kelas <?= $kelas_nama ?></h4>

  <div>
    <!-- Export Excel -->
    <a href="<?= site_url('walikelas/siswa_export_excel') ?>" class="btn btn-success btn-sm">
      <i class="fas fa-file-excel"></i> Export Excel
    </a>

    <!-- Cetak Semua Biodata -->
    <a href="<?= site_url('walikelas/cetak_biodata_all') ?>" target="_blank" class="btn btn-dark btn-sm">
      <i class="fas fa-print"></i> Cetak Semua Biodata
    </a>
  </div>
</div>


    <!-- Search -->
    <form method="get" class="row mb-3">
        <div class="col-md-4">
            <input type="text" name="search" value="<?= $search ?>" 
                   class="form-control form-control-sm"
                   placeholder="Cari nama / NIS / NISN">
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>

        <div class="col-md-2">
            <a href="<?= site_url('walikelas/siswa') ?>"
               class="btn btn-secondary btn-sm w-100">
               <i class="fas fa-sync-alt"></i> Reset
            </a>
        </div>
        
    </form>

    <table class="table table-bordered table-striped table-sm">
        <thead class="thead-light">
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>NISN</th>
                <th>Nama</th>
                <th>JK</th>
                <th>Tempat Lahir</th>
                <th>Tgl Lahir</th>
                <th>Agama</th>
                <th>Status</th>
                <th>ID Card</th>
            </tr>
        </thead>

        <tbody>
            <?php $no=1; foreach ($siswa as $s): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $s->nis ?></td>
                <td><?= $s->nisn ?></td>
                <td><?= $s->nama ?></td>
                <td><?= $s->jk ?></td>
                <td><?= $s->tempat_lahir ?></td>
                <td><?= $s->tgl_lahir ?></td>
                <td><?= $s->agama ?></td>
                <td>
                    <span class="badge badge-success">Aktif</span>
                </td>
                <td class="text-center">
                    <a href="<?= site_url('idcard/cetak/'.$s->id) ?>" 
                       class="btn btn-primary btn-sm" target="_blank">
                       <i class="fas fa-id-card"></i>
                    </a>
                    <a href="<?= site_url('walikelas/cetak_biodata/'.$s->id) ?>" 
   class="btn btn-info btn-sm" target="_blank"
   title="Cetak Biodata">
   <i class="fas fa-print"></i>
</a>

                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
