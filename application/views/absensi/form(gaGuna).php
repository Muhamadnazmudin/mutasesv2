<h4>Absensi Kelas <?= $kelas->nama ?></h4>
<p>Tanggal: <?= $tanggal ?> <br> Tahun Pelajaran: <?= $tahun ?></p>

<form method="post" action="<?= base_url('Absensi/Absensi/simpan') ?>">
    <input type="hidden" 
           name="<?= $this->security->get_csrf_token_name(); ?>" 
           value="<?= $this->security->get_csrf_hash(); ?>">


    <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
    <input type="hidden" name="id_kelas" value="<?= $kelas->id ?>">
    <input type="hidden" name="tahun_pelajaran" value="<?= $tahun ?>">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Status (Kosong = Hadir)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($siswa as $s) { ?>
            <tr>
                <td><?= $s->nama_siswa ?></td>
                <td>
                    <select name="status[<?= $s->id_siswa ?>]" class="form-control">
                        <option value="">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpa">Alpa</option>
                    </select>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <button class="btn btn-success">Simpan Absensi</button>
</form>
