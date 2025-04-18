<?php
session_start();
require_once 'database/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['role'] : 'guest';
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Get unread notification count
$unreadNotifications = 0;
if ($isLoggedIn) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $unreadNotifications = $stmt->fetchColumn();
}
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room Reservation System</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,container-queries"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: {
                            50: '#fdf2f2',
                            100: '#fde8e8',
                            200: '#fbd5d5',
                            300: '#f8b4b4',
                            400: '#f98080',
                            500: '#f05252',
                            600: '#e02424',
                            700: '#c81e1e',
                            800: '#9b1c1c',
                            900: '#800000',
                        },
                        gold: {
                            400: '#FFD700',
                            500: '#e6b800',
                        }
                    },
                    transitionProperty: {
                        'height': 'height',
                        'spacing': 'margin, padding',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .animate-fade-in {
            animation: fadeIn 0.2s ease-out;
        }

        .animate-fade-out {
            animation: fadeOut 0.2s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-5px);
            }
        }

        .notification-badge {
            box-shadow: 0 0 0 2px #800000;
        }
    </style>
    <script src="assets/js/jquery360.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-50 antialiased">
    <!-- Navigation -->
    <nav class="bg-maroon-900 shadow-sm sticky top-0 z-50">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo and mobile menu button -->
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="md:hidden text-gray-200 hover:text-white focus:outline-none mr-2">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars h-6 w-6"></i>
                    </button>

                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="flex items-center text-white text-lg font-semibold tracking-tight">
                            <i class="fas fa-calendar-check mr-2 text-gold-400"></i>
                            <span class="hidden sm:inline">Meeting Room</span>
                            <span class="sm:hidden">MRS</span>
                        </a>
                    </div>

                    <!-- Desktop navigation -->
                    <div class="hidden md:ml-8 md:flex md:space-x-1">
                        <a href="rooms.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-200 hover:text-white hover:bg-maroon-800 transition-colors">
                            <i class="fas fa-door-open mr-1"></i> Rooms
                        </a>
                        <a href="bookings.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-200 hover:text-white hover:bg-maroon-800 transition-colors">
                            <i class="fas fa-calendar-plus mr-1"></i> Bookings
                        </a>
                        <?php if ($isLoggedIn && in_array($userRole, ['approver', 'admin'])): ?>
                            <a href="approvals.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-200 hover:text-white hover:bg-maroon-800 transition-colors">
                                <i class="fas fa-check-circle mr-1"></i> Approvals
                            </a>
                        <?php endif; ?>
                        <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                            <a href="admin.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-200 hover:text-white hover:bg-maroon-800 transition-colors">
                                <i class="fas fa-cog mr-1"></i> Admin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right side controls -->
                <div class="flex items-center space-x-3">
                    <?php if ($isLoggedIn): ?>
                        <!-- Notifications -->
                        <div class="relative">
                            <button id="notifications-button" class="p-1 rounded-full text-gray-200 hover:text-white focus:outline-none relative">
                                <span class="sr-only">View notifications</span>
                                <i class="fas fa-bell h-5 w-5"></i>
                                <?php if ($unreadNotifications > 0): ?>
                                    <span class="absolute top-0 right-0 h-3 w-3 rounded-full bg-red-500 notification-badge flex items-center justify-center text-[8px] text-white">
                                        <?= $unreadNotifications > 9 ? '9+' : $unreadNotifications ?>
                                    </span>
                                <?php endif; ?>
                            </button>

                            <!-- Notifications dropdown -->
                            <div id="notifications-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">
                                <div class="py-1">
                                    <div class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded-t-lg">
                                        <h3 class="text-sm font-medium text-gray-700">Notifications</h3>
                                        <!-- <?php if ($unreadNotifications > 0): ?>
                                            <a href="mark_all_read.php" class="text-xs text-maroon-600 hover:text-maroon-800">Mark all as read</a>
                                        <?php endif; ?> -->
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <?php
                                        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                                        $stmt->execute([$userId]);
                                        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        if (empty($notifications)): ?>
                                            <div class="flex items-center justify-center py-6">
                                                <span class="text-sm text-gray-500">No notifications</span>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($notifications as $notification): ?>
                                                <a href="notification.php?id=<?= $notification['notification_id'] ?>" class="block px-4 py-3 text-sm hover:bg-gray-50 transition-colors border-l-2 <?= $notification['is_read'] ? 'border-transparent' : 'border-maroon-500' ?>">
                                                    <div class="flex justify-between">
                                                        <span class="<?= $notification['is_read'] ? 'text-gray-600' : 'font-semibold text-gray-900' ?>"><?= $notification['title'] ?></span>
                                                        <span class="text-xs text-gray-500"><?= time_elapsed_string($notification['created_at']) ?></span>
                                                    </div>
                                                    <div class="text-gray-500 mt-1 truncate"><?= substr($notification['message'], 0, 50) ?>...</div>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="border-t border-gray-200"></div>
                                    <!-- <a href="notifications.php" class="block text-center px-4 py-2 text-sm text-maroon-700 font-medium hover:bg-gray-100 transition-colors">View all notifications</a> -->
                                </div>
                            </div>
                        </div>

                        <!-- User dropdown -->
                        <div class="relative">
                            <button id="user-menu-button" class="flex items-center text-sm rounded-full focus:outline-none">
                                <span class="sr-only">Open user menu</span>
                                <div class="h-8 w-8 rounded-full bg-maroon-700 text-white flex items-center justify-center hover:bg-maroon-600 transition-colors">
                                    <?= strtoupper(substr($_SESSION['first_name'], 0, 1)) ?>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-200 hidden lg:inline"><?= $_SESSION['first_name'] ?></span>
                                <i class="fas fa-chevron-down ml-1 text-gray-300 text-xs hidden lg:inline"></i>
                            </button>

                            <!-- User dropdown menu -->
                            <div id="user-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm text-gray-500">Signed in as</p>
                                    <p class="text-sm font-semibold text-gray-900 truncate"><?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></p>
                                </div>
                                <a href="bookings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-calendar-alt mr-2 text-gray-500 w-4 text-center"></i> All Bookings
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-2 text-gray-500 w-4 text-center"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-200 hover:text-white hover:bg-maroon-800 transition-colors">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="register.php" class="px-3 py-2 rounded-md text-sm font-medium text-white bg-maroon-700 hover:bg-maroon-600 transition-colors">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-maroon-800 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="pt-2 pb-3 space-y-1 px-2">
                <a href="rooms.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                    <i class="fas fa-door-open mr-2"></i> Rooms
                </a>
                <a href="bookings.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                    <i class="fas fa-calendar-plus mr-2"></i> Bookings
                </a>
                <?php if ($isLoggedIn && in_array($userRole, ['approver', 'admin'])): ?>
                    <a href="approvals.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                        <i class="fas fa-check-circle mr-2"></i> Approvals
                    </a>
                <?php endif; ?>
                <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                    <a href="admin.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                        <i class="fas fa-cog mr-2"></i> Admin
                    </a>
                <?php endif; ?>
            </div>
            <?php if ($isLoggedIn): ?>
                <div class="pt-4 pb-3 border-t border-maroon-700 px-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-maroon-600 text-white flex items-center justify-center">
                                <?= strtoupper(substr($_SESSION['first_name'], 0, 1)) ?>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white"><?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></div>
                            <div class="text-sm font-medium text-gold-400"><?= ucfirst($userRole) ?></div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="my_bookings.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                            <i class="fas fa-calendar-alt mr-2"></i> My Bookings
                        </a>
                        <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="pt-4 pb-3 border-t border-maroon-700 px-4">
                    <div class="space-y-1">
                        <a href="login.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-maroon-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                        <a href="register.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-maroon-700 hover:bg-maroon-600 transition-colors">
                            <i class="fas fa-user-plus mr-2"></i> Register
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });

            // Dropdown toggle functions
            function toggleDropdown(buttonId, dropdownId) {
                const button = document.getElementById(buttonId);
                const dropdown = document.getElementById(dropdownId);

                if (!button || !dropdown) return;

                button.addEventListener('click', function(e) {
                    e.stopPropagation();

                    // Close other dropdowns
                    document.querySelectorAll('[id$="-dropdown"]').forEach(dd => {
                        if (dd.id !== dropdownId) {
                            dd.classList.add('hidden');
                        }
                    });

                    // Toggle current dropdown
                    dropdown.classList.toggle('hidden');
                });

                // Close when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            }

            // Initialize dropdowns
            toggleDropdown('notifications-button', 'notifications-dropdown');
            toggleDropdown('user-menu-button', 'user-dropdown');
        });

        // Helper function for notification time
        function time_elapsed_string(datetime) {
            // Implement your time formatting logic here
            return "Just now";
        }
    </script>

    <!-- Sidebar and main content -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 border-r border-gray-200 bg-white">
                <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                    <nav class="flex-1 px-3 space-y-1">
                        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <i class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'fas fa-tachometer-alt text-maroon-600' : 'fas fa-tachometer-alt text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                            Dashboard
                        </a>
                        <a href="book_room.php" class="<?= basename($_SERVER['PHP_SELF']) == 'book_room.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <i class="<?= basename($_SERVER['PHP_SELF']) == 'book_room.php' ? 'fas fa-plus-circle text-maroon-600' : 'fas fa-plus-circle text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                            Book a Room
                        </a>
                        <a href="rooms.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <i class="<?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'fas fa-door-open text-maroon-600' : 'fas fa-door-open text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                            View Rooms
                        </a>
                        <a href="meetings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'meetings.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <i class="<?= basename($_SERVER['PHP_SELF']) == 'meetings.php' ? 'fas fa-users text-maroon-600' : 'fas fa-users text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                            All Meetings
                        </a>
                        <?php if ($isLoggedIn && in_array($userRole, ['approver', 'admin'])): ?>
                            <a href="approvals.php" class="<?= basename($_SERVER['PHP_SELF']) == 'approvals.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <i class="<?= basename($_SERVER['PHP_SELF']) == 'approvals.php' ? 'fas fa-check-square text-maroon-600' : 'fas fa-check-square text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                                Approve Bookings
                            </a>
                        <?php endif; ?>
                        <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                            <div class="px-3 pt-4">
                                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</h3>
                            </div>
                            <a href="manage_rooms.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <i class="<?= basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'fas fa-cog text-maroon-600' : 'fas fa-cog text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                                Manage Rooms
                            </a>
                            <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <i class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'fas fa-users-cog text-maroon-600' : 'fas fa-users-cog text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                                Manage Users
                            </a>
                            <a href="manage_departments.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_departments.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <i class="<?= basename($_SERVER['PHP_SELF']) == 'manage_departments.php' ? 'fas fa-sitemap text-maroon-600' : 'fas fa-sitemap text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                                Manage Departments
                            </a>
                            <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-maroon-50 text-maroon-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <i class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'fas fa-chart-bar text-maroon-600' : 'fas fa-chart-bar text-gray-400 group-hover:text-gray-500' ?> mr-3 w-5 text-center"></i>
                                Reports
                            </a>
                        <?php endif; ?>
                    </nav>

                </div>
            </div>
        </div>
        <!-- Main content -->
        <div class="flex-1 overflow-auto">
            <main class="px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto relative">