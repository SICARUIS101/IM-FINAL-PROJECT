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

class Registrar {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAllAdmins($search = '', $sort = 'ASC') {
        $query = "SELECT registrar_id, username FROM registrar";
        if (!empty($search)) {
            $query .= " WHERE username ILIKE :search";
        }
        $query .= " ORDER BY username $sort";
        
        $stmt = $this->db->prepare($query);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addAdmin($username, $password) {
        $query = "INSERT INTO registrar (username, password) VALUES (:username, :password)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        return $stmt->execute();
    }
    
    public function deleteAdmin($id) {
        $query = "DELETE FROM registrar WHERE registrar_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

// Initialize database and registrar objects
$db = new Database();
$registrar = new Registrar($db->getConnection());

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
                $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
                
                if ($username && $password) {
                    $registrar->addAdmin($username, $password);
                }
                break;
                
            case 'delete':
                $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
                if ($id) {
                    $registrar->deleteAdmin($id);
                }
                break;
        }
    }
    header("Location: admin_page.php");
    exit();
}

// Get search and sort parameters
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '';
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'ASC';
$admins = $registrar->getAllAdmins($search, $sort);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELE TECH - Admins</title>
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
                            <a class="nav-link collapse-item" href="attendance.html">Attendance</a>
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
                    <a class="nav-link active" href="admin_page.php">
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
                <button class="btn menu-toggle ms-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
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
                    <h1 class="mt-4">Admin List</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Admin List</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6><i class="bi bi-people-fill me-2"></i>All Admins</h6>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                                <i class="bi bi-plus-lg me-1"></i>Add Admin
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-4">
                                    <label for="searchAdminInput" class="form-label visually-hidden">Search Admin Username</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="searchAdminInput" name="search" placeholder="Search by admin username..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="searchAdminBtn"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="btn-group float-end" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm <?php echo $sort === 'ASC' ? 'active' : ''; ?>" id="sortAdminAZ" data-sort="ASC">
                                            A-Z <i class="bi bi-sort-alpha-down"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm <?php echo $sort === 'DESC' ? 'active' : ''; ?>" id="sortAdminZA" data-sort="DESC">
                                            Z-A <i class="bi bi-sort-alpha-down-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3" id="adminCardContainer">
                                <?php if (empty($admins)): ?>
                                    <div class="col-12" id="noAdminsMessage">
                                        <p class="text-center text-muted py-5">No admins found or still loading...</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($admins as $admin): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                                        <td>
                                                            <form action="admin_page.php" method="POST" style="display:inline;">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="id" value="<?php echo $admin['registrar_id']; ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this admin?')">
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

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="admin_page.php">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAdminModalLabel">Add Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adminUsername" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="adminUsername" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adminPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="adminPassword" name="password" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="admin_page.js"></script>
</body>
</html>
