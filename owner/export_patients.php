<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $format = clean($_POST['format']);
    $start_date = clean($_POST['start_date']);
    $end_date = clean($_POST['end_date']);

    // Fetch patient data within date range
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            COUNT(DISTINCT a.appointment_id) as total_appointments,
            COUNT(DISTINCT pb.booking_id) as total_packages,
            SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
            SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments
        FROM patients p
        LEFT JOIN appointments a ON p.patient_id = a.patient_id
        LEFT JOIN package_bookings pb ON p.patient_id = pb.patient_id
        WHERE p.created_at BETWEEN ? AND ?
        GROUP BY p.patient_id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers based on format
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="patients_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'ID',
            'First Name',
            'Middle Name',
            'Last Name',
            'Username',
            'Phone',
            'Email',
            'Created At',
            'Total Appointments',
            'Completed Appointments',
            'Cancelled Appointments',
            'Total Packages',
            'Joined Date'
        ]);

        // Add data rows
        foreach ($patients as $patient) {
            fputcsv($output, [
                $patient['patient_id'],
                $patient['first_name'],
                $patient['middle_name'],
                $patient['last_name'],
                $patient['username'],
                $patient['phone'],
                $patient['email'],
                $patient['created_at'],
                $patient['total_appointments'],
                $patient['completed_appointments'],
                $patient['cancelled_appointments'],
                $patient['total_packages'],
                $patient['created_at']
            ]);
        }

        fclose($output);
    } else {
        // Excel format
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="patients_' . date('Y-m-d') . '.xls"');
        
        echo '<table border="1">';
        
        // Add headers
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>First Name</th>';
        echo '<th>Middle Name</th>';
        echo '<th>Last Name</th>';
        echo '<th>Username</th>';
        echo '<th>Phone</th>';
        echo '<th>Email</th>';
        echo '<th>Created At</th>';
        echo '<th>Total Appointments</th>';
        echo '<th>Completed Appointments</th>';
        echo '<th>Cancelled Appointments</th>';
        echo '<th>Total Packages</th>';
        echo '<th>Joined Date</th>';
        echo '</tr>';

        // Add data rows
        foreach ($patients as $patient) {
            echo '<tr>';
            echo '<td>' . $patient['patient_id'] . '</td>';
            echo '<td>' . $patient['first_name'] . '</td>';
            echo '<td>' . $patient['middle_name'] . '</td>';
            echo '<td>' . $patient['last_name'] . '</td>';
            echo '<td>' . $patient['username'] . '</td>';
            echo '<td>' . $patient['phone'] . '</td>';
            echo '<td>' . $patient['email'] . '</td>';
            echo '<td>' . $patient['created_at'] . '</td>';
            echo '<td>' . $patient['total_appointments'] . '</td>';
            echo '<td>' . $patient['completed_appointments'] . '</td>';
            echo '<td>' . $patient['cancelled_appointments'] . '</td>';
            echo '<td>' . $patient['total_packages'] . '</td>';
            echo '<td>' . $patient['created_at'] . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
    exit();
} else {
    // If not POST request, redirect back to patients page
    header('Location: patients.php');
    exit();
} 