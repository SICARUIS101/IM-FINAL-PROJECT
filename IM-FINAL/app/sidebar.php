<?php
  $currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
  .navbar {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    padding: 12px 20px;
    background: linear-gradient(to right, #212529, #343a40);
  }

  .navbar .navbar-brand {
    font-weight: bold;
    letter-spacing: 1px;
    font-size: 1.3rem;
    color: #f8f9fa;
  }

  .navbar .btn-outline-light {
    border: none;
    font-size: 1.2rem;
  }

  .navbar .btn-outline-light:hover {
    background-color: #495057;
    color: #ffffff;
  }

  .bi-list {
    font-size: 1.5rem;
  }
</style>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <button class="btn btn-outline-light" id="toggleSidebar">
        <i class="bi bi-list"></i>
      </button>
      <span class="navbar-brand mb-0 h1">ELE TECH</span>
    </div>
  </nav>


<div class="sidebar p-3" id="sidebar">
  <h5 class="text-white mb-4">Menu</h5>

  <a href="index.html" class="nav-link <?= ($currentPage == 'index.php') ? 'active' : '' ?>"><i class="bi bi-house-door"></i> Home</a>

  <!-- Student Menu -->
  <a class="nav-link" data-bs-toggle="collapse" href="#studentMenu" role="button" aria-expanded="false" aria-controls="studentMenu">
    <i class="bi bi-person"></i> Student
  </a>
  <div class="collapse collapse-inner" id="studentMenu">
    <a href="#" class="nav-link">Student List</a>
    <a href="#" class="nav-link">Add Student</a>
  </div>

  <!-- Teacher Menu -->
  <a class="nav-link" data-bs-toggle="collapse" href="#teacherMenu" role="button" aria-expanded="false" aria-controls="teacherMenu">
    <i class="bi bi-person-badge"></i> Teacher
  </a>
  <div class="collapse collapse-inner" id="teacherMenu">
    <a href="teacher_list.php" class="nav-link <?= ($currentPage == 'teacher_list.php') ? 'active' : '' ?>">Teacher List</a>
    <a href="add_teacher.php" class="nav-link <?= ($currentPage == 'add_teacher.php') ? 'active' : '' ?>">Add Teacher</a>
  </div>

  <!-- Attendance Menu -->
  <a class="nav-link" data-bs-toggle="collapse" href="#attendanceMenu" role="button" aria-expanded="false" aria-controls="attendanceMenu">
    <i class="bi bi-clipboard-check"></i> Take Attendance
  </a>
  <div class="collapse collapse-inner" id="attendanceMenu">
    <a href="#" class="nav-link">Teachers</a>
    <a href="#" class="nav-link">Students</a>
  </div>

  <!-- Grades Menu -->
  <a class="nav-link" data-bs-toggle="collapse" href="#gradesMenu" role="button" aria-expanded="false" aria-controls="gradesMenu">
    <i class="bi bi-journal-check"></i> Grades
  </a>
  <div class="collapse collapse-inner" id="gradesMenu">
    <a href="#" class="nav-link">Grades</a>
    <a href="#" class="nav-link">Assessment</a>
  </div>

  <!-- Class Menu -->
  <a class="nav-link" data-bs-toggle="collapse" href="#classMenu" role="button" aria-expanded="false" aria-controls="classMenu">
    <i class="bi bi-building"></i> Class
  </a>
  <div class="collapse collapse-inner" id="classMenu">
    <a href="#" class="nav-link">Add Class</a>
    <a href="#" class="nav-link">Class List</a>
  </div>

  <!-- Other links -->
  <a href="#" class="nav-link"><i class="bi bi-chat-dots"></i> Message Center</a>
  <a href="#" class="nav-link"><i class="bi bi-person-circle"></i> Profile</a>
  <a href="#" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>


