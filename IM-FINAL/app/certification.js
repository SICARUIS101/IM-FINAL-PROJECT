document.addEventListener('DOMContentLoaded', () => {
    // Form validation for the assign certification form
    const form = document.querySelector('#certificationForm');
    form.addEventListener('submit', (e) => {
        const studentSelect = document.querySelector('#studentSelect');
        const courseSelect = document.querySelector('#courseSelect');
        const certificationDate = document.querySelector('#certificationDate');
        const certificateNumber = document.querySelector('#certificateNumber');

        if (!studentSelect.value || !courseSelect.value || !certificationDate.value || !certificateNumber.value) {
            e.preventDefault();
            alert('Please fill out all required fields.');
        }
    });

    // Form validation for the edit certification form
    const editForm = document.querySelector('#editCertificationForm');
    editForm.addEventListener('submit', (e) => {
        const editStudentSelect = document.querySelector('#editStudentSelect');
        const editCourseSelect = document.querySelector('#editCourseSelect');
        const editCertificationDate = document.querySelector('#editCertificationDate');
        const editCertificateNumber = document.querySelector('#editCertificateNumber');

        if (!editStudentSelect.value || !editCourseSelect.value || !editCertificationDate.value || !editCertificateNumber.value) {
            e.preventDefault();
            alert('Please fill out all required fields.');
        }
    });

    // Populate edit modal with certification data
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const certId = button.getAttribute('data-cert-id');
            const studentId = button.getAttribute('data-student-id');
            const courseId = button.getAttribute('data-course-id');
            const certNumber = button.getAttribute('data-cert-number');
            const certDate = button.getAttribute('data-cert-date');

            document.querySelector('#editCertificationId').value = certId;
            document.querySelector('#editStudentSelect').value = studentId;
            document.querySelector('#editCourseSelect').value = courseId;
            document.querySelector('#editCertificateNumber').value = certNumber;
            document.querySelector('#editCertificationDate').value = certDate;
        });
    });

    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this certification?')) {
                e.preventDefault();
            }
        });
    });
});