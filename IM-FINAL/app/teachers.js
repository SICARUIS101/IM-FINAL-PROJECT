$(document).ready(function() {
    // Handle search button click
    $('#searchTeacherBtn').click(function() {
        var searchValue = $('#searchTeacherInput').val();
        window.location.href = 'teachers.php?search=' + encodeURIComponent(searchValue);
    });

    // Handle search input enter key
    $('#searchTeacherInput').keypress(function(e) {
        if (e.which == 13) { // Enter key
            e.preventDefault();
            $('#searchTeacherBtn').click();
        }
    });

    // Handle sort buttons
    $('#sortTeacherAZ, #sortTeacherZA').click(function() {
        var sortValue = $(this).data('sort');
        var searchValue = $('#searchTeacherInput').val();
        window.location.href = 'teachers.php?sort=' + sortValue + (searchValue ? '&search=' + encodeURIComponent(searchValue) : '');
    });

    // Show/hide no teachers message
    var teacherContainer = $('#teacherCardContainer');
    var noTeachersMessage = $('#noTeachersMessage');
    if (teacherContainer.find('table').length === 0) {
        noTeachersMessage.show();
    } else {
        noTeachersMessage.hide();
    }
});