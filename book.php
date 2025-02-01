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
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare("INSERT INTO bookings (room_id, user_id, event_name, start_time, end_time) 
                              VALUES (:room_id, :user_id, :event_name, :start_time, :end_time)");
    $stmt->bindValue(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':event_name', $event_name, PDO::PARAM_STR);
    $stmt->bindValue(':start_time', $start_time, PDO::PARAM_STR);
    $stmt->bindValue(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}

// Fetch available rooms
$rooms = $db->query("SELECT room_id, room_name FROM boardrooms");

$rooms = $rooms->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room</title>
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-gray-700 mb-6">Book a Room</h1>
        <form method="POST" action="book.php" class="space-y-4">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-600">Room:</label>
                <select id="room_id" name="room_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="event_name" class="block text-sm font-medium text-gray-600">Event Name:</label>
                <input type="text" id="event_name" name="event_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-2">
            </div>
            <div>
                <label for="start_time" class="block text-sm font-medium text-gray-600">Start Time:</label>
                <input type="datetime-local" id="start_time" name="start_time" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-2">
            </div>
            <div>
                <label for="end_time" class="block text-sm font-medium text-gray-600">End Time:</label>
                <input type="datetime-local" id="end_time" name="end_time" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-2">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-md hover:bg-blue-700 transition duration-200">Book</button>
        </form>
    </div>
</body>

</html>