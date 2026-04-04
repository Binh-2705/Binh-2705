<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
        <main class="main-content">
            <header>
                <h1>📊 Danh sách báo cáo</h1>
            </header>

            <div class="actions">
                <a href="index.php?controller=baocao&action=create" class="btn add">
                    ➕ Thêm báo cáo mới
                </a>
                <a href="index.php?controller=baocao&action=exportExcel" class="btn">Xuất Excel</a>
                <a href="index.php?controller=baocao&action=exportJson" class="btn">Xuất JSON</a>
                <li>
<a href="index.php?controller=baocao&action=dashboard">
📊 Dashboard thống kê
</a>
</li>
                <input type="text" class="search-box" placeholder="Tìm kiếm báo cáo...">
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên báo cáo</th>
                        <th>Loại</th>
                        <th>Từ ngày</th>
                        <th>Đến ngày</th>
                        <th>Người tạo</th>
                        <th>Thời điểm tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($baocaos)){ ?>
                    <tr>
                        <td><?php echo $row['MaBC'] ?></td>
                        <td><strong><?php echo $row['TenBaoCao'] ?></strong></td>
                        <td><?php echo $row['LoaiBaoCao'] ?></td>
                        <td><?php echo $row['TuNgay'] ?></td>
                        <td><?php echo $row['DenNgay'] ?></td>
                        <td><?php echo $row['NguoiTao'] ?></td>
                        <td><?php echo $row['ThoiDiemTao'] ?></td>
                        <td>
                            <div class="table-actions">
                            <a href="index.php?controller=baocao&action=edit&id=<?php echo $row['MaBC'] ?>" 
                               class="btn edit"
                               title="Chỉnh sửa">✏️</a>
                            <a onclick="return confirm('Bạn có chắc chắn muốn xóa báo cáo này?')" 
                               href="index.php?controller=baocao&action=delete&id=<?php echo $row['MaBC'] ?>" 
                               class="btn delete"
                               title="Xóa">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </main>
    <?php include 'views/layout/footer.php'; ?>