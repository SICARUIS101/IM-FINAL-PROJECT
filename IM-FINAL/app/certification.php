<?php
session_start();
require_once 'Database.php';

// Database connection (update with your credentials)
$db = new Database('localhost', 'EletechTrack', 'postgres', 'almartinez');

// Fetch data for dropdowns and table
$students = $db->getStudents();
$courses = $db->getCourses();
$certifications = $db->getCertifications();

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ELE TECH - Assign Certifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Page Layout with Sidebar -->
    <div id="layoutSidenav">
        <!-- Sidebar Navigation -->
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
                            <a class="nav-link collapse-item" href="assessments.php">Assessments</a>
                            <a class="nav-link collapse-item active" href="certification.php">Certification</a>
                            <a class="nav-link collapse-item" href="module_completion.php">Module Progress</a>
                        </nav>
                    </div>
                    <div class="sb-sidenav-menu-heading">Administration</div>
                    <a class="nav-link" href="teachers.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <span class="nav-text">Teachers</span>
                    </a>
                    <a class="nav-link" href="admin_page.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                        <span class="nav-text">Admins</span>
                    </a>
                    <a class="nav-link" href="programs.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-graduation-cap"></i></div>
                        <span class="nav-text">Programs</span>
                    </a>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                <span>Admin User</span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div id="layoutSidenav_content">
            <nav class="sb-topnav navbar navbar-expand navbar-light bg-light">
                <a class="navbar-brand ps-3" href="dashboard.php">ELE TECH</a>
                <ul class="navbar-nav ms-auto">
                    <li>
                        <form action="logout.php" method="POST">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
            <main class="dashboard-content">
                <div class="container-fluid">
                    <h1 class="mt-2">Assign Certifications</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Assign Certifications</li>
                    </ol>

                    <!-- Display success/error messages -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Certification Assignment Form -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="m-0">Assign New Certification</h6>
                                </div>
                                <div class="card-body">
                                    <form id="certificationForm" action="assign_certification.php" method="POST">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="studentSelect" class="form-label">Select Student</label>
                                                <select class="form-select" id="studentSelect" name="student_id" required>
                                                    <option value="" disabled selected>Select a student</option>
                                                    <?php foreach ($students as $student): ?>
                                                        <option value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                                            <?php echo htmlspecialchars($student['student_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="courseSelect" class="form-label">Select Course</label>
                                                <select class="form-select" id="courseSelect" name="course_id" required>
                                                    <option value="" disabled selected>Select a course</option>
                                                    <?php foreach ($courses as $course): ?>
                                                        <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="certificationDate" class="form-label">Certification Date</label>
                                            <input type="date" class="form-control" id="certificationDate" name="certification_date" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="certificateNumber" class="form-label">Certificate Number</label>
                                            <input type="text" class="form-control" id="certificateNumber" name="certificate_number" placeholder="Enter certificate number" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-plus-lg me-1"></i> Assign Certification
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Certifications Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="m-0">Assigned Certifications</h6>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-web" type="button" id="certificationOptions" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="certificationOptions">
                                            <li><a class="dropdown-item" href="#">Export Report</a></li>
                                            <li><a class="dropdown-item" href="#">Print Certificates</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Course</th>
                                                    <th>Certificate Number</th>
                                                    <th>Certification Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($certifications as $cert): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($cert['student_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($cert['course_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($cert['certificate_number']); ?></td>
                                                        <td><?php echo htmlspecialchars($cert['certification_date']); ?></td>
                                                        <td>
                                                            <span class="badge <?php echo $cert['status'] === 'Issued' ? 'bg-success' : ($cert['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                                                <?php echo htmlspecialchars($cert['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <!-- Edit Button -->
                                                            <button class="btn btn-sm btn-outline-primary me-1 edit-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editCertificationModal"
                                                                    data-cert-id="<?php echo htmlspecialchars($cert['certification_id']); ?>"
                                                                    data-student-id="<?php echo htmlspecialchars($cert['student_id']); ?>"
                                                                    data-course-id="<?php echo htmlspecialchars($cert['course_id']); ?>"
                                                                    data-cert-number="<?php echo htmlspecialchars($cert['certificate_number']); ?>"
                                                                    data-cert-date="<?php echo htmlspecialchars($cert['certification_date']); ?>">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <!-- Approve/Reject Buttons -->
                                                            <?php if ($cert['status'] === 'Pending'): ?>
                                                                <form action="update_certification.php" method="POST" style="display:inline;">
                                                                    <input type="hidden" name="certification_id" value="<?php echo htmlspecialchars($cert['certification_id']); ?>">
                                                                    <input type="hidden" name="status" value="Issued">
                                                                    <button type="submit" class="btn btn-sm btn-outline-success me-1">
                                                                        <i class="bi bi-check-circle"></i>
                                                                    </button>
                                                                </form>
                                                                <form action="update_certification.php" method="POST" style="display:inline;">
                                                                    <input type="hidden" name="certification_id" value="<?php echo htmlspecialchars($cert['certification_id']); ?>">
                                                                    <input type="hidden" name="status" value="Rejected">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger me-1">
                                                                        <i class="bi bi-x-circle"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            <!-- Delete Button -->
                                                            <form action="delete_certification.php" method="POST" style="display:inline;">
                                                                <input type="hidden" name="certification_id" value="<?php echo htmlspecialchars($cert['certification_id']); ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger delete-btn">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Certification Modal -->
                    <div class="modal fade" id="editCertificationModal" tabindex="-1" aria-labelledby="editCertificationModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCertificationModalLabel">Edit Certification</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editCertificationForm" action="update_certification.php" method="POST">
                                        <input type="hidden" name="certification_id" id="editCertificationId">
                                        <div class="mb-3">
                                            <label for="editStudentSelect" class="form-label">Select Student</label>
                                            <select class="form-select" id="editStudentSelect" name="student_id" required>
                                                <option value="" disabled>Select a student</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                                        <?php echo htmlspecialchars($student['student_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editCourseSelect" class="form-label">Select Course</label>
                                            <select class="form-select" id="editCourseSelect" name="course_id" required>
                                                <option value="" disabled>Select a course</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editCertificationDate" class="form-label">Certification Date</label>
                                            <input type="date" class="form-control" id="editCertificationDate" name="certification_date" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editCertificateNumber" class="form-label">Certificate Number</label>
                                            <input type="text" class="form-control" id="editCertificateNumber" name="certificate_number" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; ELE TECH 2025</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dashboard.js"></script>
    <script src="certification.js"></script>
</body>
</html>