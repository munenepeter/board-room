<?php
// Check if user is admin
// if ($userRole !== 'admin') {
//     header("Location: dashboard.php");
//     exit;
// }

require_once 'database/db.php';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_report_' . date('Y-m-d') . '.csv');



// Get report parameters
$reportType = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$startDate = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$endDate = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Validate dates
if (!strtotime($startDate) || !strtotime($endDate)) {
    $_SESSION['error_message'] = "Invalid date range";
    header("Location: reports.php");
    exit;
}

// Generate report data based on type
switch ($reportType) {
    case 'bookings':
        exportBookingsReport($startDate, $endDate);
        break;
    case 'rooms':
        exportRoomUtilizationReport($startDate, $endDate);
        break;
    case 'users':
        exportUserActivityReport($startDate, $endDate);
        break;
    default:
        $_SESSION['error_message'] = "Invalid report type";
        header("Location: reports.php");
        exit;
}

function exportBookingsReport($startDate, $endDate) {
    global $db;

    // Get bookings data
    $stmt = $db->prepare("
        SELECT 
            b.booking_id,
            b.event_name,
            r.room_name,
            u.first_name || ' ' || u.last_name as booked_by,
            b.start_time,
            b.end_time,
            b.attendees_count,
            b.status,
            b.created_at
        FROM bookings b
        JOIN boardrooms r ON b.room_id = r.room_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.start_time >= :start_date
        AND b.end_time <= :end_date
        ORDER BY b.start_time
    ");
    $stmt->execute([
        ':start_date' => $startDate . ' 00:00:00',
        ':end_date' => $endDate . ' 23:59:59'
    ]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);



    // Create output file pointer
    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, [
        'Booking ID',
        'Event Name',
        'Room',
        'Booked By',
        'Start Time',
        'End Time',
        'Attendees',
        'Status',
        'Created At'
    ]);

    // Add data rows
    foreach ($bookings as $booking) {
        fputcsv($output, [
            $booking['booking_id'],
            $booking['event_name'],
            $booking['room_name'],
            $booking['booked_by'],
            $booking['start_time'],
            $booking['end_time'],
            $booking['attendees_count'],
            $booking['status'],
            $booking['created_at']
        ]);
    }

    fclose($output);
    exit;
}

function exportRoomUtilizationReport($startDate, $endDate) {
    global $db;

    // Get room utilization data
    $stmt = $db->prepare("
        SELECT 
            r.room_id,
            r.room_name,
            r.location,
            r.capacity,
            COUNT(b.booking_id) as booking_count,
            SUM((julianday(b.end_time) - julianday(b.start_time)) * 24 as total_hours
        FROM boardrooms r
        LEFT JOIN bookings b ON r.room_id = b.room_id 
            AND b.status = 'Approved'
            AND b.start_time >= :start_date
            AND b.end_time <= :end_date
        WHERE r.is_active = 1
        GROUP BY r.room_id
        ORDER BY total_hours DESC
    ");
    $stmt->execute([
        ':start_date' => $startDate . ' 00:00:00',
        ':end_date' => $endDate . ' 23:59:59'
    ]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate utilization percentage
    $maxHours = (strtotime($endDate) - strtotime($startDate)) / (60 * 60) * 8; // 8 hours per day

    // Create output file pointer
    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, [
        'Room ID',
        'Room Name',
        'Location',
        'Capacity',
        'Booking Count',
        'Total Hours',
        'Utilization %'
    ]);

    // Add data rows
    foreach ($rooms as $room) {
        $utilization = $maxHours > 0 ? ($room['total_hours'] / $maxHours) * 100 : 0;

        fputcsv($output, [
            $room['room_id'],
            $room['room_name'],
            $room['location'],
            $room['capacity'],
            $room['booking_count'],
            round($room['total_hours'], 2),
            round($utilization, 2)
        ]);
    }

    fclose($output);
    exit;
}

function exportUserActivityReport($startDate, $endDate) {
    global $db;

    // Get user activity data
    $stmt = $db->prepare("
        SELECT 
            u.user_id,
            u.first_name || ' ' || u.last_name as user_name,
            d.department_name,
            COUNT(b.booking_id) as bookings_made,
            SUM(CASE WHEN b.status = 'Approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN b.status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN b.status = 'Rejected' THEN 1 ELSE 0 END) as rejected
        FROM users u
        LEFT JOIN bookings b ON u.user_id = b.user_id
            AND b.start_time >= :start_date
            AND b.end_time <= :end_date
        LEFT JOIN departments d ON u.department_id = d.department_id
        GROUP BY u.user_id
        ORDER BY bookings_made DESC
    ");
    $stmt->execute([
        ':start_date' => $startDate . ' 00:00:00',
        ':end_date' => $endDate . ' 23:59:59'
    ]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=user_activity_' . date('Y-m-d') . '.csv');

    // Create output file pointer
    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, [
        'User ID',
        'Name',
        'Department',
        'Total Bookings',
        'Approved',
        'Pending',
        'Rejected'
    ]);

    // Add data rows
    foreach ($users as $user) {
        fputcsv($output, [
            $user['user_id'],
            $user['user_name'],
            $user['department_name'] ?? 'N/A',
            $user['bookings_made'],
            $user['approved'],
            $user['pending'],
            $user['rejected']
        ]);
    }

    fclose($output);
    exit;
}
