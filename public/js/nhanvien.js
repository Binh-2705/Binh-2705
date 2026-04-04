$(document).ready(function () {

    /* ==============================
       LOAD BẬC LƯƠNG THEO NGẠCH
    ============================== */
    $('#select-ngach').on('change', function () {

        var maNgach = $(this).val();
        var $selectBac = $('#select-bac');

        if (maNgach) {
            $.ajax({
                url: 'index.php?controller=nhanvien&action=getBacLuongByNgach',
                type: 'GET',
                data: { ma_ngach: maNgach },

                beforeSend: function () {
                    $selectBac.html('<option>⏳ Đang tải...</option>');
                },

                success: function (data) {
                    $selectBac.html(data);
                    $selectBac.prop('disabled', false);
                },

                error: function () {
                    alert('❌ Không thể tải bậc lương!');
                }
            });
        } else {
            $selectBac.html('<option>-- Chọn ngạch trước --</option>');
            $selectBac.prop('disabled', true);
        }
    });


    /* ==============================
       NÚT XÓA NHÂN VIÊN
    ============================== */
    $(document).on('click', '.btn-delete-nv', function (e) {

        e.preventDefault();

        var maNV = $(this).data('id');
        var tenNV = $(this).data('name');

        if (confirm('Bạn có chắc muốn xóa nhân viên: ' + tenNV + ' (Mã: ' + maNV + ')?')) {
            window.location.href =
                'index.php?controller=nhanvien&action=xoa&manv=' + maNV;
        }
    });

});