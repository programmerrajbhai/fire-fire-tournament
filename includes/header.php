<?php
session_start();
require_once 'db.php';

// ডেমো পারপাস: অটো লগইন ধরে নিচ্ছি ১ নাম্বার ইউজারকে
if(!isset($_SESSION['user_id'])){
    $_SESSION['user_id'] = 1; 
}
$user_id = $_SESSION['user_id'];

// ইউজারের ব্যালেন্স আনা
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$balance = $user ? $user['balance'] : 0;

// ডাটাবেস থেকে সেটিংস (লোগো) আনা
$setting_stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $setting_stmt->fetch();
$site_logo = $settings ? $settings['logo'] : 'logo.png';
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
        <img src="assets/images/<?= htmlspecialchars($site_logo) ?>" alt="Logo" class="w-8 h-8 rounded-full bg-gray-800 object-cover border border-gray-700" onerror="this.src='https://ui-avatars.com/api/?name=KF&background=eab308&color=fff'">
        <h1 class="text-lg font-bold tracking-wide">KheloFreeFire</h1>
    </div>
    <div class="bg-gray-800 px-3 py-1 rounded-full flex items-center gap-2 border border-gray-700 shadow-inner">
        <i class="fa-solid fa-circle-check text-green-400 text-sm"></i>
        <span class="font-bold">৳ <?= $balance; ?></span>
    </div>
</div>