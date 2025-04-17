<?php
session_start();

// Redirect logged-in users to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room Reservation Management System</title>
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1571624436279-b272aff752b5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-blend-mode: overlay;
        }
        .bg-maroon {
            background-color: #800000;
        }
        .bg-maroon-light {
            background-color: rgba(128, 0, 0, 0.1);
        }
        .hover\:bg-maroon-dark:hover {
            background-color: #5a0000;
        }
        .text-maroon {
            color: #800000;
        }
        .border-maroon {
            border-color: #800000;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 bg-opacity-70">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <!-- Main Content -->
        <div class="bg-white bg-opacity-90 rounded-2xl shadow-xl p-8 max-w-4xl w-full text-center space-y-6">
            <!-- Logo -->
            <div class="mx-auto">
                <!-- Replace with your actual logo -->
                <img src="assets\imgs\MRMS.png" alt="Company Logo" class="h-20 mx-auto">
            </div>
            
            <div class="space-y-4">
                <h1 class="text-3xl sm:text-4xl font-bold text-maroon">
                    Welcome to the Meeting Room Reservation Management System
                </h1>
                <p class="text-lg text-gray-700">
                    Streamline your meeting space management with our intuitive booking/reservation system
                </p>
            </div>

            <div class="pt-4">
                <a href="login.php" 
                   class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-white bg-maroon rounded-lg shadow-sm hover:bg-maroon-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon transition-colors duration-200">
                    Sign in to your account
                </a>
            </div>

            <!-- Feature List -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-8 text-left">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-maroon-light rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">Easy Booking</h3>
                    </div>
                    <p class="text-sm text-gray-600">Schedule meetings with just a few clicks</p>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-maroon-light rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">Real-time Updates</h3>
                    </div>
                    <p class="text-sm text-gray-600">Get instant confirmation and updates</p>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-maroon-light rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">Smart Management</h3>
                    </div>
                    <p class="text-sm text-gray-600">Efficient room allocation and tracking</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-8 text-center text-gray-300 text-sm">
            <p>&copy; <?php echo date("Y"); ?> Meeting Room Reservation Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>