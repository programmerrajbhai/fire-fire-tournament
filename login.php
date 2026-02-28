<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$msg = '';
if (isset($_SESSION['success_msg'])) {
    $msg = "<div class='bg-green-500/20 text-green-400 p-3 rounded-lg text-sm font-bold text-center mb-4'>" . $_SESSION['success_msg'] . "</div>";
    unset($_SESSION['success_msg']);
}

if (isset($_POST['login'])) {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // লগইন সফল, সেশন সেট করা
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        $msg = "<div class='bg-red-500/20 text-red-500 p-3 rounded-lg text-sm font-bold text-center mb-4'>ইমেইল অথবা পাসওয়ার্ড ভুল!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KheloFreeFire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#0f111a] text-white flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-sm bg-[#1a1c29] p-8 rounded-3xl border border-gray-800 shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-600/40">
                <i class="fa-solid fa-gamepad text-2xl text-white"></i>
            </div>
            <h2 class="text-2xl font-black text-white uppercase tracking-wider">Welcome Back</h2>
            <p class="text-xs text-gray-400 mt-1">Login to continue playing.</p>
        </div>

        <?= $msg ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Email Address</label>
                <div class="relative">
                    <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-500"></i>
                    <input type="email" name="email" required class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 pl-10 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Password</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-500"></i>
                    <input type="password" name="password" required class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 pl-10 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
            </div>
            
            <button type="submit" name="login" class="w-full bg-indigo-600 text-white font-black py-3.5 rounded-xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg shadow-indigo-600/30 uppercase mt-2">
                Login Now
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            Don't have an account? <a href="register.php" class="text-indigo-400 font-bold hover:underline">Register here</a>
        </p>
    </div>

</body>
</html>