<?php
require_once 'includes/header.php';

// Check if user is admin
if ($userRole !== 'admin') {
    header("Location: rooms.php");
    exit;
}

// Handle room deletion
if (isset($_POST['delete_room'])) {
    $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    
    if ($roomId) {
        // Soft delete (set is_active to 0)
        $stmt = $db->prepare("UPDATE boardrooms SET is_active = 0 WHERE room_id = ?");
        $stmt->execute([$roomId]);
        
        $_SESSION['success_message'] = "Room has been deleted successfully.";
        header("Location: manage_rooms.php");
        exit;
    }
}

// Get all rooms (including inactive for admin)
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'active';

$query = "SELECT r.*, 
          (SELECT COUNT(*) FROM bookings b 
           WHERE b.room_id = r.room_id 
           AND b.start_time > datetime('now') 
           AND b.status IN ('Pending', 'Approved')) as upcoming_bookings
          FROM boardrooms r 
          WHERE (r.room_name LIKE :search OR r.location LIKE :search)";

$params = [':search' => $search];

if ($statusFilter === 'active') {
    $query .= " AND r.is_active = 1";
} elseif ($statusFilter === 'inactive') {
    $query .= " AND r.is_active = 0";
}

$query .= " ORDER BY r.is_active DESC, r.room_name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold text-maroon-800">Manage Rooms</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Add, edit, and manage meeting rooms in your organization
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <a href="add_room.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-plus mr-2"></i> Add Room
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
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md">
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rooms Table -->
            <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Room
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Capacity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bookings
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($rooms as $room): ?>
                            <tr class="<?= $room['is_active'] ? '' : 'bg-gray-50' ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if ($room['thumbnail_url']): ?>
                                            <img class="h-10 w-10 rounded-md object-cover" src="<?= htmlspecialchars($room['thumbnail_url']) ?>" alt="">
                                            <?php else: ?>
                                            <div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center text-gray-400">
                                                <i class="fas fa-door-open"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($room['room_name']) ?></div>
                                            <?php if (!empty($room['amenities'])): 
                                                $amenities = json_decode($room['amenities'], true); ?>
                                            <div class="text-sm text-gray-500">
                                                <?= implode(', ', array_slice($amenities, 0, 2)) ?>
                                                <?= count($amenities) > 2 ? '...' : '' ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($room['location']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $room['capacity'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $room['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= $room['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $room['upcoming_bookings'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="edit_room.php?id=<?= $room['room_id'] ?>" class="text-maroon-600 hover:text-maroon-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($room['is_active']): ?>
                                        <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to deactivate this room? Existing bookings will not be affected.');">
                                            <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                                            <button type="submit" name="delete_room" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <form method="post" action="activate_room.php" class="inline">
                                            <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (empty($rooms)): ?>
            <div class="mt-12 text-center">
                <i class="fas fa-door-open text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">No rooms found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <?= isset($_GET['search']) || isset($_GET['status']) ? 
                        'Try adjusting your search or filter criteria.' : 
                        'There are currently no meeting rooms.' ?>
                </p>
                <div class="mt-6">
                    <a href="add_room.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-plus mr-2"></i> Add New Room
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

</body>
</html>