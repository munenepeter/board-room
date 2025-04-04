<?php
require_once 'includes/header.php';

$pending_approvals = $db->prepare("
SELECT b.booking_id, b.event_name, b.attendees_count, b.start_time, b.end_time, b.status, r.room_name
FROM bookings b
JOIN boardrooms r ON b.room_id = r.room_id
WHERE b.status = 'Pending';
");
$pending_approvals->execute();

$pendingApprovals = $pending_approvals->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 bg-maroon-50 mb-4">
        <h3 class="text-lg leading-6 font-medium text-maroon-900">Pending Approvals</h3>
    </div>
    <?php if (empty($pendingApprovals)): ?>
        <div class="mt-12 text-center py-12">
            <i class="fas fa-users text-4xl text-gray-400 mb-3"></i>
            <h3 class="text-lg font-medium text-gray-900">Oops, Seems like you have no pending booking requests</h3>
            <p class="mt-1 text-sm text-gray-500">Please come back later for more
            </p>
        </div>
    <?php else: ?>
        <ul class="divide-y divide-gray-200 mt-2">
            <?php foreach ($pendingApprovals as $booking): ?>
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-maroon-600 truncate">
                                <?= $booking['event_name'] ?>
                            </p>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending Approval
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-door-open text-gray-400 mr-1.5"></i>
                                    <?= $booking['room_name'] ?>
                                </p>
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    <i class="fas fa-users text-gray-400 mr-1.5"></i>
                                    <?= $booking['attendees_count'] ?> attendees
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1.5"></i>
                                <time datetime="<?= date('Y-m-d\TH:i:s', strtotime($booking['start_time'])) ?>">
                                    <?= date('M j, Y g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?>
                                </time>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-end space-x-2">
                            <a href="approve_booking.php?id=<?= $booking['booking_id'] ?>" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Approve
                            </a>
                            <a href="reject_booking.php?id=<?= $booking['booking_id'] ?>" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Reject
                            </a>
                            <a href="booking_details.php?id=<?= $booking['booking_id'] ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                Details
                            </a>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>

        </ul>
    <?php endif; ?>

</div>
</body>
</html>