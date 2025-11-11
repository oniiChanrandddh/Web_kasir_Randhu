<?php
include("../config/db.php");

$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','kasir') NOT NULL
)";

if ($conn->query($sql_users) === TRUE) {
    echo "Tabel users siap.<br>";
} else {
    echo "Error buat tabel: " . $conn->error . "<br>";
}

$admin_username = "Admin";
$admin_password = password_hash("YamahaR25", PASSWORD_DEFAULT);
$admin_role     = "admin";

$kasir_username = "Kasir";
$kasir_password = password_hash("YamahaR6", PASSWORD_DEFAULT);
$kasir_role     = "kasir";

$sql_insert = "INSERT INTO users (username, password, role) VALUES 
    ('$admin_username', '$admin_password', '$admin_role'),
    ('$kasir_username', '$kasir_password', '$kasir_role')
";

if ($conn->query($sql_insert) === TRUE) {
    echo "Data awal berhasil ditambahkan.<br>";
} else {
    echo "Error insert: " . $conn->error . "<br>";
}

$conn->close();
?>
