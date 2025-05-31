<?php
require_once 'models/Journal.php';
$journalModel = new Journal();
$ledgers = $journalModel->getLedgers();
?>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Buku Besar</h6>
    </div>
    <div class="card-body">
        <?php foreach ($ledgers as $ledger): ?>
        <div class="mb-4">
            <h6><?= $ledger['account_code'] ?> - <?= $ledger['account_name'] ?></h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $saldo = 0; foreach ($ledger['entries'] as $entry): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($entry['date'])) ?></td>
                            <td><?= htmlspecialchars($entry['description']) ?></td>
                            <td class="text-end"><?= $entry['debit'] ? number_format($entry['debit'],0,',','.') : '-' ?></td>
                            <td class="text-end"><?= $entry['kredit'] ? number_format($entry['kredit'],0,',','.') : '-' ?></td>
                            <td class="text-end">
                                <?php
                                if ($entry['debit']) $saldo += $entry['debit'];
                                if ($entry['kredit']) $saldo -= $entry['kredit'];
                                echo number_format($saldo,0,',','.');
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div> 