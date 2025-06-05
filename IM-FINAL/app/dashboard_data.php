<?php
require_once 'config.php';

function getDashboardStats($pdo) {
    $stats = [
        'total_students' => 0,
        'total_teachers' => 0,
        'active_courses' => 0,
        'certifications' => 0
    ];

    // Total Students
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM students");
    $stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total Teachers
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM teacher");
    $stats['total_teachers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Active Courses
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM courses");
    $stats['active_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Certifications Issued
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM assessments WHERE certification_status = 'Issued'");
    $stats['certifications'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    return $stats;
}

function getRecentActivities($pdo, $limit = 5) {
    $query = "
        SELECT 
            CASE 
                WHEN a.assessment_id IS NOT NULL THEN 'Assessment Completed'
                ELSE 'New Student Registration'
            END AS activity_type,
            COALESCE(a.assessment_title, CONCAT(s.first_name, ' ', s.last_name, ' registered')) AS description,
            COALESCE(c.course_name, c2.course_name) AS course_name,
            COALESCE(a.created_at, s.created_at) AS activity_time
        FROM students s
        LEFT JOIN courses c2 ON s.course_id = c2.course_id
        LEFT JOIN assessments a ON a.student_id = s.student_id
        LEFT JOIN courses c ON a.course_id = c.course_id
        WHERE a.created_at IS NOT NULL OR s.created_at IS NOT NULL
        ORDER BY COALESCE(a.created_at, s.created_at) DESC
        LIMIT :limit
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCertificationStatus($pdo) {
    $query = "
        SELECT 
            c.course_name,
            COUNT(smp.progress_id) AS completed_modules,
            COUNT(m.module_id) AS total_modules,
            ROUND(COUNT(smp.progress_id) * 100.0 / NULLIF(COUNT(m.module_id), 0), 2) AS completion_percentage
        FROM courses c
        LEFT JOIN modules m ON c.course_id = m.course_id
        LEFT JOIN student_module_progress smp ON m.module_id = smp.module_id AND smp.is_completed = TRUE
        GROUP BY c.course_id, c.course_name
        HAVING COUNT(m.module_id) > 0
        ORDER BY c.course_name
    ";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUpcomingAssessments($pdo, $limit = 5) {
    $query = "
        SELECT 
            a.assessment_title AS title,
            c.course_name AS course,
            a.date_conducted AS scheduled_date,
            a.status,
            a.assessment_id
        FROM assessments a
        JOIN courses c ON a.course_id = c.course_id
        WHERE a.date_conducted >= CURRENT_DATE
        ORDER BY a.date_conducted ASC
        LIMIT :limit
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>