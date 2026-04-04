<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

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
<?php include 'views/layout/footer.php'; ?>
