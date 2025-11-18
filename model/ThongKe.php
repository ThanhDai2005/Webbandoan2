<?php

class ThongKe {
    private $pdo; // Kết nối PDO đến DB

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Tính tổng doanh thu
    public function tongDoanhThu($startDate = null, $endDate = null) {
        $sql = "SELECT SUM(TONG_TIEN) AS total FROM donhang WHERE TINH_TRANG = 'Đã giao thành công'";
        $params = [];
        if ($startDate) {
            $sql .= " AND NGAY_TAO >= :start";
            $params[':start'] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND NGAY_TAO <= :end";
            $params[':end'] = $endDate;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    // Số lượng khách hàng
    public function soLuongKhachHang() {
        $sql = "SELECT COUNT(*) AS total FROM khachhang";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    // Thống kê đơn hàng theo trạng thái
    public function thongKeDonHang($tinhTrang) {
        $sql = "SELECT COUNT(*) AS total FROM donhang WHERE TINH_TRANG = :tinhTrang";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tinhTrang', $tinhTrang);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }
}