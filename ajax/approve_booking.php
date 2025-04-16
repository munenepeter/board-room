<?php
require_once '../database/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id'])) {
    die('Invalid request');
}

$bookingId = intval($_POST['booking_id']);
$approvalNotes = isset($_POST['approval_notes']) ? trim($_POST['approval_notes']) : '';

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
        SET status = 'Approved', 
            approver_id = ?, 
            approval_notes = ?
        WHERE booking_id = ?
    ");
    $stmt->execute([$approverId, $approvalNotes, $bookingId]);

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
            'Booking Approved',
            'Your booking request has been approved.',
            'booking',
            $bookingId
        ]);
    }

    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to approve booking: ' . $e->getMessage()]);
}
