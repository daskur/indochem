CREATE DATABASE IF NOT EXISTS ipla CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ipla;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS quiz_attempts;
DROP TABLE IF EXISTS quiz_answers;
DROP TABLE IF EXISTS quiz_questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS user_progress;
DROP TABLE IF EXISTS siwes_reports;
DROP TABLE IF EXISTS siwes_logbooks;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS glossary;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS process_flow_diagrams;
DROP TABLE IF EXISTS process_applications;
DROP TABLE IF EXISTS process_quality_control;
DROP TABLE IF EXISTS process_safety;
DROP TABLE IF EXISTS process_reactions;
DROP TABLE IF EXISTS process_steps;
DROP TABLE IF EXISTS process_equipment;
DROP TABLE IF EXISTS process_materials;
DROP TABLE IF EXISTS process_end_products;
DROP TABLE IF EXISTS processes;
DROP TABLE IF EXISTS chemicals;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    profile_image VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_password_resets_user (user_id),
    KEY idx_password_resets_expiry (expires_at),
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE equipment (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    category VARCHAR(100) NULL,
    description TEXT NULL,
    function TEXT NULL,
    working_principle TEXT NULL,
    main_parts TEXT NULL,
    maintenance TEXT NULL,
    safety_precautions TEXT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE chemicals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    chemical_formula VARCHAR(100) NULL,
    cas_number VARCHAR(50) NULL,
    physical_state VARCHAR(50) NULL,
    properties TEXT NULL,
    uses TEXT NULL,
    hazards TEXT NULL,
    storage_methods TEXT NULL,
    safety_information TEXT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE processes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    category VARCHAR(100) NULL,
    short_description VARCHAR(255) NULL,
    description MEDIUMTEXT NULL,
    featured_image VARCHAR(255) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_processes_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE process_materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    material_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    purpose TEXT NULL,
    quantity DECIMAL(12,3) NULL,
    unit VARCHAR(50) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_process_material_name (process_id,material_name),
    CONSTRAINT fk_material_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE process_equipment (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    purpose TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_process_equipment (process_id,equipment_id),
    CONSTRAINT fk_process_equipment_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE,
    CONSTRAINT fk_process_equipment_item FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE process_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    step_number INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description MEDIUMTEXT NULL,
    image VARCHAR(255) NULL,
    temperature VARCHAR(100) NULL,
    pressure VARCHAR(100) NULL,
    duration VARCHAR(100) NULL,
    input_material TEXT NULL,
    output_material TEXT NULL,
    equipment_used TEXT NULL,
    safety_note TEXT NULL,
    quality_note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_process_step_number (process_id,step_number),
    CONSTRAINT fk_process_steps_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE process_reactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    step_id BIGINT UNSIGNED NULL,
    reaction_title VARCHAR(180) NOT NULL,
    reactants TEXT NULL,
    products TEXT NULL,
    equation VARCHAR(255) NULL,
    conditions TEXT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_process_reaction_title (process_id,reaction_title),
    CONSTRAINT fk_reactions_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE,
    CONSTRAINT fk_reactions_step FOREIGN KEY (step_id) REFERENCES process_steps(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE process_safety (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    hazard_level ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_safety_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE process_quality_control (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    parameter VARCHAR(150) NULL,
    acceptable_range VARCHAR(150) NULL,
    testing_method TEXT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quality_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE process_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE process_end_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_end_products_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE process_flow_diagrams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    diagram_image VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_flow_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE videos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    video_url VARCHAR(500) NOT NULL,
    thumbnail VARCHAR(255) NULL,
    process_id BIGINT UNSIGNED NULL,
    equipment_id BIGINT UNSIGNED NULL,
    duration VARCHAR(20) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_videos_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE SET NULL,
    CONSTRAINT fk_videos_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE quizzes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    passing_score INT UNSIGNED NOT NULL DEFAULT 50,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quizzes_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE quiz_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id BIGINT UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('multiple_choice','true_false') NOT NULL DEFAULT 'multiple_choice',
    marks INT UNSIGNED NOT NULL DEFAULT 1,
    explanation TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    answer TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    quiz_id BIGINT UNSIGNED NOT NULL,
    score INT UNSIGNED NOT NULL DEFAULT 0,
    total_marks INT UNSIGNED NOT NULL DEFAULT 0,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    passed BOOLEAN NOT NULL DEFAULT FALSE,
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    CONSTRAINT fk_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempts_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    process_id BIGINT UNSIGNED NOT NULL,
    last_step INT UNSIGNED NOT NULL DEFAULT 0,
    progress_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    completed BOOLEAN NOT NULL DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_process_progress (user_id,process_id),
    CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookmarks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    content_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_bookmark (user_id,content_type,content_id),
    CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE glossary (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(180) NOT NULL UNIQUE,
    definition TEXT NOT NULL,
    category VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE news (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    image VARCHAR(255) NULL,
    category VARCHAR(100) NULL,
    author_id BIGINT UNSIGNED NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_news_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NULL,
    link VARCHAR(255) NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE siwes_logbooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    activity VARCHAR(255) NOT NULL,
    description TEXT NULL,
    skills_learned TEXT NULL,
    challenges TEXT NULL,
    supervisor_comment TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_logbooks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE siwes_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    report_type VARCHAR(100) NOT NULL,
    title VARCHAR(180) NOT NULL,
    content MEDIUMTEXT NULL,
    status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    process_id BIGINT UNSIGNED NULL,
    certificate_number VARCHAR(80) NOT NULL UNIQUE,
    title VARCHAR(180) NOT NULL,
    issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_certificates_user_process (user_id, process_id),
    CONSTRAINT fk_certificates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_process FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE SET NULL
) ENGINE=InnoDB;
