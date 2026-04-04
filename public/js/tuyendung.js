document.addEventListener("DOMContentLoaded", function () {

    const cards = document.querySelectorAll(".card");
    const columns = document.querySelectorAll(".column");

    if (!cards.length || !columns.length) return;

    /* ===== DRAG START / END ===== */
    cards.forEach(card => {

        card.addEventListener("dragstart", () => {
            card.classList.add("dragging");
        });

        card.addEventListener("dragend", () => {
            card.classList.remove("dragging");
        });

    });

    /* ===== DRAG OVER + DROP ===== */
    columns.forEach(column => {

        column.addEventListener("dragover", (e) => {
            e.preventDefault();

            const card = document.querySelector(".dragging");
            if (card) {
                column.appendChild(card);
            }
        });

        column.addEventListener("drop", () => {

            const card = document.querySelector(".dragging");
            if (!card) return;

            const maHS = card.dataset.id;
            const trangthai = column.dataset.status;

            fetch("index.php?controller=tuyendung&action=updateKanban", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `MaHS=${maHS}&TrangThai=${trangthai}`
            })
            .then(res => res.text())
            .then(res => {
                if (res.trim() !== "ok") {
                    alert("Lỗi cập nhật: " + res);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Lỗi server!");
            });

        });

    });

});