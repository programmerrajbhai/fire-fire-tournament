<?php
session_start();
require_once '../includes/db.php'; 

// সাধারণ সিকিউরিটি (ভবিষ্যতে এখানে অ্যাডমিন লগইন চেক বসাবেন)
$admin_logged_in = true; 

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - KheloFreeFire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#0f111a] text-white flex h-screen overflow-hidden font-sans">

    <div class="w-64 bg-[#1a1c29] border-r border-gray-800 hidden md:flex flex-col transition-all duration-300">
        
        <div class="p-6 border-b border-gray-800 text-center">
            <h2 class="font-extrabold text-xl text-indigo-400 tracking-wider flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-shield"></i> ADMIN
            </h2>
        </div>
        
        <div class="p-4 space-y-1.5 flex-1 overflow-y-auto">
            <a href="index.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'index.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-gauge w-5 text-center"></i> Dashboard
            </a>
            <a href="create_match.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'create_match.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-square-plus w-5 text-center"></i> Create Match
            </a>
            <a href="update_room.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'update_room.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-key w-5 text-center"></i> Room Manager
            </a>
            
            <a href="match_participants.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'match_participants.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-users-rays w-5 text-center"></i> Match Players
            </a>

            <a href="users.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'users.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-wallet w-5 text-center"></i> Users & Wallets
            </a>

            <a href="transactions.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'transactions.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-money-bill-transfer w-5 text-center"></i> Transactions
            </a>
            <a href="categories.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'categories.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-layer-group w-5 text-center"></i> Categories
            </a>
            <a href="settings.php" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?= ($current_page == 'settings.php') ? 'bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white text-sm' ?>">
                <i class="fa-solid fa-gear w-5 text-center"></i> App Settings
            </a>

            <div class="my-4 border-t border-gray-800"></div>
            
            <a href="../index.php" target="_blank" class="flex items-center gap-3 p-3 rounded-xl text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/10 transition-all duration-200 text-sm">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center"></i> View Live App
            </a>
        </div>
    </div>

    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#0f111a]">
        
        <div class="bg-[#1a1c29] p-4 flex justify-between items-center border-b border-gray-800 z-10 shadow-sm">
            <h1 class="font-bold text-lg text-gray-200 capitalize flex items-center gap-2">
                <?php 
                    $title = str_replace('_', ' ', str_replace('.php', '', $current_page));
                    echo ($title == 'index') ? 'Dashboard' : $title;
                ?>
            </h1>
            <div class="bg-indigo-600/20 border border-indigo-500/50 px-4 py-2 rounded-full text-xs font-bold text-indigo-300 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-user-shield"></i> Super Admin
            </div>
        </div>
        
        <div class="p-6 flex-1 overflow-y-auto pb-20">