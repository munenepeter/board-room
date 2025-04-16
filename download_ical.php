<?php
require_once 'database/db.php';

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit;
}

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Get booking details
$stmt = $db->prepare("
    SELECT b.*, r.room_name, r.location 
    FROM bookings b
    JOIN boardrooms r ON b.room_id = r.room_id
    WHERE b.booking_id = ? AND (b.user_id = ? OR b.status = 'Approved')
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: bookings.php");
    exit;
}

// Format dates for iCal
$start = new DateTime($booking['start_time']);
$end = new DateTime($booking['end_time']);
$created = new DateTime($booking['created_at']);
$uid = $bookingId . '@' . parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);

// Create iCal content
$ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Meeting Room System//EN
BEGIN:VEVENT
UID:{$uid}
DTSTAMP:" . $created->format('Ymd\THis\Z') . "
DTSTART:" . $start->format('Ymd\THis\Z') . "
DTEND:" . $end->format('Ymd\THis\Z') . "
SUMMARY:" . str_replace(["\r", "\n"], '', $booking['event_name']) . "
DESCRIPTION:Meeting room booking for " . str_replace(["\r", "\n"], ' ', $booking['event_name']) . "\\nLocation: " . str_replace(["\r", "\n"], ' ', $booking['room_name']) . ", " . str_replace(["\r", "\n"], ' ', $booking['location']) . "
LOCATION:" . str_replace(["\r", "\n"], '', $booking['room_name'] . ', ' . $booking['location']) . "
STATUS:CONFIRMED
SEQUENCE:0
END:VEVENT
END:VCALENDAR";

// Set headers for download
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=meeting_' . $bookingId . '.ics');
echo $ical;
exit;
