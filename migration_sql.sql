-- ============================================
-- Safe Migration SQL for Duplicate Cleanup
-- Run this in your MySQL database interface
-- ============================================

-- Step 1: Create backup table for duplicates
CREATE TABLE IF NOT EXISTS `registered_courses_duplicates` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `academic_detail_id` bigint unsigned NOT NULL,
    `department_course_id` bigint unsigned NOT NULL,
    `student_level_id` bigint unsigned DEFAULT NULL,
    `academic_session` varchar(50) NOT NULL,
    `removed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `removal_reason` text DEFAULT 'Duplicate registration cleanup - same student, course, and session',
    `original_id` bigint unsigned DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `registered_courses_duplicates_academic_detail_id_foreign` (`academic_detail_id`),
    KEY `registered_courses_duplicates_department_course_id_foreign` (`department_course_id`),
    KEY `registered_courses_duplicates_student_level_id_foreign` (`student_level_id`),
    KEY `idx_duplicates_academic_detail_course_session` (`academic_detail_id`, `department_course_id`, `academic_session`),
    CONSTRAINT `registered_courses_duplicates_academic_detail_id_foreign` FOREIGN KEY (`academic_detail_id`) REFERENCES `academic_details` (`id`) ON DELETE CASCADE,
    CONSTRAINT `registered_courses_duplicates_department_course_id_foreign` FOREIGN KEY (`department_course_id`) REFERENCES `department_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `registered_courses_duplicates_student_level_id_foreign` FOREIGN KEY (`student_level_id`) REFERENCES `student_levels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Move duplicates to backup table
-- This identifies duplicates (same student, course, session) and moves them to backup
INSERT INTO `registered_courses_duplicates` (
    `academic_detail_id`, 
    `department_course_id`, 
    `student_level_id`, 
    `academic_session`, 
    `original_id`, 
    `created_at`, 
    `updated_at`
)
SELECT 
    t1.`academic_detail_id`, 
    t1.`department_course_id`, 
    t1.`student_level_id`, 
    t1.`academic_session`, 
    t1.`id` as `original_id`, 
    t1.`created_at`, 
    t1.`updated_at`
FROM `registered_courses` t1
INNER JOIN (
    SELECT 
        `academic_detail_id`, 
        `department_course_id`, 
        `academic_session`, 
        MIN(`id`) as min_id
    FROM `registered_courses`
    GROUP BY `academic_detail_id`, `department_course_id`, `academic_session`
    HAVING COUNT(*) > 1
) t2 ON t1.`academic_detail_id` = t2.`academic_detail_id` 
    AND t1.`department_course_id` = t2.`department_course_id` 
    AND t1.`academic_session` = t2.`academic_session`
    AND t1.`id` != t2.`min_id`;

-- Step 3: Delete duplicates from main table
DELETE t1 FROM `registered_courses` t1
INNER JOIN (
    SELECT 
        `academic_detail_id`, 
        `department_course_id`, 
        `academic_session`, 
        MIN(`id`) as min_id
    FROM `registered_courses`
    GROUP BY `academic_detail_id`, `department_course_id`, `academic_session`
    HAVING COUNT(*) > 1
) t2 ON t1.`academic_detail_id` = t2.`academic_detail_id` 
    AND t1.`department_course_id` = t2.`department_course_id` 
    AND t1.`academic_session` = t2.`academic_session`
    AND t1.`id` != t2.`min_id`;

-- Step 4: Add unique constraint for carry-over support
ALTER TABLE `registered_courses` 
ADD UNIQUE KEY `reg_course_session_unique` (`academic_detail_id`, `department_course_id`, `academic_session`);

-- Step 5: Add performance indexes
ALTER TABLE `registered_courses` 
ADD INDEX `idx_academic_session` (`academic_session`);

ALTER TABLE `registered_courses` 
ADD INDEX `idx_student_session` (`academic_detail_id`, `academic_session`);

ALTER TABLE `registered_courses` 
ADD INDEX `idx_course_session` (`department_course_id`, `academic_session`);

-- ============================================
-- Verification Queries (Run after migration)
-- ============================================

-- Check how many duplicates were moved
SELECT COUNT(*) as total_duplicates_moved FROM `registered_courses_duplicates`;

-- Verify no duplicates remain in main table
SELECT COUNT(*) as remaining_duplicates 
FROM `registered_courses` 
GROUP BY `academic_detail_id`, `department_course_id`, `academic_session` 
HAVING COUNT(*) > 1;

-- View sample of moved duplicates
SELECT * FROM `registered_courses_duplicates` LIMIT 10;

-- ============================================
-- Rollback SQL (Use if you need to undo)
-- ============================================

-- Restore duplicates from backup table
INSERT INTO `registered_courses` (
    `id`, 
    `academic_detail_id`, 
    `department_course_id`, 
    `student_level_id`, 
    `academic_session`, 
    `created_at`, 
    `updated_at`
)
SELECT 
    `original_id`, 
    `academic_detail_id`, 
    `department_course_id`, 
    `student_level_id`, 
    `academic_session`, 
    `created_at`, 
    `updated_at`
FROM `registered_courses_duplicates`;

-- Drop the constraints and indexes
ALTER TABLE `registered_courses` DROP INDEX `reg_course_session_unique`;
ALTER TABLE `registered_courses` DROP INDEX `idx_academic_session`;
ALTER TABLE `registered_courses` DROP INDEX `idx_student_session`;
ALTER TABLE `registered_courses` DROP INDEX `idx_course_session`;

-- Drop the backup table
DROP TABLE IF EXISTS `registered_courses_duplicates`;