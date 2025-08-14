CREATE TABLE student_checklists (
    student_id VARCHAR(50) NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    final_grade VARCHAR(10) DEFAULT '',
    evaluator_remarks VARCHAR(255) DEFAULT 'Pending',
    professor_instructor VARCHAR(255) DEFAULT '',
    PRIMARY KEY (student_id, course_code),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (course_code) REFERENCES checklist_bscs(course_code)
);
CREATE TABLE adviser (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    sex ENUM('Male', 'Female') NOT NULL,
    pronoun ENUM('Mr.', 'Ms.', 'Mrs.') NOT NULL
);
CREATE TABLE admins (
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL PRIMARY KEY UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);


CREATE TABLE students (
    student_id VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    contact_no VARCHAR(50),
    address VARCHAR(255),
    admission_date DATE,
    picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id)
);

ALTER TABLE students ADD COLUMN status VARCHAR(20) DEFAULT 'pending';

CREATE TABLE adviser_batch (
    id INT,
    batch INT CHECK (batch >= 2101 AND batch <= 9999),
    PRIMARY KEY (id, batch),
    FOREIGN KEY (id) REFERENCES adviser(id)
);

CREATE TABLE checklist_bscs (
    course_code VARCHAR(20) NOT NULL,
    course_title VARCHAR(100),
    credit_unit_lec INT,
    credit_unit_lab INT,
    contact_hrs_lec INT,
    contact_hrs_lab INT,
    pre_requisite VARCHAR(50),
    year VARCHAR(20),    
    semester VARCHAR(20),
    PRIMARY KEY (course_code)
);


ALTER TABLE student_checklists ADD UNIQUE(student_id, course_code);

ALTER TABLE student_checklists
MODIFY COLUMN evaluator_remarks VARCHAR(255);


-- FIRST YEAR, First Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('GNED 02', 'Ethics', 3, 0, 3, 0, 'NONE', '1st Yr', '1st Sem'),
('GNED 05', 'Purposive Communication', 3, 0, 3, 0, 'NONE', '1st Yr', '1st Sem'),
('GNED 11', 'Kontesktwalisadong Komunikasyon sa Filipino', 3, 0, 3, 0, 'NONE', '1st Yr', '1st Sem'),
('COSC 50', 'Discrete Structures I', 3, 0, 3, 0, 'NONE', '1st Yr', '1st Sem'),
('DCIT 21', 'Introduction to Computing', 2, 1, 2, 3, 'NONE', '1st Yr', '1st Sem'),
('DCIT 22', 'Computer Programming I', 1, 2, 1, 6, 'NONE', '1st Yr', '1st Sem'),
('FITT 1', 'Movement Enhancement', 2, 0, 2, 0, 'NONE', '1st Yr', '1st Sem'),
('NSTP 1', 'National Service Training Program 1', 3, 0, 3, 0, 'NONE', '1st Yr', '1st Sem'),
('CvSU 101', 'Institutional Orientation (non-credit)', 1, 0, 1, 0, 'NONE', '1st Yr', '1st Sem');

-- FIRST YEAR, Second Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('GNED 01', 'Art Appreciation', 3, 0, 3, 0, 'NONE', '1st Yr', '2nd Sem'),
('GNED 03', 'Mathematics in the Modern World', 3, 0, 3, 0, 'NONE', '1st Yr', '2nd Sem'),
('GNED 06', 'Science, Technology and Society', 3, 0, 3, 0, 'NONE', '1st Yr', '2nd Sem'),
('GNED 12', 'Dalumat Ng/Sa Filipino', 3, 0, 3, 0, 'GNED 11', '1st Yr', '2nd Sem'),
('DCIT 23', 'Computer Programming II', 1, 2, 1, 6, 'DCIT 22', '1st Yr', '2nd Sem'),
('ITEC 50', 'Web Systems and Technologies', 2, 1, 2, 3, 'DCIT 21', '1st Yr', '2nd Sem'),
('FITT 2', 'Fitness Exercises', 2, 0, 2, 0, 'FITT 1', '1st Yr', '2nd Sem'),
('NSTP 2', 'National Service Training Program 2', 3, 0, 3, 0, 'NSTP 1', '1st Yr', '2nd Sem');

