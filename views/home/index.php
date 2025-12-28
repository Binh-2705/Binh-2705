<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hệ thống Quản lý Nhân sự</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
  
    <nav class="sidebar">
      <h2>HỆ THỐNG<br>QUẢN LÝ NHÂN SỰ</h2>
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
        <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý đăng nhập – phân quyền</a></li>
        <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
       <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
      </ul>
    </nav>
    
    <main class="main-content">
      <header>
        <h1>Chào mừng đến với Hệ thống Quản lý Nhân sự</h1>
        <p>Vui lòng chọn chức năng từ menu bên trái để bắt đầu làm việc.</p>
      </header>

      <section class="dashboard">
        <div class="card">
          👥
          <h3>Quản lý nhân viên</h3>
          <p>Thêm, sửa, xóa và xem danh sách nhân viên</p>
          <a href="index.php?controller=nhanvien&action=index">Xem chi tiết</a>
        </div>
        <div class="card">
          🏢
          <h3>Quản lý phòng ban</h3>
          <p>Danh sách các phòng ban trong công ty</p>
          <a href="index.php?controller=phongban&action=index">Xem chi tiết</a>
        </div>
        <div class="card">
          💰
          <h3>Quản lý lương</h3>
          <p>Tính toán và quản lý bảng lương</p>
        </div>
        <div class="card">
          📄
          <h3>Quản lý hợp đồng</h3>
          <p>Thông tin hợp đồng lao động của nhân viên</p>
        </div>
        <div class="card">
          📆
          <h3>Quản lý nghỉ phép</h3>
          <p>Duyệt và theo dõi đơn nghỉ phép</p>
        </div>
        <div class="card">
          🏅
          <h3>Khen thưởng - Kỷ luật</h3>
          <p>Ghi nhận các quyết định thưởng hoặc phạt</p>
        </div>
        <div class="card">
          📊
          <h3>Thống kê - Báo cáo</h3>
          <p>Tổng hợp và hiển thị số liệu nhân sự</p>
        </div>
        <div class="card">
          👤
          <h3>Hồ sơ cá nhân</h3>
          <p>Xem và cập nhật thông tin của bản thân</p>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
