document.addEventListener("DOMContentLoaded", function () {

    /* ================= TÍNH TỔNG LƯƠNG ================= */

    function tinhTongLuong() {
        const luongcb = parseFloat(document.getElementById('luongcb')?.value) || 0;
        const phucap  = parseFloat(document.getElementById('phucap')?.value) || 0;
        const thuong  = parseFloat(document.getElementById('thuong')?.value) || 0;
        const khautru = parseFloat(document.getElementById('khautru')?.value) || 0;

        const tong = luongcb + phucap + thuong - khautru;

        const tongInput = document.getElementById('tongluong');
        if (tongInput) {
            tongInput.value = tong.toLocaleString('vi-VN');
        }
    }

    /* ================= LẤY SỐ NGÀY LÀM ================= */

    function laySoNgayLam() {
        const manv  = document.getElementById('manv')?.value;
        const thang = document.getElementById('thang')?.value;

        if (!manv || !thang) return;

        fetch(`index.php?controller=chamcong&action=getSoNgayLam&manv=${manv}&thang=${thang}`)
            .then(res => res.json())
            .then(data => {
                const soNgayInput = document.getElementById('soNgayLam');
                if (soNgayInput) {
                    soNgayInput.value = data.SoNgayLam || 0;
                }
                tinhTongLuong();
            })
            .catch(err => {
                console.error(err);
                alert("Lỗi lấy ngày công!");
            });
    }

    /* ================= TÍNH LƯƠNG THÁNG ================= */

    function tinhLuongThang() {
        const thang = document.getElementById('thang')?.value;
        const nam   = document.getElementById('nam')?.value;

        if (!thang || !nam) {
            alert("Vui lòng nhập tháng năm");
            return;
        }

        if (!confirm(`Tính lương tháng ${thang}/${nam}?`)) return;

        fetch("index.php?controller=luong&action=tinhLuongThang", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `thang=${thang}&nam=${nam}`
        })
        .then(res => res.text())
        .then(() => {
            alert("Đã tính xong!");
            location.reload();
        })
        .catch(err => {
            console.error(err);
            alert("Lỗi tính lương!");
        });
    }

    /* ================= GẮN EVENT ================= */

    const inputs = ['luongcb','phucap','thuong','khautru'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', tinhTongLuong);
    });

    const manvEl  = document.getElementById('manv');
    const thangEl = document.getElementById('thang');

    if (manvEl)  manvEl.addEventListener('change', laySoNgayLam);
    if (thangEl) thangEl.addEventListener('change', laySoNgayLam);

    const btnTinh = document.getElementById('btnTinhLuong');
    if (btnTinh) btnTinh.addEventListener('click', tinhLuongThang);

});