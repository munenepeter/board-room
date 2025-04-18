<?php
require_once '../database/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id']) || empty($_POST['approval_notes'])) {
    die('Invalid request');
}

$bookingId = intval($_POST['booking_id']);
$rejectionNotes = trim($_POST['approval_notes']);

// Get current user ID (assuming you have authentication in place)
session_start();
$approverId = $_SESSION['user_id'] ?? null;

if (!$approverId) {
    die('Unauthorized');
}

try {
    $db->beginTransaction();

    // Update booking status
    $stmt = $db->prepare("
        UPDATE bookings 
        SET status = 'Rejected', 
            approver_id = ?, 
            approval_notes = ?
        WHERE booking_id = ?
    ");
    $stmt->execute([$approverId, $rejectionNotes, $bookingId]);

    // Create notification for the requester
    $stmt = $db->prepare("
        SELECT user_id FROM bookings WHERE booking_id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $stmt = $db->prepare("
            INSERT INTO notifications 
            (user_id, title, message, related_entity_type, related_entity_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $booking['user_id'],
            'Booking Rejected',
            'Your booking request has been rejected. Reason: ' . $rejectionNotes,
            'booking',
            $bookingId
        ]);
    }

    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to reject booking: ' . $e->getMessage()]);
}
