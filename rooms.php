<?php
require_once 'includes/header.php';

// Get all active rooms with optional search/filter
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$capacityFilter = isset($_GET['capacity']) ? (int)$_GET['capacity'] : null;

$query = "SELECT r.*, 
          (SELECT COUNT(*) FROM bookings b 
           WHERE b.room_id = r.room_id 
           AND b.start_time > datetime('now') 
           AND b.status IN ('Pending', 'Approved')) as upcoming_bookings
          FROM boardrooms r 
          WHERE r.is_active = 1 
          AND (r.room_name LIKE :search OR r.location LIKE :search)";

$params = [':search' => $search];

if ($capacityFilter) {
    $query .= " AND r.capacity >= :capacity";
    $params[':capacity'] = $capacityFilter;
}

$query .= " ORDER BY r.room_name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold text-maroon-800">Meeting Rooms</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Browse and book available meeting rooms in your organization
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <a href="book_room.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-plus mr-2"></i> New Booking
                    </a>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="mt-6 bg-white shadow rounded-lg p-4">
                <form method="get" class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" name="search" id="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                                class="block w-full pr-10 border-gray-300 focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md"
                                placeholder="Search by name or location">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="capacity" class="block text-sm font-medium text-gray-700">Minimum Capacity</label>
                        <select id="capacity" name="capacity" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md">
                            <option value="">Any capacity</option>
                            <option value="2" <?= isset($_GET['capacity']) && $_GET['capacity'] == 2 ? 'selected' : '' ?>>2+ people</option>
                            <option value="5" <?= isset($_GET['capacity']) && $_GET['capacity'] == 5 ? 'selected' : '' ?>>5+ people</option>
                            <option value="10" <?= isset($_GET['capacity']) && $_GET['capacity'] == 10 ? 'selected' : '' ?>>10+ people</option>
                            <option value="20" <?= isset($_GET['capacity']) && $_GET['capacity'] == 20 ? 'selected' : '' ?>>20+ people</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rooms Grid -->
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($rooms as $room): ?>
                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:border-maroon-300 transition-colors">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <?php if ($room['thumbnail_url']): ?>
                        <img src="<?= htmlspecialchars($room['thumbnail_url']) ?>" alt="<?= htmlspecialchars($room['room_name']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                            <i class="fas fa-door-open text-4xl"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg leading-6 font-medium text-gray-900"><?= htmlspecialchars($room['room_name']) ?></h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-maroon-100 text-maroon-800">
                                <?= $room['capacity'] ?> seats
                            </span>
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <i class="fas fa-map-marker-alt text-maroon-500 mr-1.5"></i>
                            <?= htmlspecialchars($room['location']) ?>
                        </div>
                        
                        <?php if (!empty($room['amenities'])): 
                            $amenities = json_decode($room['amenities'], true); ?>
                        <div class="mt-3">
                            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Amenities</h4>
                            <div class="mt-1 flex flex-wrap gap-1">
                                <?php foreach ($amenities as $amenity): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <?= htmlspecialchars($amenity) ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-calendar-alt text-maroon-500 mr-1"></i>
                                <?= $room['upcoming_bookings'] ?> upcoming bookings
                            </div>
                            <div class="flex space-x-2">
                                <a href="book_room.php?room_id=<?= $room['room_id'] ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                    Book Now
                                </a>
                                <!-- <a href="room_details.php?id=<?= $room['room_id'] ?>" class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                    Details
                                </a> -->
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($rooms)): ?>
            <div class="mt-12 text-center">
                <i class="fas fa-door-open text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">No rooms found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <?= isset($_GET['search']) || isset($_GET['capacity']) ? 
                        'Try adjusting your search or filter criteria.' : 
                        'There are currently no active meeting rooms.' ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

</body>
</html>