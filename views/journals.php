<?php
require_once 'models/Journal.php';
$journalModel = new Journal();
$jurnals = $journalModel->getAll();
?>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Jurnal Umum</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Detail (Akun, Debit/Kredit, Jumlah)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jurnals as $j): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($j['date'])) ?></td>
                        <td><?= htmlspecialchars($j['description']) ?></td>
                        <td><?= nl2br(htmlspecialchars($j['entries'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 