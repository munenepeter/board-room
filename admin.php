<?php
require_once 'includes/header.php';

// Verify admin access
if ($userRole !== 'admin') {
    header("Location: index.php");
    exit();
}



// Get system stats
$userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$departmentCount = $db->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$roomCount = $db->query("SELECT COUNT(*) FROM boardrooms")->fetchColumn();
$pendingApprovals = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
?>
<div class="bg-green-800 my-6 p-1.5 text-center text-white text-sm rounded">
    Hey Admin, a note from the dev, The features in this page are in continous development and in works, you will be notified when complete.
</div>
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4 mt-4">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-maroon-50 text-maroon-600">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-gray-500 text-sm font-medium">Total Users</h3>
                <p class="text-2xl font-semibold text-gray-900"><?= $userCount ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                <i class="fas fa-building text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-gray-500 text-sm font-medium">Departments</h3>
                <p class="text-2xl font-semibold text-gray-900"><?= $departmentCount ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-50 text-green-600">
                <i class="fas fa-door-open text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-gray-500 text-sm font-medium">Meeting Rooms</h3>
                <p class="text-2xl font-semibold text-gray-900"><?= $roomCount ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-gray-500 text-sm font-medium">Pending Approvals</h3>
                <p class="text-2xl font-semibold text-gray-900"><?= $pendingApprovals ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- ERP Integration Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">ERP System Integration</h3>
            <p class="mt-1 text-sm text-gray-500">Connect with external systems to sync users and departments</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <!-- Google Workspace -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <img src="assets/imgs/Google_logo.svg" alt="Google" class="h-8 w-8">
                        <div class="ml-4">
                            <h4 class="font-medium text-gray-900">Google Workspace</h4>
                            <p class="text-sm text-gray-500">Sync users and groups from Google</p>
                        </div>
                    </div>
                    <button class="px-4 py-2 bg-blue-50 text-blue-600 rounded-md text-sm font-medium hover:bg-blue-100">
                        <i class="fas fa-plug mr-2"></i> Connect
                    </button>
                </div>

                <!-- Microsoft 365 -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <img src="assets/imgs/Microsoft_logo.svg" alt="Microsoft" class="h-8 w-8">
                        <div class="ml-4">
                            <h4 class="font-medium text-gray-900">Microsoft 365</h4>
                            <p class="text-sm text-gray-500">Sync users and departments from Azure AD</p>
                        </div>
                    </div>
                    <button class="px-4 py-2 bg-blue-50 text-blue-600 rounded-md text-sm font-medium hover:bg-blue-100">
                        <i class="fas fa-plug mr-2"></i> Connect
                    </button>
                </div>

                <!-- Zoho -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <img src="assets/imgs/zoho-logo.png" alt="Zoho" class="h-8 w-8">
                        <div class="ml-4">
                            <h4 class="font-medium text-gray-900">Zoho People</h4>
                            <p class="text-sm text-gray-500">Sync HR data from Zoho</p>
                        </div>
                    </div>
                    <button class="px-4 py-2 bg-blue-50 text-blue-600 rounded-md text-sm font-medium hover:bg-blue-100">
                        <i class="fas fa-plug mr-2"></i> Connect
                    </button>
                </div>

                <!-- Manual Import -->
                <div class="mt-6">
                    <h4 class="font-medium text-gray-900 mb-2">Manual Import</h4>
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">CSV File</label>
                            <div class="flex items-center">
                                <input type="file" class="hidden" id="csv-upload">
                                <label for="csv-upload" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                                    <i class="fas fa-file-csv mr-2"></i> Choose File
                                </label>
                                <span class="ml-2 text-sm text-gray-500">No file chosen</span>
                            </div>
                        </div>
                        <button class="self-end px-4 py-2 bg-maroon-600 text-white rounded-md text-sm font-medium hover:bg-maroon-700">
                            <i class="fas fa-upload mr-2"></i> Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Tools Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">System Tools</h3>
            <p class="mt-1 text-sm text-gray-500">Automation and system management tools</p>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <!-- Report Scheduling -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">Report Scheduling</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Daily Usage Report</p>
                                <p class="text-sm text-gray-500">Sent every morning at 8:00 AM</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-maroon-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-maroon-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Weekly Analytics</p>
                                <p class="text-sm text-gray-500">Sent every Monday at 9:00 AM</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-maroon-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-maroon-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Monthly Summary</p>
                                <p class="text-sm text-gray-500">Sent on the 1st of each month</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-maroon-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-maroon-600"></div>
                            </label>
                        </div>

                        <button class="mt-2 px-4 py-2 bg-maroon-600 text-white rounded-md text-sm font-medium hover:bg-maroon-700">
                            <i class="fas fa-plus mr-2"></i> Add New Schedule
                        </button>
                    </div>
                </div>

                <!-- Calendar Sync -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">Calendar Synchronization</h4>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" id="sync-google" class="h-4 w-4 text-maroon-600 focus:ring-maroon-500 border-gray-300 rounded" checked>
                            <label for="sync-google" class="ml-2 block text-sm text-gray-700">Sync with Google Calendar</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="sync-outlook" class="h-4 w-4 text-maroon-600 focus:ring-maroon-500 border-gray-300 rounded">
                            <label for="sync-outlook" class="ml-2 block text-sm text-gray-700">Sync with Outlook Calendar</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="sync-ical" class="h-4 w-4 text-maroon-600 focus:ring-maroon-500 border-gray-300 rounded" checked>
                            <label for="sync-ical" class="ml-2 block text-sm text-gray-700">Enable iCal feeds</label>
                        </div>
                        <button class="mt-2 px-4 py-2 bg-maroon-600 text-white rounded-md text-sm font-medium hover:bg-maroon-700">
                            <i class="fas fa-sync mr-2"></i> Update Sync Settings
                        </button>
                    </div>
                </div>

                <!-- Admin Chat -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">User Communication</h4>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <i class="fas fa-comments text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">Admin chat functionality coming soon</p>
                        <button class="mt-3 px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300 cursor-not-allowed" disabled>
                            <i class="fas fa-rocket mr-2"></i> Coming Soon
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Logs Section -->
<div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Recent System Activity</h3>
        <p class="mt-1 text-sm text-gray-500">Logs of important system events</p>
    </div>
    <div class="divide-y divide-gray-200">
        <div class="px-6 py-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-2 text-green-600">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">System backup completed</p>
                    <p class="text-sm text-gray-500">2 hours ago</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-full p-2 text-blue-600">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">New user imported from Google Workspace</p>
                    <p class="text-sm text-gray-500">5 hours ago</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-full p-2 text-yellow-600">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Room capacity exceeded in booking #4572</p>
                    <p class="text-sm text-gray-500">Yesterday</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-maroon-100 rounded-full p-2 text-maroon-600">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">System settings updated</p>
                    <p class="text-sm text-gray-500">2 days ago</p>
                </div>
            </div>
        </div>
    </div>
    <div class="px-6 py-4 bg-gray-50 text-right">
        <a href="system_logs.php" class="text-sm font-medium text-maroon-600 hover:text-maroon-900">
            View all logs <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>

<script>
    // Simple file input display
    document.getElementById('csv-upload').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
        e.target.nextElementSibling.nextElementSibling.textContent = fileName;
    });

    // Placeholder for ERP connection functionality
    document.querySelectorAll('[data-erp-connect]').forEach(button => {
        button.addEventListener('click', function() {
            const provider = this.getAttribute('data-erp-connect');
            alert(`Connecting to ${provider}... This would launch OAuth flow in a real implementation.`);
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>