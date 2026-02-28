<?php
session_start();
require_once 'includes/db.php';

// 🚨 রিয়েল লগইন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: result.php");
    exit;
}
$match_id = intval($_GET['id']);

// ==========================================
// 🎨 FETCH MATCH & CATEGORY DATA
// ==========================================
$stmt = $pdo->prepare("SELECT m.*, c.image_type, c.image_path FROM matches m 
                       LEFT JOIN categories c ON m.category = c.name 
                       WHERE m.id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch();

if (!$match) {
    die("<div class='p-10 text-center text-white font-bold'>Match not found!</div>");
}

// Category Image Source
$img_src = 'assets/images/br.jpg'; 
if(!empty($match['image_path'])) {
    $img_src = ($match['image_type'] == 'url') ? $match['image_path'] : 'assets/images/' . $match['image_path'];
}

// ==========================================
// 🏆 FETCH LEADERBOARD DATA
// ==========================================
$leaderboard = [];
try {
    // যারা সবচেয়ে বেশি টাকা জিতেছে এবং কিল করেছে, তাদের উপরে দেখাবে
    $l_stmt = $pdo->prepare("
        SELECT u.name, u.ff_uid, jm.kills, jm.prize_won 
        FROM joined_matches jm 
        JOIN users u ON jm.user_id = u.id 
        WHERE jm.match_id = ? 
        ORDER BY jm.prize_won DESC, jm.kills DESC, jm.id ASC
    ");
    $l_stmt->execute([$match_id]);
    $leaderboard = $l_stmt->fetchAll();
} catch (PDOException $e) {
    // যদি কেউ SQL কলাম রান করতে ভুলে যায়, তার জন্য ফলব্যাক
    $l_stmt = $pdo->prepare("SELECT u.name, u.ff_uid, 0 as kills, 0 as prize_won FROM joined_matches jm JOIN users u ON jm.user_id = u.id WHERE jm.match_id = ?");
    $l_stmt->execute([$match_id]);
    $leaderboard = $l_stmt->fetchAll();
}

$current_page = 'result';
require_once 'includes/header.php';
?>

<div class="p-4 pb-28 relative">
    
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-72 h-40 bg-indigo-600/20 blur-[70px] -z-10 rounded-full"></div>

    <div class="flex items-center mb-6">
        <a href="javascript:history.back()" class="text-white mr-4 bg-[#1a1c29] border border-gray-700 p-2.5 rounded-full w-10 h-10 flex items-center justify-center active:scale-90 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-black tracking-wide text-white uppercase">Match Result</h2>
    </div>

    <div class="bg-[#1a1c29] rounded-3xl overflow-hidden border border-gray-800 shadow-2xl mb-8 relative">
        <div class="aspect-[21/9] w-full overflow-hidden relative bg-gray-900">
            <img src="<?= htmlspecialchars($img_src) ?>" class="w-full h-full object-cover opacity-40 blur-[2px]">
            <div class="absolute inset-0 bg-gradient-to-t from-[#1a1c29] via-black/50 to-transparent"></div>
            
            <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-6 mt-4">
                <p class="text-indigo-400 text-[10px] font-black uppercase tracking-widest mb-1 shadow-black drop-shadow-md">Match #<?= $match['id'] ?></p>
                <h2 class="text-2xl font-black text-white uppercase tracking-wide drop-shadow-lg leading-tight mb-3"><?= htmlspecialchars($match['title']) ?></h2>
                <span class="bg-green-500/20 text-green-400 border border-green-500/30 text-[10px] font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-lg">
                    <i class="fa-solid fa-check-circle mr-1"></i> Completed
                </span>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4 px-1">
        <h3 class="font-black text-white text-lg uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-ranking-star text-yellow-500 text-xl"></i> Leaderboard
        </h3>
        <p class="text-xs text-gray-400 font-bold bg-[#1a1c29] px-3 py-1 rounded-lg border border-gray-800">
            Players: <?= count($leaderboard) ?>
        </p>
    </div>

    <div class="space-y-3">
        <?php if(count($leaderboard) > 0): ?>
            <?php foreach($leaderboard as $index => $player): 
                // Rank Styling Logic
                $rank = $index + 1;
                $rank_style = "bg-gray-800 border-gray-700 text-gray-400"; // Default
                $icon = "<span class='font-black text-sm'>#{$rank}</span>";

                if ($rank == 1) {
                    $rank_style = "bg-gradient-to-tr from-yellow-600 to-yellow-400 text-white shadow-[0_0_15px_rgba(234,179,8,0.4)] border-none";
                    $icon = "<i class='fa-solid fa-crown text-lg drop-shadow-md'></i>";
                } elseif ($rank == 2) {
                    $rank_style = "bg-gradient-to-tr from-gray-400 to-gray-300 text-white shadow-[0_0_15px_rgba(156,163,175,0.4)] border-none";
                    $icon = "<i class='fa-solid fa-medal text-lg drop-shadow-md'></i>";
                } elseif ($rank == 3) {
                    $rank_style = "bg-gradient-to-tr from-orange-600 to-orange-400 text-white shadow-[0_0_15px_rgba(249,115,22,0.4)] border-none";
                    $icon = "<i class='fa-solid fa-award text-lg drop-shadow-md'></i>";
                }
            ?>
            
            <div class="bg-[#1a1c29] border border-gray-800 rounded-2xl p-3 flex items-center gap-3 shadow-lg relative overflow-hidden group">
                
                <div class="w-12 h-12 rounded-xl flex items-center justify-center border shrink-0 <?= $rank_style ?>">
                    <?= $icon ?>
                </div>

                <div class="flex-1 pr-2">
                    <p class="font-bold text-[14px] text-white leading-tight mb-0.5"><?= htmlspecialchars($player['name']) ?></p>
                    <p class="text-[10px] text-gray-500 font-bold tracking-widest uppercase">UID: <?= htmlspecialchars($player['ff_uid']) ?></p>
                </div>

                <div class="flex items-center gap-3 text-right">
                    <div class="bg-gray-800/50 p-1.5 rounded-lg border border-gray-700/50 text-center min-w-[45px]">
                        <p class="text-[8px] text-gray-400 uppercase font-black">Kills</p>
                        <p class="font-black text-sm text-indigo-400"><?= $player['kills'] ?></p>
                    </div>
                    
                    <div class="bg-gray-800/50 p-1.5 rounded-lg border border-gray-700/50 text-center min-w-[55px]">
                        <p class="text-[8px] text-gray-400 uppercase font-black">Won</p>
                        <p class="font-black text-sm <?= $player['prize_won'] > 0 ? 'text-green-400' : 'text-gray-500' ?>">৳<?= $player['prize_won'] ?></p>
                    </div>
                </div>
                
            </div>
            
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-10">
                <i class="fa-solid fa-ghost text-4xl text-gray-600 mb-3"></i>
                <p class="text-sm font-bold text-gray-500 tracking-wide uppercase">No players joined this match.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>