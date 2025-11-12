<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>➕ Thêm Lương Nhân Viên</title>
<link rel="stylesheet" href="style.css">
<script>
function tinhTongLuong() {
    let luongcb = parseFloat(document.getElementById('luongcb').value) || 0;
    let phucap = parseFloat(document.getElementById('phucap').value) || 0;
    let thuong = parseFloat(document.getElementById('thuong').value) || 0;
    let soNgayLam = parseInt(document.getElementById('soNgayLam').value) || 0;
    let khautru = 0;

    const ngayChuan = 26; // Số ngày chuẩn/tháng
    khautru = ((ngayChuan - soNgayLam) / ngayChuan) * luongcb;

    let tong = luongcb + phucap + thuong - khautru;
    document.getElementById('khautru').value = Math.round(khautru);
    document.getElementById('tongluong').value = Math.round(tong).toLocaleString('vi-VN');
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
    <li><a href="index.php">🏠 Trang chủ</a></li>
    <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
    <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
    <li><a href="index.php?controller=luong&action=index" class="active">💰 Quản lý lương</a></li>
    <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
  </ul>
</nav>

<main class="main-content">
<header><h1>➕ Thêm Lương Nhân Viên</h1></header>

<form action="index.php?controller=luong&action=store" method="POST" class="form-nv">
<div class="form-group">
    <label for="maluong">Mã lương:</label>
    <input type="text" id="maluong" name="maluong" placeholder="Nhập mã lương..." required>
</div>

<div class="form-group">
    <label for="manv">Chọn nhân viên:</label>
    <select id="manv" name="manv" required onchange="laySoNgayLam()">
        <option value="">-- Chọn nhân viên --</option>
        <?php foreach ($dsNV as $nv): ?>
            <option value="<?= $nv['MaNV'] ?>"><?= $nv['HoTen'] ?> (<?= $nv['MaNV'] ?>)</option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="thang">Tháng:</label>
    <input type="month" id="thang" name="thang" required onchange="laySoNgayLam()">
</div>

<div class="form-group">
    <label for="luongcb">Lương cơ bản (VNĐ):</label>
    <input type="number" id="luongcb" name="luongcb" min="0" oninput="tinhTongLuong()" required>
</div>

<div class="form-group">
    <label for="phucap">Phụ cấp (VNĐ):</label>
    <input type="number" id="phucap" name="phucap" min="0" oninput="tinhTongLuong()">
</div>

<div class="form-group">
    <label for="thuong">Thưởng (VNĐ):</label>
    <input type="number" id="thuong" name="thuong" min="0" oninput="tinhTongLuong()">
</div>

<div class="form-group">
    <label for="soNgayLam">Số ngày làm thực tế:</label>
    <input type="number" id="soNgayLam" name="soNgayLam" readonly>
</div>

<div class="form-group">
    <label for="khautru">Khấu trừ (VNĐ):</label>
    <input type="number" id="khautru" name="khautru" readonly>
</div>

<div class="form-group">
    <label for="tongluong">Tổng lương (VNĐ):</label>
    <input type="text" id="tongluong" disabled>
</div>

<div class="form-buttons">
    <button type="submit" class="btn add">💾 Lưu</button>
    <a href="index.php?controller=luong&action=index" class="btn cancel">↩️ Quay lại</a>
</div>
</form>
</main>
</div>


</body>
</html>
