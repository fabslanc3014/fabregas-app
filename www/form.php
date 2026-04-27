<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<form action="form.php" method="POST">
    <label>Username</label>
    <input type="text" name="username"><br><br>

    <label>Email</label>
    <input type="text" name="email"><br><br>

    <label>Password</label>
    <input type="password" name="password"><br><br>
    

    <button type="submit">Signup</button>
</form>

<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    echo "<hr>";
    echo "<h3>Submitted Data</h3>";

    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    echo "Username: $username<br>";
    echo "Email: $email<br>";
    echo "Password: $password<br>";
} else {
    echo "No data Submitted";
}
?>

</body>
</html>