<?php
session_start();
require_once 'db.php';
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
                            900: '#800000', // Primary maroon
                        },
                        gold: {
                            400: '#FFD700',
                            500: '#e6b800',
                        }
                    }
                }
            }
        }
    </script>
     <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
     <style>
    [x-cloak] {
        display: none !important;
    }
</style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-maroon-900 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center justify-start">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-white text-xl font-bold">
                            <i class="fas fa-calendar-check mr-2"></i> Meeting Room Reservation System
                        </a>
                    </div>

                    <!-- Primary Nav -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="rooms.php" class="text-white hover:text-gold-400 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-door-open mr-1"></i> Rooms
                        </a>
                        <a href="bookings.php" class="text-white hover:text-gold-400 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-calendar-plus mr-1"></i> Bookings
                        </a>
                        <?php if ($isLoggedIn && in_array($userRole, ['approver', 'admin'])): ?>
                            <a href="approvals.php" class="text-white hover:text-gold-400 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-check-circle mr-1"></i> Approvals
                            </a>
                        <?php endif; ?>
                        <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                            <a href="admin.php" class="text-white hover:text-gold-400 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-cog mr-1"></i> Admin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Nav -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <?php if ($isLoggedIn): ?>
                        <!-- Notifications -->
                        <div class="ml-3 relative">
                            <div class="relative">
                                <button id="notifications-button" class="p-1 rounded-full text-white hover:text-gold-400 focus:outline-none">
                                    <span class="sr-only">View notifications</span>
                                    <i class="fas fa-bell h-6 w-6"></i>
                                    <?php if ($unreadNotifications > 0): ?>
                                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
                                            <?= $unreadNotifications ?>
                                        </span>
                                    <?php endif; ?>
                                </button>
                            </div>

                            <!-- Notifications dropdown -->
                            <div id="notifications-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 z-50">
                                <div class="py-1">
                                    <h3 class="px-4 py-2 text-sm font-medium text-gray-700">Notifications</h3>
                                    <?php
                                    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                                    $stmt->execute([$userId]);
                                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (empty($notifications)): ?>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-500">No notifications</a>
                                    <?php else: ?>
                                        <?php foreach ($notifications as $notification): ?>
                                            <a href="notification.php?id=<?= $notification['notification_id'] ?>" class="block px-4 py-2 text-sm <?= $notification['is_read'] ? 'text-gray-600' : 'font-bold text-gray-900' ?> hover:bg-gray-100">
                                                <div class="font-medium"><?= $notification['title'] ?></div>
                                                <div class="text-gray-500 truncate"><?= substr($notification['message'], 0, 50) ?>...</div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-200"></div>
                                    <a href="notifications.php" class="block text-center px-4 py-2 text-sm text-maroon-700 font-medium hover:bg-gray-100">View all notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- User dropdown -->
                        <div class="ml-3 relative">
                            <div>
                                <button id="user-menu-button" class="flex text-sm rounded-full focus:outline-none">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 rounded-full bg-maroon-700 text-white flex items-center justify-center">
                                        <?= strtoupper(substr($_SESSION['first_name'], 0, 1)) ?>
                                    </div>
                                </button>
                            </div>

                            <!-- User dropdown menu -->
                            <div id="user-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                <div class="px-4 py-2 text-sm text-gray-700">
                                    <div>Signed in as</div>
                                    <div class="font-medium truncate"><?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></div>
                                </div>
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> Profile
                                </a>
                                <a href="my_bookings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-calendar-alt mr-2"></i> My Bookings
                                </a>
                                <div class="border-t border-gray-200"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-white hover:text-gold-400 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="register.php" class="text-white hover:text-gold-400 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="-mr-2 flex items-center sm:hidden">
                    <button id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gold-400 focus:outline-none">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars h-6 w-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden sm:hidden bg-maroon-800">
            <div class="pt-2 pb-3 space-y-1">
                <a href="rooms.php" class="text-white hover:bg-maroon-700 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-door-open mr-2"></i> Rooms
                </a>
                <a href="bookings.php" class="text-white hover:bg-maroon-700 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-calendar-plus mr-2"></i> Bookings
                </a>
                <?php if ($isLoggedIn && in_array($userRole, ['approver', 'admin'])): ?>
                    <a href="approvals.php" class="text-white hover:bg-maroon-700 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-check-circle mr-2"></i> Approvals
                    </a>
                <?php endif; ?>
                <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                    <a href="admin.php" class="text-white hover:bg-maroon-700 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-cog mr-2"></i> Admin
                    </a>
                <?php endif; ?>
            </div>
            <div class="pt-4 pb-3 border-t border-maroon-700">
                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center px-4">
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
                        <a href="profile.php" class="block px-4 py-2 text-base font-medium text-white hover:bg-maroon-700">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="my_bookings.php" class="block px-4 py-2 text-base font-medium text-white hover:bg-maroon-700">
                            <i class="fas fa-calendar-alt mr-2"></i> My Bookings
                        </a>
                        <a href="logout.php" class="block px-4 py-2 text-base font-medium text-white hover:bg-maroon-700">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-3 space-y-1">
                        <a href="login.php" class="block px-4 py-2 text-base font-medium text-white hover:bg-maroon-700">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                        <a href="register.php" class="block px-4 py-2 text-base font-medium text-white hover:bg-maroon-700">
                            <i class="fas fa-user-plus mr-2"></i> Register
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-white border-r border-gray-200">
                <div class="h-0 flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                    <nav class="flex-1 px-2 space-y-1">
                        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-tachometer-alt <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                            Dashboard
                        </a>
                        <a href="book_room.php" class="<?= basename($_SERVER['PHP_SELF']) == 'book_room.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-plus-circle <?= basename($_SERVER['PHP_SELF']) == 'book_room.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                            Book a Room
                        </a>
                        <a href="rooms.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-door-open <?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                            View Rooms
                        </a>
                        <!-- <a href="calendar.php" class="<?= basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-calendar-week <?= basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                            Calendar View
                        </a> -->
                        <a href="meetings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'meetings.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <i class="fas fa-users <?= basename($_SERVER['PHP_SELF']) == 'meetings.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                            All Meetings
                        </a>
                        <?php if ($isLoggedIn && in_array($userRole, ['approver', 'admin'])): ?>
                            <a href="approvals.php" class="<?= basename($_SERVER['PHP_SELF']) == 'approvals.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-check-square <?= basename($_SERVER['PHP_SELF']) == 'approvals.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                                Approve Bookings
                            </a>
                        <?php endif; ?>
                        <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                            <a href="manage_rooms.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-cog <?= basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                                Manage Rooms
                            </a>
                            <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-users-cog <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                                Manage Users
                            </a>
                            <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_departments.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-users-cog <?= basename($_SERVER['PHP_SELF']) == 'manage_departments.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                                Manage Departments
                            </a>
                            <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-maroon-50 text-maroon-900 border-l-4 border-maroon-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-chart-bar <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'text-maroon-600' : 'text-gray-400 group-hover:text-gray-500' ?> mr-3"></i>
                                Reports
                            </a>
                        <?php endif; ?>
                    </nav>

                    <div class="px-4 mt-8">
                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Actions</h3>
                        <div class="mt-1 space-y-1">
                            <a href="book_room.php?quick=1" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                <i class="fas fa-bolt text-gray-400 group-hover:text-gray-500 mr-3"></i>
                                Quick Book
                            </a>
                            <a href="find_room.php" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                <i class="fas fa-search text-gray-400 group-hover:text-gray-500 mr-3"></i>
                                Find Available Room
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 overflow-auto">
            <main class="py-6 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto">