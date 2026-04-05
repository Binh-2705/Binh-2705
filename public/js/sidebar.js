document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.menu-toggle').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            this.parentElement.classList.toggle('open');
        });
    });

    var accountWidget = document.getElementById('sidebarAccountWidget');
    var accountTrigger = document.getElementById('sidebarAccountTrigger');

    if (accountWidget && accountTrigger) {
        accountWidget.classList.remove('open');
        accountTrigger.setAttribute('aria-expanded', 'false');

        accountTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var isOpen = accountWidget.classList.toggle('open');
            accountTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function(e) {
            if (!accountWidget.contains(e.target)) {
                accountWidget.classList.remove('open');
                accountTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
});