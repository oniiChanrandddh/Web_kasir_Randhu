<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "kasir") {
    header("Location: index.php");
    exit();
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['selected_member'])) $_SESSION['selected_member'] = null;
$messages = [];

$level_diskon = [
    'Bronze' => 2,
    'Silver' => 5,
    'Gold' => 10,
    'Platinum' => 15
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        $removeId = intval($_POST['remove_item']);
        foreach ($_SESSION['cart'] as $k => $it) {
            if ($it['id'] == $removeId) {
                unset($_SESSION['cart'][$k]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                $messages[] = "Item dihapus dari keranjang.";
                break;
            }
        }
    }

    if (isset($_POST['remove_member'])) {
        $_SESSION['selected_member'] = null;
        $messages[] = "Member yang dicek telah dihapus.";
    }

    if (isset($_POST['check_member']) && !empty(trim($_POST['member_search']))) {
        $term = trim($_POST['member_search']);
        $member = null;

        if (is_numeric($term)) {
            $stmt = $conn->prepare("SELECT id, nama, level FROM members WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $term);
            $stmt->execute();
            $res = $stmt->get_result();
            $member = $res->fetch_assoc();
            $stmt->close();
        }

        if (empty($member)) {
            $like = '%' . $term . '%';
            $stmt = $conn->prepare("SELECT id, nama, level FROM members WHERE nama LIKE ? ORDER BY tanggal_daftar DESC LIMIT 1");
            $stmt->bind_param("s", $like);
            $stmt->execute();
            $res = $stmt->get_result();
            $member = $res->fetch_assoc();
            $stmt->close();
        }

        if ($member) {
            $member['diskon_member'] = $level_diskon[$member['level']] ?? 0;
            $_SESSION['selected_member'] = $member;
            $messages[] = "Member terdeteksi: " . htmlspecialchars($member['nama']) . " (Diskon {$member['diskon_member']}%).";
        } else {
            $_SESSION['selected_member'] = null;
            $messages[] = "Member tidak ditemukan. Lanjut tanpa diskon.";
        }
    }

    if (isset($_POST['barang_id'], $_POST['jumlah']) && !isset($_POST['pay'])) {
        $barangId = intval($_POST['barang_id']);
        $jumlah   = intval($_POST['jumlah']);
        if ($barangId > 0 && $jumlah > 0) {
            $stmt = $conn->prepare("SELECT id, nama, harga, stok FROM barang WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $barangId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $barang = $res->fetch_assoc();
                if ($jumlah <= $barang['stok']) {
                    $exists = false;
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['id'] == $barangId) {
                            $item['jumlah'] += $jumlah;
                            $item['subtotal'] = $item['jumlah'] * $barang['harga'];
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $_SESSION['cart'][] = [
                            'id' => $barang['id'],
                            'nama' => $barang['nama'],
                            'harga' => $barang['harga'],
                            'jumlah' => $jumlah,
                            'subtotal' => $jumlah * $barang['harga']
                        ];
                    }
                    $messages[] = "Berhasil menambahkan ke keranjang.";
                } else {
                    $messages[] = "Stok tidak mencukupi. Stok: {$barang['stok']}.";
                }
            } else {
                $messages[] = "Barang tidak ditemukan.";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['pay'])) {
        if (!empty($_SESSION['cart'])) {
            $total = 0;
            foreach ($_SESSION['cart'] as $item) $total += $item['subtotal'];

            $member_id = null;
            $is_member = 0;
            $diskon_nominal = 0;
            $diskon_persen = 0;

            if (!empty($_SESSION['selected_member']) && isset($_SESSION['selected_member']['id'])) {
                $member_id = intval($_SESSION['selected_member']['id']);
                $is_member = 1;
                $diskon_persen = floatval($_SESSION['selected_member']['diskon_member']);
                $diskon_nominal = round($total * ($diskon_persen / 100), 2);
            }

            $total_akhir = $total - $diskon_nominal;

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO transaksi (kasir_id, member_id, is_member, total, diskon, total_akhir, tanggal) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiiddd", $userId, $member_id_param, $is_member_param, $total_param, $diskon_param, $total_akhir_param);
                $member_id_param = $member_id ?? null;
                $is_member_param = $is_member;
                $total_param = $total;
                $diskon_param = $diskon_nominal;
                $total_akhir_param = $total_akhir;
                if (!$stmt->execute()) throw new Exception("Gagal simpan transaksi: " . $stmt->error);
                $transId = $conn->insert_id;
                $stmt->close();

                $stmtDetail = $conn->prepare("INSERT INTO detail_transaksi (transaksi_id, barang_id, jumlah, subtotal) VALUES (?, ?, ?, ?)");
                $stmtUpdateStok = $conn->prepare("UPDATE barang SET stok = stok - ? WHERE id = ? AND stok >= ?");

                foreach ($_SESSION['cart'] as $item) {
                    $harga_asli = $item['subtotal'];
                    $subtotal_akhir = $harga_asli;

                    if ($is_member && $diskon_persen > 0) {
                        $subtotal_akhir = $harga_asli - ($harga_asli * ($diskon_persen / 100));
                    }

                    $stmtDetail->bind_param("iiid", $transId, $item['id'], $item['jumlah'], $subtotal_akhir);
                    if (!$stmtDetail->execute()) throw new Exception("Gagal simpan detail: " . $stmtDetail->error);

                    $stmtUpdateStok->bind_param("iii", $item['jumlah'], $item['id'], $item['jumlah']);
                    if (!$stmtUpdateStok->execute()) throw new Exception("Gagal update stok: " . $stmtUpdateStok->error);
                    if ($stmtUpdateStok->affected_rows === 0) throw new Exception("Stok tidak cukup untuk barang ID {$item['id']}");
                }

                $stmtDetail->close();
                $stmtUpdateStok->close();

                if ($is_member && $member_id !== null) {
                    $stmtMemberUp = $conn->prepare("UPDATE members SET total_transaksi = total_transaksi + 1, total_spent = total_spent + ?, last_transaction = NOW() WHERE id = ?");
                    $stmtMemberUp->bind_param("di", $total_akhir, $member_id);
                    if (!$stmtMemberUp->execute()) throw new Exception("Gagal update member: " . $stmtMemberUp->error);
                    $stmtMemberUp->close();
                }

                $conn->commit();
                $_SESSION['cart'] = [];
                $_SESSION['selected_member'] = null;

                $messages[] = "Pembayaran berhasil. Total Akhir: Rp " . number_format($total_akhir, 0, ",", ".");
            } catch (Exception $e) {
                $conn->rollback();
                $messages[] = "Error pembayaran: " . $e->getMessage();
            }
        } else {
            $messages[] = "Keranjang kosong.";
        }
    }


    $_SESSION['flash_messages'] = $messages;
    header("Location: dashboardKasir.php#transaksi");
    exit();
}

