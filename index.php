<?php
session_start();
require 'config/database.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['avatar'] = $row['avatar'];
            header("Location: dashboard.php");
            exit;
        }
    }

    $error = true;
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - Inventory System</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#137fec",
              "background-light": "#f6f7f8",
              "background-dark": "#101922",
            },
            fontFamily: {
              "display": ["Inter", "sans-serif"]
            },
          },
        },
      }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
<div class="relative flex h-screen w-full flex-col justify-center items-center overflow-hidden">
    <div class="w-full max-w-md p-4">
        <div class="flex justify-center mb-8">
            <svg class="text-primary h-12 w-12" fill="none" height="48" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="48" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <path d="M2 17l10 5 10-5"></path>
                <path d="M2 12l10 5 10-5"></path>
            </svg>
        </div>
        <div class="bg-white dark:bg-slate-900 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <div class="flex flex-col gap-6">
                <div class="text-center">
                    <h2 class="text-slate-900 dark:text-slate-50 text-2xl font-bold">Sign in to your account</h2>
                    <p class="text-slate-600 dark:text-slate-400 mt-2">Inventory Management System</p>
                </div>
                
                <?php if($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">Username / Password salah.</span>
                </div>
                <?php endif; ?>

                <form action="" method="post" class="flex flex-col gap-4">
                    <label class="flex flex-col w-full">
                        <span class="text-slate-900 dark:text-slate-50 font-medium pb-2">Username</span>
                        <input type="text" name="username" required class="form-input w-full rounded-lg border-slate-300 dark:border-slate-700 h-12 px-4 focus:ring-primary focus:border-primary" placeholder="Enter your username"/>
                    </label>
                    <label class="flex flex-col w-full">
                        <span class="text-slate-900 dark:text-slate-50 font-medium pb-2">Password</span>
                        <input type="password" name="password" required class="form-input w-full rounded-lg border-slate-300 dark:border-slate-700 h-12 px-4 focus:ring-primary focus:border-primary" placeholder="Enter your password"/>
                    </label>
                    <button type="submit" name="login" class="w-full h-12 bg-primary text-white font-bold rounded-lg hover:bg-blue-600 transition">Log In</button>
                </form>
                
                <div class="text-center text-sm text-slate-500">
                    <p>Default Admin: admin / password</p>
                    <p>Default User: user / password</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
