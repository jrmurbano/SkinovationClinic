-- Drop the existing notifications table
DROP TABLE IF EXISTS notifications;

-- Create the new notifications table
CREATE TABLE notifications (
    notification_id INT NOT NULL AUTO_INCREMENT,
    type ENUM('appointment', 'package', 'cancellation', 'reschedule') NOT NULL,
    appointment_id INT DEFAULT NULL,
    patient_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 