<?php
require_once __DIR__ . '/models/Mahasiswa.php';

$mahasiswa = new Mahasiswa();
$message   = '';
$error     = '';
$editData  = null;

// ─── Handle POST actions ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']   ?? '';
    $id       = (int)($_POST['id'] ?? 0);
    $nim      = trim($_POST['nim']      ?? '');
    $nama     = trim($_POST['nama']     ?? '');
    $jurusan  = trim($_POST['jurusan']  ?? '');
    $angkatan = trim($_POST['angkatan'] ?? '');
    $ipk      = (float)($_POST['ipk']  ?? 0);

    try {
        if ($action === 'create') {
            if (empty($nim) || empty($nama) || empty($jurusan) || empty($angkatan)) {
                $error = 'Semua field wajib diisi!';
            } elseif ($mahasiswa->nimExists($nim)) {
                $error = "NIM $nim sudah terdaftar!";
            } elseif ($mahasiswa->create($nim, $nama, $jurusan, $angkatan, $ipk)) {
                $message = 'Data mahasiswa berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan data.';
            }
        } elseif ($action === 'update') {
            if ($mahasiswa->nimExists($nim, $id)) {
                $error = "NIM $nim sudah digunakan mahasiswa lain!";
            } elseif ($mahasiswa->update($id, $nim, $nama, $jurusan, $angkatan, $ipk)) {
                $message = 'Data mahasiswa berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui data.';
            }
        } elseif ($action === 'delete') {
            if ($mahasiswa->delete($id)) {
                $message = 'Data mahasiswa berhasil dihapus!';
            } else {
                $error = 'Gagal menghapus data.';
            }
        }
    } catch (RuntimeException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
    header("Location: index.php" . ($message ? "?msg=" . urlencode($message) : "?err=" . urlencode($error)));
    exit;
}

// ─── Handle GET actions ────────────────────────────────────────────────
$action = $_GET['action'] ?? '';
if ($action === 'edit' && isset($_GET['id'])) {
    $editData = $mahasiswa->getById((int)$_GET['id']);
    if (!$editData) $error = 'Data tidak ditemukan.';
}
if (isset($_GET['msg'])) $message = htmlspecialchars($_GET['msg']);
if (isset($_GET['err'])) $error   = htmlspecialchars($_GET['err']);

