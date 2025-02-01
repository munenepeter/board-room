<?php
require_once 'db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// user's bookings
$user_id = $_SESSION['user_id'];
$query = "SELECT b.booking_id, b.event_name, b.start_time, b.end_time, r.room_name 
          FROM bookings b 
          JOIN boardrooms r ON b.room_id = r.room_id 
          WHERE b.user_id = $user_id";

// handle filtering by date
if (isset($_GET['filter_date'])) {
    $filter_date = $_GET['filter_date'];
    $query .= " AND DATE(b.start_time) = '$filter_date'";
}

$bookings = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Board Room Management</title>
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
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
                    <?php if (isAdmin()): ?>
                        <a href="reports.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            Reports
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Logout
                    </a>
                    <a href="book.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        Book Room
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Welcome, <?php echo $_SESSION['first_name']; ?>!</h2>
            <p class="mt-1 text-gray-600">Manage your board room bookings and schedule</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Calendar Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Calendar</h3>
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Bookings Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Bookings</h3>

                    <!-- Filter Form -->
                    <form method="GET" action="dashboard.php" class="mb-6">
                        <div class="space-y-2">
                            <label for="filter_date" class="block text-sm font-medium text-gray-700">Filter by Date</label>
                            <input type="date" id="filter_date" name="filter_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors">
                            <div class="flex space-x-2">
                                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    Apply Filter
                                </button>
                                <a href="dashboard.php" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center text-sm">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Bookings List -->
                    <div class="space-y-4">
                        <?php while ($row = $bookings->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900"><?php echo $row['event_name']; ?></h4>
                                <div class="mt-2 space-y-1">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="w-20 text-gray-500">Room:</span>
                                        <span><?php echo $row['room_name']; ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="w-20 text-gray-500">Start:</span>
                                        <span><?php echo date('M j, Y g:i A', strtotime($row['start_time'])); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="w-20 text-gray-500">End:</span>
                                        <span><?php echo date('M j, Y g:i A', strtotime($row['end_time'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script>
        $(document).ready(function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                events: [
                    <?php
                    $calendar_bookings = $db->query("SELECT event_name, start_time, end_time FROM bookings WHERE user_id = $user_id");
                    while ($row = $calendar_bookings->fetch(PDO::FETCH_ASSOC)) {
                        echo "{
                            title: '" . addslashes($row['event_name']) . "',
                            start: '" . $row['start_time'] . "',
                            end: '" . $row['end_time'] . "'
                        },";
                    }
                    ?>
                ],
                eventClick: function(info) {
                    alert('Event: ' + info.event.title + '\nStart: ' + info.event.start.toLocaleString());
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                }
            });
            calendar.render();
        });
    </script>
</body>

</html>