-- SECOND YEAR, First Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('GNED 04', 'Mga Babasahin Hinggil sa Kasaysayan ng Pilipinas', 3, 0, 3, 0, 'NONE', '2nd Yr', '1st Sem'),
('MATH 1', 'Analytic Geometry', 3, 0, 3, 0, 'GNED 03', '2nd Yr', '1st Sem'),
('COSC 55', 'Discrete Structures II', 3, 0, 3, 0, 'COSC 50', '2nd Yr', '1st Sem'),
('COSC 60', 'Digital Logic Design', 2, 1, 2, 3, 'COSC 50, DCIT 23', '2nd Yr', '1st Sem'),
('DCIT 50', 'Object Oriented Programming', 2, 1, 2, 3, 'DCIT 23', '2nd Yr', '1st Sem'),
('DCIT 24', 'Information Management', 2, 1, 2, 3, 'DCIT 23', '2nd Yr', '1st Sem'),
('INSY 50', 'Fundamentals of Information Systems', 3, 0, 3, 0, 'DCIT 21', '2nd Yr', '1st Sem'),
('FITT 3', 'Physical Activities towards Health and Fitness 1', 2, 0, 2, 0, 'FITT 1', '2nd Yr', '1st Sem');

-- SECOND YEAR, Second Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('GNED 08', 'Understanding the Self', 3, 0, 3, 0, 'NONE', '2nd Yr', '2nd Sem'),
('GNED 14', 'Panitikang Panlipunan', 3, 0, 3, 0, 'NONE', '2nd Yr', '2nd Sem'),
('MATH 2', 'Calculus', 3, 0, 3, 0, 'GNED 03', '2nd Yr', '2nd Sem'),
('COSC 65', 'Computer Architecture and Organization', 2, 1, 2, 3, 'COSC 60', '2nd Yr', '2nd Sem'),
('COSC 70', 'Software Engineering I', 3, 0, 3, 0, 'DCIT 50, DCIT 24', '2nd Yr', '2nd Sem'),
('DCIT 25', 'Data Structures and Algorithms', 2, 1, 2, 3, 'DCIT 23', '2nd Yr', '2nd Sem'),
('DCIT 55', 'Advanced Database Management System', 2, 1, 2, 3, 'DCIT 24', '2nd Yr', '2nd Sem'),
('FITT 4', 'Physical Activities towards Health and Fitness 2', 2, 0, 2, 0, 'FITT 1', '2nd Yr', '2nd Sem');

-- THIRD YEAR, First Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('MATH 3', 'Linear Algebra', 3, 0, 3, 0, 'MATH 2', '3rd Yr', '1st Sem'),
('COSC 75', 'Software Engineering II', 2, 1, 2, 3, 'COSC 70', '3rd Yr', '1st Sem'),
('COSC 80', 'Operating Systems', 2, 1, 2, 3, 'DCIT 25', '3rd Yr', '1st Sem'),
('COSC 85', 'Networks and Communication', 2, 1, 2, 3, 'ITEC 50', '3rd Yr', '1st Sem'),
('COSC 101', 'CS Elective 1 (Computer Graphics and Visual Computing)', 2, 1, 2, 3, 'DCIT 23', '3rd Yr', '1st Sem'),
('DCIT 26', 'Applications Dev’t and Emerging Technologies', 2, 1, 2, 3, 'ITEC 50', '3rd Yr', '1st Sem'),
('DCIT 65', 'Social and Professional Issues', 3, 0, 3, 0, 'NONE', '3rd Yr', '1st Sem');

-- THIRD YEAR, Second Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('GNED 09', 'Life and Works of Rizal', 3, 0, 3, 0, 'GNED 04', '3rd Yr', '2nd Sem'),
('MATH 4', 'Experimental Statistics', 2, 1, 2, 3, 'MATH 2', '3rd Yr', '2nd Sem'),
('COSC 90', 'Design and Analysis of Algorithm', 3, 0, 3, 0, 'DCIT 25', '3rd Yr', '2nd Sem'),
('COSC 95', 'Programming Languages', 3, 0, 3, 0, 'DCIT 25', '3rd Yr', '2nd Sem'),
('COSC 106', 'CS Elective 2 (Introduction to Game Development)', 2, 1, 2, 3, 'MATH 3, COSC 101', '3rd Yr', '2nd Sem'),
('DCIT 60', 'Methods of Research', 3, 0, 3, 0, '3rd year Standing', '3rd Yr', '2nd Sem'),
('ITEC 85', 'Information Assurance and Security', 3, 0, 3, 0, 'DCIT 24', '3rd Yr', '2nd Sem');

