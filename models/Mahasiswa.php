<?php
require_once __DIR__ . '/../config/Database.php';

class Mahasiswa {
    private mysqli $conn;
    private string $table = 'mahasiswa';

    public int    $id;
    public string $nim;
    public string $nama;
    public string $jurusan;
    public string $angkatan;
    public float  $ipk;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // READ - Ambil semua data
    public function getAll(): mysqli_result|false {
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC";
        return $this->conn->query($query);
    }

    // READ - Ambil satu data berdasarkan ID
    public function getById(int $id): array|null {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1"
        );
        if (!$stmt) {
            $this->throwError('Prepare gagal: ' . $this->conn->error);
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data   = $result->fetch_assoc();
        $stmt->close();
        return $data ?: null;
    }

    // CREATE - Tambah data baru (Prepared Statement)
    public function create(string $nim, string $nama, string $jurusan, string $angkatan, float $ipk): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (nim, nama, jurusan, angkatan, ipk) VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            $this->throwError('Prepare gagal: ' . $this->conn->error);
        }
        $stmt->bind_param('ssssd', $nim, $nama, $jurusan, $angkatan, $ipk);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // UPDATE - Edit data (Prepared Statement)
    public function update(int $id, string $nim, string $nama, string $jurusan, string $angkatan, float $ipk): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET nim=?, nama=?, jurusan=?, angkatan=?, ipk=? WHERE id=?"
        );
        if (!$stmt) {
            $this->throwError('Prepare gagal: ' . $this->conn->error);
        }
        $stmt->bind_param('ssssdi', $nim, $nama, $jurusan, $angkatan, $ipk, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // DELETE - Hapus data (Prepared Statement)
    public function delete(int $id): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table} WHERE id = ?"
        );
        if (!$stmt) {
            $this->throwError('Prepare gagal: ' . $this->conn->error);
        }
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Cek apakah NIM sudah ada (untuk validasi duplikasi)
    public function nimExists(string $nim, int $excludeId = 0): bool {
        $stmt = $this->conn->prepare(
            "SELECT id FROM {$this->table} WHERE nim = ? AND id != ? LIMIT 1"
        );
        $stmt->bind_param('si', $nim, $excludeId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function throwError(string $msg): never {
        throw new RuntimeException($msg);
    }
}
