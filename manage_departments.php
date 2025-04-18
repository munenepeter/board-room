<?php
require_once 'includes/header.php';

// Verify admin access
if ($userRole !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_department'])) {
        $departmentName = trim($_POST['department_name']);

        if (!empty($departmentName)) {
            $stmt = $db->prepare("INSERT INTO departments (department_name) VALUES (?)");
            $stmt->execute([$departmentName]);
            $_SESSION['success_message'] = "Department added successfully!";
        }
    } elseif (isset($_POST['edit_department'])) {
        $departmentId = intval($_POST['department_id']);
        $departmentName = trim($_POST['department_name']);

        if (!empty($departmentName)) {
            $stmt = $db->prepare("UPDATE departments SET department_name = ? WHERE department_id = ?");
            $stmt->execute([$departmentName, $departmentId]);
            $_SESSION['success_message'] = "Department updated successfully!";
        }
    } elseif (isset($_POST['delete_department'])) {
        $departmentId = intval($_POST['department_id']);

        // Check if department has users before deleting
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE department_id = ?");
        $stmt->execute([$departmentId]);
        $userCount = $stmt->fetchColumn();

        if ($userCount == 0) {
            $stmt = $db->prepare("DELETE FROM departments WHERE department_id = ?");
            $stmt->execute([$departmentId]);
            $_SESSION['success_message'] = "Department deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Cannot delete department with assigned users!";
        }
    }

    // Redirect to prevent form resubmission
    header("Location: manage_departments.php");
    exit();
}

// Get all departments
$departments = $db->query("SELECT * FROM departments ORDER BY department_name")->fetchAll(PDO::FETCH_ASSOC);

// Get department count
$departmentCount = $db->query("SELECT COUNT(*) FROM departments")->fetchColumn();

// Get user count per department
$userCounts = [];
$stmt = $db->query("SELECT department_id, COUNT(*) as user_count FROM users GROUP BY department_id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $userCounts[$row['department_id']] = $row['user_count'];
}
?>

<!-- Add Department Modal -->
<div class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full" id="add-department-modal">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 bg-white rounded-lg shadow sm:p-5">
            <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                <h3 class="text-lg font-semibold text-gray-900">Add New Department</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-toggle="add-department-modal">
                    <i class="fas fa-times"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form action="manage_departments.php" method="POST">
                <div class="mb-4">
                    <label for="department_name" class="block mb-2 text-sm font-medium text-gray-900">Department Name</label>
                    <input type="text" name="department_name" id="department_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-maroon-500 focus:border-maroon-500 block w-full p-2.5" placeholder="Enter department name" required>
                </div>
                <div class="flex items-center space-x-4">
                    <button type="submit" name="add_department" class="text-white bg-maroon-700 hover:bg-maroon-800 focus:ring-4 focus:outline-none focus:ring-maroon-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Add Department
                    </button>
                    <button type="button" data-modal-toggle="add-department-modal" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="hidden absolute top-50 max-w-6xl left-50 z-50 justify-center items-center h-screen" id="edit-department-modal">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 bg-white rounded-lg shadow sm:p-5">
            <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                <h3 class="text-lg font-semibold text-gray-900">Edit Department</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-toggle="edit-department-modal">
                    <i class="fas fa-times"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form action="manage_departments.php" method="POST">
                <input type="hidden" name="department_id" id="edit_department_id">
                <div class="mb-4">
                    <label for="edit_department_name" class="block mb-2 text-sm font-medium text-gray-900">Department Name</label>
                    <input type="text" name="department_name" id="edit_department_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-maroon-500 focus:border-maroon-500 block w-full p-2.5" required>
                </div>
                <div class="flex items-center space-x-4">
                    <button type="submit" name="edit_department" class="text-white bg-maroon-700 hover:bg-maroon-800 focus:ring-4 focus:outline-none focus:ring-maroon-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Update Department
                    </button>
                    <button type="button" data-modal-toggle="edit-department-modal" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Department Modal -->
<div class="hidden absolute top-50 max-w-6xl left-50 z-50 justify-center items-center" id="delete-department-modal">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 bg-white rounded-lg shadow sm:p-5">
            <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                <h3 class="text-lg font-semibold text-gray-900">Delete Department</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-toggle="delete-department-modal">
                    <i class="fas fa-times"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form action="manage_departments.php" method="POST">
                <input type="hidden" name="department_id" id="delete_department_id">
                <p class="mb-4 text-gray-600">Are you sure you want to delete this department? This action cannot be undone.</p>
                <div class="flex items-center space-x-4">
                    <button type="submit" name="delete_department" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Yes, delete it
                    </button>
                    <button type="button" data-modal-toggle="delete-department-modal" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        No, cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="fixed top-4 right-4 z-50">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= $_SESSION['success_message'] ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <button class="text-green-500 hover:text-green-700" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </span>
        </div>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="fixed top-4 right-4 z-50">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= $_SESSION['error_message'] ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <button class="text-red-500 hover:text-red-700" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </span>
        </div>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!-- Main Content -->
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Department Management</h2>
            <p class="text-gray-600">Manage all departments in the system</p>
        </div>
        <button type="button" data-modal-toggle="add-department-modal" class="text-white bg-maroon-700 hover:bg-maroon-800 focus:ring-4 focus:ring-maroon-300 font-medium rounded-lg text-sm px-5 py-2.5 inline-flex items-center mt-4 md:mt-0">
            <i class="fas fa-plus mr-2"></i> Add New Department
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Department Name</th>
                    <th scope="col" class="px-6 py-3">Users</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($departments)): ?>
                    <tr class="bg-white border-b">
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No departments found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($departments as $department): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                <?= htmlspecialchars($department['department_name']) ?>
                            </th>
                            <td class="px-6 py-4">
                                <?= $userCounts[$department['department_id']] ?? 0 ?> users
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="openEditModal(<?= $department['department_id'] ?>, '<?= htmlspecialchars($department['department_name']) ?>')" class="text-maroon-600 hover:text-maroon-900 font-medium text-sm px-3 py-1.5 mr-2">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button onclick="openDeleteModal(<?= $department['department_id'] ?>)" class="text-red-600 hover:text-red-900 font-medium text-sm px-3 py-1.5">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-500">
            Showing <span class="font-medium"><?= count($departments) ?></span> of <span class="font-medium"><?= $departmentCount ?></span> departments
        </div>
    </div>
</div>

<script>
    // Modal toggle functions
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }

    // Open edit modal with department data
    function openEditModal(id, name) {
        document.getElementById('edit_department_id').value = id;
        document.getElementById('edit_department_name').value = name;
        toggleModal('edit-department-modal');
    }

    // Open delete modal with department id
    function openDeleteModal(id) {
        document.getElementById('delete_department_id').value = id;
        toggleModal('delete-department-modal');
    }

    // Initialize modal toggles
    document.querySelectorAll('[data-modal-toggle]').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal-toggle');
            toggleModal(modalId);
        });
    });

    // Close modals when clicking outside
    document.querySelectorAll('.fixed').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    });

    // Auto-close success/error messages after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('[role="alert"]').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
</script>

<?php require_once 'includes/footer.php'; ?>