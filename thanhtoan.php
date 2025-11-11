<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/font-awesome-pro-v6-6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <title>Thanh toán - Đặc sản 3 miền</title>
    <link href="./assets/img/logo.png" rel="icon" type="image/x-icon" />
    <style>
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 20px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            opacity: 0;
            transition: all 0.4s;
            min-width: 320px;
        }

        .notification.show {
            opacity: 1;
        }

        .notification-icon i {
            color: #28a745;
            font-size: 28px;
            margin-right: 12px;
        }

        .notification-title {
            font-weight: bold;
            color: #333;
        }

        .notification-message {
            color: #666;
            font-size: 14px;
        }

        .notification-close {
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 10px;
        }

        .notification-close i {
            color: #aaa;
        }

        .notification-close:hover i {
            color: #333;
        }

        .button {
            background: var(--color-bg2);
            color: #fff;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
        }

        .button:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div id="success-notification" class="notification">
        <div class="notification-icon"><i class="fas fa-check-circle"></i></div>
        <div class="notification-content">
            <div class="notification-title">Thành công!</div>
            <div class="notification-message">Đơn hàng đã được tạo. Cảm ơn bạn!</div>
        </div>
        <button class="notification-close" onclick="closeNotification()"><i class="fas fa-times"></i></button>
    </div>

    <?php include "includes/headerlogin.php"; ?>
    <div class="ThongTin">
        <div class="container">
            <form action="" method="post">
                <div class="row">
                    <div class="col-xl-7 col-lg-7">
                        <div class="inner-item">
                            <div class="inner-tt">Giỏ hàng của bạn</div>
                            <?php
                            include "connect.php";
                            $tong_tien = 0;
                            if (!isset($_SESSION['makh'])) {
                                echo '<p class="text-danger">Vui lòng đăng nhập!</p>';
                            } else {
                                $makh = $_SESSION['makh'];
                                $sql = "
                                SELECT ct.SO_LUONG, sp.TEN_SP, sp.HINH_ANH, sp.GIA_CA,
                                       (ct.SO_LUONG * sp.GIA_CA) AS THANH_TIEN
                                FROM giohang gh
                                JOIN chitietgiohang ct ON gh.MA_GH = ct.MA_GH
                                JOIN sanpham sp ON ct.MA_SP = sp.MA_SP
                                WHERE gh.MA_KH = ?
                            ";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt)
                                    die("Lỗi SQL giỏ hàng: " . $conn->error);
                                $stmt->bind_param("i", $makh);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows == 0) {
                                    echo '<p>Giỏ hàng trống.</p>';
                                } else {
                                    while ($row = $result->fetch_assoc()) {
                                        $tong_tien += $row['THANH_TIEN'];
                                        echo '<div class="inner-gth">
                                        <div class="inner-img"><img src="' . htmlspecialchars($row['HINH_ANH']) . '"/></div>
                                        <div class="inner-mota">
                                            <div class="inner-ten">' . htmlspecialchars($row['TEN_SP']) . '</div>
                                            <div class="inner-sl">Số lượng: ' . $row['SO_LUONG'] . '</div>
                                            <div class="inner-gia">' . number_format($row['THANH_TIEN'], 0, ',', '.') . 'đ</div>
                                        </div>
                                    </div>';
                                    }
                                }
                                $stmt->close();
                            }
                            ?>
                        </div>

                        <div class="inner-item">
                            <div class="inner-tt">Thông tin giao hàng</div>
                            <?php if (isset($_SESSION['makh'])) {
                                $sql = "SELECT TEN_KH, DIA_CHI, SO_DIEN_THOAI FROM khachhang WHERE MA_KH = ?";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt)
                                    die("Lỗi SQL khách hàng: " . $conn->error);
                                $stmt->bind_param("i", $makh);
                                $stmt->execute();
                                $kh = $stmt->get_result()->fetch_assoc();
                                ?>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group"><label>Họ tên</label><input type="text" class="form-control"
                                                value="<?= htmlspecialchars($kh['TEN_KH']) ?>" readonly></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group"><label>Số điện thoại</label><input type="text"
                                                class="form-control" value="<?= htmlspecialchars($kh['SO_DIEN_THOAI']) ?>"
                                                readonly></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group"><label>Địa chỉ giao hàng</label><input type="text"
                                                name="diachi" class="form-control"
                                                value="<?= htmlspecialchars($kh['DIA_CHI']) ?>" required></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group"><label>Ghi chú</label><textarea name="ghichu"
                                                class="form-control" rows="3"></textarea></div>
                                    </div>
                                </div>
                                <?php $stmt->close();
                            } ?>
                        </div>

                        <div class="inner-item">
                            <div class="inner-tt">Phương thức thanh toán</div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="pttt"
                                    value="Tiền mặt" checked required><label>Thanh toán khi nhận hàng (COD)</label>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="pttt"
                                    value="Chuyển khoản"><label>Chuyển khoản ngân hàng</label></div>
                        </div>
                    </div>

                    <div class="col-xl-5 col-lg-5">
                        <div class="inner-item">
                            <div class="inner-tien">
                                <div class="inner-th">Tiền hàng</div>
                                <div class="inner-st"><?= number_format($tong_tien, 0, ',', '.') ?>đ</div>
                            </div>
                            <div class="inner-tien">
                                <div class="inner-pvc">Phí vận chuyển</div>
                                <div class="inner-st">Miễn phí</div>
                            </div>
                            <div class="inner-tientong">
                                <div class="inner-tong">Tổng cộng</div>
                                <div class="inner-total"><?= number_format($tong_tien, 0, ',', '.') ?>đ</div>
                            </div>
                            <input type="hidden" name="tongtien" value="<?= $tong_tien ?>">
                            <button type="submit" name="thanhtoan" class="button">HOÀN TẤT ĐẶT HÀNG</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- XỬ LÝ ĐẶT HÀNG -->
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thanhtoan'])) {
        if (!isset($_SESSION['makh'])) {
            echo '<script>alert("Vui lòng đăng nhập!"); window.location="login.php";</script>';
            exit;
        }

        $makh = $_SESSION['makh'];
        $diachi = $conn->real_escape_string(trim($_POST['diachi']));
        $ghichu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));
        $pttt = $_POST['pttt'];

        // BƯỚC 1: TÍNH TỔNG TIỀN + LẤY MA_GH
        $sql = "SELECT gh.MA_GH, COALESCE(SUM(ct.SO_LUONG * sp.GIA_CA), 0) AS tong
                FROM giohang gh
                LEFT JOIN chitietgiohang ct ON gh.MA_GH = ct.MA_GH
                LEFT JOIN sanpham sp ON ct.MA_SP = sp.MA_SP
                WHERE gh.MA_KH = ?
                GROUP BY gh.MA_GH";
        $stmt = $conn->prepare($sql);
        if (!$stmt)
            die("LỖI SQL: " . $conn->error);
        $stmt->bind_param("i", $makh);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row || $row['tong'] == 0) {
            echo '<script>alert("Giỏ hàng trống!"); history.back();</script>';
            exit;
        }
        $ma_gh = $row['MA_GH'];
        $tong_tien = $row['tong'];
        // Lấy sản phẩm từ giỏ
        $sql = "SELECT ct.MA_SP, ct.SO_LUONG, sp.GIA_CA
        FROM chitietgiohang ct JOIN sanpham sp ON ct.MA_SP = sp.MA_SP
        WHERE ct.MA_GH = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $ma_gh);
        $stmt->execute();
        $result = $stmt->get_result();

        $products_str = [];
        while ($row = $result->fetch_assoc()) {
            $products_str[] = $row['MA_SP'] . ':' . $row['SO_LUONG'] . ':' . $row['GIA_CA'];
        }
        $ghichu = implode(',', $products_str);  // Hoặc thêm user ghi chú: $ghichu_user . '||' . implode(...)
        $stmt->close();

        // Sau đó INSERT với $ghichu này
    
        // BƯỚC 2: TẠO ĐƠN HÀNG
        $sql = "INSERT INTO donhang (MA_KH, TONG_TIEN, DIA_CHI, GHI_CHU, PHUONG_THUC, TINH_TRANG)
                VALUES (?, ?, ?, ?, ?, 'Chưa xác nhận')";
        $stmt = $conn->prepare($sql);
        if (!$stmt)
            die("LỖI INSERT ĐƠN: " . $conn->error);
        $stmt->bind_param("iisss", $makh, $tong_tien, $diachi, $ghichu, $pttt);
        $stmt->execute();
        $stmt->close();

        // BƯỚC 3: DỌN GIỎ HÀNG
        $stmt = $conn->prepare("DELETE FROM chitietgiohang WHERE MA_GH = ?");
        $stmt->bind_param("i", $ma_gh);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE giohang SET TONG_TIEN = 0 WHERE MA_GH = ?");
        $stmt->bind_param("i", $ma_gh);
        $stmt->execute();
        $stmt->close();

        // THÀNH CÔNG
        echo '<script>
            document.getElementById("success-notification").classList.add("show");
            setTimeout(() => { window.location="login.php"; }, 2000);
        </script>';
        exit;
    }
    ?>

    <?php include "includes/footer.php"; ?>
    <script>
        function closeNotification() {
            document.getElementById("success-notification").classList.remove("show");
        }
    </script>
</body>

</html>