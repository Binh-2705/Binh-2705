document.addEventListener('DOMContentLoaded', function () {

    const selectNhanVien = document.getElementById('selectNhanVien');
    const pb = document.getElementById('selectPhongBan');
    const cv = document.getElementById('selectChucVu');

    if (!selectNhanVien) return;

    selectNhanVien.addEventListener('change', function () {
        let maNV = this.value;

        if (maNV) {
            fetch(`index.php?controller=hosocanhan&action=getNhanVienInfo&MaNV=${maNV}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.MaPB) {
                        pb.innerHTML = `<option value="${data.MaPB}">${data.TenPB}</option>`;
                        cv.innerHTML = `<option value="${data.MaCV}">${data.TenCV}</option>`;
                    } else {
                        pb.innerHTML = `<option value="">Không xác định</option>`;
                        cv.innerHTML = `<option value="">Không xác định</option>`;
                    }
                })
                .catch(err => {
                    console.error("Lỗi Fetch:", err);
                    pb.innerHTML = `<option value="">Lỗi tải dữ liệu</option>`;
                    cv.innerHTML = `<option value="">Lỗi tải dữ liệu</option>`;
                });
        } else {
            pb.innerHTML = `<option value="">-- Tự động theo nhân viên --</option>`;
            cv.innerHTML = `<option value="">-- Tự động theo nhân viên --</option>`;
        }
    });

});