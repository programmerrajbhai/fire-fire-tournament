<?php
require_once 'admin_header.php';

// ১. স্ট্যাটিস্টিক্স (ডাটাবেস থেকে কাউন্ট আনা)
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_matches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$pending_transactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn();

// ২. রিসেন্ট পেন্ডিং ট্রানজেকশন (ড্যাশবোর্ডে দ্রুত দেখার জন্য)
$recent_trx = $pdo->query("SELECT t.*, u.name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.status = 'pending' ORDER BY t.id DESC LIMIT 5")->fetchAll();
?>

<div class="max-w-7xl mx-auto pb-10">
    
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-1">Welcome back, Admin! 👋</h2>
        <p class="text-sm text-gray-400">Here is what's happening in your app today.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-[#1a1c29] border border-gray-700 rounded-xl p-6 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 text-indigo-500 opacity-10 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-users text-8xl"></i>
            </div>
            <div class="text-indigo-400 text-3xl mb-3"><i class="fa-solid fa-users"></i></div>
            <h3 class="text-3xl font-extrabold text-white mb-1"><?= $total_users ?></h3>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Total Users</p>
        </div>
        
        <div class="bg-[#1a1c29] border border-gray-700 rounded-xl p-6 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 text-orange-500 opacity-10 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-gamepad text-8xl"></i>
            </div>
            <div class="text-orange-400 text-3xl mb-3"><i class="fa-solid fa-gamepad"></i></div>
            <h3 class="text-3xl font-extrabold text-white mb-1"><?= $total_matches ?></h3>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Total Matches</p>
        </div>

        <div class="bg-[#1a1c29] border border-gray-700 rounded-xl p-6 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 text-emerald-500 opacity-10 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-layer-group text-8xl"></i>
            </div>
            <div class="text-emerald-400 text-3xl mb-3"><i class="fa-solid fa-layer-group"></i></div>
            <h3 class="text-3xl font-extrabold text-white mb-1"><?= $total_categories ?></h3>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Categories</p>
        </div>
        
        <div class="bg-[#1a1c29] border border-gray-700 rounded-xl p-6 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 text-pink-500 opacity-10 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-bell text-8xl"></i>
            </div>
            <div class="text-pink-400 text-3xl mb-3"><i class="fa-solid fa-bell"></i></div>
            <h3 class="text-3xl font-extrabold text-white mb-1"><?= $pending_transactions ?></h3>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Pending Action</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-[#1a1c29] rounded-xl p-6 border border-gray-700 shadow-xl">
                <h3 class="font-bold text-lg mb-4 text-gray-200 border-b border-gray-700 pb-2"><i class="fa-solid fa-bolt text-yellow-500 mr-2"></i> Quick Actions</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="create_match.php" class="bg-indigo-600/20 border border-indigo-500 text-indigo-400 px-4 py-3 rounded-lg text-sm font-bold shadow hover:bg-indigo-600 hover:text-white transition flex items-center justify-between">
                        <span><i class="fa-solid fa-plus mr-2"></i> Create Match</span> <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                    <a href="categories.php" class="bg-emerald-600/20 border border-emerald-500 text-emerald-400 px-4 py-3 rounded-lg text-sm font-bold shadow hover:bg-emerald-600 hover:text-white transition flex items-center justify-between">
                        <span><i class="fa-solid fa-layer-group mr-2"></i> Manage Categories</span> <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                    <a href="update_room.php" class="bg-orange-600/20 border border-orange-500 text-orange-400 px-4 py-3 rounded-lg text-sm font-bold shadow hover:bg-orange-600 hover:text-white transition flex items-center justify-between">
                        <span><i class="fa-solid fa-key mr-2"></i> Room Credentials</span> <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                    <a href="settings.php" class="bg-gray-700/50 border border-gray-600 text-gray-300 px-4 py-3 rounded-lg text-sm font-bold shadow hover:bg-gray-600 hover:text-white transition flex items-center justify-between">
                        <span><i class="fa-solid fa-gear mr-2"></i> App Settings</span> <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-[#1a1c29] rounded-xl border border-gray-700 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-200"><i class="fa-solid fa-clock-rotate-left text-pink-500 mr-2"></i> Recent Pending Requests</h3>
                    <a href="transactions.php" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">View All</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-800/50 text-gray-400">
                            <tr>
                                <th class="p-4 font-bold">User</th>
                                <th class="p-4 font-bold">Type</th>
                                <th class="p-4 font-bold">Amount</th>
                                <th class="p-4 font-bold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($recent_trx) > 0): ?>
                                <?php foreach($recent_trx as $trx): ?>
                                <tr class="border-b border-gray-800 hover:bg-gray-800/30 transition">
                                    <td class="p-4 font-bold text-gray-200"><?= htmlspecialchars($trx['name']) ?></td>
                                    <td class="p-4">
                                        <?php if($trx['type'] == 'add_money'): ?>
                                            <span class="bg-green-500/10 text-green-400 border border-green-500/20 px-2 py-1 rounded text-[10px] font-bold uppercase">Add Money</span>
                                        <?php else: ?>
                                            <span class="bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-1 rounded text-[10px] font-bold uppercase">Withdraw</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 font-bold text-indigo-300">৳ <?= $trx['amount'] ?></td>
                                    <td class="p-4 text-right">
                                        <a href="transactions.php" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-indigo-500 transition">Review</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-500">
                                        <i class="fa-solid fa-check-circle text-4xl mb-2 text-gray-600"></i>
                                        <p>All caught up! No pending transactions.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

</div></div></body></html>