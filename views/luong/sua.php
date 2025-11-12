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
    <li><a href="index.php">🏠 Trang chủ</a></li>
    <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
    <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
    <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
    <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
  </ul>
</nav>

<main class="main-content">
<header><h1>✏️ Sửa Lương Nhân Viên</h1></header>

<form action="index.php?controller=luong&action=update" method="POST" class="form-nv">
    <input type="hidden" name="maluong" value="<?= $luong['MaLuong'] ?>">

    <div class="form-group">
        <label for="manv">Nhân viên:</label>
        <select id="manv" name="manv" required>
            <?php foreach ($dsNV as $nv): ?>
                <option value="<?= $nv['MaNV'] ?>" <?= $nv['MaNV'] == $luong['MaNV'] ? 'selected' : '' ?>>
                    <?= $nv['HoTen'] ?> (<?= $nv['MaNV'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="thang">Tháng:</label>
        <input type="month" id="thang" name="thang" value="<?= $luong['Thang'] ?>" required>
    </div>

    <div class="form-group">
        <label for="luongcb">Lương cơ bản:</label>
        <input type="number" id="luongcb" name="luongcb" value="<?= $luong['LuongCB'] ?>" oninput="tinhTongLuong()" required>
    </div>

    <div class="form-group">
        <label for="phucap">Phụ cấp:</label>
        <input type="number" id="phucap" name="phucap" value="<?= $luong['PhuCap'] ?>" oninput="tinhTongLuong()">
    </div>

    <div class="form-group">
        <label for="thuong">Thưởng:</label>
        <input type="number" id="thuong" name="thuong" value="<?= $luong['Thuong'] ?>" oninput="tinhTongLuong()">
    </div>

    <div class="form-group">
        <label for="khautru">Khấu trừ:</label>
        <input type="number" id="khautru" name="khautru" value="<?= $luong['KhauTru'] ?>" oninput="tinhTongLuong()">
    </div>

    <div class="form-group">
        <label for="tongluong">Tổng lương:</label>
        <input type="text" id="tongluong" value="<?= number_format($luong['LuongCB'] + $luong['PhuCap'] + $luong['Thuong'] - $luong['KhauTru'], 0, ',', '.') ?>" disabled>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn edit">💾 Cập nhật</button>
        <a href="index.php?controller=luong&action=index" class="btn cancel">↩️ Quay lại</a>
    </div>
</form>
</main>
</div>


</body>
</html>
