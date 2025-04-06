<?php
function createNotification($userId, $title, $message, $entityType, $entityId = null) {
    global $db;

    $stmt = $db->prepare("
        INSERT INTO notifications (
            user_id, title, message, related_entity_type, related_entity_id, created_at
        ) VALUES (
            :user_id, :title, :message, :entity_type, :entity_id, datetime('now')
        )
    ");

    return $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':message' => $message,
        ':entity_type' => $entityType,
        ':entity_id' => $entityId
    ]);
}

function sendEmail($to, $subject, $message) {

    $headers = "From: meeting-system@example.com\r\n";
    $headers .= "Reply-To: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";


    error_log("Email sent to: {$to}\nSubject: {$subject}\nMessage: {$message}\n");


    return mail($to, $subject, $message, $headers);
}

function getRoomName($roomId) {
    global $db;
    $stmt = $db->prepare("SELECT room_name FROM boardrooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    return $stmt->fetchColumn();
}

function getSiteUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
}

function generateGoogleCalendarLink($booking) {
    $start = urlencode((new DateTime($booking['start_time']))->format('Ymd\THis\Z'));
    $end = urlencode((new DateTime($booking['end_time']))->format('Ymd\THis\Z'));
    $title = urlencode($booking['event_name']);
    $details = urlencode("Meeting room booking for {$booking['event_name']}\nLocation: {$booking['room_name']}, {$booking['location']}");
    $location = urlencode("{$booking['room_name']}, {$booking['location']}");

    return "https://www.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$start}/{$end}&details={$details}&location={$location}&sf=true&output=xml";
}

function generateOutlookCalendarLink($booking) {
    $start = (new DateTime($booking['start_time']))->format('Y-m-d\TH:i:s');
    $end = (new DateTime($booking['end_time']))->format('Y-m-d\TH:i:s');
    $title = urlencode($booking['event_name']);
    $location = urlencode("{$booking['room_name']}, {$booking['location']}");
    $body = urlencode("Meeting room booking for {$booking['event_name']}\n\nLocation: {$booking['room_name']}, {$booking['location']}\n\nAttendees: {$booking['attendees_count']}");

    return "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&startdt={$start}&enddt={$end}&subject={$title}&location={$location}&body={$body}";
}
