<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = $_SESSION['user_id'];

// ইউজার এবং তার স্ট্যাটিস্টিক্স ডাটাবেস থেকে আনা
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ইউজার মোট কয়টি ম্যাচ খেলেছে (Count)
$count_stmt = $pdo->prepare("SELECT COUNT(*) as total_played FROM joined_matches WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$stats = $count_stmt->fetch();

$current_page = 'profile';
require_once 'includes/header.php';
?>

<div class="p-4 pb-24 text-center">
    
    <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-tr from-purple-600 to-pink-500 flex items-center justify-center text-4xl font-bold border-[3px] border-[#1a1c29] shadow-lg mt-4 relative">
        <?= strtoupper(substr($user['name'], 0, 2)) ?>
    </div>
    
    <h2 class="text-2xl font-bold mt-4 tracking-wide"><?= htmlspecialchars($user['name']) ?></h2>
    <p class="text-sm text-gray-400 bg-[#1a1c29] border border-gray-700 inline-flex items-center px-4 py-1.5 rounded-full mt-2">
        <i class="fa-solid fa-envelope mr-2"></i> <?= htmlspecialchars($user['email']) ?>
    </p>
    
    <p class="mt-4 text-sm font-semibold text-gray-300 flex items-center justify-center gap-2">
        UID: <span id="uidText" class="tracking-wider"><?= htmlspecialchars($user['ff_uid']) ?></span> 
        <i onclick="copyUID()" class="fa-regular fa-copy text-gray-400 cursor-pointer hover:text-white transition"></i>
    </p>

    <div class="bg-gradient-to-br from-gray-800 to-[#1a1c29] border border-gray-700 rounded-2xl p-6 mt-8 text-left relative overflow-hidden shadow-2xl">
        
        <i class="fa-brands fa-cc-mastercard text-yellow-500 text-3xl mb-5"></i>
        <i class="fa-solid fa-wallet text-gray-700 text-6xl absolute top-4 right-4 opacity-50"></i>

        <p class="text-[10px] text-gray-400 font-bold tracking-widest uppercase">Total Balance</p>
        <h1 class="text-3xl font-bold text-white mb-6 mt-1 flex items-center gap-1">
            <span class="text-green-400 text-2xl">৳</span> <?= $user['balance'] ?>
        </h1>
        
        <div class="flex gap-10 mb-5">
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Deposit</p>
                <p class="font-bold text-[15px] text-white mt-1">৳ <?= $user['deposit_balance'] ?></p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Winnings</p>
                <p class="font-bold text-[15px] text-white mt-1">৳ <?= $user['winning_balance'] ?></p>
            </div>
        </div>
        
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Card Holder</p>
        <p class="font-bold text-sm text-white uppercase mt-1 tracking-widest"><?= htmlspecialchars($user['name']) ?></p>
    </div>

    <div class="flex gap-4 mt-6">
        <div class="flex-1 bg-[#1a1c29] border border-gray-700 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-500 bg-opacity-20 flex items-center justify-center text-indigo-400 text-xl"><i class="fa-solid fa-play"></i></div>
            <div class="text-left">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Played</p>
                <p class="font-bold text-xl text-white"><?= $stats['total_played'] ?></p>
            </div>
        </div>
        
        <div class="flex-1 bg-[#1a1c29] border border-gray-700 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-500 bg-opacity-20 flex items-center justify-center text-green-400 text-xl"><i class="fa-solid fa-trophy"></i></div>
            <div class="text-left">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Won</p>
                <p class="font-bold text-xl text-green-400">৳ <?= $user['winning_balance'] ?></p>
            </div>
        </div>
    </div>
    
    <a href="add_money.php" class="bg-[#1a1c29] border border-gray-700 rounded-2xl p-4 flex items-center gap-4 mt-6 active:scale-95 transition-transform">
        <div class="w-12 h-12 bg-green-500 bg-opacity-20 flex items-center justify-center rounded-xl text-green-400 text-2xl"><i class="fa-solid fa-plus"></i></div>
        <div class="text-left flex-1">
            <h3 class="font-bold text-white text-[15px]">Add Money</h3>
            <p class="text-xs text-gray-400 mt-0.5">Top up your wallet balance.</p>
        </div>
        <div class="bg-gray-800 w-8 h-8 rounded-full flex items-center justify-center">
            <i class="fa-solid fa-chevron-right text-gray-400 text-xs"></i>
        </div>
    </a>

</div>

<script>
// Copy UID Function
function copyUID() {
    var uid = document.getElementById("uidText").innerText;
    navigator.clipboard.writeText(uid);
    alert("UID Copied: " + uid);
}
</script>

<?php require_once 'includes/footer.php'; ?>