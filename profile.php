<?php
session_start();
require_once 'includes/db.php';

// 🚨 রিয়েল লগইন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// ইউজার এবং তার স্ট্যাটিস্টিক্স ডাটাবেস থেকে আনা
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// ইউজার মোট কয়টি ম্যাচ খেলেছে (Count)
$count_stmt = $pdo->prepare("SELECT COUNT(*) as total_played FROM joined_matches WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$stats = $count_stmt->fetch();

$current_page = 'profile';
require_once 'includes/header.php';
?>

<div class="p-4 pb-28 relative">
    
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-32 bg-indigo-600/30 blur-[60px] -z-10 rounded-full"></div>

    <div class="text-center mt-4">
        <div class="relative inline-block">
            <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 p-1 shadow-xl shadow-purple-500/20">
                <div class="w-full h-full bg-[#1a1c29] rounded-full flex items-center justify-center text-3xl font-black text-white">
                    <?= strtoupper(substr($user['name'], 0, 2)) ?>
                </div>
            </div>
            <div class="absolute bottom-1 right-1 bg-green-500 text-white w-6 h-6 rounded-full flex items-center justify-center border-2 border-[#0f111a] shadow-lg">
                <i class="fa-solid fa-check text-[10px]"></i>
            </div>
        </div>
        
        <h2 class="text-2xl font-black mt-3 tracking-wide text-white"><?= htmlspecialchars($user['name']) ?></h2>
        <p class="text-xs text-gray-400 font-medium mt-1 mb-4 flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
        </p>

        <div class="flex flex-wrap justify-center gap-3">
            <div onclick="copyText('uidText', 'UID')" class="flex items-center gap-2 bg-[#1a1c29] border border-gray-700 hover:border-indigo-500 px-4 py-2 rounded-xl cursor-pointer active:scale-95 transition-all group shadow-sm">
                <span class="text-[10px] text-gray-500 font-black uppercase tracking-widest">UID</span>
                <span id="uidText" class="text-sm font-bold text-gray-200"><?= htmlspecialchars($user['ff_uid']) ?></span>
                <i class="fa-regular fa-copy text-gray-500 group-hover:text-indigo-400 transition-colors ml-1"></i>
            </div>
            
            <div onclick="copyText('referText', 'Refer Code')" class="flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30 hover:border-indigo-500 px-4 py-2 rounded-xl cursor-pointer active:scale-95 transition-all group shadow-sm">
                <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest">REFER</span>
                <span id="referText" class="text-sm font-bold text-white"><?= htmlspecialchars($user['refer_code']) ?></span>
                <i class="fa-regular fa-copy text-indigo-400 group-hover:text-white transition-colors ml-1"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-[#2b2f42] to-[#1a1c29] border border-gray-700/50 rounded-3xl p-6 mt-8 relative overflow-hidden shadow-[0_15px_30px_rgba(0,0,0,0.4)]">
        
        <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
        <div class="absolute right-6 top-6 opacity-30">
            <i class="fa-solid fa-wifi text-2xl rotate-90 text-gray-300"></i>
        </div>
        <i class="fa-brands fa-cc-visa text-gray-500/30 text-8xl absolute -bottom-4 -right-4"></i>

        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-6 bg-yellow-500/80 rounded-md flex items-center justify-center border border-yellow-400/50 opacity-80">
                    <div class="w-6 h-4 border border-yellow-600 rounded-sm"></div>
                </div>
                <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">KheloFreeFire Wallet</span>
            </div>

            <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mb-1">Total Balance</p>
            <h1 class="text-4xl font-black text-white mb-6 flex items-end gap-1.5 drop-shadow-md">
                <span class="text-green-400 text-2xl mb-1">৳</span><?= $user['balance'] ?>
            </h1>
            
            <div class="flex items-center justify-between border-t border-gray-700/50 pt-4 mt-2">
                <div>
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Deposit</p>
                    <p class="font-black text-sm text-gray-200 mt-0.5">৳ <?= $user['deposit_balance'] ?></p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Winning</p>
                    <p class="font-black text-sm text-green-400 mt-0.5">৳ <?= $user['winning_balance'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-6">
        <a href="add_money.php" class="bg-[#1a1c29] border border-green-500/20 rounded-2xl p-4 flex flex-col items-center justify-center gap-3 shadow-lg hover:bg-gray-800 active:scale-95 transition-all group">
            <div class="w-12 h-12 bg-green-500/10 text-green-400 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <span class="font-black text-[13px] text-gray-200 uppercase tracking-wide">Add Money</span>
        </a>

        <a href="withdraw.php" class="bg-[#1a1c29] border border-pink-500/20 rounded-2xl p-4 flex flex-col items-center justify-center gap-3 shadow-lg hover:bg-gray-800 active:scale-95 transition-all group">
            <div class="w-12 h-12 bg-pink-500/10 text-pink-400 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <span class="font-black text-[13px] text-gray-200 uppercase tracking-wide">Withdraw</span>
        </a>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4">
        <div class="bg-[#1a1c29] border border-gray-700/60 rounded-2xl p-4 flex items-center gap-3 shadow-lg">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 text-lg border border-indigo-500/20">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <div>
                <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Matches Played</p>
                <p class="font-black text-lg text-white leading-tight"><?= $stats['total_played'] ?></p>
            </div>
        </div>
        
        <div class="bg-[#1a1c29] border border-gray-700/60 rounded-2xl p-4 flex items-center gap-3 shadow-lg">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 text-lg border border-yellow-500/20">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <div>
                <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Total Won</p>
                <p class="font-black text-lg text-yellow-500 leading-tight">৳ <?= $user['winning_balance'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="mt-8 mb-4">
        <a href="logout.php" onclick="return confirm('আপনি কি লগআউট করতে চান?')" class="w-full bg-red-500/10 border border-red-500/30 text-red-500 rounded-2xl py-4 flex items-center justify-center gap-2 hover:bg-red-500 hover:text-white active:scale-95 transition-all shadow-lg font-black uppercase tracking-widest text-[13px]">
            <i class="fa-solid fa-right-from-bracket text-lg"></i> Secure Logout
        </a>
    </div>

</div>

<div id="snackbar" class="fixed bottom-24 left-1/2 -translate-x-1/2 bg-gray-800 text-white px-6 py-3 rounded-full text-sm font-bold shadow-2xl transition-all duration-300 transform translate-y-20 opacity-0 z-[100] whitespace-nowrap border border-gray-700 flex items-center gap-2">
    <i class="fa-solid fa-circle-check text-green-400"></i> <span id="snackbarText">Copied!</span>
</div>

<script>
// Dynamic Copy Text Function with Modern Snackbar
function copyText(elementId, type) {
    var text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text);
    
    var snackbar = document.getElementById("snackbar");
    document.getElementById("snackbarText").innerText = type + " Copied!";
    
    // Show snackbar
    snackbar.classList.remove("translate-y-20", "opacity-0");
    snackbar.classList.add("translate-y-0", "opacity-100");
    
    // Hide after 3 seconds
    setTimeout(function(){ 
        snackbar.classList.remove("translate-y-0", "opacity-100");
        snackbar.classList.add("translate-y-20", "opacity-0");
    }, 3000);
}
</script>

<?php require_once 'includes/footer.php'; ?>