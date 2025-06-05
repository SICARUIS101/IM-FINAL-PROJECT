$(document).ready(function() {
    // Handle search button click
    $('#searchAdminBtn').click(function() {
        var searchValue = $('#searchAdminInput').val();
        window.location.href = 'admins.php?search=' + encodeURIComponent(searchValue);
    });

    // Handle search input enter key
    $('#searchAdminInput').keypress(function(e) {
        if (e.which == 13) { // Enter key
            e.preventDefault();
            $('#searchAdminBtn').click();
        }
    });

    // Handle sort buttons
    $('#sortAdminAZ, #sortAdminZA').click(function() {
        var sortValue = $(this).data('sort');
        var searchValue = $('#searchAdminInput').val();
        window.location.href = 'admins.php?sort=' + sortValue + (searchValue ? '&search=' + encodeURIComponent(searchValue) : '');
    });

    // Show/hide no admins message
    var adminContainer = $('#adminCardContainer');
    var noAdminsMessage = $('#noAdminsMessage');
    if (adminContainer.find('table').length === 0) {
        noAdminsMessage.show();
    } else {
        noAdminsMessage.hide();
    }
});