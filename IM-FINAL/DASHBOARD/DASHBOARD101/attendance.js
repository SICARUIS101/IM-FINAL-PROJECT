$(document).ready(function () {
    let selectedCourseId = 1; // Default course_id (Computer System Servicing NC II)
    let allStudents = []; // Store full student list for filtering

    // Initialize
    loadCourses();
    loadStudents();
    loadAttendance();

    // Load courses for filter dropdown
    function loadCourses() {
        const courses = [
            { id: 1, name: 'Computer System Servicing NC II' },
            { id: 2, name: 'Dressmaking NC II' },
            { id: 3, name: 'Electronic Products Assembly Servicing NC II' },
            { id: 4, name: 'Shielded Metal Arc Welding (SMAW) NC I' },
            { id: 5, name: 'Shielded Metal Arc Welding (SMAW) NC II' }
        ];
        let options = '';
        courses.forEach(course => {
            options += `<option value="${course.id}" ${course.id === selectedCourseId ? 'selected' : ''}>${course.name}</option>`;
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
                    allStudents = response.data; // Store full student list
                    updateStudentDropdown(allStudents); // Populate dropdown with all students
                    $('#studentSelectDelete').html(updateStudentDropdown(allStudents, true)); // Populate delete student dropdown
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

    // Helper function to update student dropdown
    function updateStudentDropdown(students, forDelete = false) {
        let options = '<option value="">Select a student</option>';
        students.forEach(student => {
            options += `<option value="${student.student_id}" data-course-id="${student.course_id}">${student.student_id} - ${student.first_name} ${student.last_name} (${student.course_name})</option>`;
        });
        $('#studentSelect').html(options);
        if (forDelete) {
            $('#studentSelectDelete').html(options);
        }
    }

    // Search Student ID event
    $('#studentIdSearch').on('input', function () {
        const searchId = $(this).val().toLowerCase().trim();
        if (searchId === '') {
            updateStudentDropdown(allStudents); // Reset to full list if search is empty
        } else {
            const filteredStudents = allStudents.filter(student =>
                student.student_id.toString().toLowerCase().includes(searchId) ||
                `${student.first_name} ${student.last_name}`.toLowerCase().includes(searchId)
            );
            updateStudentDropdown(filteredStudents); // Update dropdown with filtered students
        }
    });

    // Load attendance records for the selected course
    function loadAttendance() {
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: { action: 'get_attendance', course_id: selectedCourseId },
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
                                    <td>${record.course_name}</td>
                                    <td>${record.attendance_date}</td>
                                    <td>${record.status}</td>
                                    <td>${record.notes || ''}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm edit-btn" data-id="${record.attendance_id}" data-student-id="${record.student_id}" data-course-id="${record.course_id}" data-date="${record.attendance_date}" data-status="${record.status}" data-notes="${record.notes || ''}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="${record.attendance_id}" data-course-id="${record.course_id}"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>`;
                        });
                    }
                    $('#attendanceTable tbody').html(rows);

                    const students = [...new Set(response.data.map(record => `${record.student_id}|${record.first_name} ${record.last_name}`))];
                    const dates = [...new Set(response.data.map(record => record.attendance_date))].sort();
                    let dayTableRows = '';
                    let headerRow = '<tr><th>Student Name</th>';
                    dates.forEach(date => {
                        headerRow += `<th>${date}</th>`;
                    });
                    headerRow += '</tr>';
                    $('#attendanceByDayTable thead').html(headerRow);

                    students.forEach(student => {
                        const [studentId, studentName] = student.split('|');
                        let row = `<tr><td>${studentName}</td>`;
                        dates.forEach(date => {
                            const record = response.data.find(r => r.student_id === parseInt(studentId) && r.attendance_date === date);
                            let statusClass = '';
                            let statusText = '';
                            if (record) {
                                statusText = record.status.charAt(0);
                                statusClass = record.status === 'Present' ? 'bg-success text-white' :
                                    record.status === 'Absent' ? 'bg-danger text-white' :
                                        'bg-warning text-dark';
                            } else {
                                statusText = '-';
                            }
                            row += `<td class="${statusClass} text-center">${statusText}</td>`;
                        });
                        row += '</tr>';
                        dayTableRows += row;
                    });

                    if (students.length === 0) {
                        dayTableRows = '<tr><td colspan="' + (dates.length + 1) + '">No attendance records found.</td></tr>';
                    }
                    $('#attendanceByDayTable tbody').html(dayTableRows);
                } else {
                    alert('Error loading attendance: ' + (response.message || 'No attendance records found'));
                    $('#attendanceTable tbody').html('<tr><td colspan="6">No attendance records found.</td></tr>');
                    $('#attendanceByDayTable thead').html('<tr><th>Student Name</th></tr>');
                    $('#attendanceByDayTable tbody').html('<tr><td>No attendance records found.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (loadAttendance):', xhr.status, status, error);
                alert('Failed to load attendance. Check server status and console for details.');
                $('#attendanceTable tbody').html('<tr><td colspan="6">Failed to load attendance.</td></tr>');
                $('#attendanceByDayTable thead').html('<tr><th>Student Name</th></tr>');
                $('#attendanceByDayTable tbody').html('<tr><td>Failed to load attendance.</td></tr>');
            }
        });
    }

    // Course filter change event
    $('#courseFilter').on('change', function () {
        selectedCourseId = $(this).val();
        loadAttendance();
    });

    // Add/Edit attendance record
    $('#saveAttendance').click(function () {
        const attendanceId = $('#attendanceId').val();
        const studentId = $('#studentSelect').val();
        const courseId = $('#studentSelect option:selected').data('course-id');
        const attendanceDate = $('#attendanceDate').val();
        const status = $('#attendanceStatus').val();
        const notes = $('#attendanceNotes').val();
        const action = attendanceId ? 'update_attendance' : 'add_attendance';

        if (!studentId || !courseId || !attendanceDate || !status) {
            alert('Please fill all required fields.');
            return;
        }

        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: {
                action: action,
                attendance_id: attendanceId,
                student_id: studentId,
                course_id: courseId,
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
                    $('#studentIdSearch').val('');
                    loadAttendance();
                    alert(response.message);
                } else {
                    if (response.message.includes('does not exist') || response.message.includes('Foreign key')) {
                        alert('Error: ' + response.message + ' Please refresh the student list.');
                        loadStudents();
                    } else {
                        alert('Error: ' + (response.message || 'Could not save attendance'));
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (saveAttendance):', xhr.status, xhr.statusText, error);
                alert('Failed to save attendance. Check server status and console for details.');
            }
        });
    });

    // Add student
    $('#saveStudent').click(function () {
        const firstName = $('#firstName').val();
        const lastName = $('#lastName').val();
        const birthdate = $('#birthdate').val();
        const contactNumber = $('#contactNumber').val();
        const courseId = $('#course').val();

        if (!firstName || !lastName || !birthdate || !courseId) {
            alert('Please fill all required fields');
            return;
        }

        if (contactNumber && !/^[0-9]{10,12}$/.test(contactNumber)) {
            alert('Please enter a valid contact number (10-12 digits)');
            return;
        }

        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: {
                action: 'add_student',
                first_name: firstName,
                last_name: lastName,
                birthdate: birthdate,
                contact_number: contactNumber,
                course_id: courseId
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    $('#addStudentModal').modal('hide');
                    $('#addStudentForm')[0].reset();
                    loadStudents();
                    loadAttendance(); // Refresh attendance to include new students
                    alert('Successfully added student');
                } else {
                    alert('Error: ' + (response.message || 'Could not add student'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (response):', xhr.status, error);
                console.error('Response:', xhr.responseText);
                alert('Failed to add student. Check server status and console for details.');
            }
        });
    });

    // Delete student
    $('#deleteStudentBtn').click(function () {
        loadStudents();
    });

    $('#confirmDeleteStudent').click(function () {
        const studentId = $('#studentSelectDelete').val();
        if (!studentId) {
            alert('Please select a student to delete.');
            return;
        }

        const confirmDelete = confirm('Are you sure you want to delete this student? This will also delete their attendance records.');
        if (!confirmDelete) return;

        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: {
                action: 'delete_student',
                student_id: studentId
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    $('#deleteStudentModal').modal('hide'); // Close the modal
                    loadStudents();
                    loadAttendance(); // Refresh attendance table
                    alert(response.message);
                } else {
                    alert('Error: ' + (response.message || 'Could not delete student. Please try again.'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (deleteStudent):', xhr.status, error);
                console.error('Response:', xhr.responseText);
                alert('Failed to delete student. Check server status and console for details.');
            }
        });
    });

    // View Students
    $('#viewStudentsBtn').click(function () {
        loadStudentsForView();
        $('#viewStudentsModal').modal('show');
    });

    function loadStudentsForView() {
        $('#studentsContainer').html('<p>Loading students...</p>');

        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: { action: 'get_students' },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    if (response.data && response.data.length > 0) {
                        const groupedStudents = {};
                        response.data.forEach(student => {
                            if (!groupedStudents[student.course_id]) {
                                groupedStudents[student.course_id] = [];
                            }
                            groupedStudents[student.course_id].push(student);
                        });

                        let html = '';
                        for (const courseId in groupedStudents) {
                            const courseName = groupedStudents[courseId][0].course_name;
                            html += `
                                <div class="course-section mb-4">
                                    <h5 class="text-primary">${courseName}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Student ID</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Birthdate</th>
                                                    <th>Contact Number</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;
                            groupedStudents[courseId].forEach(student => {
                                html += `
                                    <tr>
                                        <td>${student.student_id}</td>
                                        <td>${student.first_name}</td>
                                        <td>${student.last_name}</td>
                                        <td>${student.birthdate}</td>
                                        <td>${student.contact_number || '-'}</td>
                                    </tr>`;
                            });
                            html += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>`;
                        }
                        $('#studentsContainer').html(html);
                    } else {
                        $('#studentsContainer').html('<p>No students found.</p>');
                    }
                } else {
                    $('#studentsContainer').html('<p>Error loading students: ' + (response.message || 'Unknown error') + '</p>');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (loadStudentsForView):', xhr.status, error);
                console.error('Response:', xhr.responseText);
                $('#studentsContainer').html('<p>Failed to load students. Check server status and console for details.</p>');
            }
        });
    }

    // Edit attendance
    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        const studentId = $(this).data('student-id');
        const courseId = $(this).data('course-id');
        const date = $(this).data('date');
        const status = $(this).data('status');
        const notes = $(this).data('notes');

        $('#studentIdSearch').val('');
        updateStudentDropdown(allStudents);
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
        const courseId = $(this).data('course-id');
        $.ajax({
            url: 'attendance_api.php',
            type: 'POST',
            data: { action: 'delete_attendance', attendance_id: id, course_id: courseId },
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    loadAttendance();
                    alert(response.message);
                } else {
                    alert('Error: ' + (response.message || 'Could not delete attendance record'));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error (deleteAttendance):', xhr.status, error);
                alert('Failed to delete attendance. Check server status and console for details.');
            }
        });
    });

    // Reset form when modal is closed
    $('#addAttendanceModal').on('hidden.bs.modal', function () {
        $('#addAttendanceForm')[0].reset();
        $('#attendanceId').remove();
        $('#studentIdSearch').val('');
        updateStudentDropdown(allStudents);
    });
});
