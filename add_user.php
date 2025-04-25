<?php
require_once 'includes/header.php';

// Check if user is admin
if ($userRole !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Get all departments for dropdown
$departments = $db->query("SELECT * FROM departments ORDER BY department_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $departmentId = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Validate required fields
        if (empty($firstName)) throw new Exception("First name is required");
        if (empty($lastName)) throw new Exception("Last name is required");
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Valid email is required");
        if (empty($password)) throw new Exception("Password is required");
        if ($password !== $confirmPassword) throw new Exception("Passwords do not match");
        if (!$departmentId) throw new Exception("Department is required");
        if (!in_array($role, ['user', 'approver', 'admin'])) throw new Exception("Invalid role");

        // Check if email already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Email already exists");
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $db->prepare("
            INSERT INTO users (
                first_name, last_name, email, password, department_id, role
            ) VALUES (
                :first_name, :last_name, :email, :password, :department_id, :role
            )
        ");
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password' => $passwordHash,
            ':department_id' => $departmentId,
            ':role' => $role
        ]);

        $_SESSION['success_message'] = "User added successfully";
        if (!headers_sent()) {
            header("Location: manage_users.php");
        } else {
            echo "<script>window.location.href = 'manage_users.php';</script>";
        }
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold text-maroon-800 mb-6">Add New User</h1>

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
                    <!-- Personal Info -->
                    <div>
                        <h2 class="text-lg font-medium text-maroon-700 mb-3">Personal Information</h2>
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                <input type="text" name="first_name" id="first_name" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                <input type="text" name="last_name" id="last_name" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-6">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" name="email" id="email" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div>
                        <h2 class="text-lg font-medium text-maroon-700 mb-3">Account Information</h2>
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                                <input type="password" name="password" id="password" required minlength="8"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                                <input type="password" name="confirm_password" id="confirm_password" required minlength="8"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="department_id" class="block text-sm font-medium text-gray-700">Department *</label>
                                <select id="department_id" name="department_id" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md">
                                    <option value="">Select a department</option>
                                    <?php while ($dept = $departments->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                                <select id="role" name="role" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md">
                                    <option value="user">User</option>
                                    <option value="approver">Approver</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Add User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
</body>

</html>