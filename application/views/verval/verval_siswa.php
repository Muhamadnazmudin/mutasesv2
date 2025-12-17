<div class="container-fluid">
    <h1 class="h4 mb-4">Verval Data Siswa</h1>

    <div class="card shadow">
        <div class="card-body table-responsive">
            <form method="get" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="small">Kelas</label>
            <select name="kelas" class="form-control form-control-sm">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k->id ?>" <?= $this->input->get('kelas')==$k->id?'selected':'' ?>>
                        <?= $k->nama ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="small">Cari Nama / NISN</label>
            <input type="text" name="q"
                   class="form-control form-control-sm"
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
            <a href="<?= site_url('verval/siswa') ?>" class="btn btn-sm btn-secondary">
                Reset
            </a>
        </div>
    </div>
</form>

            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="4%">No</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th width="14%">Status Verval</th>
                        <th width="20%">Aksi</th>
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
                                <span class="badge badge-success px-3 py-2">
                                    Sudah
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary px-3 py-2">
                                    Belum
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($s->status_verval == 0): ?>
                                <a href="<?= site_url('verval/set/'.$s->id.'/1') ?>"
                                   class="btn btn-sm btn-success"
                                   onclick="return confirm('Tandai siswa ini SUDAH melakukan verval?')">
                                   Tandai Sudah
                                </a>
                            <?php else: ?>
                                <a href="<?= site_url('verval/set/'.$s->id.'/0') ?>"
                                   class="btn btn-sm btn-warning"
                                   onclick="return confirm('Batalkan status verval siswa ini?')">
                                   Batalkan
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">
        Total data: <?= $total_rows ?>
    </small>
    <?= $pagination ?>
</div>

        </div>
    </div>
</div>
