<?php
session_start();

const ADMIN_USER = "admin";
const ADMIN_PASS = "123456"; // بعداً بهترش می‌کنیم

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        $_POST['username'] === ADMIN_USER &&
        $_POST['password'] === ADMIN_PASS
    ) {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit;
    }

    $error = "اطلاعات اشتباه است";
}
?>

<form method="post">

    <input name="username" placeholder="Username">
    <input name="password" type="password" placeholder="Password">

    <button type="submit">Login</button>

    <?php if (isset($error)) echo $error; ?>

</form>