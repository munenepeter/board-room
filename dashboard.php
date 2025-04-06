<?php
require_once 'includes/header.php';
?>
   
   <div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Calendar Section (Left) -->
        <div class="lg:w-2/3">
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-xl font-semibold text-maroon-800 mb-4">Meeting Calendar</h2>
                <div id="calendar" class="fc"></div>
            </div>
        </div>

        <!-- Bookings List Section (Right) -->
        <div class="lg:w-1/3">
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-maroon-800">Upcoming Bookings</h2>
                    <a href="bookings.php" class="text-sm text-maroon-600 hover:text-maroon-800">View All</a>
                </div>
                
                <div class="space-y-4">
                    <?php
                    $upcoming_bookings = $db->prepare("
                        SELECT b.booking_id, b.event_name, b.start_time, b.end_time, b.status, 
                        r.room_name, r.location
                        FROM bookings b
                        JOIN boardrooms r ON b.room_id = r.room_id AND b.start_time >= DATETIME('now')
                        ORDER BY b.start_time ASC
                        LIMIT 3
                    ");
                    $upcoming_bookings->execute();

                    $bookings = $upcoming_bookings->fetchAll(PDO::FETCH_ASSOC);
                
                    if (count($bookings) > 0):
                        foreach ($bookings as $booking):
                            $start_date = new DateTime($booking['start_time']);
                            $end_date = new DateTime($booking['end_time']);
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-maroon-300 transition-colors">
                        <div class="flex justify-between items-start">
                            <h3 class="font-medium text-gray-900"><?= htmlspecialchars($booking['event_name']) ?></h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                <?= $booking['status'] == 'Approved' ? 'bg-green-100 text-green-800' : '' ?>
                                <?= $booking['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                <?= $booking['status'] == 'Rejected' ? 'bg-red-100 text-red-800' : '' ?>">
                                <?= $booking['status'] ?>
                            </span>
                        </div>
                        
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <i class="fas fa-door-open text-maroon-500 mr-2"></i>
                            <?= htmlspecialchars($booking['room_name']) ?> (<?= htmlspecialchars($booking['location']) ?>)
                        </div>
                        
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <i class="fas fa-calendar-day text-maroon-500 mr-2"></i>
                            <?= $start_date->format('M j, Y') ?>
                        </div>
                        
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <i class="fas fa-clock text-maroon-500 mr-2"></i>
                            <?= $start_date->format('g:i A') ?> - <?= $end_date->format('g:i A') ?>
                        </div>
                        
                        <div class="mt-3 flex justify-end">
                            <a href="booking_details.php?id=<?= $booking['booking_id'] ?>" class="text-sm text-maroon-600 hover:text-maroon-800 font-medium">
                                View Details
                            </a>
                        </div>
                    </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <div class="text-center py-6">
                        <i class="fas fa-calendar-times text-gray-400 text-4xl mb-2"></i>
                        <p class="text-gray-500">No upcoming bookings</p>
                        <a href="book_room.php" class="mt-2 inline-block text-sm text-maroon-600 hover:text-maroon-800 font-medium">
                            Book a room now
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-lg shadow-md p-4 mt-6">
                <h2 class="text-xl font-semibold text-maroon-800 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="book_room.php" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-maroon-50 hover:border-maroon-300 transition-colors">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-maroon-100 text-maroon-600 flex items-center justify-center mr-3">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">New Booking</h3>
                            <p class="text-sm text-gray-500">Book a meeting room</p>
                        </div>
                    </a>
                    <a href="find_room.php" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-maroon-50 hover:border-maroon-300 transition-colors">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-maroon-100 text-maroon-600 flex items-center justify-center mr-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Find Available Room</h3>
                            <p class="text-sm text-gray-500">Check real-time availability</p>
                        </div>
                    </a>
                    <?php if (in_array($userRole, ['approver', 'admin'])): ?>
                    <a href="approvals.php" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-maroon-50 hover:border-maroon-300 transition-colors">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-maroon-100 text-maroon-600 flex items-center justify-center mr-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Pending Approvals</h3>
                            <p class="text-sm text-gray-500">Review booking requests</p>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FullCalendar Styles and Script -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            
            // Disable weekends by setting these properties
            //weekends: false, // This hides Saturday and Sunday completely
            
            weekends: true,
            dayCellClassNames: function(arg) {
                if (arg.date.getDay() === 0 || arg.date.getDay() === 6) { // Sunday or Saturday
                    return 'fc-day-disabled';
                }
                return '';
            },
            
            events: [
                <?php
                $calendar_bookings = $db->prepare("
                    SELECT b.booking_id, b.event_name, b.start_time, b.end_time, b.status, r.room_name
                    FROM bookings b
                    JOIN boardrooms r ON b.room_id = r.room_id
                    WHERE b.user_id = :user_id OR 
                          (b.status = 'Approved' AND :user_role IN ('approver', 'admin'))
                ");
                $calendar_bookings->execute([
                    ':user_id' => $userId,
                    ':user_role' => $userRole
                ]);
                
                while ($row = $calendar_bookings->fetch(PDO::FETCH_ASSOC)) {
                    $color = '';
                    if ($row['status'] == 'Pending') $color = '#f59e0b';
                    else if ($row['status'] == 'Approved') $color = '#065f46';
                    else if ($row['status'] == 'Rejected') $color = '#b91c1c';
                    
                    echo "{
                        title: '" . addslashes($row['event_name']) . " - " . addslashes($row['room_name']) . "',
                        start: '" . $row['start_time'] . "',
                        end: '" . $row['end_time'] . "',
                        color: '" . $color . "',
                        extendedProps: {
                            status: '" . $row['status'] . "'
                        }
                    },";
                }
                ?>
            ],
            
            // Add validation for any event interactions
            selectAllow: function(selectInfo) {
                const start = selectInfo.start;
                const end = selectInfo.end;
                
                // Prevent selection of weekends
                if (start.getDay() === 0 || start.getDay() === 6 || 
                    end.getDay() === 0 || end.getDay() === 6) {
                    return false;
                }
                
                return true;
            },
            
            // Make dates non-selectable if they're weekends
            selectConstraint: {
                daysOfWeek: [1, 2, 3, 4, 5] // Only allow Monday-Friday (1-5)
            },
            
            eventClick: function(info) {
                // You could show a modal here instead of an alert
                alert(
                    'Event: ' + info.event.title + '\n' +
                    'Status: ' + info.event.extendedProps.status + '\n' +
                    'Start: ' + info.event.start.toLocaleString() + '\n' +
                    'End: ' + info.event.end.toLocaleString()
                );
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            views: {
                timeGridWeek: {
                    titleFormat: { year: 'numeric', month: 'short', day: 'numeric' },
                    weekends: false // Disable weekends in week view too
                },
                timeGridDay: {
                    titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
                }
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            }
        });
        calendar.render();
    });
</script>

<!-- Add this style if you're using the alternative approach to display but disable weekends -->
<style>
    .fc-day-disabled {
        background-color: #f1f1f1 !important;
        color: #ccc !important;
        pointer-events: none;
        opacity: 0.6;
    }
</style>
</body>

</html>