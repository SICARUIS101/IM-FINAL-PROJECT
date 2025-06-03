
-- Drop and recreate tables (removing gender from students table)
DROP TABLE IF EXISTS attendance CASCADE;
DROP TABLE IF EXISTS students CASCADE;
DROP TABLE IF EXISTS courses CASCADE;

-- Create courses table (unchanged)
CREATE TABLE IF NOT EXISTS courses (
    course_id SERIAL PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL UNIQUE
);

-- Insert courses data (unchanged)
INSERT INTO courses (course_name) VALUES
('Computer System Servicing NC II'),
('Dressmaking NC II'),
('Electronic Products Assembly Servicing NC II'),
('Shielded Metal Arc Welding (SMAW) NC I'),
('Shielded Metal Arc Welding (SMAW) NC II')
ON CONFLICT (course_name) DO NOTHING;

-- Create students table (removed gender column)
CREATE TABLE IF NOT EXISTS students (
    student_id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birthdate DATE NOT NULL,
    contact_number VARCHAR(12),
    course_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE RESTRICT
);

-- Attendance table (unchanged)
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    attendance_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL CHECK (status IN ('Present', 'Absent', 'Late')),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE RESTRICT,
    UNIQUE(student_id, course_id, attendance_date)
);

-- Indexes (unchanged)
CREATE INDEX IF NOT EXISTS idx_students_course_id ON students(course_id);
CREATE INDEX IF NOT EXISTS idx_students_last_name ON students(last_name);
CREATE INDEX IF NOT EXISTS idx_attendance_student_id ON attendance(student_id);
CREATE INDEX IF NOT EXISTS idx_attendance_course_id ON attendance(course_id);
CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(attendance_date);
CREATE INDEX IF NOT EXISTS idx_attendance_student_date ON attendance(student_id, course_id, attendance_date);

-- Trigger function (unchanged)
CREATE OR REPLACE FUNCTION set_course_id_from_student()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.course_id IS NULL THEN
        SELECT course_id INTO NEW.course_id FROM students WHERE student_id = NEW.student_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


DROP TRIGGER IF EXISTS trg_set_course_id ON attendance;
CREATE TRIGGER trg_set_course_id
BEFORE INSERT ON attendance
FOR EACH ROW
EXECUTE FUNCTION set_course_id_from_student();

-- Verification queries (modified to remove gender)
SELECT 'COURSES VERIFICATION:' AS info;
SELECT course_id, course_name FROM courses ORDER BY course_id;

SELECT 'Total courses:' AS info, COUNT(*) AS count FROM courses;

SELECT 'Checking course_id=2:' AS info;
SELECT * FROM courses WHERE course_id = 2;

SELECT 'EXISTING STUDENTS:' AS info;

SELECT s.student_id, s.first_name, s.last_name, s.course_id, c.course_name
FROM students s
LEFT JOIN courses c ON s.course_id = c.course_id
ORDER BY s.student_id;

SELECT 'ORPHANED STUDENTS (invalid course_id):' AS info;

SELECT s.student_id, s.first_name, s.last_name, s.course_id
FROM students s
LEFT JOIN courses c ON s.course_id = c.course_id
WHERE c.course_id IS NULL;

-- Safe insert student function (removed gender parameter)
CREATE OR REPLACE FUNCTION safe_insert_student(
    p_first_name VARCHAR(100),
    p_last_name VARCHAR(100),
    p_birthdate DATE,
    p_contact_number VARCHAR(12),
    p_course_name VARCHAR(255)
)
RETURNS INTEGER AS $$
DECLARE
    v_course_id INTEGER;
    v_student_id INTEGER;
BEGIN
    SELECT course_id INTO v_course_id 
    FROM courses 
    WHERE course_name = p_course_name;
    
    IF v_course_id IS NULL THEN
        RAISE EXCEPTION 'Course "%" does not exist', p_course_name;
    END IF;
    
    INSERT INTO students (first_name, last_name, birthdate, contact_number, course_id)
    VALUES (p_first_name, p_last_name, p_birthdate, p_contact_number, v_course_id)
    RETURNING student_id INTO v_student_id;
    
    RETURN v_student_id;
END;
$$ LANGUAGE plpgsql;

-- Modified reporting queries
SELECT 
    s.student_id, 
    s.first_name, 
    s.last_name, 
    s.birthdate, 
    s.contact_number, 
    c.course_name AS course, 
    s.created_at 
FROM students s
JOIN courses c ON s.course_id = c.course_id
ORDER BY s.last_name ASC, s.first_name ASC;

-- Student count by course (unchanged)
SELECT 
    c.course_name AS course,
    COUNT(s.student_id) AS student_count
FROM courses c
LEFT JOIN students s ON s.course_id = c.course_id
GROUP BY c.course_id, c.course_name
ORDER BY c.course_name;

-- Get available courses for dropdown (unchanged)
SELECT course_id, course_name FROM courses ORDER BY course_name;

SELECT * FROM students;
