document.addEventListener("DOMContentLoaded", function () {

    // PHÒNG BAN
    new Chart(document.getElementById("chartPhongBan"), {
        type: 'bar',
        data: {
            labels: phongbanLabels,
            datasets: [{
                label: 'Số nhân viên',
                data: phongbanData
            }]
        }
    });

    // TUYỂN DỤNG
    new Chart(document.getElementById("chartTuyenDung"), {
        type: 'line',
        data: {
            labels: thangLabels.map(t => "Tháng " + t),
            datasets: [{
                label: 'Số đợt tuyển',
                data: tuyenData
            }]
        }
    });

    // GIỚI TÍNH
    new Chart(document.getElementById("chartGioiTinh"), {
        type: 'pie',
        data: {
            labels: gtLabels,
            datasets: [{
                data: gtData
            }]
        }
    });

    // CHẤM CÔNG
    new Chart(document.getElementById("chartChamCong"), {
        type: 'line',
        data: {
            labels: ccLabels.map(t => "Tháng " + t),
            datasets: [{
                label: 'Số lần chấm công',
                data: ccData
            }]
        }
    });

    // HỢP ĐỒNG
    new Chart(document.getElementById("chartHopDong"), {
        type: 'doughnut',
        data: {
            labels: hdLabels,
            datasets: [{
                data: hdData
            }]
        }
    });

    // LƯƠNG
    new Chart(document.getElementById("chartLuong"), {
        type: 'bar',
        data: {
            labels: luongLabels.map(t => "Tháng " + t),
            datasets: [{
                label: 'Tổng lương',
                data: luongData
            }]
        }
    });

    // AUTO REFRESH
    setInterval(function () {
        location.reload();
    }, 60000);

});