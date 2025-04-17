<?php
require_once 'includes/header.php';

// Check if user is admin
if ($userRole !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Default report period (last 30 days)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate dates
if (!strtotime($startDate) || !strtotime($endDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d');
}

// Get report data
$reportData = [
    'room_utilization' => getRoomUtilizationReport($startDate, $endDate),
    'booking_stats' => getBookingStats($startDate, $endDate),
    'user_activity' => getUserActivityReport($startDate, $endDate)
];

// Helper functions for reports
function getRoomUtilizationReport($startDate, $endDate) {
    global $db;

    $stmt = $db->prepare("
        SELECT 
            r.room_id,
            r.room_name,
            COUNT(b.booking_id) as booking_count,
            SUM((julianday(b.end_time) - julianday(b.start_time)) * 24) AS total_hours
        FROM boardrooms r
        LEFT JOIN bookings b ON r.room_id = b.room_id 
            AND b.status = 'Approved'
            AND b.start_time >= :start_date
            AND b.end_time <= :end_date
        WHERE r.is_active = 1
        GROUP BY r.room_id
        ORDER BY total_hours DESC
    ");
    $stmt->execute([
        ':start_date' => $startDate . ' 00:00:00',
        ':end_date' => $endDate . ' 23:59:59'
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBookingStats($startDate, $endDate) {
    global $db;

    $stats = [];

    // Total bookings
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_bookings,
               SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
               SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
               SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
               SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM bookings
        WHERE start_time >= :start_date
        AND end_time <= :end_date
    ");
    $stmt->execute([
        ':start_date' => $startDate . ' 00:00:00',
        ':end_date' => $endDate . ' 23:59:59'
    ]);
    $stats['bookings'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Average booking duration
    $stmt = $db->prepare("
        SELECT 
            AVG((julianday(end_time) - julianday(start_time)) * 24) AS avg_hours
        FROM bookings
        WHERE status = 'Approved'
        AND start_time >= :start_date
        AND end_time <= :end_date
    ");
    $stmt->execute([
        ':start_date' => $startDate . ' 00:00:00',
        ':end_date' => $endDate . ' 23:59:59'
    ]);
    $stats['avg_duration'] = $stmt->fetchColumn();

    return $stats;
}

function getUserActivityReport($startDate, $endDate) {
    global $db;

    $stmt = $db->prepare("
        SELECT 
            u.user_id,
            u.first_name || ' ' || u.last_name as user_name,
            COUNT(b.booking_id) as bookings_made,
            d.department_name
        FROM users u
        LEFT JOIN bookings b ON u.user_id = b.user_id
            AND b.start_time >= :start_date
            AND b.end_time <= :end_date
        LEFT JOIN departments d ON u.department_id = d.department_id
        GROUP BY u.user_id
        ORDER BY bookings_made DESC
        LIMIT 5
    ");
    $stmt->execute([
        ':start_date' => $startDate . ' 00:00:00',
        ':end_date' => $endDate . ' 23:59:59'
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold text-maroon-800">Reports</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        View system usage statistics and analytics
                    </p>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="mt-6 bg-white shadow rounded-lg p-4">
                <form method="get" class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="<?= $startDate ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="<?= $endDate ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                    </div>

                    <div class="sm:col-span-2 flex items-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- Booking Statistics -->
            <div class="mt-8">
                <h2 class="text-lg font-medium text-maroon-700 mb-4">Booking Statistics</h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-maroon-500 rounded-md p-3">
                                    <i class="fas fa-calendar-check text-white"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900">
                                                <?= $reportData['booking_stats']['bookings']['total_bookings'] ?>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <i class="fas fa-check-circle text-white"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900">
                                                <?= $reportData['booking_stats']['bookings']['approved'] ?>
                                            </div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                                <?= $reportData['booking_stats']['bookings']['total_bookings'] > 0 ?
                                                    round(($reportData['booking_stats']['bookings']['approved'] / $reportData['booking_stats']['bookings']['total_bookings']) * 100) : 0 ?>%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900">
                                                <?= $reportData['booking_stats']['bookings']['pending'] ?>
                                            </div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-yellow-600">
                                                <?= $reportData['booking_stats']['bookings']['total_bookings'] > 0 ?
                                                    round(($reportData['booking_stats']['bookings']['pending'] / $reportData['booking_stats']['bookings']['total_bookings']) * 100) : 0 ?>%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                    <i class="fas fa-hourglass-half text-white"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Avg Duration</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900">
                                                <?= round($reportData['booking_stats']['avg_duration'] ?? 0) ?> hrs
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Utilization -->
            <div class="mt-8">
                <h2 class="text-lg font-medium text-maroon-700 mb-4">Room Utilization</h2>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Room
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bookings
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Hours
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Utilization
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($reportData['room_utilization'] as $room): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($room['room_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $room['booking_count'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= round($room['total_hours'] ?? 0, 1) ?> hrs
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $maxHours = (strtotime($endDate) - strtotime($startDate)) / (60 * 60) * 8; // 8 hours per day
                                            $utilization = $maxHours > 0 ? ($room['total_hours'] / $maxHours) * 100 : 0;
                                            ?>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-maroon-600 h-2.5 rounded-full" style="width: <?= min(100, $utilization) ?>%"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1"><?= round($utilization) ?>%</div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Users -->
            <div class="mt-8">
                <h2 class="text-lg font-medium text-maroon-700 mb-4">Top Users by Bookings</h2>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bookings Made
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($reportData['user_activity'] as $user): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['user_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($user['department_name'] ?? 'N/A') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $user['bookings_made'] ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="mt-8 bg-white shadow rounded-lg p-4">
                <h2 class="text-lg font-medium text-maroon-700 mb-4">Export Reports</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="export_report.php?type=bookings&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-file-csv mr-2"></i> Export Bookings
                    </a>
                    <a href="export_report.php?type=rooms&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-file-csv mr-2"></i> Export Room Utilization
                    </a>
                    <a href="export_report.php?type=users&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-file-csv mr-2"></i> Export User Activity
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

</body>

</html>