// ELE TECH Dashboard Scripts

// Toggle sidebar
document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.body.classList.toggle('sb-sidenav-toggled');
    const sidenav = document.getElementById('sideNav');
    sidenav.classList.toggle('toggled');
});

// Responsive behavior
function handleWindowResize() {
    if (window.innerWidth < 768) {
        document.body.classList.add('sb-sidenav-toggled');
        document.getElementById('sideNav').classList.add('toggled');
    } else {
        document.body.classList.remove('sb-sidenav-toggled');
        document.getElementById('sideNav').classList.remove('toggled');
    }
}

// Initialize on load
window.addEventListener('load', handleWindowResize);
window.addEventListener('resize', handleWindowResize);

// Initialize Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Add active class to current link
document.addEventListener('DOMContentLoaded', function () {
    // Get current path
    var path = window.location.pathname.split("/").pop();

    // Account for empty path
    if (path == '') {
        path = 'index.html';
    }

    // Loop through sidebar links
    var links = document.querySelectorAll('.sb-sidenav .nav-link');

    for (var i = 0; i < links.length; i++) {
        var linkPath = links[i].getAttribute('href');

        if (linkPath && linkPath.indexOf(path) !== -1) {
            links[i].classList.add('active');

            // If this is a dropdown item, expand its parent
            var parent = links[i].closest('.collapse');
            if (parent) {
                parent.classList.add('show');
                var trigger = document.querySelector('[data-bs-target="#' + parent.id + '"]');
                if (trigger) {
                    trigger.classList.remove('collapsed');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            }
        }
    }
});