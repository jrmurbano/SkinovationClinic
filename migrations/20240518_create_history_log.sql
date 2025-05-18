-- SQL for creating the history_log table
CREATE TABLE IF NOT EXISTS history_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    type ENUM('Service','Product','Package') NOT NULL,
    name VARCHAR(255) NOT NULL,
    action ENUM('Added','Edited','Deleted','Availed') NOT NULL,
    performed_by VARCHAR(255) NOT NULL,
    details TEXT,
    related_id INT DEFAULT NULL
);
