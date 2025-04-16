<?php
require_once 'includes/header.php';

// Pagination settings
$perPage = 4; // Number of items per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

$pendingPage = isset($_GET['pending_page']) ? max(1, intval($_GET['pending_page'])) : 1;
$bookingsPage = isset($_GET['bookings_page']) ? max(1, intval($_GET['bookings_page'])) : 1;

// Get total count of pending approvals
$totalPending = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
$totalPagesPending = ceil($totalPending / $perPage);

// Get pending approvals with pagination
$pending_approvals = $db->prepare("
    SELECT b.booking_id, b.event_name, b.attendees_count, b.start_time, b.end_time, b.status, r.room_name, u.first_name, u.last_name
    FROM bookings b
    JOIN boardrooms r ON b.room_id = r.room_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.status = 'Pending'
    ORDER BY b.start_time ASC
    LIMIT :limit OFFSET :offset
");
$pending_approvals->bindValue(':limit', $perPage, PDO::PARAM_INT);
$pending_approvals->bindValue(':offset', $offset, PDO::PARAM_INT);
$pending_approvals->execute();
$pendingApprovals = $pending_approvals->fetchAll(PDO::FETCH_ASSOC);

// Similar for other bookings
$totalOther = $db->query("SELECT COUNT(*) FROM bookings WHERE status != 'Pending'")->fetchColumn();
$totalPagesOther = ceil($totalOther / $perPage);

$other_bookings = $db->prepare("
    SELECT b.booking_id, b.event_name, b.attendees_count, b.start_time, b.end_time, b.status, r.room_name, u.first_name, u.last_name,
           (b.end_time < datetime('now') AND b.status = 'Approved') AS is_expired
    FROM bookings b
    JOIN boardrooms r ON b.room_id = r.room_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.status != 'Pending'
    ORDER BY b.start_time ASC
    LIMIT :limit OFFSET :offset
");
$other_bookings->bindValue(':limit', $perPage, PDO::PARAM_INT);
$other_bookings->bindValue(':offset', $offset, PDO::PARAM_INT);
$other_bookings->execute();
$otherBookings = $other_bookings->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 z-10 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <h5 class="text-lg font-medium text-gray-900">Approve Booking</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" aria-label="Close">
                        <span aria-hidden="true" class="text-2xl">&times;</span>
                    </button>
                </div>
                <form id="approveForm" method="post" class="mt-3">
                    <div class="mb-4">
                        <input type="hidden" name="booking_id" id="approveBookingId">
                        <div class="mb-4">
                            <label for="approvalNotes" class="block text-sm font-medium text-gray-700 mb-1">Approval Notes (Optional)</label>
                            <textarea id="approvalNotes" name="approval_notes" rows="3" class="shadow-sm focus:ring-maroon-500 focus:border-maroon-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirm Approval
                        </button>
                        <button aria-label="Close" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 z-10 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <h5 class="text-lg font-medium text-gray-900">Reject Booking</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" aria-label="Close">
                        <span aria-hidden="true" class="text-2xl">&times;</span>
                    </button>
                </div>
                <form id="rejectForm" method="post" class="mt-3">
                    <div class="mb-4">
                        <input type="hidden" name="booking_id" id="rejectBookingId">
                        <div class="mb-4">
                            <label for="rejectionNotes" class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection (Required)</label>
                            <textarea id="rejectionNotes" name="approval_notes" rows="3" required class="shadow-sm focus:ring-maroon-500 focus:border-maroon-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirm Rejection
                        </button>
                        <button aria-label="Close" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <!-- Pending Approvals Section -->
    <div class="px-4 py-5 sm:px-6 bg-maroon-50 mb-4">
        <h3 class="text-lg leading-6 font-medium text-maroon-900">Pending Approvals</h3>
    </div>
    <?php if (empty($pendingApprovals)): ?>
        <div class="mt-12 text-center py-12">
            <i class="fas fa-users text-4xl text-gray-400 mb-3"></i>
            <h3 class="text-lg font-medium text-gray-900">No pending booking requests</h3>
            <p class="mt-1 text-sm text-gray-500">All clear for now!</p>
        </div>
    <?php else: ?>
        <ul class="divide-y divide-gray-200 mt-2">
            <?php foreach ($pendingApprovals as $booking): ?>
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-maroon-600 truncate">
                                <?= htmlspecialchars($booking['event_name']) ?>
                            </p>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending Approval
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-door-open text-gray-400 mr-1.5"></i>
                                    <?= htmlspecialchars($booking['room_name']) ?>
                                </p>
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    <i class="fas fa-users text-gray-400 mr-1.5"></i>
                                    <?= htmlspecialchars($booking['attendees_count']) ?> attendees
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1.5"></i>
                                <time datetime="<?= date('Y-m-d\TH:i:s', strtotime($booking['start_time'])) ?>">
                                    <?= date('M j, Y g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?>
                                </time>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-end space-x-2">
                            <button onclick="showApproveModal(<?= $booking['booking_id'] ?>)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Approve
                            </button>
                            <button onclick="showRejectModal(<?= $booking['booking_id'] ?>)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Reject
                            </button>
                            <button onclick="showDetailsModal(<?= $booking['booking_id'] ?>)" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                Details
                            </button>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>

        </ul>
    <?php endif; ?>
    <?php if (!empty($pendingApprovals) && $totalPagesPending > 1): ?>
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?= ($offset + 1) ?></span> to <span class="font-medium"><?= min($offset + $perPage, $totalPending) ?></span> of <span class="font-medium"><?= $totalPending ?></span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= ($page - 1) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPagesPending; $i++): ?>
                            <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'bg-maroon-50 border-maroon-500 text-maroon-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPagesPending): ?>
                            <a href="?page=<?= ($page + 1) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <!-- Other Bookings Section -->
    <div class="px-4 py-5 sm:px-6 bg-maroon-50 mb-4 mt-8">
        <h3 class="text-lg leading-6 font-medium text-maroon-900">All Bookings</h3>
    </div>
    <?php if (empty($otherBookings)): ?>
        <div class="mt-12 text-center py-12">
            <i class="fas fa-calendar-alt text-4xl text-gray-400 mb-3"></i>
            <h3 class="text-lg font-medium text-gray-900">No bookings found</h3>
            <p class="mt-1 text-sm text-gray-500">All approved bookings will appear here</p>
        </div>
    <?php else: ?>
        <ul class="divide-y divide-gray-200 mt-2">
            <?php foreach ($otherBookings as $booking):
                $isExpired = strtotime($booking['end_time']) < time();
                $isCompleted = $booking['status'] === 'Completed';
            ?>
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-maroon-600 truncate">
                                <?= htmlspecialchars($booking['event_name']) ?>
                            </p>
                            <div class="ml-2 flex-shrink-0 flex">
                                <?php if ($isExpired): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Expired
                                    </span>
                                <?php elseif ($isCompleted): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Completed
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-door-open text-gray-400 mr-1.5"></i>
                                    <?= htmlspecialchars($booking['room_name']) ?>
                                </p>
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    <i class="fas fa-user text-gray-400 mr-1.5"></i>
                                    <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?>
                                </p>
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    <i class="fas fa-users text-gray-400 mr-1.5"></i>
                                    <?= htmlspecialchars($booking['attendees_count']) ?> attendees
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1.5"></i>
                                <time datetime="<?= date('Y-m-d\TH:i:s', strtotime($booking['start_time'])) ?>">
                                    <?= date('M j, Y g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?>
                                </time>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-end space-x-2">
                            <button onclick="showDetailsModal(<?= $booking['booking_id'] ?>)" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                Details
                            </button>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($otherBookings) && $totalPagesOther > 1): ?>
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?= ($offset + 1) ?></span> to <span class="font-medium"><?= min($offset + $perPage, $totalPagesOther) ?></span> of <span class="font-medium"><?= $totalPagesOther ?></span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= ($page - 1) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPagesOther; $i++): ?>
                            <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'bg-maroon-50 border-maroon-500 text-maroon-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPagesOther): ?>
                            <a href="?page=<?= ($page + 1) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
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
        const modals = ['approvalModal', 'rejectionModal', 'detailsModal'];

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
            url: 'ajax/get_booking_success.php',
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

    // Handle approve form submission
    $('#approveForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: 'ajax/approve_booking.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                hideModal('approvalModal');
                // location.reload(); // Refresh to show updated status
            },
            error: function() {
                alert('Failed to approve booking. Please try again.');
            }
        });
    });

    // Handle reject form submission
    $('#rejectForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: 'ajax/reject_booking.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                hideModal('rejectionModal');
                // location.reload(); // Refresh to show updated status
            },
            error: function() {
                alert('Failed to reject booking. Please try again.');
            }
        });
    });
</script>
</body>

</html>