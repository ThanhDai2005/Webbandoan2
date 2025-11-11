<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/font-awesome-pro-v6-6.2.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/base.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>Chi tiết đơn hàng - Đặc sản 3 miền</title>
  <link href="./assets/img/logo.png" rel="icon" type="image/x-icon" />
  <style>
    .inner-tt {
      font-size: 26px;
      font-weight: 700;
      color: #333;
    }

    .inner-vc {
      color: #666;
      font-size: 15px;
    }

    .inner-trangthai {
      background: #f8f9fa;
      padding: 12px;
      border-radius: 8px;
      margin: 15px 0;
    }

    .inner-diachi {
      background: #fff;
      border: 1px solid #eee;
      border-radius: 8px;
      padding: 15px;
    }

    .inner-ten {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
    }

    .inner-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px dashed #ddd;
    }

    .inner-info {
      display: flex;
      align-items: center;
    }

    .inner-img img {
      border-radius: 8px;
      object-fit: cover;
    }

    .inner-chu {
      margin-left: 12px;
    }

    .inner-sl {
      color: #666;
      font-size: 14px;
    }

    .inner-tonggia {
      margin-top: 20px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .inner-tien,
    .inner-vanchuyen,
    .inner-total {
      display: flex;
      justify-content: space-between;
      margin: 8px 0;
    }

    .inner-total {
      font-size: 18px;
      font-weight: 700;
      color: #d32f2f;
    }
  </style>
</head>

<body>

  <?php include_once "includes/headerlogin.php"; ?>

  <div class="chitiet">
    <div class="container">
      <?php
      include "connect.php";
      $donhang = null;
      $sanphams = [];
      $tienHang = 0;
      $soLuongMon = 0;

      if (!isset($_SESSION['makh'])) {
        echo '<div class="alert alert-danger text-center">Vui lòng đăng nhập!</div>';
      } elseif (!isset($_GET['madh']) || !is_numeric($_GET['madh'])) {
        echo '<div class="alert alert-danger text-center">Đơn hàng không hợp lệ!</div>';
      } else {
        $makh = $_SESSION['makh'];
        $madh = (int) $_GET['madh'];

        // LẤY THÔNG TIN ĐƠN HÀNG
        $sql = "SELECT dh.*, kh.TEN_KH, kh.SO_DIEN_THOAI 
                    FROM donhang dh 
                    JOIN khachhang kh ON dh.MA_KH = kh.MA_KH 
                    WHERE dh.MA_KH = ? AND dh.MA_DH = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt)
          die("Lỗi SQL: " . $conn->error);
        $stmt->bind_param("ii", $makh, $madh);
        $stmt->execute();
        $result = $stmt->get_result();
        $donhang = $result->fetch_assoc();
        $stmt->close();

        if (!$donhang) {
          echo '<div class="alert alert-danger text-center">Không tìm thấy đơn hàng!</div>';
        } else {
          // PARSE DANH SÁCH SẢN PHẨM TỪ GHI_CHU (định dạng "MA_SP:SL:GIA,MA_SP:SL:GIA")
          $ghichu = trim($donhang['GHI_CHU']);
          $items = [];
          if (!empty($ghichu)) {
            $parts = explode(',', $ghichu);
            foreach ($parts as $part) {
              $data = explode(':', trim($part));
              if (count($data) === 3 && is_numeric($data[0]) && is_numeric($data[1]) && is_numeric($data[2])) {
                $items[] = [
                  'MA_SP' => (int) $data[0],
                  'SO_LUONG' => (int) $data[1],
                  'GIA_CA' => (int) $data[2]
                ];
              }
            }
          }

          // QUERY SANPHAM ĐỂ LẤY TEN_SP, HINH_ANH
          if (!empty($items)) {
            $ma_sp_list = array_column($items, 'MA_SP');
            $ma_sp_str = implode(',', $ma_sp_list);
            $sql = "SELECT MA_SP, TEN_SP, HINH_ANH FROM sanpham WHERE MA_SP IN ($ma_sp_str)";
            $result = $conn->query($sql);
            $sp_info = [];
            while ($row = $result->fetch_assoc()) {
              $sp_info[$row['MA_SP']] = $row;
            }

            foreach ($items as $item) {
              $info = $sp_info[$item['MA_SP']] ?? ['TEN_SP' => 'Sản phẩm không tồn tại', 'HINH_ANH' => 'assets/img/no-image.jpg'];
              $thanh_tien = $item['SO_LUONG'] * $item['GIA_CA'];
              $sanphams[] = [
                'HINH_ANH' => $info['HINH_ANH'],
                'TEN_SP' => $info['TEN_SP'],
                'SO_LUONG' => $item['SO_LUONG'],
                'GIA_CA' => $item['GIA_CA'],
                'THANH_TIEN' => $thanh_tien
              ];
              $tienHang += $thanh_tien;
              $soLuongMon += $item['SO_LUONG'];
            }
          }
        }
      }
      ?>

      <?php if ($donhang): ?>
        <div class="inner-chitiet">
          <div class="inner-tt">Chi tiết đơn hàng DH<?= sprintf("%04d", $donhang['MA_DH']) ?></div>
          <div class="inner-vc">Ngày đặt: <?= date("d-m-Y H:i:s", strtotime($donhang['NGAY_TAO'])) ?></div>
        </div>

        <div class="inner-trangthai">
          <div class="inner-ct">
            Trạng thái: <strong class="text-primary"><?= htmlspecialchars($donhang['TINH_TRANG']) ?></strong>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6">
            <div class="inner-diachi">
              <div class="inner-ten">ĐỊA CHỈ GIAO HÀNG</div>
              <div class="inner-gth">
                <div class="inner-ten"><?= htmlspecialchars($donhang['TEN_KH']) ?></div>
                <div class="inner-dc">Địa chỉ: <?= htmlspecialchars($donhang['DIA_CHI']) ?></div>
                <div class="inner-sdt">SĐT: <?= htmlspecialchars($donhang['SO_DIEN_THOAI']) ?></div>
              </div>
            </div>
          </div>
          <div class="col-xl-3">
            <div class="inner-diachi">
              <div class="inner-ten">THANH TOÁN</div>
              <div class="inner-gth">
                <div class="inner-tt"><?= htmlspecialchars($donhang['PHUONG_THUC']) ?></div>
              </div>
            </div>
          </div>
          <div class="col-xl-3">
            <div class="inner-diachi">
              <div class="inner-ten">GHI CHÚ</div>
              <div class="inner-gth">
                <div class="inner-ghichu">
                  <?= empty($donhang['GHI_CHU']) ? 'Không có' : htmlspecialchars($donhang['GHI_CHU']) ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="inner-menu">
          <?php foreach ($sanphams as $sp): ?>
            <div class="inner-item">
              <div class="inner-info">
                <div class="inner-img">
                  <img src="<?= htmlspecialchars($sp['HINH_ANH']) ?>" width="80" height="80"
                    onerror="this.src='assets/img/no-image.jpg'" />
                </div>
                <div class="inner-chu">
                  <div class="inner-ten"><?= htmlspecialchars($sp['TEN_SP']) ?></div>
                  <div class="inner-sl">x<?= $sp['SO_LUONG'] ?> (<?= number_format($sp['GIA_CA'], 0, ',', '.') ?>₫)</div>
                </div>
              </div>
              <div class="inner-gia"><?= number_format($sp['THANH_TIEN'], 0, ',', '.') ?>₫</div>
            </div>
          <?php endforeach; ?>

          <?php if (empty($sanphams)): ?>
            <div class="text-center text-muted py-4">
              <i class="fas fa-box-open fa-3x mb-3"></i>
              <p>Không có sản phẩm nào trong đơn hàng (hoặc dữ liệu cũ).</p>
            </div>
          <?php endif; ?>

          <div class="inner-tonggia">
            <div class="inner-tien">
              <div class="inner-th">Tiền hàng <span><?= $soLuongMon ?> món</span></div>
              <div class="inner-st"><?= number_format($tienHang, 0, ',', '.') ?>₫</div>
            </div>
            <div class="inner-vanchuyen">
              <span class="inner-vc1">Vận chuyển</span>
              <span class="inner-vc2">0₫</span>
            </div>
            <div class="inner-total">
              <span class="inner-tong1">Tổng tiền:</span>
              <span class="inner-tong2"><?= number_format($donhang['TONG_TIEN'], 0, ',', '.') ?>₫</span>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php include_once "includes/footer.php"; ?>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>