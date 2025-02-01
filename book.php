<?php
// book.php - Handle room booking
require_once 'db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $event_name = $_POST['event_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $event_description = $_POST['event_description'];
    $attendees_count = $_POST['attendees_count'];
    $user_id = $_SESSION['user_id'];

    try {
        $db->beginTransaction();

        // Insert booking
        $stmt = $db->prepare("INSERT INTO bookings (room_id, user_id, event_name, start_time, end_time) 
                            VALUES (:room_id, :user_id, :event_name, :start_time, :end_time)");
        $stmt->execute([
            ':room_id' => $room_id,
            ':user_id' => $user_id,
            ':event_name' => $event_name,
            ':start_time' => $start_time,
            ':end_time' => $end_time
        ]);

        $booking_id = $db->lastInsertId();

        // Insert event details
        $stmt = $db->prepare("INSERT INTO events (booking_id, event_description, attendees_count) 
                            VALUES (:booking_id, :event_description, :attendees_count)");
        $stmt->execute([
            ':booking_id' => $booking_id,
            ':event_description' => $event_description,
            ':attendees_count' => $attendees_count
        ]);

        $db->commit();
        header("Location: bookings.php");
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "An error occurred while booking the room.";
    }
}

// Fetch available rooms with capacity
$rooms = $db->query("SELECT room_id, room_name, capacity, location FROM boardrooms ORDER BY room_name");
$rooms = $rooms->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - Board Room Management</title>
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
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Book a Room</h2>
                    <p class="mt-1 text-sm text-gray-600">Fill in the details to book a meeting room</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="p-4 bg-red-50 border-l-4 border-red-500">
                        <p class="text-red-700"><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="book.php" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="room_id" class="block text-sm font-medium text-gray-700">Select Room</label>
                            <select id="room_id" name="room_id" required 
                                class="mt-1 py-2 px-1.5 block w-full rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room['room_id']; ?>">
                                        <?php echo $room['room_name']; ?> 
                                        (Capacity: <?php echo $room['capacity']; ?>, 
                                        Location: <?php echo $room['location']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
                            <input type="text" id="event_name" name="event_name" required 
                                class="mt-1 py-2 px-1.5 block w-full rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="datetime-local" id="start_time" name="start_time" required 
                                class="mt-1 py-2 px-1.5 block w-full rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="datetime-local" id="end_time" name="end_time" required 
                                class="mt-1 py-2 px-1.5 block w-full rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="attendees_count" class="block text-sm font-medium text-gray-700">Number of Attendees</label>
                            <input type="number" id="attendees_count" name="attendees_count" required min="1"
                                class="mt-1 py-2 px-1.5 block w-full rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="event_description" class="block text-sm font-medium text-gray-700">Event Description</label>
                            <textarea id="event_description" name="event_description" rows="3"
                                class="mt-1 py-2 px-1.5 block w-full rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="dashboard.php" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Book Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>