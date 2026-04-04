// ================= AUTO TÍNH BẢO HIỂM =================
function tinhBaoHiem() {
    let mucDongInput = document.querySelector('[name="MucDong"]');
    let loaiInput = document.querySelector('[name="LoaiBaoHiem"]');

    if (!mucDongInput || !loaiInput) return;

    let mucDong = parseFloat(mucDongInput.value) || 0;
    let loai = loaiInput.value;

    let congTy = 0;
    let nhanVien = 0;

    if (loai === "BHXH") {
        congTy = mucDong * 0.175;
        nhanVien = mucDong * 0.08;
    } 
    else if (loai === "BHYT") {
        congTy = mucDong * 0.03;
        nhanVien = mucDong * 0.015;
    } 
    else if (loai === "BHTN") {
        congTy = mucDong * 0.01;
        nhanVien = mucDong * 0.01;
    }

    let congTyField = document.querySelector('[name="CongTyDong"]');
    let nvField = document.querySelector('[name="NhanVienDong"]');

    if (congTyField) congTyField.value = congTy.toFixed(2);
    if (nvField) nvField.value = nhanVien.toFixed(2);
}

// ================= INIT =================
document.addEventListener("DOMContentLoaded", function () {

    let mucDongInput = document.querySelector('[name="MucDong"]');
    let loaiInput = document.querySelector('[name="LoaiBaoHiem"]');

    if (mucDongInput) {
        mucDongInput.addEventListener("input", tinhBaoHiem);
    }

    if (loaiInput) {
        loaiInput.addEventListener("change", tinhBaoHiem);
    }

    // chạy ngay khi load (dùng cho form sửa)
    tinhBaoHiem();
});