// ─── Fetch all records ─────────────────────────────────────────────────
$result = $mahasiswa->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Data Mahasiswa</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:        #F5F2ED;
    --surface:   #FFFFFF;
    --primary:   #1A3A2A;
    --accent:    #4E9E6F;
    --accent2:   #D4A853;
    --danger:    #C0392B;
    --text:      #1C1C1C;
    --muted:     #7A7A7A;
    --border:    #E0DAD0;
    --shadow:    0 2px 12px rgba(26,58,42,.08);
    --radius:    10px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    font-size: 15px;
    line-height: 1.6;
  }

  /* ── Header ── */
  header {
    background: var(--primary);
    color: #fff;
    padding: 0 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
    position: sticky; top: 0; z-index: 100;
    box-shadow: 0 2px 16px rgba(0,0,0,.25);
  }
  header h1 {
    font-family: 'DM Serif Display', serif;
    font-size: 1.35rem;
    letter-spacing: .02em;
  }
  header span {
    font-size: .8rem;
    opacity: .65;
    font-weight: 300;
  }

  /* ── Layout ── */
  .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem; }

  /* ── Alerts ── */
  .alert {
    padding: .85rem 1.2rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    font-weight: 500;
    font-size: .9rem;
    display: flex; align-items: center; gap: .6rem;
  }
  .alert-success { background: #E8F5EE; color: #1A6B3C; border-left: 4px solid var(--accent); }
  .alert-danger  { background: #FDEEEE; color: var(--danger); border-left: 4px solid var(--danger); }

  /* ── Card ── */
  .card {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
  }
  .card-header {
    padding: 1.1rem 1.4rem;
    background: var(--primary);
    color: #fff;
    font-family: 'DM Serif Display', serif;
    font-size: 1.05rem;
    display: flex; align-items: center; gap: .5rem;
  }
  .card-body { padding: 1.4rem; }

  /* ── Form grid ── */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
  }
  .form-grid.wide { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
  .form-group label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
    margin-bottom: .35rem;
  }
  .form-group input, .form-group select {
    width: 100%;
    padding: .6rem .85rem;
    border: 1.5px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem;
    background: var(--bg);
    color: var(--text);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
  }
  .form-group input:focus, .form-group select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(78,158,111,.15);
    background: #fff;
  }

  /* ── Buttons ── */
  .btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .55rem 1.1rem;
    border: none; border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem; font-weight: 600;
    cursor: pointer; text-decoration: none;
    transition: all .18s;
  }
  .btn-primary  { background: var(--accent);  color: #fff; }
  .btn-primary:hover  { background: #3d8a5d; transform: translateY(-1px); }
  .btn-warning  { background: var(--accent2); color: #fff; }
  .btn-warning:hover  { background: #c49440; }
  .btn-danger   { background: var(--danger);  color: #fff; }
  .btn-danger:hover   { background: #a93226; }
  .btn-secondary{ background: #E8E4DC; color: var(--primary); }
  .btn-secondary:hover{ background: #d8d4cc; }
  .btn-sm { padding: .35rem .7rem; font-size: .8rem; border-radius: 5px; }
  .btn-group { display: flex; gap: .5rem; margin-top: 1.1rem; }

  /* ── Table ── */
  .table-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; font-size: .88rem; }
  thead tr { background: #F0EBE0; }
  thead th {
    padding: .75rem 1rem;
    text-align: left;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--muted);
    font-weight: 700;
    white-space: nowrap;
  }
  tbody tr {
    border-top: 1px solid var(--border);
    transition: background .15s;
  }
  tbody tr:hover { background: #FAF7F2; }
  td { padding: .75rem 1rem; vertical-align: middle; }

  /* IPK badge */
  .badge-ipk {
    display: inline-block;
    padding: .2rem .55rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: .8rem;
  }
  .ipk-high  { background: #E8F5EE; color: #1A6B3C; }
  .ipk-mid   { background: #FEF9EC; color: #9A6F10; }
  .ipk-low   { background: #FDEEEE; color: var(--danger); }

  .actions { display: flex; gap: .4rem; }

  .section-title {
    font-family: 'DM Serif Display', serif;
    font-size: 1.5rem;
    color: var(--primary);
    margin-bottom: 1.4rem;
  }
  .layout { display: grid; grid-template-columns: 350px 1fr; gap: 1.5rem; align-items: start; }
  @media (max-width: 820px) { .layout { grid-template-columns: 1fr; } }

  .empty-state {
    text-align: center; padding: 3rem 1rem; color: var(--muted);
  }
  .empty-state svg { margin-bottom: .75rem; opacity: .4; }

  /* Edit mode indicator */
  .card-header.edit-mode { background: #8B5E00; }
</style>
</head>
<body>

<header>
  <h1>📚 Manajemen Data Mahasiswa</h1>
  <span>Pemrograman Web II · MySQLi OOP</span>
</header>

<div class="container">

  <?php if ($message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger">⚠️ <?= $error ?></div>
  <?php endif; ?>

  <div class="layout">
    <!-- ── FORM PANEL ── -->
    <div>
      <div class="card">
        <div class="card-header <?= $editData ? 'edit-mode' : '' ?>">
          <?= $editData ? '✏️ Edit Data Mahasiswa' : '➕ Tambah Mahasiswa Baru' ?>
        </div>
        <div class="card-body">
          <form method="POST" action="index.php">
            <input type="hidden" name="action" value="<?= $editData ? 'update' : 'create' ?>">
            <?php if ($editData): ?>
              <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>

            <div class="form-grid" style="grid-template-columns:1fr 1fr;">
              <div class="form-group">
                <label>NIM *</label>
                <input type="text" name="nim" placeholder="2021001" maxlength="20" required
                       value="<?= htmlspecialchars($editData['nim'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Angkatan *</label>
                <input type="text" name="angkatan" placeholder="2001" maxlength="4" required
                       pattern="\d{4}"
                       value="<?= htmlspecialchars($editData['angkatan'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group" style="margin-top:.9rem;">
              <label>Nama Lengkap *</label>
              <input type="text" name="nama" placeholder="Nama mahasiswa" maxlength="100" required
                     value="<?= htmlspecialchars($editData['nama'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-top:.9rem;">
              <label>Program Studi *</label>
              <select name="jurusan" required>
                <?php
                $jurusanList = ['Teknik Informatika','Sistem Informasi','Manajemen Informatika','Teknik Komputer','Cyber Security'];
                foreach ($jurusanList as $j):
                  $sel = isset($editData['jurusan']) && $editData['jurusan'] === $j ? 'selected' : '';
                ?>
                <option value="<?= $j ?>" <?= $sel ?>><?= $j ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="margin-top:.9rem;">
              <label>IPK (0.00 – 4.00)</label>
              <input type="number" name="ipk" step="0.01" min="0" max="4" placeholder="3.75"
                     value="<?= htmlspecialchars($editData['ipk'] ?? '0.00') ?>">
            </div>

            <div class="btn-group">
              <button type="submit" class="btn btn-primary">
                <?= $editData ? '💾 Simpan Perubahan' : '➕ Tambahkan' ?>
              </button>
              <?php if ($editData): ?>
                <a href="index.php" class="btn btn-secondary">✖ Batal</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <!-- Info Box -->
      <div style="margin-top:1rem;padding:.9rem 1rem;background:#E8F5EE;border-radius:var(--radius);font-size:.82rem;color:#1A6B3C;border:1px solid #C0E0CC;">
        <strong>🔒 Keamanan:</strong> Semua input menggunakan <em>Prepared Statement</em> untuk mencegah SQL Injection.
      </div>
    </div>

    <!-- ── TABLE PANEL ── -->
    <div>
      <div class="card">
        <div class="card-header">
          📋 Daftar Mahasiswa
          <?php
            $total = $result ? $result->num_rows : 0;
            echo "<span style='margin-left:auto;font-size:.78rem;opacity:.75;font-family:DM Sans,sans-serif;font-weight:400;'>$total data</span>";
          ?>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-wrap">
            <?php if ($total === 0): ?>
              <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p style="font-weight:600">Belum ada data mahasiswa</p>
                <p style="font-size:.85rem">Tambahkan data baru menggunakan form di samping.</p>
              </div>
            <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>NIM</th>
                  <th>Nama</th>
                  <th>Program Studi</th>
                  <th>Angkatan</th>
                  <th>IPK</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
              <?php
              $no = 1;
              while ($row = $result->fetch_assoc()):
                $ipk = (float)$row['ipk'];
                $ipkClass = $ipk >= 3.5 ? 'ipk-high' : ($ipk >= 3.0 ? 'ipk-mid' : 'ipk-low');
              ?>
                <tr>
                  <td style="color:var(--muted);font-size:.8rem;"><?= $no++ ?></td>
                  <td><code style="font-size:.82rem;background:#F0EBE0;padding:.1rem .4rem;border-radius:4px;"><?= htmlspecialchars($row['nim']) ?></code></td>
                  <td style="font-weight:500;"><?= htmlspecialchars($row['nama']) ?></td>
                  <td style="font-size:.82rem;color:var(--muted);"><?= htmlspecialchars($row['jurusan']) ?></td>
                  <td><?= htmlspecialchars($row['angkatan']) ?></td>
                  <td><span class="badge-ipk <?= $ipkClass ?>"><?= number_format($ipk, 2) ?></span></td>
                  <td>
                    <div class="actions">
                      <a href="index.php?action=edit&id=<?= $row['id'] ?>"
                         class="btn btn-warning btn-sm">✏️ Edit</a>
                      <form method="POST" action="index.php" style="display:inline;"
                            onsubmit="return confirm('Yakin ingin menghapus data <?= htmlspecialchars(addslashes($row['nama'])) ?>?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id"     value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div><!-- /table panel -->
  </div><!-- /layout -->
</div>

<footer style="text-align:center;padding:2rem;color:var(--muted);font-size:.8rem;margin-top:1rem;">
  Tugas Proyek · Pemrograman Web II · MySQLi OOP Style
</footer>
</body>
</html>