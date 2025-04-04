<?php
require_once 'db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $db->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = :booking_id AND user_id = :user_id");
    $stmt->execute([':booking_id' => $booking_id, ':user_id' => $user_id]);
}

// Fetch user's bookings with event details
$query = "SELECT b.booking_id, b.event_name, b.start_time, b.end_time, b.status,
                 r.room_name, r.location, r.capacity,
                --  e.event_description, e.attendees_count
          FROM bookings b
          JOIN boardrooms r ON b.room_id = r.room_id
        --   LEFT JOIN events e ON b.booking_id = e.booking_id
          WHERE b.user_id = :user_id
          ORDER BY b.start_time DESC";

$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Board Room Management</title>
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">Board Room Management</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Dashboard
                    </a>
                    <a href="book.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        New Booking
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">My Bookings</h2>
            <p class="mt-1 text-gray-600">Manage your room bookings and events</p>
        </div>

        <div class="space-y-6">
            <?php foreach ($bookings as $booking): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($booking['event_name']); ?>
                            </h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                <?php echo $booking['status'] === 'Scheduled' ? 'bg-green-100 text-green-800' : ($booking['status'] === 'Cancelled' ? 'bg-red-100 text-red-800' :
                                        'bg-blue-100 text-blue-800'); ?>">
                                <?php echo $booking['status']; ?>
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Room Details</h4>
                                <div class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Room:</span>
                                        <?php echo htmlspecialchars($booking['room_name']); ?>
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Location:</span>
                                        <?php echo htmlspecialchars($booking['location']); ?>
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Capacity:</span>
                                        <?php echo $booking['capacity']; ?> people
                                    </p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Event Details</h4>
                                <div class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Start:</span>
                                        <?php echo date('M j, Y g:i A', strtotime($booking['start_time'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">End:</span>
                                        <?php echo date('M j, Y g:i A', strtotime($booking['end_time'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Attendees:</span>
                                        <?php echo $booking['attendees_count']; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if ($booking['event_description']): ?>
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                <p class="mt-2 text-sm text-gray-900">
                                    <?php echo nl2br(htmlspecialchars($booking['event_description'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if ($booking['status'] === 'Scheduled'): ?>
                            <div class="mt-6 flex justify-end">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <button type="submit" name="cancel_booking"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Cancel Booking
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>

</html>