<h4>Hasil Laporan Absensi</h4>
<hr>

<table class="table table-bordered table-striped">
    <thead class="thead-light">
        <tr>
            <th width="140">Tanggal</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th>Tahun Pelajaran</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($hasil as $h): ?>
        <tr>
            <td><?= $h->tanggal ?></td>
            <td><?= $h->nama_siswa ?></td>
            <td><?= $h->nama_kelas ?></td>
            <td>
                <span class="badge 
                    <?= ($h->status=='SAKIT'?'badge-warning':
                         ($h->status=='IZIN'?'badge-info':'badge-danger')) ?>">
                    <?= $h->status ?>
                </span>
            </td>
            <td><?= $h->keterangan ?></td>
            <td><?= $h->tahun_pelajaran ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="<?= site_url('Absensi/Laporan') ?>" class="btn btn-secondary">Kembali</a>
