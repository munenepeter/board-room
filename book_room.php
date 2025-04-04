<?php
require_once 'includes/header.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: login.php");
    exit;
}

// Get all active rooms for dropdown
$rooms = $db->query("SELECT room_id, room_name, capacity, location FROM boardrooms WHERE is_active = 1 ORDER BY room_name");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Validate and sanitize input
        $eventName = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        $attendees = filter_input(INPUT_POST, 'attendees_count', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $startTime = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $endTime = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $requirements = filter_input(INPUT_POST, 'requirements', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Convert to DateTime objects for validation
        $startDateTime = new DateTime($startTime);
        $endDateTime = new DateTime($endTime);

        // Validate time (must be future and end after start)
        $now = new DateTime();
        if ($startDateTime < $now) {
            throw new Exception("Start time must be in the future");
        }
        if ($endDateTime <= $startDateTime) {
            throw new Exception("End time must be after start time");
        }

        // Check room availability
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE room_id = :room_id 
            AND status IN ('Pending', 'Approved')
            AND (
                (start_time < :end_time AND end_time > :start_time)
            )
        ");
        $stmt->execute([
            ':room_id' => $roomId,
            ':start_time' => $startDateTime->format('Y-m-d H:i:s'),
            ':end_time' => $endDateTime->format('Y-m-d H:i:s')
        ]);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("The selected room is not available for the requested time slot");
        }

        // Insert booking
        $stmt = $db->prepare("
            INSERT INTO bookings (
                room_id, user_id, event_name, start_time, end_time, 
                attendees_count, status, created_at
            ) VALUES (
                :room_id, :user_id, :event_name, :start_time, :end_time,
                :attendees_count, :status, datetime('now')
            )
        ");
        $status = ($userRole === 'admin') ? 'Approved' : 'Pending';
        $stmt->execute([
            ':room_id' => $roomId,
            ':user_id' => $userId,
            ':event_name' => $eventName,
            ':start_time' => $startDateTime->format('Y-m-d H:i:s'),
            ':end_time' => $endDateTime->format('Y-m-d H:i:s'),
            ':attendees_count' => $attendees,
            ':status' => $status
        ]);
        $bookingId = $db->lastInsertId();

        // Insert booking details if provided
        if (!empty($description) || !empty($requirements)) {
            $stmt = $db->prepare("
                INSERT INTO booking_details (
                    booking_id, description, requirements
                ) VALUES (
                    :booking_id, :description, :requirements
                )
            ");
            $stmt->execute([
                ':booking_id' => $bookingId,
                ':description' => $description,
                ':requirements' => $requirements
            ]);
        }

        // Create notifications
        require_once 'includes/functions.php';

        // Notification to booker
        createNotification(
            $userId,
            "Booking Request Submitted",
            "Your booking for '{$eventName}' has been submitted and is {$status}.",
            'booking',
            $bookingId
        );

        // If pending approval, notify admin
        if ($status === 'Pending') {
            $admins = $db->query("SELECT user_id FROM users WHERE role = 'admin'");
            while ($admin = $admins->fetch(PDO::FETCH_ASSOC)) {
                createNotification(
                    $admin['user_id'],
                    "Booking Approval Required",
                    "A new booking for '{$eventName}' requires your approval.",
                    'approval',
                    $bookingId
                );
            }
        }

        // Send emails

        // Email to booker
        sendEmail(
            $_SESSION['email'] ?? 'admin@local.com',
            "Booking Confirmation - {$eventName}",
            "Your booking request has been received.\n\n" .
                "Event: {$eventName}\n" .
                "Room: " . getRoomName($roomId) . "\n" .
                "Date: " . $startDateTime->format('l, F j, Y') . "\n" .
                "Time: " . $startDateTime->format('g:i A') . " - " . $endDateTime->format('g:i A') . "\n" .
                "Status: {$status}\n\n" .
                ($status === 'Pending' ?
                    "An administrator will review your request shortly." :
                    "Your booking has been automatically approved.") . "\n\n" .
                "You can view your booking at: " . getSiteUrl() . "/booking_details.php?id={$bookingId}"
        );

        // Email to admins if pending
        if ($status === 'Pending') {
            $admins = $db->query("SELECT email FROM users WHERE role = 'admin'");
            while ($admin = $admins->fetch(PDO::FETCH_ASSOC)) {
                sendEmail(
                    $admin['email'],
                    "Booking Approval Required - {$eventName}",
                    "A new booking request requires your approval:\n\n" .
                        "Event: {$eventName}\n" .
                        "Requested by: {$_SESSION['first_name']} {$_SESSION['last_name']}\n" .
                        "Room: " . getRoomName($roomId) . "\n" .
                        "Date: " . $startDateTime->format('l, F j, Y') . "\n" .
                        "Time: " . $startDateTime->format('g:i A') . " - " . $endDateTime->format('g:i A') . "\n" .
                        "Attendees: {$attendees}\n\n" .
                        "Please review and approve or reject this booking:\n" .
                        getSiteUrl() . "/approve_booking.php?id={$bookingId}"
                );
            }
        }

        $db->commit();

        // Redirect to success page
        $_SESSION['success_message'] = "Your booking has been successfully submitted!";
        header("Location: booking_success.php?id={$bookingId}");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// Get default times (next hour for start, +1 hour for end)
$defaultStart = (new DateTime('+1 hour'))->format('Y-m-d\TH:i');
$defaultEnd = (new DateTime('+2 hours'))->format('Y-m-d\TH:i');
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold text-maroon-800 mb-6">Book a Meeting Room</h1>

            <?php if (isset($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle h-5 w-5 text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="book_room.php" method="POST">
                <div class="space-y-6">
                    <!-- Event Details -->
                    <div>
                        <h2 class="text-lg font-medium text-maroon-700 mb-3">Event Information</h2>
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name *</label>
                                <input type="text" name="event_name" id="event_name" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="attendees_count" class="block text-sm font-medium text-gray-700">Number of Attendees *</label>
                                <input type="number" name="attendees_count" id="attendees_count" min="1" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="room_id" class="block text-sm font-medium text-gray-700">Room *</label>
                                <select id="room_id" name="room_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                                    <option value="">Select a room</option>
                                    <?php while ($room = $rooms->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?= $room['room_id'] ?>"
                                            data-capacity="<?= $room['capacity'] ?>">
                                            <?= htmlspecialchars($room['room_name']) ?> (Capacity: <?= $room['capacity'] ?>, <?= htmlspecialchars($room['location']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <p id="capacity-warning" class="mt-1 text-sm text-red-600 hidden">
                                    Selected room capacity is less than number of attendees
                                </p>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm"></textarea>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="requirements" class="block text-sm font-medium text-gray-700">Special Requirements</label>
                                <textarea name="requirements" id="requirements" rows="2"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div>
                        <h2 class="text-lg font-medium text-maroon-700 mb-3">Date & Time</h2>
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time *</label>
                                <input type="datetime-local" name="start_time" id="start_time" required
                                    min="<?= date('Y-m-d\TH:i') ?>"
                                    value="<?= $defaultStart ?>"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time *</label>
                                <input type="datetime-local" name="end_time" id="end_time" required
                                    min="<?= date('Y-m-d\TH:i') ?>"
                                    value="<?= $defaultEnd ?>"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-6">
                                <div id="availability-status" class="hidden p-3 rounded-md text-sm">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span id="availability-text"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Submit Booking Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // Check room capacity vs attendees
    document.getElementById('attendees_count').addEventListener('change', checkCapacity);
    document.getElementById('room_id').addEventListener('change', checkCapacity);

    function checkCapacity() {
        const roomSelect = document.getElementById('room_id');
        const attendeesInput = document.getElementById('attendees_count');
        const warning = document.getElementById('capacity-warning');

        if (roomSelect.selectedIndex > 0 && attendeesInput.value) {
            const capacity = roomSelect.options[roomSelect.selectedIndex].dataset.capacity;
            if (parseInt(attendeesInput.value) > parseInt(capacity)) {
                warning.classList.remove('hidden');
            } else {
                warning.classList.add('hidden');
            }
        } else {
            warning.classList.add('hidden');
        }
    }

    // Check room availability when time changes
    document.getElementById('start_time').addEventListener('change', checkAvailability);
    document.getElementById('end_time').addEventListener('change', checkAvailability);
    document.getElementById('room_id').addEventListener('change', checkAvailability);

    function checkAvailability() {
        const roomId = document.getElementById('room_id').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const statusDiv = document.getElementById('availability-status');
        const statusText = document.getElementById('availability-text');

        if (!roomId || !startTime || !endTime) return;

        // Simple client-side validation for time order
        if (new Date(startTime) >= new Date(endTime)) {
            statusDiv.className = 'bg-red-50 text-red-700 p-3 rounded-md text-sm';
            statusText.textContent = 'End time must be after start time';
            statusDiv.classList.remove('hidden');
            return;
        }

        // Show loading
        statusDiv.className = 'bg-blue-50 text-blue-700 p-3 rounded-md text-sm';
        statusText.textContent = 'Checking availability...';
        statusDiv.classList.remove('hidden');

        // AJAX request to check availability
        fetch('check_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    room_id: roomId,
                    start_time: startTime,
                    end_time: endTime,
                    exclude_booking: '' // For edit scenarios
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    statusDiv.className = 'bg-green-50 text-green-700 p-3 rounded-md text-sm';
                    statusText.textContent = 'Room is available for the selected time';
                } else {
                    statusDiv.className = 'bg-red-50 text-red-700 p-3 rounded-md text-sm';
                    statusText.textContent = 'Room is not available for the selected time';
                    if (data.conflicting_event) {
                        const conflict = data.conflicting_event;
                        statusText.textContent += ` (Conflicts with "${conflict.event_name}" from ${conflict.start_time} to ${conflict.end_time})`;
                    }
                }
                statusDiv.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                statusDiv.className = 'bg-yellow-50 text-yellow-700 p-3 rounded-md text-sm';
                statusText.textContent = 'Error checking availability';
                statusDiv.classList.remove('hidden');
            });
    }
</script>

</body>

</html>