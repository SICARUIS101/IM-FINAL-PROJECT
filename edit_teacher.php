<?php
require_once 'Teacher.php';
include 'sidebar.php';

$teacher = new Teacher();
$id = $_GET['id'];
$current = $teacher->getById($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher->updateTeacher($id, $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['subject']);
    header("Location: teacher_list.php");
}
?>

<!-- sidebar.php -->
<div class="sidebar p-3" id="sidebar">
  <h5 class="text-white mb-4">Menu</h5>
  <a href="index.html" class="nav-link"><i class="bi bi-house-door"></i> Home</a>

  <!-- Student Menu -->
  <a class="nav-link" data-bs-toggle="collapse" href="#studentMenu">
        <i class="bi bi-person"></i> Student
      </a>
      <div class="collapse collapse-inner" id="studentMenu">
        <a href="#" class="nav-link">Student List</a>
        <a href="#" class="nav-link">Add Student</a>
      </div>

     

     <!-- Teacher Menu -->
      <a class="nav-link" data-bs-toggle="collapse" href="#teacherMenu">
        <i class="bi bi-person-badge"></i> Teacher
      </a>
      <div class="collapse collapse-inner" id="teacherMenu">
        <a href="#" class="nav-link">Teacher List</a>
        <a href="#" class="nav-link">Add Teacher</a>
      </div>
  

      <!-- Attendance Menu -->
      <a class="nav-link" data-bs-toggle="collapse" href="#attendanceMenu">
        <i class="bi bi-clipboard-check"></i> Take Attendance
      </a>
      <div class="collapse collapse-inner" id="attendanceMenu">
        <a href="#" class="nav-link">Teachers</a>
        <a href="#" class="nav-link">Students</a>
      </div>

      <!-- Grades Menu -->
      <a class="nav-link" data-bs-toggle="collapse" href="#gradesMenu">
        <i class="bi bi-journal-check"></i> Grades
      </a>
      <div class="collapse collapse-inner" id="gradesMenu">
        <a href="#" class="nav-link">Grades</a>
        <a href="#" class="nav-link">Assessment</a>
      </div>

      <!-- Class Menu -->
      <a class="nav-link" data-bs-toggle="collapse" href="#classMenu">
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

</div>


<div class="main p-4">
  <h3>Edit Teacher</h3>
  <form method="POST">
    <input class="form-control mb-2" name="first_name" value="<?= $current['first_name'] ?>" required>
    <input class="form-control mb-2" name="last_name" value="<?= $current['last_name'] ?>" required>
    <input class="form-control mb-2" name="email" type="email" value="<?= $current['email'] ?>" required>
    <input class="form-control mb-2" name="subject" value="<?= $current['subject'] ?>" required>
    <button class="btn btn-success" type="submit">Update</button>
  </form>
</div>
