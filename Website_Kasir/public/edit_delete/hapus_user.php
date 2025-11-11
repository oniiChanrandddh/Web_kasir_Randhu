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

$userId = intval($_GET['id']);

if (isset($_POST['confirm_delete'])) {
    $conn->query("DELETE FROM users WHERE id=$userId");
    header("Location: ../dashboardAdmin.php");
    exit();
}

$result = $conn->query("SELECT username FROM users WHERE id=$userId");
if ($result->num_rows === 0) {
    header("Location: ../dashboardAdmin.php");
    exit();
}
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus User</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f5f7; display:flex; justify-content:center; align-items:center; height:100vh; }
        .content { background:#fff; padding:30px; border-radius:12px; width:100%; max-width:400px; box-shadow:0 4px 20px rgba(0,0,0,0.1); text-align:center; }
        h1 { color:#b00020; margin-bottom:20px; }
        p { margin-bottom:25px; font-size:1rem; }
        form { display:flex; justify-content:center; gap:15px; }
        .btn-primary { background-color:#b00020; color:#fff; border:none; padding:12px 20px; border-radius:6px; cursor:pointer; font-weight:600; transition:background 0.3s; }
        .btn-primary:hover { background-color:#999; }
        .btn-secondary { background-color:#ccc; color:#333; text-decoration:none; padding:12px 20px; border-radius:6px; font-weight:600; transition:background 0.3s; display:inline-block; }
        .btn-secondary:hover { background-color:#aaa; }
        @media(max-width:600px){ .content{ padding:20px; } }
    </style>
</head>
<body>
    <div class="content">
        <h1>Hapus User</h1>
        <p>Apakah Anda yakin ingin menghapus user "<b><?= htmlspecialchars($user['username']) ?></b>"?</p>
        <form method="POST">
            <button type="submit" name="confirm_delete" class="btn-primary">Hapus</button>
            <a href="../dashboardAdmin.php#users" class="btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
