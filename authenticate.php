/** This file authenticates user
and provides a response on front-end with AJAX */
<?php
include 'main.php';

// Validate the form data
if (!isset($_POST['name'], $_POST['email'])) {
    exit('Please enter a valid name and  email address!');
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    exit('Please ener a valid email address');
}
// select account from the db based on email address
$smt = $pdo->prepare('SELECT * FROM accounts where email = ? ');
$stmt->execute([$_POST['email']]);
// fetch results and return them as an associative array
$account = $stmt->fetch(PDO::FETCH_ASSOC);
// does account exist?
if (isset($_POST['password']) && $account['role'] == 'Operator') {
    // User is an operator and provided a password
    if (password_verify($_POST['password'], $account['password'])) {
        // Password is correct, authenticate user
        $_SESSION['account_loggedin'] = true;
        $_SESSION['account_id'] = $account['id'];
        $_SESSION['account_role'] = $account['role'];
        // Update the secret code
        update_secret($pdo, $account['id'], $account['email']);
        exit('success');
    } else {
        // Invalid password
        exit('Invalid credentials');
    }
} else if ($account['role'] == 'Operator') {
    exit('operator');
} else if ($account['role'] == 'Guest') {
    // guest don`t need a passport
    $_SESSION['account_loggedin'] = true;
    $_SESSION['account_id'] = $account['id'];
    $_SESSION['account_role'] = $account['role'];
    // update secret code
    update_secret($pdo, $account['id'], $account['email']);
    exit('Success');
}