if (isset($_SESSION['flash_messages'])) {
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
}

$barangResult = $conn->query("SELECT id, nama, harga, stok FROM barang ORDER BY nama ASC");
$riwayatResult = $conn->query("
    SELECT t.id AS transaksi_id, t.tanggal, dt.barang_id, b.nama AS barang_nama, dt.jumlah, dt.subtotal, t.diskon, t.total_akhir
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN barang b ON dt.barang_id = b.id
    WHERE t.kasir_id = $userId
    ORDER BY t.tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Kasir</title>
    <link rel="stylesheet" href="styles/kasirStyle.css">
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="logo">
                <h2>Kasir<span>App</span></h2>
            </div>
            <nav class="menu">
                <ul>
                    <li><a href="#transaksi">Transaksi Penjualan</a></li>
                    <li><a href="#stok">Stok Barang</a></li>
                    <li><a href="#riwayat">Riwayat Transaksi</a></li>
                    <li><a href="logout.php" class="logout">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar">
                <h1>Dashboard Kasir</h1>
                <p>Halo, <b><?= htmlspecialchars($username) ?></b></p>
            </header>

            <?php if (!empty($messages)): ?>
                <div class="messages">
                    <?php foreach ($messages as $m): ?>
                        <div class="msg"><?= htmlspecialchars($m) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section id="transaksi" class="section">
                <header class="section-header">
                    <h2>Transaksi Penjualan</h2>
                </header>
                <div class="section-body">
                    <form class="form-transaction" method="POST" action="">
                        <select name="barang_id" required>
                            <option value="">Pilih Barang</option>
                            <?php while ($row = $barangResult->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama']) ?> - Rp <?= number_format($row['harga'], 0, ",", ".") ?> (Stok: <?= $row['stok'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="jumlah" placeholder="Jumlah" min="1" required>
                        <button type="submit" class="btn">Tambah ke Keranjang</button>
                    </form>
                   
                    <div class="cart">
                        <h3>Keranjang Belanja</h3>
                         <div class="member-box">
                        <form method="POST" action="" style="display:flex; gap:8px; align-items:center;">
                            <input type="text" name="member_search" placeholder="Masukkan ID/Nama Member" value="<?= isset($_SESSION['selected_member']['nama']) ? htmlspecialchars($_SESSION['selected_member']['nama']) : '' ?>">
                            <button type="submit" name="check_member" class="btn">Cek Member</button>
                            <?php if (!empty($_SESSION['selected_member'])): ?>
                                <button type="submit" name="remove_member" class="btn" style="background: #ff6b6b;">Batalkan</button>
                                <style>
                                    button[name="remove_member"]:hover {
                                        background: #e55a5a !important;
                                    }
                                </style>

                            <?php endif; ?>
                            <a href="../public/register_user/addMember.php" class="btn">Tambah Member Baru</a>
                        </form>
                    </div>
                    <?php if (!empty($_SESSION['selected_member'])): ?>
                        <div class="member-info">
                            <strong>Member:</strong> <?= htmlspecialchars($_SESSION['selected_member']['nama']) ?> —
                            <span class="small">Level: <?= htmlspecialchars($_SESSION['selected_member']['level']) ?> (Diskon: <?= htmlspecialchars($_SESSION['selected_member']['diskon_member']) ?>%)</span>
                        </div>
                    <?php endif; ?>

                       <ul>
                            <?php
                            $subtotal_sum = 0;
                            foreach ($_SESSION['cart'] as $item):
                                $subtotal_sum += $item['subtotal'];
                            ?>
                                <li>
                                    <div><strong><?= htmlspecialchars($item['nama']) ?></strong> x <?= $item['jumlah'] ?></div>
                                    <div>
                                        Rp <?= number_format($item['subtotal'], 0, ",", ".") ?>

                                        <form method="POST" class="delete-btn" style="display:inline;">
                                            <button type="submit" name="remove_item" value="<?= $item['id'] ?>">Hapus</button>
                                            <style>
                                                .delete-btn button {
                                                    background-color: #ff6b6b;
                                                    color: #fff;
                                                    border: none;
                                                    padding: 10px 20px;
                                                    border-radius: 6px;
                                                    cursor: pointer;
                                                    font-weight: 600;
                                                    transition: 0.3s;
                                                }

                                                .delete-btn button:hover {
                                                    background-color: #e55a5a;
                                                }
                                            </style>
                                        </form>

                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>


                        <?php if (!empty($_SESSION['cart'])): ?>
                            <form method="POST">
                                <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div class="small">Total</div>
                                        <div style="font-weight:700;">Rp <?= number_format($subtotal_sum, 0, ",", ".") ?></div>
                                        <?php
                                        $diskon_to_show = 0;
                                        if (!empty($_SESSION['selected_member']) && isset($_SESSION['selected_member']['diskon_member'])) {
                                            $diskon_persen_show = floatval($_SESSION['selected_member']['diskon_member']);
                                            $diskon_to_show = round($subtotal_sum * ($diskon_persen_show / 100), 2);
                                            echo '<div class="small">Diskon Member (' . $diskon_persen_show . '%): - Rp ' . number_format($diskon_to_show, 0, ",", ".") . '</div>';
                                            echo '<div class="small" style="margin-top:6px;">Total Setelah Diskon: <strong>Rp ' . number_format($subtotal_sum - $diskon_to_show, 0, ",", ".") . '</strong></div>';
                                        }
                                        ?>
                                    </div>
                                    <button type="submit" name="pay" class="btn-pay">Proses Pembayaran</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="stok" class="section">
                <header class="section-header">
                    <h2>Stok Barang</h2>
                </header>
                <div class="section-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $barangResult = $conn->query("SELECT id, nama, harga, stok FROM barang ORDER BY nama ASC");
                            while ($row = $barangResult->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ",", ".") ?></td>
                                    <td><?= $row['stok'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="riwayat" class="section">
                <header class="section-header">
                    <h2>Riwayat Transaksi</h2>
                </header>
                <div class="section-body">
                    <ul>
                        <?php while ($trans = $riwayatResult->fetch_assoc()): ?>
                            <li><?= date("d/m/Y H:i", strtotime($trans['tanggal'])) ?> - <?= htmlspecialchars($trans['barang_nama']) ?> (<?= $trans['jumlah'] ?>) - Rp <?= number_format($trans['subtotal'], 0, ",", ".") ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btnOpenModal = document.querySelector(".btn-open-modal");
            const modal = document.getElementById("modal-member");
            const btnCloseModal = document.getElementById("btn-close-modal");

            if (btnOpenModal) btnOpenModal.addEventListener("click", () => modal.style.display = "block");
            if (btnCloseModal) btnCloseModal.addEventListener("click", () => modal.style.display = "none");
            window.addEventListener("click", e => {
                if (e.target == modal) modal.style.display = "none";
            });
        });
    </script>
</body>

</html>