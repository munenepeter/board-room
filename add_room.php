<?php
require_once 'includes/header.php';

// Check if user is admin
if ($userRole !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Common amenities options
$commonAmenities = [
    'Projector',
    'Whiteboard',
    'TV Screen',
    'Video Conferencing',
    'Phone',
    'Computer',
    'WiFi',
    'Coffee Machine',
    'Water Cooler',
    'Wheelchair Access',
    'Air Conditioning',
    'Natural Light'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Validate and sanitize input
        $roomName = filter_input(INPUT_POST, 'room_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
        $thumbnailUrl = filter_input(INPUT_POST, 'thumbnail_url', FILTER_SANITIZE_URL);

        // Validate required fields
        if (empty($roomName)) throw new Exception("Room name is required");
        if (empty($location)) throw new Exception("Location is required");
        if (!$capacity) throw new Exception("Valid capacity is required");

        // Process custom amenities
        $customAmenities = isset($_POST['custom_amenities']) ?
            array_filter(array_map('trim', explode("\n", $_POST['custom_amenities']))) : [];
        $allAmenities = array_merge($amenities, $customAmenities);
        $amenitiesJson = json_encode(array_values($allAmenities));

        // Insert new room
        $stmt = $db->prepare("
            INSERT INTO boardrooms (
                room_name, location, capacity, amenities, thumbnail_url, is_active
            ) VALUES (
                :room_name, :location, :capacity, :amenities, :thumbnail_url, 1
            )
        ");
        $stmt->execute([
            ':room_name' => $roomName,
            ':location' => $location,
            ':capacity' => $capacity,
            ':amenities' => $amenitiesJson,
            ':thumbnail_url' => $thumbnailUrl
        ]);
        $roomId = $db->lastInsertId();

        $db->commit();

        $_SESSION['success_message'] = "Room added successfully!";
        if (!headers_sent()) {
            header("Location: manage_rooms.php");
        } else {
            echo "<script>window.location.href = 'manage_rooms.php';</script>";
        }
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold text-maroon-800 mb-6">Add New Room</h1>

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

            <form method="POST">
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div>
                        <h2 class="text-lg font-medium text-maroon-700 mb-3">Basic Information</h2>
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label for="room_name" class="block text-sm font-medium text-gray-700">Room Name *</label>
                                <input type="text" name="room_name" id="room_name" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-4">
                                <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                                <input type="text" name="location" id="location" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity *</label>
                                <input type="number" name="capacity" id="capacity" min="1" required value="4"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-6">
                                <label for="thumbnail_url" class="block text-sm font-medium text-gray-700">Thumbnail URL</label>
                                <input type="url" name="thumbnail_url" id="thumbnail_url"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500">Link to an image of the room</p>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div>
                        <h2 class="text-lg font-medium text-maroon-700 mb-3">Amenities</h2>
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Amenities</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <?php foreach ($commonAmenities as $amenity): ?>
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="amenity_<?= str_replace(' ', '_', strtolower($amenity)) ?>"
                                                    name="amenities[]" type="checkbox" value="<?= htmlspecialchars($amenity) ?>"
                                                    class="focus:ring-maroon-500 h-4 w-4 text-maroon-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="amenity_<?= str_replace(' ', '_', strtolower($amenity)) ?>" class="font-medium text-gray-700"><?= $amenity ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="custom_amenities" class="block text-sm font-medium text-gray-700">Custom Amenities</label>
                                <textarea name="custom_amenities" id="custom_amenities" rows="2"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm"
                                    placeholder="Enter custom amenities, one per line"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Add one amenity per line</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Add Room
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // Process custom amenities on form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        const customAmenitiesTextarea = document.getElementById('custom_amenities');
        if (customAmenitiesTextarea.value.trim()) {
            const customAmenities = customAmenitiesTextarea.value.split('\n')
                .map(a => a.trim())
                .filter(a => a.length > 0);

            customAmenities.forEach(amenity => {
                const checkbox = document.createElement('input');
                checkbox.type = 'hidden';
                checkbox.name = 'amenities[]';
                checkbox.value = amenity;
                this.appendChild(checkbox);
            });
        }
    });
</script>

</body>

</html>