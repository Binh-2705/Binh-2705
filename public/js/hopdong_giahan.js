$(document).ready(function () {
    const $loaiHD = $('#LoaiHopDong');
    const $ngayKT = $('#NgayKetThuc');
    const $ngayBD = $('input[name="NgayBatDau"]');

    function checkLoaiHopDong() {
        if ($loaiHD.val() === 'Không xác định thời hạn') {
            $ngayKT.val('').prop('disabled', true).css('background', '#f1f5f9');
        } else {
            $ngayKT.prop('disabled', false).css('background', '#ffffff');
            // Gợi ý: Nếu ngày kết thúc trống, tự động cộng 1 năm từ ngày bắt đầu
            if (!$ngayKT.val() && $ngayBD.val()) {
                let dateBD = new Date($ngayBD.val());
                dateBD.setFullYear(dateBD.getFullYear() + 1);
                $ngayKT.val(dateBD.toISOString().split('T')[0]);
            }
        }
    }

    $('form').on('submit', function (e) {
        // Mở disabled để dữ liệu chắc chắn được gửi lên (nếu cần xử lý phía PHP)
        $ngayKT.prop('disabled', false);

        const ngayBatDau = new Date($ngayBD.val());
        const ngayKetThucVal = $ngayKT.val();

        if ($loaiHD.val() !== 'Không xác định thời hạn' && ngayKetThucVal) {
            const ngayKetThuc = new Date(ngayKetThucVal);
            if (ngayKetThuc <= ngayBatDau) {
                alert('❌ Ngày kết thúc phải sau ngày bắt đầu gia hạn!');
                $ngayKT.focus();
                e.preventDefault();
                return false;
            }
        }
    });

    $loaiHD.on('change', checkLoaiHopDong);
    $ngayBD.on('change', checkLoaiHopDong); // Cập nhật lại ngày kết thúc gợi ý khi đổi ngày bắt đầu
    checkLoaiHopDong();
});