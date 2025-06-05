<?php
class Database {
    private $host = "localhost";
    private $db_name = "EletechTrack";
    private $username = "postgres";
    private $password = "almartinez";
    private $conn;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        try {
            $this->conn = new PDO("pgsql:host={$this->host};dbname={$this->db_name}", 
                                  $this->username, 
                                  $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }

    public function getStudents() {
        $stmt = $this->conn->query("SELECT student_id, CONCAT(first_name, ' ', last_name) AS student_name FROM students ORDER BY last_name, first_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourses() {
        $stmt = $this->conn->query("SELECT course_id, course_name FROM courses ORDER BY course_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCertifications() {
        $stmt = $this->conn->query("
            SELECT 
                c.certification_id,
                c.student_id,
                c.course_id,
                CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                co.course_name,
                c.certificate_number,
                c.certification_date,
                c.status
            FROM certifications c
            JOIN students s ON c.student_id = s.student_id
            JOIN courses co ON c.course_id = co.course_id
            ORDER BY c.certification_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignCertification($student_id, $course_id, $certificate_number, $certification_date) {
        $sql = "INSERT INTO certifications (student_id, course_id, certificate_number, certification_date, status)
                VALUES (:student_id, :course_id, :certificate_number, :certification_date, 'Pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':student_id' => $student_id,
            ':course_id' => $course_id,
            ':certificate_number' => $certificate_number,
            ':certification_date' => $certification_date
        ]);
        return $this->conn->lastInsertId();
    }

    public function deleteCertification($certification_id) {
        $sql = "DELETE FROM certifications WHERE certification_id = :certification_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':certification_id' => $certification_id]);
        return $stmt->rowCount();
    }

    public function updateCertificationStatus($certificationId, $status) {
        $stmt = $this->conn->prepare("UPDATE certifications SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE certification_id = :cert_id");
        return $stmt->execute([':status' => $status, ':cert_id' => $certificationId]);
    }

    public function updateCertification($certificationId, $studentId, $courseId, $certificateNumber, $certificationDate, $status = null) {
        $sql = "UPDATE certifications SET student_id = :student_id, course_id = :course_id, certificate_number = :cert_number, certification_date = :cert_date";
        if ($status !== null) {
            $sql .= ", status = :status";
        }
        $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE certification_id = :cert_id";
        $stmt = $this->conn->prepare($sql);
        $params = [
            ':student_id' => $studentId,
            ':course_id' => $courseId,
            ':cert_number' => $certificateNumber,
            ':cert_date' => $certificationDate,
            ':cert_id' => $certificationId
        ];
        if ($status !== null) {
            $params[':status'] = $status;
        }
        return $stmt->execute($params);
    }
}
?>