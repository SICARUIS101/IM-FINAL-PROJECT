<?php
require_once 'config.php'; 
require_once 'Course.php';
require_once 'Module.php';

$courseModel = new Course($pdo);
$moduleModel = new Module($pdo);

$programMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_program'])) {
    $programName = trim($_POST['program_name'] ?? '');
    $programDescription = trim($_POST['program_description'] ?? ''); 

    if (!empty($programName)) {
        $newProgramId = $courseModel->create($programName /*, $programDescription */); 
        if ($newProgramId) {
            $programMessage = "<div class='alert alert-success'>Program added successfully!</div>";
        } else {
            $programMessage = "<div class='alert alert-danger'>Failed to add program. It might already exist or there was a database error.</div>";
        }
    } else {
        $programMessage = "<div class='alert alert-warning'>Program name is required.</div>";
    }
}
$allCourses = $courseModel->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELE TECH - Manage Programs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> </head>
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
                        data-bs-target="#collapseStudentTracking" aria-expanded="false"
                        aria-controls="collapseStudentTracking">
                        <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                        <span class="nav-text">Student Tracking</span>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseStudentTracking">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link collapse-item" href="attendance.html">Attendance</a>
                            <a class="nav-link collapse-item" href="assessments.php">Assessments</a>
                            <a class="nav-link collapse-item" href="certification.html">Certification</a>
                            <a class="nav-link collapse-item" href="module_completion.php">Module Progress</a>
                        </nav>
                    </div>
                    <div class="sb-sidenav-menu-heading">Administration</div>
                    <a class="nav-link" href="teachers.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <span class="nav-text">Teachers</span>
                    </a>
                    <a class="nav-link active" href="programs.php">
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
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.html">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <main class="dashboard-content">
                <div class="container-fluid">
                    <h1 class="mt-4">Manage Programs <i class="fas fa-graduation-cap"></i></h1>
                    
                    <?php echo $programMessage;?>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-cogs me-2"></i>Program Settings</h6>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProgramModal" onclick="prepareAddProgramForm()"><i class="fas fa-plus me-1"></i> Add New Program</button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchProgramInput" placeholder="Search for program...">
                                        <button class="btn btn-outline-secondary" type="button" id="searchProgramBtn"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Program Name</th>
                                            <th>Description</th> <th>Modules</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="programsTableBody">
                                        <?php if (!empty($allCourses)): ?>
                                            <?php foreach ($allCourses as $course): ?>
                                                <?php $moduleCount = $moduleModel->countByCourse($course['course_id']); ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($course['course_description'] ?? 'N/A');  ?></td>
                                                    <td><span class="badge bg-secondary"><?php echo $moduleCount; ?></span></td>
                                                    <td>
                                                        <button class="btn btn-info btn-sm me-1" 
                                                                onclick="openManageModulesModal('<?php echo $course['course_id']; ?>', '<?php echo htmlspecialchars(addslashes($course['course_name'])); ?>')"
                                                                title="Manage Modules">
                                                            <i class="fas fa-tasks"></i>
                                                        </button>
                                                        <button class="btn btn-warning btn-sm me-1" 
                                                                onclick="openEditProgramModal('<?php echo $course['course_id']; ?>', '<?php echo htmlspecialchars(addslashes($course['course_name'])); ?>', '<?php echo htmlspecialchars(addslashes($course['course_description'] ?? '')); ?>')"
                                                                title="Edit Program">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="deleteProgram('<?php echo $course['course_id']; ?>', '<?php echo htmlspecialchars(addslashes($course['course_name'])); ?>')" title="Delete Program">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center">No programs found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4"><div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright © ELE TECH 2025</div>
                    <div><a href="#">Privacy Policy</a>·<a href="#">Terms & Conditions</a></div>
                </div></div>
            </footer>
        </div>
    </div>

    <div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="programModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="programForm" method="POST" action="programs.php"> 
                    <div class="modal-header">
                        <h5 class="modal-title" id="programModalLabel">Add New Program</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="programId_form" name="program_id"> <input type="hidden" name="form_action" id="programFormAction" value="add_program"> <div class="mb-3">
                            <label for="programName_form" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="programName_form" name="program_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="programDescription_form" class="form-label">Description</label>
                            <textarea class="form-control" id="programDescription_form" name="program_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveProgramBtn">Save Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manageModulesModal" tabindex="-1" aria-labelledby="manageModulesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageModulesModalLabel">Manage Modules for: <span id="modalProgramNameDisplay">Program Name</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="currentProgramIdForModuleDisplay" name="program_id">
                    <button class="btn btn-success btn-sm mb-3" onclick="prepareAddModuleForm()"><i class="fas fa-plus me-1"></i> Add New Module</button>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Module Name</th><th>Description</th><th>Actions</th></tr></thead>
                            <tbody id="modulesListTableBody">
                                <tr><td colspan="3" class="text-center">Loading modules...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEditModuleModal" tabindex="-1" aria-labelledby="moduleModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="moduleForm" method="POST"> <div class="modal-header">
                        <h5 class="modal-title" id="moduleModalLabel">Add New Module</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModuleModalAndReFocusParent()"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="moduleId_form" name="module_id">
                        <input type="hidden" id="parentProgramId_form" name="program_id">
                        <input type="hidden" name="form_action" id="moduleFormAction" value="add_module">

                        <div class="mb-3">
                            <label for="moduleName_form" class="form-label">Module Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="moduleName_form" name="module_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="moduleDescription_form" class="form-label">Description</label>
                            <textarea class="form-control" id="moduleDescription_form" name="module_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeModuleModalAndReFocusParent()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveModuleBtn">Save Module</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php?>
    <script>
        function prepareAddProgramForm() {
            document.getElementById('programForm').reset();
            document.getElementById('programModalLabel').textContent = 'Add New Program';
            document.getElementById('programId_form').value = '';
            document.getElementById('programFormAction').value = 'add_program';
            $('#addProgramModal').modal('show');
        }
        function openEditProgramModal(programId, programName, programDescription) { 
            document.getElementById('programForm').reset();
            document.getElementById('programModalLabel').textContent = 'Edit Program: ' + programName;
            document.getElementById('programId_form').value = programId;
            document.getElementById('programName_form').value = programName;
            document.getElementById('programDescription_form').value = programDescription; 
            document.getElementById('programFormAction').value = 'edit_program';
            $('#addProgramModal').modal('show');
        }
        $('#programForm').on('submit', function(e) {
            e.preventDefault(); 
            var formData = $(this).serialize(); 
            var action = $('#programFormAction').val(); 
            var url = 'api/program_actions.php'; 

            $.ajax({
                type: 'POST',
                url: url, 
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#addProgramModal').modal('hide');
                        location.reload(); 
                    } else {
                        alert('Error: ' + (response.message || 'Could not save program.'));
                    }
                },
                error: function() {
                    alert('An unexpected error occurred with the server.');
                }
            });
        });

        function deleteProgram(programId, programName) {
            if (confirm(`Are you sure you want to delete the program "${programName}"? This might fail if it has associated modules or students.`)) {
                 $.ajax({
                    type: 'POST',
                    url: 'api/program_actions.php', 
                    data: { 
                        program_id: programId,
                        form_action: 'delete_program' 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Error: ' + (response.message || 'Could not delete program.'));
                        }
                    },
                    error: function() {
                        alert('An unexpected error occurred while deleting.');
                    }
                });
            }
        }

        function openManageModulesModal(programId, programName) {
            document.getElementById('modalProgramNameDisplay').textContent = programName;
            document.getElementById('currentProgramIdForModuleDisplay').value = programId; 
            
            const modulesTbody = document.getElementById('modulesListTableBody');
            modulesTbody.innerHTML = '<tr><td colspan="3" class="text-center">Loading modules...</td></tr>';

            $.ajax({
                url: 'api/module_actions.php', 
                type: 'GET',
                data: { program_id: programId, action: 'get_modules_by_program' },
                dataType: 'json',
                success: function(response) {
                    modulesTbody.innerHTML = '';
                    if (response.success && response.modules.length > 0) {
                        response.modules.forEach(module => {
                            modulesTbody.innerHTML += `
                                <tr>
                                    <td>${escapeHtml(module.module_name)}</td>
                                    <td>${escapeHtml(module.module_description || '')}</td>
                                    <td>
                                        <button class='btn btn-warning btn-sm me-1' 
                                                onclick='prepareEditModuleForm(${module.module_id}, "${escapeHtml(addslashes(module.module_name))}", "${escapeHtml(addslashes(module.module_description || ''))}", ${programId})' 
                                                title='Edit Module'><i class='fas fa-edit'></i></button>
                                        <button class='btn btn-danger btn-sm' 
                                                onclick='deleteModule(${module.module_id}, "${escapeHtml(addslashes(module.module_name))}")' 
                                                title='Delete Module'><i class='fas fa-trash'></i></button>
                                    </td>
                                </tr>`;
                        });
                    } else if(response.success && response.modules.length === 0) {
                        modulesTbody.innerHTML = '<tr><td colspan="3" class="text-center">No modules found for this program.</td></tr>';
                    } else {
                         modulesTbody.innerHTML = '<tr><td colspan="3" class="text-center">Error loading modules.</td></tr>';
                         console.error("Error fetching modules:", response.message);
                    }
                },
                error: function() {
                    modulesTbody.innerHTML = '<tr><td colspan="3" class="text-center">Failed to load modules. Check API endpoint.</td></tr>';
                }
            });
            $('#manageModulesModal').modal('show');
        }
        
        function prepareAddModuleForm() {
            const currentProgramId = document.getElementById('currentProgramIdForModuleDisplay').value;
            document.getElementById('moduleForm').reset();
            document.getElementById('moduleModalLabel').textContent = 'Add New Module';
            document.getElementById('moduleId_form').value = '';
            document.getElementById('parentProgramId_form').value = currentProgramId;
            document.getElementById('moduleFormAction').value = 'add_module';
            $('#addEditModuleModal').modal('show');
        }

        function prepareEditModuleForm(moduleId, moduleName, moduleDescription, programId) {
            document.getElementById('moduleForm').reset();
            document.getElementById('moduleModalLabel').textContent = 'Edit Module: ' + moduleName;
            document.getElementById('moduleId_form').value = moduleId;
            document.getElementById('moduleName_form').value = moduleName;
            document.getElementById('moduleDescription_form').value = moduleDescription;
            document.getElementById('parentProgramId_form').value = programId;
            document.getElementById('moduleFormAction').value = 'edit_module';
            $('#addEditModuleModal').modal('show');
        }

        $('#moduleForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                type: 'POST',
                url: 'api/module_actions.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#addEditModuleModal').modal('hide');
                        const parentProgramId = $('#parentProgramId_form').val();
                        const parentProgramName = $('#modalProgramNameDisplay').text(); 
                        openManageModulesModal(parentProgramId, parentProgramName); 
                    } else {
                        alert('Error: ' + (response.message || 'Could not save module.'));
                    }
                },
                error: function() {
                    alert('An server error occurred while saving the module.');
                }
            });
        });
        
        function deleteModule(moduleId, moduleName) {
             if (confirm(`Are you sure you want to delete the module "${moduleName}"?`)) {
                 $.ajax({
                    type: 'POST',
                    url: 'api/module_actions.php',
                    data: { 
                        module_id: moduleId,
                        form_action: 'delete_module' 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                             const parentProgramId = $('#currentProgramIdForModuleDisplay').val(); 
                             const parentProgramName = $('#modalProgramNameDisplay').text();
                             openManageModulesModal(parentProgramId, parentProgramName);
                        } else {
                            alert('Error: ' + (response.message || 'Could not delete module.'));
                        }
                    },
                    error: function() {
                        alert('An unexpected error occurred while deleting module.');
                    }
                });
            }
        }


        function closeModuleModalAndReFocusParent() {
            $('#addEditModuleModal').modal('hide');
            
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
        function addslashes(str) {
            return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
        }
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
            });
        }
    </script>
</body>
</html>