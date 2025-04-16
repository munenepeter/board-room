<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: login.php");
    exit;
}

// Handle meeting actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_booking'])) {
        handleBookingAction('Cancelled', $_POST['booking_id']);
    } elseif (isset($_POST['delete_booking'])) {
        handleBookingAction('Deleted', $_POST['booking_id']);
    }
}

function handleBookingAction($action, $bookingId) {
    global $db;

    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (!$userId) {
        $_SESSION['error_message'] = "You must be logged in to perform this action.";
        header("Location: meetings.php");
        exit;
    }

    $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

    try {
        $db->beginTransaction();

        // Verify user owns the booking or is admin
        $stmt = $db->prepare("SELECT user_id FROM bookings WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        $bookingOwner = $stmt->fetchColumn();

        if ($bookingOwner != $userId && $is_admin === false) {
            throw new Exception("You don't have permission to modify this booking");
        }

        if ($action === 'Cancelled') {
            $stmt = $db->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ?");
            $stmt->execute([$bookingId]);

            // Create notification
            createNotification(
                $bookingOwner,
                "Booking Cancelled",
                "Your booking has been cancelled.",
                'booking',
                $bookingId
            );

            $_SESSION['success_message'] = "Booking cancelled successfully";
        } elseif ($action === 'Deleted') {
            $stmt = $db->prepare("DELETE FROM bookings WHERE booking_id = ?");
            $stmt->execute([$bookingId]);

            $_SESSION['success_message'] = "Booking deleted successfully";
        }

        $db->commit();
        if (!headers_sent()) {
            header("Location: meetings.php");
        } else {
            echo '<script>window.location.href = "meetings.php";</script>';
        }
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: meetings.php");
        exit;
    }
}

// Get all meetings with filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchQuery = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

$query = "
    SELECT 
        b.*,
        r.room_name,
        r.location,
        u.first_name || ' ' || u.last_name as booked_by,
        (u.user_id = :current_user_id) as is_my_booking
    FROM bookings b
    JOIN boardrooms r ON b.room_id = r.room_id
    JOIN users u ON b.user_id = u.user_id
    WHERE 
        (b.event_name LIKE :search OR r.room_name LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)
";

$params = [
    ':search' => $searchQuery,
    ':current_user_id' => $userId
];

if ($statusFilter && $statusFilter !== 'all') {
    $query .= " AND b.status = :status";
    $params[':status'] = $statusFilter;
}

$query .= " ORDER BY b.start_time DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 z-10 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <h5 id="detailsModalTitle" class="text-lg font-medium text-gray-900">Booking Details</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" aria-label="Close">
                        <span aria-hidden="true" class="text-2xl">&times;</span>
                    </button>
                </div>
                <div id="detailsModalBody" class="mt-3">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button aria-label="Close" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<main class="py-6 px-4 sm:px-6 lg:px-8" x-data="modal">
    <div class="max-w-7xl mx-auto">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold text-maroon-800">All Meetings</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        View all scheduled meetings in the system
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <a href="book_room.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-plus mr-2"></i> New Booking
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-6 bg-white shadow rounded-lg p-4">
                <form method="get" class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" name="search" id="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                                class="block w-full pr-10 border-gray-300 focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md"
                                placeholder="Search meetings...">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md">
                            <option value="all" <?= $statusFilter === 'all' || !$statusFilter ? 'selected' : '' ?>>All Statuses</option>
                            <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Approved" <?= $statusFilter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Meetings List -->
            <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Event
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Room
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Booked By
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($meetings as $meeting):
                                $startTime = new DateTime($meeting['start_time']);
                                $endTime = new DateTime($meeting['end_time']);
                                $isMyBooking = $meeting['is_my_booking'];
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($meeting['event_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= $meeting['attendees_count'] ?> attendees</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($meeting['room_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($meeting['location']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= $startTime->format('M j, Y') ?></div>
                                        <div class="text-sm text-gray-500"><?= $startTime->format('g:i A') ?> - <?= $endTime->format('g:i A') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($meeting['booked_by']) ?>
                                        <?= $isMyBooking ? '<span class="text-maroon-600">(You)</span>' : '' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $meeting['status'] == 'Approved' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $meeting['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                        <?= $meeting['status'] == 'Cancelled' ? 'bg-gray-100 text-gray-800' : '' ?>
                                        <?= $meeting['status'] == 'Rejected' ? 'bg-red-100 text-red-800' : '' ?>">
                                            <?= $meeting['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button type="button" onclick="showDetailsModal(<?= $meeting['booking_id'] ?>)"
                                                class="text-maroon-600 hover:text-maroon-900"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if ($isMyBooking || $userRole === 'admin'): ?>
                                                <?php if ($meeting['status'] === 'Pending' || $meeting['status'] === 'Approved'): ?>
                                                    <button @click="openModal('cancel', <?= $meeting['booking_id'] ?>, '<?= htmlspecialchars($meeting['event_name']) ?>')"
                                                        class="text-yellow-600 hover:text-yellow-900"
                                                        title="Cancel Booking">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <button @click="openModal('delete', <?= $meeting['booking_id'] ?>, '<?= htmlspecialchars($meeting['event_name']) ?>')"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Delete Booking">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>

                                                <?php if ($meeting['status'] === 'Pending' || $meeting['status'] === 'Approved'): ?>
                                                    <a href="reschedule.php?id=<?= $meeting['booking_id'] ?>"
                                                        class="text-blue-600 hover:text-blue-900"
                                                        title="Reschedule">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (empty($meetings)): ?>
                <div class="mt-12 text-center">
                    <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">No meetings found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <?= isset($_GET['search']) || isset($_GET['status']) ?
                            'Try adjusting your search or filter criteria.' :
                            'There are currently no scheduled meetings.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div x-show="modalType === 'cancel'" x-cloak class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Cancel Booking</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to cancel the booking for <span class="font-medium" x-text="modalEventName"></span>?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <form method="POST" class="sm:col-start-2">
                        <input type="hidden" name="booking_id" x-bind:value="modalBookingId">
                        <button type="submit" name="cancel_booking"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:text-sm">
                            Cancel Booking
                        </button>
                    </form>
                    <button @click="closeModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Booking Modal -->
    <div x-show="modalType === 'delete'" x-cloak class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Booking</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to permanently delete the booking for <span class="font-medium" x-text="modalEventName"></span>?
                            </p>
                            <p class="mt-2 text-sm text-red-600">
                                This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <form method="POST" class="sm:col-start-2">
                        <input type="hidden" name="booking_id" x-bind:value="modalBookingId">
                        <button type="submit" name="delete_booking"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                            Delete Booking
                        </button>
                    </form>
                    <button @click="closeModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('modal', () => ({
            modalOpen: false,
            modalType: '',
            modalBookingId: null,
            modalEventName: '',

            openModal(type, bookingId, eventName) {
                this.modalType = type;
                this.modalBookingId = bookingId;
                this.modalEventName = eventName;
                this.modalOpen = true;
                document.body.style.overflow = 'hidden'; // Prevents background scrolling
            },

            closeModal() {
                this.modalType = '';
                this.modalBookingId = null;
                this.modalEventName = '';
                this.modalOpen = false;
                document.body.style.overflow = ''; // Restores scrolling
            }
        }));
    });
</script>

<script>
    // Function to show a modal by ID
    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    // Function to hide a modal by ID
    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Initialize event listeners for all close buttons
    function initModalCloseButtons() {
        // Get all modals
        const modals = ['detailsModal'];

        // For each modal, add click event listener to its close button
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);

            // Add event listener to close button
            const closeButton = modal.querySelector('button[aria-label="Close"]');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    hideModal(modalId);
                });
            }

            // Add event listener to cancel button
            const cancelButton = modal.querySelector('button:not([type="submit"])');
            if (cancelButton) {
                cancelButton.addEventListener('click', function() {
                    hideModal(modalId);
                });
            }

            // Add click event to background overlay for closing
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideModal(modalId);
                }
            });
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initModalCloseButtons();
    });

    // Show approve modal with booking ID
    function showApproveModal(bookingId) {
        $('#approveBookingId').val(bookingId);
        showModal('approvalModal');
    }

    // Show reject modal with booking ID
    function showRejectModal(bookingId) {
        $('#rejectBookingId').val(bookingId);
        showModal('rejectionModal');
    }

    // Show details modal with booking details loaded via AJAX
    function showDetailsModal(bookingId) {
        $('#detailsModalTitle').text('Loading...');
        $('#detailsModalBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>');
        showModal('detailsModal');

        $.ajax({
            url: 'ajax/get_booking_details.php',
            type: 'GET',
            data: {
                id: bookingId
            },
            success: function(response) {
                $('#detailsModalTitle').text('Booking Details');
                $('#detailsModalBody').html(response);
            },
            error: function() {
                $('#detailsModalBody').html('<div class="text-center py-4 text-red-500">Failed to load booking details.</div>');
            }
        });
    }
</script>

</body>

</html>