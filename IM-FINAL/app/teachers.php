<?php
session_start();

class Database {
    private $conn;
    
    public function __construct() {
        $host = 'localhost';
        $dbname = 'EletechTrack';
        $username = 'postgres';
        $password = 'almartinez';
        
        try {
            $this->conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

class Teacher {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAllTeachers($search = '', $sort = 'ASC') {
        $query = "SELECT * FROM teacher";
        if (!empty($search)) {
            $query .= " WHERE name ILIKE :search";
        }
        $query .= " ORDER BY name $sort";
        
        $stmt = $this->db->prepare($query);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addTeacher($name, $phone, $email, $emergency_contact) {
        $query = "INSERT INTO teacher (name, phone, email, emergency_contact) 
                 VALUES (:name, :phone, :email, :emergency_contact)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':emergency_contact', $emergency_contact);
        return $stmt->execute();
    }
    
    public function deleteTeacher($id) {
        $query = "DELETE FROM teacher WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

// Initialize database and teacher objects
$db = new Database();
$teacher = new Teacher($db->getConnection());

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
                $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $emergency_contact = filter_input(INPUT_POST, 'emergency_contact', FILTER_SANITIZE_STRING);
                
                if ($name && $phone && $email && $emergency_contact) {
                    $teacher->addTeacher($name, $phone, $email, $emergency_contact);
                }
                break;
                
            case 'delete':
                $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
                if ($id) {
                    $teacher->deleteTeacher($id);
                }
                break;
        }
    }
    header("Location: teachers.php");
    exit();
}

// Get search and sort parameters
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '';
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'ASC';
$teachers = $teacher->getAllTeachers($search, $sort);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELE TECH - Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
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
                            <a class="nav-link collapse-item" href="attendance.php">Attendance</a>
                            <a class="nav-link collapse-item" href="assessments.php">Assessments</a>
                            <a class="nav-link collapse-item" href="certification.php">Certification</a>
                            <a class="nav-link collapse-item" href="module_completion.php">Module Progress</a>
                        </nav>
                    </div>
                    <div class="sb-sidenav-menu-heading">Administration</div>
                    <a class="nav-link active" href="teachers.php">
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
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Teacher List</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Teacher List</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6><i class="bi bi-people-fill me-2"></i>All Teachers</h6>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                                <i class="bi bi-plus-lg me-1"></i>Add Teacher
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-4">
                                    <label for="searchTeacherInput" class="form-label visually-hidden">Search Teacher Name</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="searchTeacherInput" name="search" placeholder="Search by teacher name..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="searchTeacherBtn"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="btn-group float-end" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm <?php echo $sort === 'ASC' ? 'active' : ''; ?>" id="sortTeacherAZ" data-sort="ASC">
                                            A-Z <i class="bi bi-sort-alpha-down"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm <?php echo $sort === 'DESC' ? 'active' : ''; ?>" id="sortTeacherZA" data-sort="DESC">
                                            Z-A <i class="bi bi-sort-alpha-down-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3" id="teacherCardContainer">
                                <?php if (empty($teachers)): ?>
                                    <div class="col-12" id="noTeachersMessage">
                                        <p class="text-center text-muted py-5">No teachers found or still loading...</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Email</th>
                                                    <th>Emergency Contact</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($teachers as $t): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($t['phone']); ?></td>
                                                        <td><?php echo htmlspecialchars($t['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($t['emergency_contact']); ?></td>
                                                        <td>
                                                            <form action="teachers.php" method="POST" style="display:inline;">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this teacher?')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto"></footer>
        </div>
    </div>

    < - Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="teachers.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTeacherModalLabel">Add Teacher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="teacherName" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="teacherName" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="teacherPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="teacherPhone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="teacherEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="teacherEmail" name="email" personally-identifiable="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="teacherEmergencyContact" class="form-label">Emergency Contact <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="teacherEmergencyContact" name="emergency_contact" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Teacher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="teachers.js"></script>
</body>
</html>