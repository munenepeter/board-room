<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';


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
    WHERE b.booking_id = :id AND b.user_id = :user_id
");
$stmt->execute([':id' => $bookingId, ':user_id' => $userId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: bookings.php");
    exit;
}

$startDateTime = new DateTime($booking['start_time']);
$endDateTime = new DateTime($booking['end_time']);
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="rounded-md bg-green-50 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle h-5 w-5 text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800"><?= $_SESSION['success_message'] ?></h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>Your booking details are shown below. A confirmation has been sent to your email.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <div class="border-b border-gray-200 pb-4 mb-4">
                <h1 class="text-2xl font-bold text-maroon-800">Booking Confirmation</h1>
                <p class="mt-1 text-sm text-gray-500">Reference #<?= $bookingId ?></p>
            </div>
            
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <h2 class="text-lg font-medium text-maroon-700 mb-2">Event Details</h2>
                    <div class="space-y-2">
                        <p><strong class="text-gray-700">Event:</strong> <?= htmlspecialchars($booking['event_name']) ?></p>
                        <p><strong class="text-gray-700">Room:</strong> <?= htmlspecialchars($booking['room_name']) ?></p>
                        <p><strong class="text-gray-700">Location:</strong> <?= htmlspecialchars($booking['location']) ?></p>
                        <p><strong class="text-gray-700">Attendees:</strong> <?= $booking['attendees_count'] ?></p>
                        <p><strong class="text-gray-700">Status:</strong> 
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                <?= $booking['status'] == 'Approved' ? 'bg-green-100 text-green-800' : '' ?>
                                <?= $booking['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                <?= $booking['status'] == 'Rejected' ? 'bg-red-100 text-red-800' : '' ?>">
                                <?= $booking['status'] ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-lg font-medium text-maroon-700 mb-2">Date & Time</h2>
                    <div class="space-y-2">
                        <p><strong class="text-gray-700">Date:</strong> <?= $startDateTime->format('l, F j, Y') ?></p>
                        <p><strong class="text-gray-700">Time:</strong> <?= $startDateTime->format('g:i A') ?> - <?= $endDateTime->format('g:i A') ?></p>
                        <p><strong class="text-gray-700">Duration:</strong> 
                            <?php
                            $interval = $startDateTime->diff($endDateTime);
                            echo $interval->format('%h hours %i minutes');
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="sm:col-span-2">
                    <h2 class="text-lg font-medium text-maroon-700 mb-2">Next Steps</h2>
                    <div class="bg-blue-50 rounded-md p-4">
                        <?php if ($booking['status'] === 'Pending'): ?>
                        <p class="text-blue-700">Your booking is pending approval. You'll receive an email notification once it's been reviewed by an administrator.</p>
                        <?php elseif ($booking['status'] === 'Approved'): ?>
                        <p class="text-green-700">Your booking has been approved! You'll receive a reminder email 1 hour before your meeting starts.</p>
                        <?php endif; ?>
                        
                        <div class="mt-4 flex space-x-3">
                            <a href="my_bookings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                <i class="fas fa-calendar-alt mr-2"></i> View All Bookings
                            </a>
                            <a href="calendar.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                <i class="fas fa-calendar-week mr-2"></i> View Calendar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>