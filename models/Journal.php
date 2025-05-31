<?php
class Journal {
    private $conn;
    private $table = 'journals';
    private $detailTable = 'journal_details';
    private $accountTable = 'accounts';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tambah jurnal + detail
    public function create($date, $description, $details) {
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("INSERT INTO {$this->table} (date, description) VALUES (?, ?)");
            $stmt->execute([$date, $description]);
            $journalId = $this->conn->lastInsertId();
            foreach ($details as $d) {
                $accountId = $this->getAccountIdByCode($d['account_code']);
                $stmt2 = $this->conn->prepare("INSERT INTO {$this->detailTable} (journal_id, account_id, position, amount) VALUES (?, ?, ?, ?)");
                $stmt2->execute([$journalId, $accountId, $d['position'], $d['amount']]);
            }
            $this->conn->commit();
            return $journalId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Ambil semua jurnal umum (dengan detail debit/kredit)
    public function getAll() {
        $sql = "SELECT j.*, (
            SELECT GROUP_CONCAT(CONCAT(a.code, ' - ', a.name, ' (', d.position, '): ', d.amount) SEPARATOR '\n')
            FROM {$this->detailTable} d
            JOIN {$this->accountTable} a ON d.account_id = a.id
            WHERE d.journal_id = j.id
        ) AS entries
        FROM {$this->table} j
        ORDER BY j.date DESC, j.id DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil data buku besar (mutasi per akun)
    public function getLedgers() {
        // Ambil semua akun
        $accounts = $this->conn->query("SELECT * FROM {$this->accountTable} ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);
        $ledgers = [];
        foreach ($accounts as $acc) {
            $sql = "SELECT j.date, j.description,
                CASE WHEN d.position='debit' THEN d.amount ELSE 0 END AS debit,
                CASE WHEN d.position='kredit' THEN d.amount ELSE 0 END AS kredit
                FROM {$this->detailTable} d
                JOIN {$this->table} j ON d.journal_id = j.id
                WHERE d.account_id = ?
                ORDER BY j.date, j.id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$acc['id']]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ledgers[] = [
                'account_code' => $acc['code'],
                'account_name' => $acc['name'],
                'entries' => $entries
            ];
        }
        return $ledgers;
    }

    private function getAccountIdByCode($code) {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->accountTable} WHERE code = ?");
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : null;
    }
} 