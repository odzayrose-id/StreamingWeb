<?php
session_start();
include '../koneksi.php';

// Jika sudah login, langsung arahkan ke index admin
if(isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Cek kecocokan di database
    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    
    if(mysqli_num_rows($query) > 0) {
        // Jika benar, buat sesi (session)
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="https://apps.odzayrose.my.id/favicon/streaming/apple-touch-icon.png">
    <link rel="icon" href="https://apps.odzayrose.my.id/favicon/streaming/favicon.ico">
    <link rel="manifest" href="https://apps.odzayrose.my.id/favicon/streaming/site.webmanifest">
    <title>Login - Streaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #141414; 
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0;
        }
        .card-login { 
            background-color: #1f1f1f; 
            border: 1px solid #333; 
            border-radius: 10px; 
            width: 100%; 
            max-width: 400px; 
            padding: 40px; 
        }
        .form-control { background-color: #333; border: 1px solid #444; color: white; }
        .form-control:focus { background-color: #444; color: white; border-color: #e50914; box-shadow: none; }
        .btn-netflix { background-color: #e50914; color: white; border: none; width: 100%; font-weight: bold; }
        .btn-netflix:hover { background-color: #b20710; color: white; }
    </style>
</head>
<body>

    <div class="card-login shadow-lg">
        <h2 class="text-center fw-bold mb-4">ADMIN<span class="text-danger">STREAMING</span></h2>
        
        <?php if($error): ?>
            <div class="alert alert-danger p-2 text-center small"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small text-white-50">Username</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>
            <div class="mb-4">
                <label class="form-label small text-white-50">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-netflix py-2">Masuk</button>
        </form>
    </div>

</body>
</html>
