$(document).ready(function () {
    let selectedCourse = 'Computer System Servicing NC II'; // Default course

    // Initialize
    loadCourses();
    loadStudents();
    loadAttendance();

    // Load courses for filter dropdown
    function loadCourses() {
        const courses = [
            'Computer System Servicing NC II',
            'Dressmaking NC II',
            'Electronic Products Assembly Servicing NC II',
            'Shielded Metal Arc Welding (SMAW) NC I',
            'Shielded Metal Arc Welding (SMAW) NC II'
        ];
        let options = '';
        courses.forEach(course => {
            options += `<option value="${course}" ${course === selectedCourse ? 'selected' : ''}>${course}</option>`;
        });
        $('#courseFilter').html(options);
    }

    // Load students for dropdown and display
    function loadStudents() {
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: { action: 'get_students' },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    let options = '<option value="">Select Student</option>';
                    let rows = '';
                    response.data.forEach(student => {
                        options += `<option value="${student.student_id}" data-course="${student.course}">${student.first_name} ${student.last_name}</option>`;
                        rows += `
                            <tr>
                                <td>${student.first_name} ${student.last_name}</td>
                                <td>${student.course}</td>
                                <td><button class="btn btn-danger btn-sm delete-student-btn" data-student-id="${student.student_id}"><i class="fas fa-trash"></i></button></td>
                            </tr>`;
                    });
                    $('#studentSelect').html(options);
                    $('#studentSelectDelete').html(options); // Populate delete student dropdown
                    $('#studentsTable tbody').html(rows); // Assumes a table with id="studentsTable"
                    console.log('Students loaded:', response.data);
                } else {
                    alert('Error loading students: ' + (response.message || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (loadStudents):', xhr.status, status, error);
                alert('Failed to load students. Check server status and console for details.');
            }
        });
    }

    // Load attendance records for the selected course
    function loadAttendance() {
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: { action: 'get_attendance', course: selectedCourse },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    let rows = '';
                    if (response.data.length === 0) {
                        rows = '<tr><td colspan="6">No attendance records found.</td></tr>';
                    } else {
                        response.data.forEach(record => {
                            rows += `
                                <tr>
                                    <td>${record.first_name} ${record.last_name}</td>
                                    <td>${record.course}</td>
                                    <td>${record.attendance_date}</td>
                                    <td>${record.status}</td>
                                    <td>${record.notes || ''}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm edit-btn" data-id="${record.attendance_id}" data-student-id="${record.student_id}" data-date="${record.attendance_date}" data-status="${record.status}" data-notes="${record.notes || ''}" data-course="${record.course}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="${record.attendance_id}" data-course="${record.course}"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>`;
                        });
                    }
                    $('#attendanceTable tbody').html(rows);
                } else {
                    alert('Error loading attendance: ' + (response.message || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (loadAttendance):', xhr.status, status, error);
                alert('Failed to load attendance. Check server status and console for details.');
            }
        });
    }

    // Course filter change event
    $('#courseFilter').on('change', function () {
        selectedCourse = $(this).val();
        loadAttendance();
    });

    // Add/Edit attendance record
    $('#saveAttendance').click(function () {
        const attendanceId = $('#attendanceId').val();
        const studentId = $('#studentSelect').val();
        const course = $('#studentSelect option:selected').data('course');
        const attendanceDate = $('#attendanceDate').val();
        const status = $('#attendanceStatus').val();
        const notes = $('#attendanceNotes').val();
        const action = attendanceId ? 'update_attendance' : 'add_attendance';

        // Validate required fields
        if (!studentId || !attendanceDate || !status) {
            alert('Please fill all required fields');
            return;
        }

        // Proceed with saving attendance
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: {
                action: action,
                attendance_id: attendanceId,
                student_id: studentId,
                course: course,
                attendance_date: attendanceDate,
                status: status,
                notes: notes
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    $('#addAttendanceModal').modal('hide');
                    $('#addAttendanceForm')[0].reset();
                    $('#attendanceId').remove();
                    loadAttendance();
                    alert(response.message);
                } else {
                    // Handle error if student does not exist
                    if (response.message.includes('does not exist') || response.message.includes('Foreign key error')) {
                        alert('Error: ' + response.message + ' Please refresh the student list.');
                        loadStudents(); // Refresh the student list
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (saveAttendance):', xhr.status, status, error);
                alert('Failed to save attendance. Check server status and console for details.');
            }
        });
    });

    // Add student
    $('#saveStudent').click(function () {
        const firstName = $('#firstName').val();
        const lastName = $('#lastName').val();
        const course = $('#course').val();

        if (!firstName || !lastName || !course) {
            alert('Please fill all required fields');
            return;
        }

        console.log('Adding student:', { firstName, lastName, course });
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: {
                action: 'add_student',
                first_name: firstName,
                last_name: lastName,
                course: course
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    $('#addStudentModal').modal('hide');
                    $('#addStudentForm')[0].reset();
                    loadStudents();
                    alert(response.message);
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (saveStudent):', xhr.status, status, error);
                console.error('Response:', xhr.responseText);
                alert('Failed to save student. Check server status and console for details.');
            }
        });
    });

    // Delete student
    $('#deleteStudentBtn').click(function () {
        loadStudents(); // Load students to populate the delete dropdown
    });

    $('#confirmDeleteStudent').click(function () {
        const studentId = $('#studentSelectDelete').val();
        if (!studentId) {
            alert('Please select a student to delete.');
            return;
        }

        const confirmDelete = confirm('Are you sure you want to delete this student?');
        if (!confirmDelete) return;

        const deleteAttendance = confirm('Do you also want to delete all attendance records for this student?');

        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: {
                action: 'delete_student',
                student_id: studentId,
                delete_attendance: deleteAttendance
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    loadStudents();
                    loadAttendance(); // Refresh attendance in case student had records
                    alert(response.message);
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (deleteStudent):', xhr.status, status, error);
                console.error('Response:', xhr.responseText);
                alert('Failed to delete student. Check server status and console for details.');
            }
        });
    });

    // View Students
    $('#viewStudentsBtn').click(function () {
        loadStudentsForView(); // Load students to populate the view modal
    });

    function loadStudentsForView() {
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: { action: 'get_students' },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    // Group students by course
                    const groupedStudents = {};
                    response.data.forEach(student => {
                        if (!groupedStudents[student.course]) {
                            groupedStudents[student.course] = [];
                        }
                        groupedStudents[student.course].push(student);
                    });

                    // Create HTML for each course
                    let html = '';
                    for (const course in groupedStudents) {
                        html += `
                            <div class="course-section mb-4">
                                <h5 class="text-primary">${course}</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Student ID</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                        groupedStudents[course].forEach(student => {
                            html += `
                                <tr>
                                    <td>${student.student_id}</td>
                                    <td>${student.first_name}</td>
                                    <td>${student.last_name}</td>
                                </tr>`;
                        });
                        html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>`;
                    }
                    $('#studentsContainer').html(html); // Populate the students container
                } else {
                    alert('Error loading students: ' + (response.message || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (loadStudentsForView):', xhr.status, status, error);
                alert('Failed to load students. Check server status and console for details.');
            }
        });
    }

    // Edit attendance
    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        const studentId = $(this).data('student-id');
        const date = $(this).data('date');
        const status = $(this).data('status');
        const notes = $(this).data('notes');
        const course = $(this).data('course');

        $('#studentSelect').val(studentId);
        $('#attendanceDate').val(date);
        $('#attendanceStatus').val(status);
        $('#attendanceNotes').val(notes);
        $('#addAttendanceForm').append(`<input type="hidden" id="attendanceId" value="${id}">`);
        $('#addAttendanceModal').modal('show');
    });

    // Delete attendance
    $(document).on('click', '.delete-btn', function () {
        if (!confirm('Are you sure you want to delete this record?')) return;

        const id = $(this).data('id');
        const course = $(this).data('course');
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: { action: 'delete_attendance', attendance_id: id, course: course },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    loadAttendance();
                    alert(response.message);
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (deleteAttendance):', xhr.status, status, error);
                alert('Failed to delete attendance. Check server status and console for details.');
            }
        });
    });

    // Reset form when modal is closed
    $('#addAttendanceModal').on('hidden.bs.modal', function () {
        $('#addAttendanceForm')[0].reset();
        $('#attendanceId').remove();
    });
});
