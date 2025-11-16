<div class="container mt-4">

<h3 class="mb-3">Data Izin Siswa</h3>
<hr>

<div class="table-responsive shadow-sm rounded">
<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr class="text-center">
            <th>No</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Jenis Izin</th>
            <th>Keperluan</th>
            <th>Keluar</th>
            <th>Kembali</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
    <?php 
        $no = $start + 1;
        foreach($izin as $i): 
    ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= $i->nama ?></td>
            <td class="text-center"><?= $i->kelas_nama ?></td>

            <td class="text-center">
                <?php if ($i->jenis_izin == 'pulang'): ?>
                    <span class="badge bg-danger">Pulang</span>
                <?php else: ?>
                    <span class="badge bg-primary">Keluar</span>
                <?php endif; ?>
            </td>

            <td><?= $i->keperluan ?></td>
            <td class="text-center"><?= $i->jam_keluar ?></td>
            <td class="text-center"><?= $i->jam_masuk ?: '-' ?></td>

            <td class="text-center">
                <?php if ($i->jenis_izin == 'pulang'): ?>
                    <span class="badge bg-danger">Pulang</span>

                <?php elseif ($i->status == 'keluar'): ?>
                    <span class="badge bg-warning text-dark">Belum Kembali</span>

                <?php else: ?>
                    <span class="badge bg-success">Kembali</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<!-- PAGINATION -->
<div class="mt-3 d-flex justify-content-center">
    <?= $pagination ?>
</div>

</div>
