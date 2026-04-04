document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.menu-toggle').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            this.parentElement.classList.toggle('open');
        });
    });
    
});