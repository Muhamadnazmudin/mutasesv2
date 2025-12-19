<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <h1 class="h4 mb-4">Laporan Verval Siswa</h1>

    <!-- FILTER -->
    <form method="get" class="mb-3">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label class="small">Kelas</label>
                <select name="kelas" class="form-control form-control-sm">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?= $k->id ?>"
                            <?= $this->input->get('kelas') == $k->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k->nama) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-sm btn-primary">
                    <i class="fas fa-filter"></i> Tampilkan
                </button>
                <a href="<?= site_url('verval/laporan') ?>" class="btn btn-sm btn-secondary">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- TABEL LAPORAN -->
    <div class="card shadow">
        <div class="card-body table-responsive">

            <a href="<?= site_url('verval/export_excel?kelas='.$this->input->get('kelas')) ?>"
               class="btn btn-sm btn-success mb-3">
                <i class="fas fa-file-excel"></i> Download Excel
            </a>

            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Kelas</th>
                        <th class="text-center">Total</th>
                        <th class="text-center text-success">Valid</th>
                        <th class="text-center text-warning">Perbaikan</th>
                        <th class="text-center text-primary">Sudah Verval</th>
                        <th class="text-center text-danger">Belum</th>
                        <th width="15%" class="text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($laporan)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Tidak ada data
                        </td>
                    </tr>
                <?php endif; ?>

                <?php $no = 1; foreach ($laporan as $l): 
                    $persen = ($l->total_siswa > 0)
                        ? round(($l->sudah_verval / $l->total_siswa) * 100, 1)
                        : 0;
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($l->nama_kelas) ?></td>
                        <td class="text-center font-weight-bold">
                            <?= $l->total_siswa ?>
                        </td>
                        <td class="text-center text-success font-weight-bold">
                            <?= $l->valid ?>
                        </td>
                        <td class="text-center text-warning font-weight-bold">
                            <?= $l->perbaikan ?>
                        </td>
                        <td class="text-center text-primary font-weight-bold">
                            <?= $l->sudah_verval ?>
                        </td>
                        <td class="text-center text-danger font-weight-bold">
                            <?= $l->belum_verval ?>
                        </td>
                        <td>
                            <div class="progress" style="height:18px">
                                <div class="progress-bar bg-success"
                                     role="progressbar"
                                     style="width:<?= $persen ?>%">
                                    <?= $persen ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>