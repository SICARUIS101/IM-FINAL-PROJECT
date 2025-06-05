<?php
session_start();

require_once 'dashboard_data.php';

// Fetch data
$stats = getDashboardStats($pdo);
$recent_activities = getRecentActivities($pdo);
$certification_status = getCertificationStatus($pdo);
$upcoming_assessments = getUpcomingAssessments($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELE TECH - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div id="layoutSidenav">
        <div id="sideNav" class="sb-sidenav">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Dashboard</div>
                    <a class="nav-link active" href="dashboard.php">
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
                            <a class="nav-link collapse-item" href="certification.php">Certification</a>
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
                    <h1 class="mt-4">Dashboard</h1>
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card stats-card-primary mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Total Students</div>
                                            <h4 class="mt-2 mb-0"><?php echo htmlspecialchars($stats['total_students']); ?></h4>
                                        </div>
                                        <div class="stats-icon text-primary"><i class="fas fa-users"></i></div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-primary stretched-link" href="attendance.php">View Details</a>
                                    <div class="small text-primary"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card stats-card-success mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Teachers</div>
                                            <h4 class="mt-2 mb-0"><?php echo htmlspecialchars($stats['total_teachers']); ?></h4>
                                        </div>
                                        <div class="stats-icon text-success"><i class="fas fa-chalkboard-teacher"></i></div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-success stretched-link" href="teachers.php">View Details</a>
                                    <div class="small text-success"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card stats-card-warning mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Active Courses</div>
                                            <h4 class="mt-2 mb-0"><?php echo htmlspecialchars($stats['active_courses']); ?></h4>
                                        </div>
                                        <div class="stats-icon text-warning"><i class="fas fa-book"></i></div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-warning stretched-link" href="programs.php">View Details</a>
                                    <div class="small text-warning"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card stats-card-danger mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Assessments</div>
                                            <h4 class="mt-2 mb-0"><?php echo htmlspecialchars($stats['certifications']); ?></h4>
                                        </div>
                                        <div class="stats-icon text-danger"><i class="fas fa-certificate"></i></div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-danger stretched-link" href="certification.php">View Details</a>
                                    <div class="small text-danger"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6>Recent Activities</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($recent_activities as $activity): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($activity['activity_type']); ?></strong><br>
                                                    <?php echo htmlspecialchars($activity['description'] . ' - ' . $activity['course_name']); ?>
                                                </div>
                                                <span class="small text-muted"><?php echo date('Y-m-d H:i', strtotime($activity['activity_time'])); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6>Student Certification Status</h6>
                                    <div>
                                        <a class="btn btn-primary btn-sm" href="certification.php">View Details</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger'];
                                    $color_index = 0;
                                    foreach ($certification_status as $status): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div><?php echo htmlspecialchars($status['course_name']); ?></div>
                                                <div class="fw-bold"><?php echo number_format($status['completion_percentage'], 2); ?>%</div>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar <?php echo $colors[$color_index % count($colors)]; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $status['completion_percentage']; ?>%;" 
                                                     aria-valuenow="<?php echo $status['completion_percentage']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <?php $color_index++; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6>Upcoming Assessments</h6>
                                    <div>
                                        <a class="btn btn-primary btn-sm" href="assessments.php">Add New</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Course</th>
                                                    <th>Scheduled Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $status_colors = [
                                                    'Pending' => 'bg-warning',
                                                    'Scheduled' => 'bg-success',
                                                    'Completed' => 'bg-primary',
                                                    'To be assessed' => 'bg-info',
                                                    'Cancelled' => 'bg-danger'
                                                ];
                                                foreach ($upcoming_assessments as $assessment): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($assessment['title']); ?></td>
                                                        <td><?php echo htmlspecialchars($assessment['course']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($assessment['scheduled_date'])); ?></td>
                                                        <td><span class="badge <?php echo $status_colors[$assessment['status']] ?? 'bg-secondary'; ?>">
                                                            <?php echo htmlspecialchars($assessment['status']); ?>
                                                        </span></td>
                                                        <td>
                                                            <button class="btn btn-primary btn-sm" onclick="editAssessment(<?php echo $assessment['assessment_id']; ?>)"><i class="fas fa-edit"></i></button>
                                                            <button class="btn btn-danger btn-sm" onclick="deleteAssessment(<?php echo $assessment['assessment_id']; ?>)"><i class="fas fa-trash"></i></button>
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
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright © ELE TECH 2025</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            ·
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="dashboard.js"></script>
    <script>
        function editAssessment(id) {
            // Implement edit functionality (e.g., redirect to edit page or show modal)
            window.location.href = 'assessments.php?edit=' + id;
        }

        function deleteAssessment(id) {
            if (confirm('Are you sure you want to delete this assessment?')) {
                $.post('delete_assessment.php', { assessment_id: id }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting assessment: ' + response.error);
                    }
                }, 'json');
            }
        }
    </script>
</body>
</html>