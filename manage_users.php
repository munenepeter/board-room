<?php
require_once 'includes/header.php';

// Check if user is admin
if ($userRole !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        
        // Prevent deleting yourself
        if ($userId && $userId != $_SESSION['user_id']) {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $_SESSION['success_message'] = "User has been deleted successfully.";
            header("Location: manage_users.php");
            exit;
        }
    } elseif (isset($_POST['update_role'])) {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $newRole = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if ($userId && in_array($newRole, ['user', 'approver', 'admin'])) {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->execute([$newRole, $userId]);
            
            $_SESSION['success_message'] = "User role updated successfully.";
            header("Location: manage_users.php");
            exit;
        }
    }
}

// Get all users
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

$query = "SELECT u.*, d.department_name 
          FROM users u
          LEFT JOIN departments d ON u.department_id = d.department_id
          WHERE (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";

$params = [':search' => $search];

if ($roleFilter) {
    $query .= " AND u.role = :role";
    $params[':role'] = $roleFilter;
}

$query .= " ORDER BY u.role, u.last_name, u.first_name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold text-maroon-800">Manage Users</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        View and manage all system users
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <a href="add_user.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-user-plus mr-2"></i> Add User
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
                                placeholder="Search by name or email">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="role" name="role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-maroon-500 focus:border-maroon-500 sm:text-sm rounded-md">
                            <option value="">All Roles</option>
                            <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="approver" <?= $roleFilter === 'approver' ? 'selected' : '' ?>>Approver</option>
                            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Department
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Role
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Joined
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-maroon-100 text-maroon-800 flex items-center justify-center">
                                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?>
                                                <?= $user['user_id'] == $_SESSION['user_id'] ? '(You)' : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($user['department_name'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <select name="role" onchange="this.form.submit()" 
                                            class="<?= $user['user_id'] == $_SESSION['user_id'] ? 'bg-gray-100 cursor-not-allowed' : '' ?> border-gray-300 focus:ring-maroon-500 focus:border-maroon-500 text-sm rounded-md"
                                            <?= $user['user_id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="approver" <?= $user['role'] === 'approver' ? 'selected' : '' ?>>Approver</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <noscript>
                                            <button type="submit" name="update_role" class="ml-2 text-xs text-maroon-600">Update</button>
                                        </noscript>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <button type="submit" name="delete_user" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (empty($users)): ?>
            <div class="mt-12 text-center">
                <i class="fas fa-users text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">No users found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <?= isset($_GET['search']) || isset($_GET['role']) ? 
                        'Try adjusting your search or filter criteria.' : 
                        'There are currently no users in the system.' ?>
                </p>
                <div class="mt-6">
                    <a href="add_user.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-maroon-600 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                        <i class="fas fa-user-plus mr-2"></i> Add New User
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>