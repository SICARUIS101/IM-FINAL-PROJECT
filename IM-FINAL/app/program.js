$(document).ready(function () {
    console.log('program.js loaded');

    // Toggle sidebar
    $('#sidebarToggle').on('click', function () {
        $('#layoutSidenav').toggleClass('sb-sidenav-toggled');
        console.log('Sidebar toggled');
    });

    // Load programs on page load
    loadPrograms();

    // Function to load programs
    function loadPrograms() {
        console.log('Loading programs...');
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: { action: 'getPrograms' },
            dataType: 'json',
            success: function (response) {
                console.log('Programs response:', response);
                if (response.success) {
                    let html = '';
                    response.data.forEach(program => {
                        html += `
                            <tr>
                                <td>${program.course_name}</td>
                                <td>${program.course_description ? program.course_description.split(' ').slice(0, 5).join(' ') + '...' : 'No description'}</td>
                                <td>${program.enrolled_students || 0}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm view-modules" data-id="${program.course_id}"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-primary btn-sm edit-program" data-id="${program.course_id}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm delete-program" data-id="${program.course_id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>`;
                    });
                    $('table tbody').html(html);
                    console.log('Programs loaded:', response.data.length);
                } else {
                    console.error('Error loading programs:', response.message);
                    alert('Error loading programs: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error, xhr.responseText);
                alert('Error connecting to server. Check console for details.');
            }
        });
    }

    // Open Add Program Modal and populate students
    $('button[data-bs-target="#addProgramModal"]').on('click', function () {
        console.log('Opening add program modal');
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: { action: 'getStudents' },
            dataType: 'json',
            success: function (response) {
                console.log('Students response:', response);
                if (response.success) {
                    let studentCheckboxes = '';
                    response.data.forEach(student => {
                        studentCheckboxes += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="programStudents" value="${student.student_id}" id="student_${student.student_id}">
                                <label class="form-check-label" for="student_${student.student_id}">
                                    ${student.first_name} ${student.last_name}
                                </label>
                            </div>`;
                    });
                    $('#programStudents').html(studentCheckboxes);
                } else {
                    console.error('Error loading students:', response.message);
                    alert('Error loading students: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for students:', status, error, xhr.responseText);
                alert('Error loading students. Check console for details.');
            }
        });
        $('#addProgramModal form')[0].reset();
        console.log('Add program modal form reset');
    });

    // Save new program
    $('#addProgramModal .btn-primary').on('click', function () {
        console.log('Saving new program');
        let studentIds = [];
        $('#programStudents input[name="programStudents"]:checked').each(function () {
            studentIds.push($(this).val());
        });
        let formData = {
            action: 'addProgram',
            course_name: $('#programName').val().trim(),
            course_description: $('#programDescription').val().trim(),
            student_ids: studentIds
        };
        console.log('Add program form data:', formData);
        if (!formData.course_name) {
            alert('Program name is required.');
            return;
        }
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log('Add program response:', response);
                if (response.success) {
                    $('#addProgramModal').modal('hide');
                    loadPrograms();
                    alert('Program added successfully.');
                } else {
                    console.error('Error adding program:', response.message);
                    alert('Error adding program: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for add program:', status, error, xhr.responseText);
                alert('Error adding program. Check console for details.');
            }
        });
    });

    // Edit program
    $(document).on('click', '.edit-program', function () {
        let course_id = $(this).data('id');
        console.log('Editing program with ID:', course_id);
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: { action: 'getProgram', course_id: course_id },
            dataType: 'json',
            success: function (response) {
                console.log('Get program response:', response);
                if (response.success) {
                    let program = response.data;
                    $('#editProgramName').val(program.course_name);
                    $('#editProgramDescription').val(program.course_description);
                    $('#editProgramModal').data('course_id', program.course_id);
                    $.ajax({
                        url: 'ProgramController.php',
                        type: 'POST',
                        data: { action: 'getStudents' },
                        dataType: 'json',
                        success: function (res) {
                            console.log('Students for edit response:', res);
                            if (res.success) {
                                let studentCheckboxes = '';
                                res.data.forEach(student => {
                                    let checked = program.student_ids.includes(parseInt(student.student_id)) ? 'checked' : '';
                                    studentCheckboxes += `
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="editProgramStudents" value="${student.student_id}" id="edit_student_${student.student_id}" ${checked}>
                                            <label class="form-check-label" for="edit_student_${student.student_id}">
                                                ${student.first_name} ${student.last_name}
                                            </label>
                                        </div>`;
                                });
                                $('#editProgramStudents').html(studentCheckboxes);
                                $('#editProgramModal').modal('show');
                            } else {
                                console.error('Error loading students for edit:', res.message);
                                alert('Error loading students: ' + res.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error for students in edit:', status, error, xhr.responseText);
                            alert('Error loading students. Check console for details.');
                        }
                    });
                } else {
                    console.error('Error loading program:', response.message);
                    alert('Error loading program: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for get program:', status, error, xhr.responseText);
                alert('Error loading program. Check console for details.');
            }
        });
    });

    // Save edited program
    $('#editProgramModal .btn-primary').on('click', function () {
        console.log('Saving edited program');
        let course_id = $('#editProgramModal').data('course_id');
        let studentIds = [];
        $('#editProgramStudents input[name="editProgramStudents"]:checked').each(function () {
            studentIds.push($(this).val());
        });
        let formData = {
            action: 'updateProgram',
            course_id: course_id,
            course_name: $('#editProgramName').val().trim(),
            course_description: $('#editProgramDescription').val().trim(),
            student_ids: studentIds
        };
        console.log('Edit program form data:', formData);
        if (!formData.course_name) {
            alert('Program name is required.');
            return;
        }
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log('Update program response:', response);
                if (response.success) {
                    $('#editProgramModal').modal('hide');
                    loadPrograms();
                    alert('Program updated successfully.');
                } else {
                    console.error('Error updating program:', response.message);
                    alert('Error updating program: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for update program:', status, error, xhr.responseText);
                alert('Error updating program. Check console for details.');
            }
        });
    });

    // Delete program
    $(document).on('click', '.delete-program', function () {
        let course_id = $(this).data('id');
        console.log('Deleting program with ID:', course_id);
        if (confirm('Are you sure you want to delete this program?')) {
            $.ajax({
                url: 'ProgramController.php',
                type: 'POST',
                data: { action: 'deleteProgram', course_id: course_id },
                dataType: 'json',
                success: function (response) {
                    console.log('Delete program response:', response);
                    if (response.success) {
                        loadPrograms();
                        alert('Program deleted successfully.');
                    } else {
                        console.error('Error deleting program:', response.message);
                        alert('Error deleting program: ' + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error for delete program:', status, error, xhr.responseText);
                    alert('Error deleting program. Check console for details.');
                }
            });
        }
    });

    // View modules
    $(document).on('click', '.view-modules', function () {
        let course_id = $(this).data('id');
        console.log('Viewing modules for course ID:', course_id);
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: { action: 'getModules', course_id: course_id },
            dataType: 'json',
            success: function (response) {
                console.log('Modules response:', response);
                if (response.success) {
                    let html = '';
                    response.data.forEach(module => {
                        html += `
                            <tr>
                                <td>${module.module_name}</td>
                                <td>${module.module_description ? module.module_description.split(' ').slice(0, 5).join(' ') + '...' : 'No description'}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm edit-module" data-id="${module.module_id}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm delete-module" data-id="${module.module_id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>`;
                    });
                    $('#viewModulesModal table tbody').html(html);
                    $('#viewModulesModal').modal('show');
                    $('#addModuleModal .btn-primary').data('course_id', course_id);
                } else {
                    console.error('Error loading modules:', response.message);
                    alert('Error loading modules: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for modules:', status, error, xhr.responseText);
                alert('Error loading modules. Check console for details.');
            }
        });
    });

    // Add module
    $('#addModuleModal .btn-primary').on('click', function () {
        let course_id = $(this).data('course_id');
        console.log('Adding module for course ID:', course_id);
        let formData = {
            action: 'addModule',
            course_id: course_id,
            module_name: $('#moduleName').val().trim(),
            module_description: $('#moduleDuration').val().trim()
        };
        console.log('Add module form data:', formData);
        if (!formData.module_name) {
            alert('Module name is required.');
            return;
        }
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log('Add module response:', response);
                if (response.success) {
                    $('#addModuleModal').modal('hide');
                    $('.view-modules[data-id="' + course_id + '"]').click();
                    alert('Module added successfully.');
                } else {
                    console.error('Error adding module:', response.message);
                    alert('Error adding module: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for add module:', status, error, xhr.responseText);
                alert('Error adding module. Check console for details.');
            }
        });
    });

    // Edit module
    $(document).on('click', '.edit-module', function () {
        let module_id = $(this).data('id');
        console.log('Editing module with ID:', module_id);
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: { action: 'getModule', module_id: module_id },
            dataType: 'json',
            success: function (response) {
                console.log('Get module response:', response);
                if (response.success) {
                    let module = response.data;
                    $('#editModuleName').val(module.module_name);
                    $('#editModuleDuration').val(module.module_description);
                    $('#editModuleProgram').val(module.course_id);
                    $('#editModuleModal').data('module_id', module.module_id);
                    $('#editModuleModal').modal('show');
                } else {
                    console.error('Error loading module:', response.message);
                    alert('Error loading module: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for get module:', status, error, xhr.responseText);
                alert('Error loading module. Check console for details.');
            }
        });
    });

    // Save edited module
    $('#editModuleModal .btn-primary').on('click', function () {
        let module_id = $('#editModuleModal').data('module_id');
        console.log('Saving edited module with ID:', module_id);
        let formData = {
            action: 'updateModule',
            module_id: module_id,
            module_name: $('#editModuleName').val().trim(),
            module_description: $('#editModuleDuration').val().trim(),
            course_id: $('#editModuleProgram').val()
        };
        console.log('Edit module form data:', formData);
        if (!formData.module_name) {
            alert('Module name is required.');
            return;
        }
        $.ajax({
            url: 'ProgramController.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log('Update module response:', response);
                if (response.success) {
                    $('#editModuleModal').modal('hide');
                    $('.view-modules[data-id="' + formData.course_id + '"]').click();
                    alert('Module updated successfully.');
                } else {
                    console.error('Error updating module:', response.message);
                    alert('Error updating module: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error for update module:', status, error, xhr.responseText);
                alert('Error updating module. Check console for details.');
            }
        });
    });

    // Delete module
    $(document).on('click', '.delete-module', function () {
        let module_id = $(this).data('id');
        console.log('Deleting module with ID:', module_id);
        if (confirm('Are you sure you want to delete this module?')) {
            $.ajax({
                url: 'ProgramController.php',
                type: 'POST',
                data: { action: 'deleteModule', module_id: module_id },
                dataType: 'json',
                success: function (response) {
                    console.log('Delete module response:', response);
                    if (response.success) {
                        $('.view-modules').click();
                        alert('Module deleted successfully.');
                    } else {
                        console.error('Error deleting module:', response.message);
                        alert('Error deleting module: ' + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error for delete module:', status, error, xhr.responseText);
                    alert('Error deleting module. Check console for details.');
                }
            });
        }
    });
});