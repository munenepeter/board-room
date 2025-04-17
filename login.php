<?php
require_once 'database/db.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
        .focus\:ring-maroon:focus {
            --tw-ring-color: #800000;
        }
        .focus\:border-maroon:focus {
            border-color: #800000;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-900 bg-opacity-70 flex items-center justify-center p-4">
    
    <div class="bg-white bg-opacity-90 rounded-2xl shadow-xl w-full max-w-lg p-8 space-y-8">
        <div class="text-center">
            <!-- Logo -->
            <div class="mb-6">
                <img src="assets\imgs\MRMS.png" alt="Company Logo" class="h-20 mx-auto">
            </div>
            
            <h2 class="text-3xl font-bold text-maroon">Welcome Back</h2>
            <p class="mt-2 text-gray-700">Sign in to your  account</p>
            <?php if (isset($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
        </div>

        <form method="POST" action="login.php" class="space-y-6">
            <div class="space-y-4">
                <div class="relative">
                    <label class="absolute left-3 top-3.5 h-5 w-5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </label>
                    <input name="email"
                        type="email"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon focus:border-maroon outline-none transition-colors"
                        placeholder="Email address"
                        required />
                </div>

                <div class="relative">
                    <label class="absolute left-3 top-3.5 h-5 w-5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </label>
                    <input name="password"
                        type="password"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon focus:border-maroon outline-none transition-colors"
                        placeholder="Password"
                        required />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="remember"
                        class="h-4 w-4 rounded border-gray-300 text-maroon focus:ring-maroon" />
                    <label htmlFor="remember" class="ml-2 text-sm text-gray-600">
                        Remember me
                    </label>
                </div>
               
            </div>

            <button
                type="submit"
                class="w-full bg-maroon text-white py-3 rounded-lg hover:bg-maroon-dark transition-colors duration-200 font-medium">
                Sign in
            </button>
        </form>
    </div>
</body>

</html>