<?php
require_once 'config.php'; 
require_once 'Course.php';
require_once 'Assessment.php';

$courseModel = new Course($pdo);
$assessmentModel = new Assessment($pdo);

$allCoursesForFilter = $courseModel->getAll();

$assessmentMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_assessment'])) { 
    $assessmentData = [
        'student_id' => filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT),
        'course_id' => filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT),
        'assessment_title' => trim(filter_input(INPUT_POST, 'assessment_title', FILTER_SANITIZE_STRING)),
        'date_conducted' => trim(filter_input(INPUT_POST, 'date_conducted', FILTER_SANITIZE_STRING)), 
        'status' => trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING)),
        'result' => trim(filter_input(INPUT_POST, 'result', FILTER_SANITIZE_STRING)),
        'score' => trim(filter_input(INPUT_POST, 'score', FILTER_SANITIZE_STRING)),
        'assessor' => trim(filter_input(INPUT_POST, 'assessor', FILTER_SANITIZE_STRING)),
        'tries' => trim(filter_input(INPUT_POST, 'tries', FILTER_SANITIZE_STRING)),
        'remarks' => trim(filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_STRING)),
        'certification_status' => trim(filter_input(INPUT_POST, 'certification_status', FILTER_SANITIZE_STRING)),
    ];
    $assessment_id_to_edit = filter_input(INPUT_POST, 'assessment_id', FILTER_VALIDATE_INT);

    if ($assessmentData['student_id'] && $assessmentData['course_id'] && !empty($assessmentData['assessment_title']) && !empty($assessmentData['date_conducted'])) {
        if ($assessment_id_to_edit) { 
            if ($assessmentModel->update($assessment_id_to_edit, $assessmentData)) {
                $assessmentMessage = "<div class='alert alert-success'>Assessment updated successfully!</div>";
            } else {
                $assessmentMessage = "<div class='alert alert-danger'>Failed to update assessment.</div>";
            }
        } else {
            if ($assessmentModel->create($assessmentData)) {
                $assessmentMessage = "<div class='alert alert-success'>Assessment recorded successfully!</div>";
            } else {
                $assessmentMessage = "<div class='alert alert-danger'>Failed to record assessment.</div>";
            }
        }
    } else {
        $assessmentMessage = "<div class='alert alert-warning'>Please fill in all required fields for the assessment.</div>";
    }
}
$filterParams = [];
if (!empty($_GET['filter_program_id'])) {
    $filterParams['course_id'] = $_GET['filter_program_id'];
}
if (!empty($_GET['filter_result'])) {
    $filterParams['assessment_result'] = $_GET['filter_result'];
}
$eligibleStudents = $assessmentModel->getEligibleStudentsForAssessment($filterParams);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELE TECH - Attendance</title>
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
                    <a class="nav-link" href="dashboard.php">
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
                            <a class="nav-link collapse-item" href="attendance.html">Student Records</a>
                            <a class="nav-link collapse-item active" href="assessments.php">Assessments</a>
                            <a class="nav-link collapse-item" href="certification.php">Certification</a>
                            <a class="nav-link collapse-item" href="module_completion.php">Module Progress</a>
                        </nav>
                    </div>
                    <div class="sb-sidenav-menu-heading">Administration</div>
                    
                    <a class="nav-link" href="programs.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-graduation-cap"></i></div>
                        <span class="nav-text">Programs</span>
                    </a>
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
                    <li>
                        <form action="logout.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>

           <main class="dashboard-content">
                <div class="container-fluid">
                    <h1 class="mt-4">Student Assessments <i class="fas fa-clipboard-check"></i></h1>
                    <?php echo $assessmentMessage; ?>
                    <div class="card mb-4">
                        <div class="card-header"><h6><i class="fas fa-list-alt me-2"></i>Eligible Students for Assessment</h6></div>
                        <div class="card-body">
                            <form method="GET" action="assessments.php" class="row g-2 mb-3 align-items-end" id="assessmentFilterForm">
                                <div class="col-md-3">
                                    <label for="searchStudentAssessment" class="form-label small">Search Student:</label>
                                    <input type="text" class="form-control form-control-sm" name="search_term" id="searchStudentAssessment" placeholder="Enter student name..." value="<?php echo htmlspecialchars($_GET['search_term'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="filterAssessmentProgram" class="form-label small">Program:</label>
                                    <select class="form-select form-select-sm" name="filter_program_id" id="filterAssessmentProgram">
                                        <option value="">All Programs</option>
                                        <?php if (!empty($allCoursesForFilter)): ?>
                                            <?php foreach ($allCoursesForFilter as $course): ?>
                                                <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php echo (isset($_GET['filter_program_id']) && $_GET['filter_program_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="filterAssessmentResultStatus" class="form-label small">Result:</label>
                                    <select class="form-select form-select-sm" name="filter_result" id="filterAssessmentResultStatus">
                                        <option value="">All Results</option>
                                        <option value="COMPETENT" <?php echo (isset($_GET['filter_result']) && $_GET['filter_result'] == 'COMPETENT') ? 'selected' : ''; ?>>COMPETENT</option>
                                        <option value="NOT YET COMPETENT" <?php echo (isset($_GET['filter_result']) && $_GET['filter_result'] == 'NOT YET COMPETENT') ? 'selected' : ''; ?>>NOT YET COMPETENT</option>
                                        <option value="N/A" <?php echo (isset($_GET['filter_result']) && $_GET['filter_result'] == 'N/A') ? 'selected' : ''; ?>>No Assessment (N/A)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Date Conducted:</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                                        <span class="input-group-text">-</span>
                                        <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                                </div>
                            </form>
                             <div class="mb-2"> <button class="btn btn-outline-secondary btn-sm" data-sort="a-z">Sort A-Z <i class="fas fa-sort-alpha-down"></i></button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead><tr><th style="width: 5%;">#</th><th style="width: 30%;">Student Name</th><th style="width: 30%;">Program</th><th style="width: 20%;">Assessment Result</th><th style="width: 15%;">Assess</th></tr></thead>
                                    <tbody id="assessmentStudentsTableBody">
                                        <?php
                                        $count = 1;
                                        if (!empty($eligibleStudents)):
                                            foreach ($eligibleStudents as $student):
                                                $studentFullName = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                                                $programName = htmlspecialchars($student['course_name']);
                                                $assessmentResult = htmlspecialchars($student['assessment_result'] ?: 'N/A');
                                                $programId = htmlspecialchars($student['course_id']);
                                                $existingAssessmentId = htmlspecialchars($student['assessment_id'] ?? '');


                                                $badgeClass = 'bg-secondary';
                                                if ($assessmentResult === 'COMPETENT') $badgeClass = 'bg-success';
                                                elseif ($assessmentResult === 'NOT YET COMPETENT') $badgeClass = 'bg-danger';
                                        ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td><?php echo $studentFullName; ?></td>
                                                <td><?php echo $programName; ?></td>
                                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $assessmentResult; ?></span></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#assessmentFormModal"
                                                            data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>"
                                                            data-student-name="<?php echo $studentFullName; ?>"
                                                            data-program-id="<?php echo $programId; ?>"
                                                            data-program-name="<?php echo $programName; ?>"
                                                            data-assessment-id="<?php echo $existingAssessmentId; ?>"
                                                            onclick="loadAssessmentData(this)"> <i class="fas fa-edit me-1"></i> Assess
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
                                            <tr><td colspan="5" class="text-center">No students are currently eligible for assessment or match filters.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto"> </footer>
        </div>
    </div>

    <div class="modal fade" id="assessmentFormModal" tabindex="-1" aria-labelledby="assessmentFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="assessmentForm" method="POST" action="assessments.php"> <input type="hidden" name="save_assessment" value="1"> <div class="modal-header">
                        <h5 class="modal-title" id="assessmentFormModalLabel">Assessment Details for: <span id="assessmentStudentNameDisplay">Student Name</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="assessmentStudentId_form" name="student_id">
                        <input type="hidden" id="assessmentId_form" name="assessment_id"> <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="qualificationApplied_form" class="form-label">Qualification Applied <span class="text-danger">*</span></label>
                                <select class="form-select" id="qualificationApplied_form" name="program_id" required>
                                    <option selected disabled value="">-- Select Program --</option>
                                    <?php if (!empty($allCoursesForFilter)): ?>
                                        <?php foreach ($allCoursesForFilter as $course): ?>
                                            <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                                <?php echo htmlspecialchars($course['course_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="assessmentTitle_form" class="form-label">Assessment Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="assessmentTitle_form" name="assessment_title" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dateConducted_form" class="form-label">Date to be Conducted <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dateConducted_form" name="date_conducted" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="assessmentStatus_form" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="assessmentStatus_form" name="status" required>
                                    <option value="To be assessed">To be assessed</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="assessmentResultField_form" class="form-label">Result</label>
                                <select class="form-select" id="assessmentResultField_form" name="result">
                                    <option value="To be assessed" selected>To be assessed</option>
                                    <option value="COMPETENT">COMPETENT</option>
                                    <option value="NOT YET COMPETENT">NOT YET COMPETENT</option>
                                    <option value="N/A">N/A (Not Applicable/Taken)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="assessmentTries_form" class="form-label">Tries</label>
                                <select class="form-select" id="assessmentTries_form" name="tries">
                                    <option value="">-- Select --</option> <option value="1st">1st</option>
                                    <option value="2nd">2nd</option>
                                    <option value="3rd">3rd</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="assessmentScore_form" class="form-label">Score</label>
                                <input type="text" class="form-control" id="assessmentScore_form" name="score" placeholder="e.g., 85/100 or Pass">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="assessorName_form" class="form-label">Assessor</label>
                                <input type="text" class="form-control" id="assessorName_form" name="assessor">
                            </div>
                            <div class="col-md-6 mb-3"> <label for="assessmentCertification_form" class="form-label">Certification Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="assessmentCertification_form" name="certification_status" required>
                                    <option value="Not Issued">Not Issued</option>
                                    <option value="Issued">Issued</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="assessmentRemarks_form" class="form-label">Remarks</label>
                                <textarea class="form-control" id="assessmentRemarks_form" name="remarks" rows="3"></textarea>
                            </div>
                        </div>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Assessment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php ?>
    <script>
        function loadAssessmentData(buttonElement) {
            const studentId = buttonElement.getAttribute('data-student-id');
            const studentName = buttonElement.getAttribute('data-student-name');
            const programId = buttonElement.getAttribute('data-program-id');
            const programName = buttonElement.getAttribute('data-program-name');
            const assessmentId = buttonElement.getAttribute('data-assessment-id');

            $('#assessmentForm').trigger("reset"); 
            $('#assessmentStudentNameDisplay').text(studentName);
            $('#assessmentStudentId_form').val(studentId);
            $('#qualificationApplied_form').val(programId); 
            $('#assessmentId_form').val(assessmentId); 

            if (assessmentId) {
                $.ajax({
                    url: 'api/assessment_actions.php',
                    type: 'GET',
                    data: { assessment_id: assessmentId, action: 'get_assessment_details' },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success && data.assessment) {
                            const assessment = data.assessment;
                            $('#assessmentTitle_form').val(assessment.assessment_title);
                            $('#dateConducted_form').val(assessment.date_conducted);
                            $('#assessmentStatus_form').val(assessment.status);
                            $('#assessmentResultField_form').val(assessment.result);
                            $('#assessmentScore_form').val(assessment.score);
                            $('#assessorName_form').val(assessment.assessor);
                            $('#assessmentTries_form').val(assessment.tries);
                            $('#assessmentRemarks_form').val(assessment.remarks);
                            $('#assessmentCertification_form').val(assessment.certification_status); 
                        } else {
                            console.log("No existing assessment data found or error fetching.");
                        }
                    },
                    error: function() {
                        console.log("Error fetching assessment details.");
                    }
                });
            }
        }
    </script>
</body>
</html>