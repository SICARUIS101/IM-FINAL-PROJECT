<?php
require_once 'Teacher.php';
include 'sidebar.php';

$teacher = new Teacher();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher->addTeacher($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['subject']);
    header("Location: teacher_list.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Teacher</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      overflow-x: hidden;
    }
    .wrapper {
      display: flex;
      min-height: 100vh;
      flex-grow: 1;
      padding: 20px;
      transition: margin-left 0.3s ease;
    
    }
    .sidebar {
      width: 250px;
      background-color: #343a40;
      color: white;
      transition: transform 0.3s ease;
    }
    .sidebar.collapsed {
      transform: translateX(-100%);
    }
    .sidebar .nav-link {
      color: white;
      padding: 10px 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: #495057;
    }
    .collapse-inner {
      padding-left: 20px;
    }
    .main {
      flex-grow: 1;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }
    @media (max-width: 768px) {
      .sidebar {
        position: absolute;
        height: 100%;
        z-index: 1040;
      }
    }
  </style>
</head>
<body>

  <div class="wrapper">
    <!-- Sidebar included earlier -->
    <div class="main">
      <h3 class="mb-4">Add Teacher</h3>
      <form method="POST" class="w-50">
        <input class="form-control mb-2" type="text" name="first_name" placeholder="First Name" required>
        <input class="form-control mb-2" type="text" name="last_name" placeholder="Last Name" required>
        <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
        <input class="form-control mb-3" type="text" name="subject" placeholder="Subject" required>
        <button class="btn btn-primary" type="submit">Add Teacher</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
    });
  </script>
</body>
</html>
