document.addEventListener("DOMContentLoaded", function () {

    /* ================= CHẤM CÔNG NHANH (TABLE) ================= */

    const cells = document.querySelectorAll('.cell');

    if (cells.length) {
        cells.forEach(td => {

            td.addEventListener('click', function () {

                let val = prompt("Nhập:\nX = Đi làm\nP = Nghỉ phép\nM = Đi muộn");

                if (!val) return;

                val = val.trim().toUpperCase();

                if (!['X', 'P', 'M'].includes(val)) {
                    alert("Sai ký hiệu!");
                    return;
                }

                const maNV = this.dataset.manv;
                const day  = this.dataset.day;

                const thang = CHAMCONG_CONFIG?.thang;
                const nam   = CHAMCONG_CONFIG?.nam;

                if (!maNV || !day || !thang || !nam) {
                    alert("Thiếu dữ liệu!");
                    return;
                }

                const ngay = `${nam}-${String(thang).padStart(2,'0')}-${String(day).padStart(2,'0')}`;

                fetch('index.php?controller=chamcong&action=chamNhanh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `MaNV=${maNV}&Ngay=${ngay}&TrangThai=${val}`
                })
                .then(r => r.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        location.reload();
                    } else {
                        alert("Có lỗi: " + res);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Lỗi server!");
                });

            });

        });
    }

    /* ================= FORM CHẤM CÔNG ================= */

    const ngayInput = document.getElementById("NgayLamViec");

    if (ngayInput) {

        const thuLabel  = document.getElementById("thuLabel");
        const gioVao    = document.getElementById("GioVao");
        const gioRa     = document.getElementById("GioRa");
        const trangThai = document.getElementById("TrangThai");

        ngayInput.addEventListener("change", function () {

            const d = new Date(this.value);
            const thu = ["Chủ nhật","Thứ 2","Thứ 3","Thứ 4","Thứ 5","Thứ 6","Thứ 7"];

            thuLabel.innerHTML = "→ " + thu[d.getDay()];

            if (d.getDay() === 0) {
                trangThai.value = "Nghỉ phép";
                autoFill();
            }
        });

        function autoFill() {
            if (trangThai.value === "Đi làm") {
                gioVao.disabled = false;
                gioRa.disabled  = false;

                if (!gioVao.value) gioVao.value = "08:30";
                if (!gioRa.value)  gioRa.value  = "17:30";
            } else {
                gioVao.value = "";
                gioRa.value  = "";
                gioVao.disabled = true;
                gioRa.disabled  = true;
            }
        }

        trangThai.addEventListener("change", autoFill);

        // default
        gioVao.value = "08:30";
        gioRa.value  = "17:30";
    }

});