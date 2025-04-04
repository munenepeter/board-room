<?php
require_once 'db.php';

header('Content-Type: application/json');

$roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
$startTime = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$endTime = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$excludeBooking = filter_input(INPUT_POST, 'exclude_booking', FILTER_VALIDATE_INT);

try {
    $query = "
        SELECT b.event_name, b.start_time, b.end_time 
        FROM bookings b
        WHERE b.room_id = :room_id
        AND b.status IN ('Pending', 'Approved')
        AND (
            (b.start_time < :end_time AND b.end_time > :start_time)
        )
    ";
    
    $params = [
        ':room_id' => $roomId,
        ':start_time' => $startTime,
        ':end_time' => $endTime
    ];
    
    if ($excludeBooking) {
        $query .= " AND b.booking_id != :exclude_booking";
        $params[':exclude_booking'] = $excludeBooking;
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $conflicting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'available' => empty($conflicting),
        'conflicting_event' => $conflicting ?: null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>