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

$stmt = $conn->prepare("SELECT nama FROM barang WHERE id = ?");
$stmt->bind_param("i", $barangId);
$stmt->execute();
$stmt->bind_result($namaBarang);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: ../dashboardAdmin.php");
    exit();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->query("DELETE FROM barang WHERE id=$barangId");
    header("Location: ../dashboardAdmin.php#products");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Barang</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f5f7; color: #333; }

        .layout { display: flex; justify-content: center; padding: 50px 20px; }
        .content { background: #fff; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }

        .topbar h1 { font-size: 1.8rem; margin-bottom: 15px; color: #b00020; }

        .section-edit { margin-top: 20px; }
        .section-edit p.warning { color: #b00020; margin-bottom: 15px; background: #fdd; padding: 10px; border-radius: 6px; }

        .form-edit { display: flex; flex-direction: column; gap: 15px; }
        .form-edit label { font-weight: 600; color: #b00020; }
        .form-edit input { padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; transition: border 0.3s; }
        .form-edit input:focus { border-color: #b00020; outline: none; }

        .btn-primary { background-color: #b00020; color: #fff; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.3s; }
        .btn-primary:hover { background-color: #800018; }

        .btn-secondary { background-color: #555; color: #fff; text-decoration: none; text-align: center; padding: 12px; border-radius: 6px; display: inline-block; transition: background 0.3s; }
        .btn-secondary:hover { background-color: #333; color: #fff; }

        @media(max-width:600px){ 
            .content { padding: 20px; }
            .topbar h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <div class="content">
            <div class="topbar">
                <h1>Hapus Barang</h1>
            </div>

            <div class="section-edit">
                <p class="warning">Apakah Anda yakin ingin menghapus barang berikut?</p>
                <p><b><?= htmlspecialchars($namaBarang) ?></b></p>

                <form class="form-edit" action="" method="POST">
                    <button type="submit" class="btn-primary">Hapus</button>
                    <a href="../dashboardAdmin.php#products" class="btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
