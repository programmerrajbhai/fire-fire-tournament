<?php
// যদি আগে থেকে সেশন স্টার্ট করা না থাকে, তবেই করবে
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// 🚨 রিয়েল লগইন চেক: লগইন করা না থাকলে login.php তে পাঠিয়ে দেবে
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ইউজারের ব্যালেন্স আনা
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// যদি ডাটাবেস থেকে ইউজার ডিলিট হয়ে যায়, তবে তাকে বের করে দেবে
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$balance = $user['balance'];

// ডাটাবেস থেকে সেটিংস (লোগো) আনা এবং URL সাপোর্ট
$setting_stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $setting_stmt->fetch();
$site_logo = $settings ? $settings['logo'] : 'logo.png';
$logo_src = (strpos($site_logo, 'http') === 0) ? $site_logo : 'assets/images/' . $site_logo;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KheloFreeFire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f111a; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-bar { background-color: #1a1c29; }
        .bottom-nav { background-color: #1a1c29; border-top: 1px solid #2d3748; }
        .nav-item.active { color: #8b5cf6; } 
    </style>
</head>
<body class="pb-20"> 
<div class="top-bar sticky top-0 z-50 flex justify-between items-center p-4 shadow-md border-b border-gray-800">
    <div class="flex items-center gap-2">
        <img src="<?= htmlspecialchars($logo_src) ?>" alt="Logo" class="w-8 h-8 rounded-full bg-gray-800 object-cover border border-gray-700" onerror="this.src='https://ui-avatars.com/api/?name=KF&background=eab308&color=fff'">
        <h1 class="text-lg font-bold tracking-wide">KheloFreeFire</h1>
    </div>
    <div class="bg-gray-800 px-3 py-1 rounded-full flex items-center gap-2 border border-gray-700 shadow-inner">
        <i class="fa-solid fa-circle-check text-green-400 text-sm"></i>
        <span class="font-bold">৳ <?= $balance; ?></span>
    </div>
</div>