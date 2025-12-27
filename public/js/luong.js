function layLuongCoBan() {
    const manv = document.getElementById('manv').value;
    if (!manv) return;

    fetch(`index.php?controller=luong&action=getLuongCoBan&manv=${manv}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('luongcb').value = data.LuongCoBan || 0;
            tinhTongLuong();
        })
        .catch(err => console.error(err));
}

function laySoNgayLam() {
    const manv = document.getElementById('manv').value;
    const thang = document.getElementById('thang').value;
    if (!manv || !thang) return;

    fetch(`index.php?controller=chamcong&action=getSoNgayLam&manv=${manv}&thang=${thang}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('soNgayLam').value = data.SoNgayLam || 0;
            tinhTongLuong();
        })
        .catch(err => console.error(err));
}

function tinhTongLuong() {
    let luongcb = parseFloat(document.getElementById('luongcb').value) || 0;
    let phucap = parseFloat(document.getElementById('phucap').value) || 0;
    let thuong = parseFloat(document.getElementById('thuong').value) || 0;
    let kyluat = parseFloat(document.getElementById('kyluat').value) || 0;
    let soNgayLam = parseInt(document.getElementById('soNgayLam').value) || 0;

    const ngayChuan = 26;

    // Khấu trừ do thiếu ngày công
    let khautruNgayCong = ((ngayChuan - soNgayLam) / ngayChuan) * luongcb;
    if (khautruNgayCong < 0) khautruNgayCong = 0;

    // Tổng khấu trừ = ngày công + kỷ luật
    let tongKhauTru = khautruNgayCong + kyluat;

    let tong = luongcb + phucap + thuong - tongKhauTru;

    document.getElementById('khautru').value = Math.round(tongKhauTru);
    document.getElementById('tongluong').value =
        Math.round(tong).toLocaleString('vi-VN');
}

function layThuongKyLuat() {
    const manv = document.getElementById('manv').value;
    const thang = document.getElementById('thang').value;
    if (!manv || !thang) return;

    fetch(`index.php?controller=luong&action=getThuongKyLuat&manv=${manv}&thang=${thang}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('thuong').value = data.TongThuong || 0;
            document.getElementById('kyluat').value = data.TongKyLuat || 0;
            tinhTongLuong();
        });
}

