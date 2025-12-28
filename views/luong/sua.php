<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>✏️ Sửa Lương Nhân Viên</title>
<link rel="stylesheet" href="style.css">
<script>
function tinhTongLuong() {
    let luongcb = parseFloat(document.getElementById('luongcb').value) || 0;
    let phucap = parseFloat(document.getElementById('phucap').value) || 0;
    let thuong = parseFloat(document.getElementById('thuong').value) || 0;
    let khautru = parseFloat(document.getElementById('khautru').value) || 0;
    let tong = luongcb + phucap + thuong - khautru;
    document.getElementById('tongluong').value = tong.toLocaleString('vi-VN');

}
function laySoNgayLam() {
    let manv = document.getElementById('manv').value;
    let thang = document.getElementById('thang').value;
    if (!manv || !thang) return;

    fetch(`index.php?controller=chamcong&action=getSoNgayLam&manv=${manv}&thang=${thang}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('soNgayLam').value = data.SoNgayLam || 0;
            tinhTongLuong();
        })
        .catch(err => console.error(err));
}
</script>
</head>
<body>
<div class="container">
<nav class="sidebar">
  <h2>QUẢN LÝ NHÂN SỰ</h2>
  <ul>
    <li><a href="index.php?controller=home&action=index" class="active">🏠 Trang chủ</a></li>
        <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
        <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
        <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
        <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
        <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
        <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
        <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
        <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
        <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
        <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
        <li><a href="index.php?controller=tuyendung&action=index">💼 Quản lý tuyển dụng</a></li>
        <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
        <li><a href="index.php?controller=phanquyen&action=index">🗂 Quản lý đăng nhập – phân quyền</a></li>
        <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
        <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
  </ul>
</nav>

<main class="main-content">
<header><h1>✏️ Sửa Lương Nhân Viên</h1></header>

<form action="index.php?controller=luong&action=update" method="POST" class="form-nv">
    <input type="hidden" name="maluong" value="<?= $luong['MaLuong'] ?>">

    <div class="form-group">
    <label for="manv">Chọn nhân viên:</label>
  <select id="manv" name="manv" onchange="layLuongCoBan(); layThuongKyLuat()" required>
    <option value="">-- Chọn nhân viên --</option>
    <?php foreach ($dsNV as $nv): ?>
        <option value="<?= $nv['MaNV'] ?>"><?= $nv['HoTen'] ?></option>
    <?php endforeach; ?>
</select>


</div>

<div class="form-group">
    <label for="thang">Tháng:</label>
    <input type="month" id="thang" name="thang"
           required onchange="laySoNgayLam(); layThuongKyLuat()">
</div>


<div class="form-group">
    <label>Lương cơ bản:</label>
   <input type="number" id="luongcb" name="luongcb" readonly>


</div>


<div class="form-group">
    <label for="phucap">Phụ cấp (VNĐ):</label>
   <input type="number" id="phucap" name="phucap" oninput="tinhTongLuong()">


</div>

<div class="form-group">
    <label for="thuong">Thưởng (VNĐ):</label>
   <input type="number" id="thuong" name="thuong" readonly>


</div>

<div class="form-group">
    <label for="soNgayLam">Số ngày làm thực tế:</label>
    <input type="number" id="soNgayLam" name="soNgayLam" readonly>
</div>

<div class="form-group">
    <label for="kyluat">Kỷ luật (VNĐ):</label>
  <input type="number" id="kyluat" name="kyluat" readonly>
</div>


<div class="form-group">
    <label for="khautru">Khấu trừ (VNĐ):</label>
   <input type="number" id="khautru" readonly>
</div>


    <div class="form-group">
        <label>Tổng lương:</label>
        <input type="text" id="tongluong" disabled
               value="<?= number_format(
                    $luong['LuongCB'] + $luong['PhuCap'] + $luong['Thuong']
                    - $luong['KyLuat'] - $luong['KhauTru'],
                    0, ',', '.'
               ) ?>">
    </div>


    <div class="form-buttons">
        <button type="submit" class="btn edit">💾 Cập nhật</button>
        <a href="index.php?controller=luong&action=index" class="btn cancel">↩️ Quay lại</a>
    </div>
</form>
</main>
</div>


<script src="public/js/luong.js"></script>
</body>
</html>
