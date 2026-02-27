<?php
require_once 'admin_header.php';

// স্ট্যাটিস্টিক্স আনা
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_matches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$pending_transactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 shadow-lg">
        <div class="text-indigo-400 text-4xl mb-2"><i class="fa-solid fa-users"></i></div>
        <h3 class="text-2xl font-bold"><?= $total_users ?></h3>
        <p class="text-gray-400 text-sm">Total Registered Users</p>
    </div>
    
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 shadow-lg">
        <div class="text-orange-400 text-4xl mb-2"><i class="fa-solid fa-gamepad"></i></div>
        <h3 class="text-2xl font-bold"><?= $total_matches ?></h3>
        <p class="text-gray-400 text-sm">Total Matches Created</p>
    </div>
    
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 shadow-lg">
        <div class="text-green-400 text-4xl mb-2"><i class="fa-solid fa-bell"></i></div>
        <h3 class="text-2xl font-bold"><?= $pending_transactions ?></h3>
        <p class="text-gray-400 text-sm">Pending Transactions</p>
        <a href="transactions.php" class="text-indigo-400 text-xs mt-2 inline-block">View Details &rarr;</a>
    </div>
</div>

<div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
    <h3 class="font-bold text-lg mb-4">Quick Actions</h3>
    <div class="flex gap-4">
        <a href="create_match.php" class="bg-indigo-600 px-5 py-2 rounded-lg text-sm font-bold shadow-lg hover:bg-indigo-500">Create New Match</a>
        <a href="update_room.php" class="bg-gray-600 px-5 py-2 rounded-lg text-sm font-bold shadow-lg hover:bg-gray-500">Update Room Credentials</a>
    </div>
</div>

</div></div></body></html>