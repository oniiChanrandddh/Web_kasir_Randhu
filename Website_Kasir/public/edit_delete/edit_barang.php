<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../dashboardAdmin.php");
    exit();
}

$barangId = intval($_GET['id']);
$res = $conn->query("SELECT * FROM barang WHERE id=$barangId");
if ($res->num_rows === 0) {
    echo "Barang tidak ditemukan.";
    exit();
}
$barang = $res->fetch_assoc();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = $conn->real_escape_string($_POST['nama']);
    $harga = floatval($_POST['harga']);
    $stok  = intval($_POST['stok']);

    if ($nama === "" || $harga <= 0 || $stok < 0) {
        $error = "Semua field harus diisi dengan benar.";
    } else {
        $update = $conn->query("UPDATE barang SET nama='$nama', harga=$harga, stok=$stok WHERE id=$barangId");
        if ($update) {
            $success = "Data barang berhasil diupdate!";
            $barang = ['nama'=>$nama, 'harga'=>$harga, 'stok'=>$stok];
        } else {
            $error = "Terjadi kesalahan: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Barang</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f5f7; color: #333; }

        .layout { display: flex; justify-content: center; padding: 50px 20px; }
        .content { background: #fff; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .topbar h1 { font-size: 1.8rem; margin-bottom: 15px; color: #4B0082; }
        .section-edit { margin-top: 20px; }
        .section-edit p.error { color: #b00020; margin-bottom: 15px; background: #fdd; padding: 10px; border-radius: 6px; }
        .section-edit p.success { color: #006400; margin-bottom: 15px; background: #dff0d8; padding: 10px; border-radius: 6px; }
        .form-edit { display: flex; flex-direction: column; gap: 15px; }
        .form-edit label { font-weight: 600; color: #4B0082; }
        .form-edit input { padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; transition: border 0.3s; }
        .form-edit input:focus { border-color: #4B0082; outline: none; }
        .btn-primary { background-color: #006400; color: #fff; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.3s; }
        .btn-primary:hover { background-color: #35006e; }
        .btn-secondary { background-color: #b00020; color: #fff; text-decoration: none; text-align: center; padding: 12px; border-radius: 6px; display: inline-block; transition: background 0.3s; }
        .btn-secondary:hover { background-color: #999; color: #fff; }

        @media(max-width:600px){ 
            .content { padding: 20px; }
            .topbar h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <main class="content">
            <header class="topbar">
                <h1>Edit Barang</h1>
            </header>
            <div class="section section-edit">
                <?php if($error): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif; ?>
                <?php if($success): ?>
                    <p class="success"><?= $success ?></p>
                <?php endif; ?>
                <form method="POST" class="form-edit">
                    <label for="nama">Nama Barang</label>
                    <input id="nama" type="text" name="nama" value="<?= $barang['nama'] ?>" required>
                    
                    <label for="harga">Harga</label>
                    <input id="harga" type="number" name="harga" value="<?= $barang['harga'] ?>" required>
                    
                    <label for="stok">Stok</label>
                    <input id="stok" type="number" name="stok" value="<?= $barang['stok'] ?>" required>

                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    <a href="../dashboardAdmin.php" class="btn-secondary">Batal</a>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
