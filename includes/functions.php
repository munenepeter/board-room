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
    // In a real application, you would use PHPMailer or similar
    // This is a mock implementation for demonstration
    
    $headers = "From: meeting-system@example.com\r\n";
    $headers .= "Reply-To: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // For development, just log the email
    error_log("Email sent to: {$to}\nSubject: {$subject}\nMessage: {$message}\n");
    
    // In production, you would use:
    // return mail($to, $subject, $message, $headers);
    return true;
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
?>