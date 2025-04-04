<?php
// auth.php - Authentication functions
if(!session_id()) session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to log in a user
function login($email, $password) {
    print_r($email);

    global $db;
    
    // Prepare statement to fetch user by email
    $stmt = $db->prepare("SELECT user_id, first_name, last_name, password, role FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    print_r($user);

    print_r(password_hash($password, PASSWORD_DEFAULT));

    // Verify user exists and check password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role']; // Store the user's role
        return true;
    }

    return false;
}
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to log out a user
function logout() {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>