/**
 * File xử lý logic cho Module Quản lý Hợp đồng
 */
$(document).ready(function () {

    /* ================== 1. XÓA HỢP ĐỒNG ================== */
    $('.btn-delete-hd').on('click', function (e) {
        e.preventDefault();
        const maHD = $(this).data('id');
        const soHD = $(this).data('so');

        if (confirm(`XÁC NHẬN XÓA\n-------------------------\nSố hợp đồng: ${soHD}\n\nBạn có chắc chắn muốn xóa không?\nDữ liệu không thể khôi phục!`)) {
            // Đổi maHD thành MaHopDong cho khớp với Controller
window.location.href = `index.php?controller=hopdong&action=xoa&MaHopDong=${maHD}`;
        }
    });

    /* ================== 2. LOẠI HỢP ĐỒNG ================== */
    const $loaiHD = $('select[name="LoaiHopDong"]');
    const $ngayKT = $('input[name="NgayKetThuc"]');

    function xuLyLoaiHopDong() {
        if ($loaiHD.val() === 'Không xác định thời hạn') {
            $ngayKT.val('').prop('disabled', true).css('background', '#f5f5f5');
        } else {
            $ngayKT.prop('disabled', false).css('background', '#ffffff');
        }
    }

    $loaiHD.on('change', xuLyLoaiHopDong);
    xuLyLoaiHopDong(); // Chạy ngay khi load trang

    /* ================== 3. BẬC LƯƠNG & HIỂN THỊ LƯƠNG ================== */
    const $selectBac = $('#MaBac');
    const $luongCB = $('#LuongCoBan');
    const $heSo = $('#HeSoLuong');

    function hienThiLuongTheoBac() {
        const $opt = $selectBac.find(':selected');
        if ($opt.length && $opt.data('luong')) {
            // Hiển thị định dạng tiền tệ cho người dùng xem
            $luongCB.val(Number($opt.data('luong')).toLocaleString('vi-VN') + ' đ');
            $heSo.val($opt.data('heso'));
        } else {
            $luongCB.val('');
            $heSo.val('');
        }
    }

    if ($selectBac.length) {
        $selectBac.on('change', hienThiLuongTheoBac);
        hienThiLuongTheoBac();
    }

    /* ================== 4. NHÂN VIÊN → TỰ ĐỘNG CHỌN BẬC LƯƠNG ================== */
   
/* ================== 4. NHÂN VIÊN → TỰ ĐỘNG CHỌN BẬC LƯƠNG ================== */
const $selectNV = $('#MaNV');


$selectNV.on('change', function () {
    // Lấy giá trị từ option được chọn
    const selectedOption = $(this).find('option:selected');
    const maBacCuaNV = selectedOption.attr('data-mabac'); // Dùng .attr() để lấy chính xác chuỗi

    console.log("Mã bậc nhân viên lấy được:", maBacCuaNV);

    if (maBacCuaNV && maBacCuaNV !== "undefined" && maBacCuaNV !== "") {
        // Chuyển về string để khớp với giá trị của thẻ select bậc lương
        $selectBac.val(String(maBacCuaNV)).trigger('change');
    } else {
        $selectBac.val('').trigger('change');
    }
});

});