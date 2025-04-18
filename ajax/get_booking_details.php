<?php
require_once '../database/db.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    die('Invalid request');
}

$bookingId = intval($_GET['id']);

$stmt = $db->prepare("
    SELECT b.*, r.room_name, r.capacity, r.location, r.amenities,
           u.first_name, u.last_name, u.email, u.department_id,
           d.department_name, a.first_name as approver_first, a.last_name as approver_last,
           bd.description, bd.requirements
    FROM bookings b
    JOIN boardrooms r ON b.room_id = r.room_id
    JOIN users u ON b.user_id = u.user_id
    JOIN departments d ON u.department_id = d.department_id
    LEFT JOIN users a ON b.approver_id = a.user_id
    LEFT JOIN booking_details bd ON b.booking_id = bd.booking_id
    WHERE b.booking_id = ?
");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die('Booking not found');
}

$isExpired = strtotime($booking['end_time']) < time();
$isPending = $booking['status'] === 'Pending';
?>

<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h4 class="font-medium text-gray-900">Event Information</h4>
            <dl class="mt-2 space-y-2">
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Event Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($booking['event_name']) ?></dd>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Room</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($booking['room_name']) ?></dd>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Date & Time</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <?= date('M j, Y', strtotime($booking['start_time'])) ?><br>
                        <?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?>
                        <?php if ($isExpired): ?>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Expired</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Attendees</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($booking['attendees_count']) ?> (Capacity: <?= htmlspecialchars($booking['capacity']) ?>)</dd>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <?php
                        $statusClasses = [
                            'Pending' => 'bg-yellow-100 text-yellow-800',
                            'Approved' => 'bg-green-100 text-green-800',
                            'Rejected' => 'bg-red-100 text-red-800',
                            'Completed' => 'bg-blue-100 text-blue-800',
                            'Cancelled' => 'bg-gray-100 text-gray-800'
                        ];
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClasses[$booking['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                            <?= htmlspecialchars($booking['status']) ?>
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div>
            <h4 class="font-medium text-gray-900">Requester Information</h4>
            <dl class="mt-2 space-y-2">
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></dd>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($booking['email']) ?></dd>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Department</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($booking['department_name']) ?></dd>
                </div>
                <?php if ($booking['approver_first']): ?>
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($booking['approver_first'] . ' ' . $booking['approver_last']) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <?php if ($booking['description'] || $booking['requirements']): ?>
        <div>
            <h4 class="font-medium text-gray-900">Additional Details</h4>
            <div class="mt-2 space-y-4">
                <?php if ($booking['description']): ?>
                    <div>
                        <h5 class="text-sm font-medium text-gray-500">Description</h5>
                        <p class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($booking['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($booking['requirements']): ?>
                    <div>
                        <h5 class="text-sm font-medium text-gray-500">Special Requirements</h5>
                        <p class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($booking['requirements'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($booking['approval_notes']): ?>
        <div>
            <h4 class="font-medium text-gray-900">Approval Notes</h4>
            <p class="mt-2 text-sm text-gray-700"><?= nl2br(htmlspecialchars($booking['approval_notes'])) ?></p>
        </div>
    <?php endif; ?>
</div>