<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
    integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />

  <link rel="stylesheet" href="assets/font-awesome-pro-v6-6.2.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/base.css" />
  <link rel="stylesheet" href="assets/css/style.css" />

  <title>Đặc sản 3 miền</title>
  <link href="./assets/img/logo.png" rel="icon" type="image/x-icon" />
</head>

<body>
  <?php
  include_once "includes/headerlogin.php";
  ?>

  <!-- ChiTiet -->

  <div class="chitiet">
    <?php
    include "connect.php";

    if (isset($_SESSION['makh']) && isset($_GET['madh'])) {
      $makh = $_SESSION['makh'];
      $madh = $_GET['madh'];

      // 1. Lấy thông tin đơn hàng + khách hàng
      $sql = "SELECT dh.*, kh.tenkh, kh.sodienthoai 
            FROM donhang dh 
            JOIN khachhang kh ON dh.makh = kh.makh 
            WHERE dh.makh = ? AND dh.madh = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ii", $makh, $madh);
      $stmt->execute();
      $donhang = $stmt->get_result()->fetch_assoc();

      // 2. Lấy danh sách sản phẩm trong đơn hàng
      $sql2 = "SELECT sp.Image, sp.Name, ch.soluong, ch.giabanle, (ch.soluong * ch.giabanle) AS thanhtien 
            FROM chitietdonhang ch 
            JOIN sanpham sp ON ch.masp = sp.ID 
            WHERE ch.madh = ?";
      $stmt2 = $conn->prepare($sql2);
      $stmt2->bind_param("i", $madh);
      $stmt2->execute();
      $result_sp = $stmt2->get_result();

      $tienHang = 0;
      $soLuongMon = 0;
      $vanChuyen = 0;
      $sanphams = [];

      while ($sp = $result_sp->fetch_assoc()) {
        $tienHang += $sp['thanhtien'];
        $soLuongMon += $sp['soluong'];
        $sanphams[] = $sp;
      }

      $tongTien = $tienHang + $vanChuyen;
    }
    ?>


    <div class="container">
      <div class="inner-chitiet">
        <div class="inner-tt">Chi tiết đơn hàng DH<?= $donhang['madh'] ?></div>
        <div class="inner-vc">Ngày đặt hàng: <?= date("d-m-Y", strtotime($donhang['ngaytao'])) ?></div>
      </div>

      <div class="inner-trangthai">
        <div class="inner-ct">
          Trạng thái thanh toán: <i><?= htmlspecialchars($donhang['trangthai']) ?></i>
        </div>
      </div>

      <div class="row">
        <div class="col-xl-6">
          <div class="inner-diachi">
            <div class="inner-ten">ĐỊA CHỈ GIAO HÀNG</div>
            <div class="inner-gth">
              <div class="inner-ten"><?= htmlspecialchars($donhang['tenkh']) ?></div>
              <div class="inner-dc">Địa chỉ: <?= htmlspecialchars($donhang['diachi']) ?></div>
              <div class="inner-sdt">Số điện thoại: <?= htmlspecialchars($donhang['sodienthoai'] ?? 'Không có') ?></div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
          <div class="inner-diachi">
            <div class="inner-ten">THANH TOÁN</div>
            <div class="inner-gth">
              <div class="inner-tt"><?= htmlspecialchars($donhang['phuongthuc']); ?> </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
          <div class="inner-diachi">
            <div class="inner-ten">GHI CHÚ</div>
            <div class="inner-gth">
              <div class="inner-ghichu"><?= htmlspecialchars($donhang['ghichu']); ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sản phẩm -->
      <div class="inner-menu">
        <?php foreach ($sanphams as $sp): ?>
          <div class="inner-item">
            <div class="inner-info">
              <div class="inner-img">
                <img src="<?= htmlspecialchars($sp['Image']) ?>" width="80px" height="80px" />
              </div>
              <div class="inner-chu">
                <div class="inner-ten"><?= htmlspecialchars($sp['Name']) ?></div>
                <div class="inner-sl">x<?= $sp['soluong'] ?></div>
              </div>
            </div>
            <div class="inner-gia"><?= number_format($sp['thanhtien'], 0, ',', '.') ?>₫</div>
          </div>
        <?php endforeach; ?>

        <div class="inner-tonggia">
          <div class="inner-tien">
            <div class="inner-th">Tiền hàng <span><?= $soLuongMon ?> món</span></div>
            <div class="inner-st"><?= number_format($tienHang, 0, ',', '.') ?>₫</div>
          </div>
          <div class="inner-vanchuyen">
            <span class="inner-vc1">Vận chuyển</span>
            <span class="inner-vc2"><?= number_format($vanChuyen, 0, ',', '.') ?>₫</span>
          </div>
          <div class="inner-total">
            <span class="inner-tong1">Tổng tiền:</span>
            <span class="inner-tong2"><?= number_format($tongTien, 0, ',', '.') ?>₫</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- End ChiTiet -->

  <?php
  include_once "includes/footer.php";
  ?>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
    integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
    crossorigin="anonymous"></script>

  <script src="assets/js/main.js"></script>
</body>

</html>