-- THIRD YEAR, Mid Year
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('COSC 199', 'Practicum (minimum of 200 hrs.)', 3, 0, 0, 0, 'Incoming 4th Yr.', '3rd Yr', 'Mid Year');

-- FOURTH YEAR, First Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('ITEC 80', 'Human Computer Interaction', 3, 0, 3, 0, 'ITEC 85', '4th Yr', '1st Sem'),
('COSC 100', 'Automata Theory and Formal Languages', 3, 0, 3, 0, 'COSC 90', '4th Yr', '1st Sem'),
('COSC 105', 'Intelligent Systems', 2, 1, 2, 3, 'MATH 4, COSC 55, DCIT 50', '4th Yr', '1st Sem'),
('COSC 111', 'CS Elective 3 (Internet of Things)', 2, 1, 2, 3, 'COSC 60', '4th Yr', '1st Sem'),
('COSC 200A', 'Undergraduate Thesis I', 3, 0, 3, 0, '4th year Standing', '4th Yr', '1st Sem');

-- FOURTH YEAR, Second Semester
INSERT INTO checklist_bscs (course_code, course_title, credit_unit_lec, credit_unit_lab, contact_hrs_lec, contact_hrs_lab, pre_requisite, year, semester)
VALUES 
('GNED 07', 'The Contemporary World', 3, 0, 3, 0, 'NONE' ,'4th Yr', '2nd Sem'),
('GNED 10', 'Gender and Society', 3, 0, 3, 0,'NONE'  ,'4th Yr', '2nd Sem'),
('COSC 110', 'Numerical and Symbolic Computation', 2, 1, 2, 3, 'COSC 60', '4th Yr', '2nd Sem'),
('COSC 200B', 'Undergraduate Thesis II', 3, 0, 3, 0, 'COSC 200A', '4th Yr', '2nd Sem');

-------------------------------------------------------------------------------------------------------

ALTER TABLE student_checklists
DROP COLUMN id;

ALTER TABLE students ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending';

ALTER TABLE adviser_batch DROP FOREIGN KEY adviser_batch_ibfk_1;



ALTER TABLE adviser_batch ADD CONSTRAINT adviser_batch_ibfk_1 FOREIGN KEY (id) REFERENCES adviser(id);

CREATE TABLE batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch VARCHAR(255) NOT NULL
);

------------------------------------------------------------
ALTER TABLE adviser_batch DROP PRIMARY KEY;
ALTER TABLE adviser_batch ADD PRIMARY KEY (id, batch);

-------------------------------------------------------------------------

ALTER TABLE student_checklists MODIFY COLUMN professor_instructor VARCHAR(255) DEFAULT '';


ALTER TABLE student_checklists DROP COLUMN professor_instructor;
ALTER TABLE student_checklists ADD COLUMN professor_instructor VARCHAR(255) DEFAULT '';

-----------------------------------------------------------------------------------------------

DROP TABLE IF EXISTS pre_enrollment_courses;
DROP TABLE IF EXISTS pre_enrollments;

CREATE TABLE pre_enrollments (
    id VARCHAR(50) PRIMARY KEY,
    student_id VARCHAR(50),
    name VARCHAR(255),
    year_level VARCHAR(20),
    course VARCHAR(50),
    section_major VARCHAR(100),
    classification VARCHAR(50),
    registration_status VARCHAR(50),
    scholarship_awarded VARCHAR(255),
    mode_of_payment VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pre_enrollment_courses (
    id VARCHAR(50) PRIMARY KEY,
    pre_enrollment_id VARCHAR(50),
    course_codes TEXT NOT NULL,
    course_titles TEXT NOT NULL,
    units TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pre_enrollment_id) REFERENCES pre_enrollments(id) ON DELETE CASCADE
);

