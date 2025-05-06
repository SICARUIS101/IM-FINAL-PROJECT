<?php
require_once 'Teacher.php';

if (isset($_GET['id'])) {
    $teacher = new Teacher();
    $teacher->deleteTeacher($_GET['id']);
}
header("Location: teacher_list.php");
