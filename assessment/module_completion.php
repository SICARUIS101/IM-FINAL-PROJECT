<?php

require_once 'config.php'; 
require_once 'Course.php'; 

$courseModel = new Course($pdo);
$allCourses = $courseModel->getAll(); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELE TECH - Module Completion Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="layoutSidenav">
        <div id="sideNav" class="sb-sidenav">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Dashboard</div>
                    <a class="nav-link" href="dashboard.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                    <div class="sb-sidenav-menu-heading">Student Management</div>
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                        data-bs-target="#collapseStudentTracking" aria-expanded="true" aria-controls="collapseStudentTracking">
                        <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                        <span class="nav-text">Student Tracking</span>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse show" id="collapseStudentTracking"> <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link collapse-item" href="attendance.html">Attendance</a>
                            <a class="nav-link collapse-item" href="assessments.php">Assessments</a>
                            <a class="nav-link collapse-item" href="certification.html">Certification</a>
                            <a class="nav-link collapse-item active" href="module_completion.php">Module Progress</a>
                        </nav>
                    </div>
                    <div class="sb-sidenav-menu-heading">Administration</div>
                    <a class="nav-link" href="teachers.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <span class="nav-text">Teachers</span>
                    </a>
                    <a class="nav-link" href="programs.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-graduation-cap"></i></div>
                        <span class="nav-text">Programs</span>
                    </a>
                    <a class="nav-link" href="settings.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                        <span class="nav-text">Settings</span>
                    </a>
                    <a class="nav-link" href="help.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-question-circle"></i></div>
                        <span class="nav-text">Help Center</span>
                    </a>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                <span>Admin User</span>
            </div>
        </div>
        <div id="layoutSidenav_content">
            <nav class="sb-topnav navbar navbar-expand navbar-light bg-light">
                <a class="navbar-brand ps-3" href="dashboard.html">ELE TECH</a>
                <button class="btn menu-toggle ms-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-dropdown" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="rounded-circle" src="https://via.placeholder.com/32" alt="User ">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.html">Profile</a></li>
                            <li><a class="dropdown-item" href="settings.html">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.html">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <main class="dashboard-content">
                <div class="container-fluid">
                    <h1 class="mt-4">Module Completion Tracking <i class="fas fa-check-circle"></i></h1>
                    <div class="card mb-4">
                        <div class="card-header"><h6><i class="fas fa-filter me-2"></i>Select Program and Module</h6></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="selectProgramForCompletion" class="form-label">1. Choose Program:</label>
                                    <select class="form-select" id="selectProgramForCompletion">
                                        <option selected disabled value="">-- Select a Program --</option>
                                        <?php if (!empty($allCourses)): ?>
                                            <?php foreach ($allCourses as $course): ?>
                                                <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="selectModuleForCompletion" class="form-label">2. Choose Module:</label>
                                    <select class="form-select" id="selectModuleForCompletion" disabled>
                                        <option selected disabled value="">-- Select a Program First --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="studentModuleProgressSection" class="card mb-4" style="display: none;">
                        <div class="card-header"><h6><i class="fas fa-users me-2"></i>Student Progress for: <span id="selectedModuleDisplay">Module Name</span></h6></div>
                        <div class="card-body">
                            <div class="row mb-3 align-items-center"> 
                                <div class="col-md-4">
                                    <label for="searchStudentProgressInput" class="form-label visually-hidden">Search Student Name</label> 
                                    <input type="text" class="form-control form-control-sm" id="searchStudentProgressInput" placeholder="Search student name...">
                                </div>
                                <div class="col-md-8">
                                    <div class="btn-group float-end" role="group" aria-label="Filter student progress"> 
                                        <button type="button" class="btn btn-outline-secondary btn-sm filter-btn-progress active" data-filter="all">All</button> 
                                        <button type="button" class="btn btn-outline-secondary btn-sm filter-btn-progress" data-filter="a-z">A-Z <i class="fas fa-sort-alpha-down"></i></button>
                                        <button type="button" class="btn btn-outline-success btn-sm filter-btn-progress" data-filter="completed">Completed</button>
                                        <button type="button" class="btn btn-outline-warning btn-sm filter-btn-progress" data-filter="not-completed">Not Yet Completed</button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead><tr><th>Student Name</th><th>Program</th><th>Status for Selected Module</th><th>Action</th></tr></thead>
                                    <tbody id="studentProgressTableBody">
                                        <tr><td colspan="4" class="text-center">Select a program and module to view student progress.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto"></footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
       
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
            });
        }

        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') {
                if (unsafe === null || typeof unsafe === 'undefined') return '';
                unsafe = String(unsafe);
            }
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }


        $(document).ready(function() {
            $('#selectProgramForCompletion').on('change', function() {
                const programId = $(this).val();
                const moduleSelect = $('#selectModuleForCompletion');
                moduleSelect.html('<option selected disabled value="">-- Loading Modules --</option>').prop('disabled', true);
                $('#studentModuleProgressSection').hide();
                $('#studentProgressTableBody').html('<tr><td colspan="4" class="text-center">Select a module to view student progress.</td></tr>');

                if (programId) {
                    $.ajax({
                        url: 'api/module_actions.php',
                        type: 'GET',
                        data: { program_id: programId, action: 'get_modules_by_program' },
                        dataType: 'json',
                        success: function(response) {
                            moduleSelect.html('<option selected disabled value="">-- Select a Module --</option>');
                            if (response.success && response.modules.length > 0) {
                                response.modules.forEach(function(module) {
                                    moduleSelect.append($('<option></option>').attr('value', module.module_id).text(escapeHtml(module.module_name)));
                                });
                                moduleSelect.prop('disabled', false);
                            } else if(response.success) {
                                moduleSelect.html('<option selected disabled value="">-- No modules found --</option>');
                            } else {
                                moduleSelect.html('<option selected disabled value="">-- Error loading modules --</option>');
                                console.error("Error fetching modules:", response.message);
                            }
                        },
                        error: function() {
                            moduleSelect.html('<option selected disabled value="">-- Error connecting --</option>');
                            alert('Could not fetch modules. Please try again.');
                        }
                    });
                }
            });

            $('#selectModuleForCompletion').on('change', function() {
                const moduleId = $(this).val();
                const programId = $('#selectProgramForCompletion').val(); 
                const moduleName = $(this).find('option:selected').text();
                
                if (moduleId && programId) {
                    $('#selectedModuleDisplay').text(escapeHtml(moduleName));
                    $('#studentModuleProgressSection').show();
                    loadStudentProgress(programId, moduleId);
                } else {
                    $('#studentModuleProgressSection').hide();
                }
            });

            function loadStudentProgress(programId, moduleId) {
                const tbody = $('#studentProgressTableBody');
                tbody.html('<tr><td colspan="4" class="text-center">Loading students... <i class="fas fa-spinner fa-spin"></i></td></tr>');
                
                $.ajax({
                    url: 'api/progress_actions.php', 
                    type: 'GET',
                    data: { program_id: programId, module_id: moduleId, action: 'get_students_for_module_status' },
                    dataType: 'json',
                    success: function(response) {
                        tbody.empty();
                        if (response.success && response.students.length > 0) {
                            response.students.forEach(function(student) {
                                let studentFullName = escapeHtml(student.first_name + ' ' + student.last_name);
                                let courseName = escapeHtml(student.course_name);
                               
                                let statusText = student.is_completed ? 'completed' : 'not yet completed'; 
                                let statusBadgeHTML = student.is_completed ? 
                                    `<span class="badge bg-success" data-status-text="${statusText}">Completed</span>` : 
                                    `<span class="badge bg-warning" data-status-text="${statusText}">Not Yet Completed</span>`;
                                
                                let buttonClass = student.is_completed ? 'btn-danger' : 'btn-success';
                                let buttonIcon = student.is_completed ? 'fa-times' : 'fa-check';
                                let buttonText = student.is_completed ? ' Undo Completion' : ' Mark Complete';
                                let button = `<button class="btn ${buttonClass} btn-sm toggle-complete-btn" data-student-id="${student.student_id}" data-module-id="${moduleId}" data-completed="${student.is_completed}">
                                                <i class="fas ${buttonIcon} me-1"></i> ${buttonText}
                                              </button>`;
                                tbody.append(`<tr class="student-row">
                                                <td class="student-name">${studentFullName}</td>
                                                <td>${courseName}</td>
                                                <td class="completion-status">${statusBadgeHTML}</td>
                                                <td>${button}</td>
                                             </tr>`);
                            });
                        } else if (response.success) {
                            tbody.html('<tr><td colspan="4" class="text-center">No students found for this program/module.</td></tr>');
                        } else {
                            tbody.html('<tr><td colspan="4" class="text-center">Error loading student progress: ${escapeHtml(response.message)}</td></tr>');
                        }
                        $('.filter-btn-progress').removeClass('active');
                        $('.filter-btn-progress[data-filter="all"]').addClass('active');
                        $('#searchStudentProgressInput').val('');
                        applyStudentTableFilters('all', ''); 
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        tbody.html('<tr><td colspan="4" class="text-center">Failed to load student progress.</td></tr>');
                        console.error("AJAX error fetching student progress: ", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            }

            $('#studentProgressTableBody').on('click', '.toggle-complete-btn', function() {
                const button = $(this);
                const studentId = button.data('student-id');
                const moduleId = button.data('module-id');
                let currentCompletedStatus = button.data('completed');
                const newCompletedStatus = !currentCompletedStatus;

                $.ajax({
                    url: 'api/progress_actions.php', 
                    type: 'POST',
                    data: { 
                        student_id: studentId, 
                        module_id: moduleId,
                        is_completed: newCompletedStatus,
                        action: 'set_progress' 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            button.data('completed', newCompletedStatus);
                            const row = button.closest('tr');
                            const statusCell = row.find('td.completion-status');
                            let newStatusText = newCompletedStatus ? 'completed' : 'not yet completed';
                            let newBadgeHTML = newCompletedStatus ? 
                                `<span class="badge bg-success" data-status-text="${newStatusText}">Completed</span>` : 
                                `<span class="badge bg-warning" data-status-text="${newStatusText}">Not Yet Completed</span>`;

                            statusCell.html(newBadgeHTML);
                            if (newCompletedStatus) {
                                button.removeClass('btn-success').addClass('btn-danger').html('<i class="fas fa-times me-1"></i> Undo Completion');
                            } else {
                                button.removeClass('btn-danger').addClass('btn-success').html('<i class="fas fa-check me-1"></i> Mark Complete');
                            }
                            var activeFilter = $('.filter-btn-progress.active').data('filter');
                            applyStudentTableFilters(activeFilter, $('#searchStudentProgressInput').val().toLowerCase());
                        } else {
                           alert('Failed to update status: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       alert('Error connecting to server to update status.');
                       console.error("AJAX error updating status: ", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            });

            function applyStudentTableFilters(filterType, searchTerm) {
                console.log(`Applying filters: type='${filterType}', term='${searchTerm}'`); 
                $('#studentProgressTableBody tr.student-row').each(function(index) {
                    const row = $(this);
                    const studentName = row.find('td.student-name').text().toLowerCase();
                    const badgeElement = row.find('td.completion-status .badge');
                    const completionStatusText = badgeElement.attr('data-status-text'); 

                    let showRow = true;

                    if (searchTerm && studentName.indexOf(searchTerm) === -1) {
                        showRow = false;
                    }

                    if (showRow) {
                        if (filterType === 'completed') {
                            if (completionStatusText !== 'completed') {
                                showRow = false;
                            }
                        } else if (filterType === 'not-completed') {
                            if (completionStatusText !== 'not yet completed') {
                                showRow = false;
                            }
                        }
                       
                    }
                    
                    row.toggle(showRow);
                });
            }
            
            $('#searchStudentProgressInput').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                const activeFilter = $('.filter-btn-progress.active').data('filter');
                applyStudentTableFilters(activeFilter, searchTerm);
            });

            $('.filter-btn-progress').on('click', function() {
                const button = $(this);
                const filterType = button.data('filter');
                
                $('.filter-btn-progress').removeClass('active');
                button.addClass('active');
                
                const searchTerm = $('#searchStudentProgressInput').val().toLowerCase();

                if (filterType === 'a-z') {
                   
                    applyStudentTableFilters('all', searchTerm); 
                    
                    var rowsToSort = $('#studentProgressTableBody tr.student-row:visible').get(); 
                    rowsToSort.sort(function(a, b) {
                        var keyA = $(a).find('td.student-name').text().toUpperCase();
                        var keyB = $(b).find('td.student-name').text().toUpperCase();
                        if (keyA < keyB) return -1;
                        if (keyA > keyB) return 1;
                        return 0;
                    });
                    $.each(rowsToSort, function(index, row) {
                        $('#studentProgressTableBody').append(row); 
                    });
                } else {
                    applyStudentTableFilters(filterType, searchTerm);
                }
            });
        });
    </script>
</body>
</html>
