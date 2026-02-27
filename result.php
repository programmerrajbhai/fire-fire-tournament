<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$category = isset($_GET['cat']) ? $_GET['cat'] : 'BR MATCH';

// শুধুমাত্র completed ম্যাচগুলো ডাটাবেস থেকে আনা
$stmt = $pdo->prepare("SELECT * FROM matches WHERE category = ? AND status = 'completed' ORDER BY start_time DESC");
$stmt->execute([$category]);
$matches = $stmt->fetchAll();

$current_page = 'result';
require_once 'includes/header.php';
?>

<div class="p-4 pb-24">
    <h2 class="text-xl font-bold mb-4">Result</h2>

    <div class="flex overflow-x-auto gap-3 pb-2 mb-6 scrollbar-hide">
        <a href="?cat=BR MATCH" class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-bold border <?= $category == 'BR MATCH' ? 'bg-white text-black border-white' : 'bg-[#1a1c29] text-gray-400 border-gray-700 hover:text-white' ?>">
            <i class="fa-solid fa-fire mr-1"></i> BR MATCH
        </a>
        <a href="?cat=CLASH SQUAD" class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-bold border <?= $category == 'CLASH SQUAD' ? 'bg-white text-black border-white' : 'bg-[#1a1c29] text-gray-400 border-gray-700 hover:text-white' ?>">
            <i class="fa-solid fa-sliders mr-1"></i> Clash Squad Matches
        </a>
        <a href="?cat=LONE WOLF" class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-bold border <?= $category == 'LONE WOLF' ? 'bg-white text-black border-white' : 'bg-[#1a1c29] text-gray-400 border-gray-700 hover:text-white' ?>">
            <i class="fa-regular fa-user mr-1"></i> Lone Wolf
        </a>
    </div>

    <?php if (count($matches) > 0): ?>
        <?php foreach($matches as $match): ?>
        <div class="bg-[#1a1c29] rounded-2xl overflow-hidden border border-gray-700 mb-6 shadow-xl relative">
            
            <div class="absolute top-0 right-0 bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">
                #<?= $match['id'] ?>
            </div>

            <div class="p-4">
                <div class="flex items-center gap-3 mb-5 mt-2">
                    <img src="assets/images/br.jpg" class="w-14 h-14 rounded-xl object-cover border border-gray-600" onerror="this.src='https://via.placeholder.com/150'">
                    <div>
                        <h3 class="font-bold text-[15px]"><?= htmlspecialchars($match['title']) ?></h3>
                        <p class="text-[11px] text-orange-400 font-semibold mt-1"><?= date('Y-m-d h:i A', strtotime($match['start_time'])) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center mb-5">
                    <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                        <p class="text-[9px] text-gray-400">WIN PRIZE</p>
                        <p class="font-bold text-lg text-white"><?= $match['win_prize'] ?></p>
                        <p class="text-[9px] text-gray-400 uppercase mt-1"><?= $match['type'] ?></p>
                    </div>
                    <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                        <p class="text-[9px] text-gray-400">PER KILL</p>
                        <p class="font-bold text-lg text-white"><?= $match['per_kill'] ?></p>
                        <p class="text-[9px] text-gray-400 uppercase mt-1"><?= $match['map'] ?></p>
                    </div>
                    <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                        <p class="text-[9px] text-gray-400">ENTRY FEE</p>
                        <p class="font-bold text-lg text-white"><?= $match['entry_fee'] ?></p>
                        <p class="text-[9px] text-gray-400 uppercase mt-1">TPP</p>
                    </div>
                </div>

                <a href="match_leaderboard.php?id=<?= $match['id'] ?>" class="block text-center text-indigo-400 font-bold text-sm py-2 hover:text-indigo-300 transition">
                    View Result <i class="fa-solid fa-chevron-right text-xs ml-1"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-gray-500 mt-20">
            <i class="fa-solid fa-trophy text-4xl mb-3 opacity-50"></i>
            <p>No results published yet for this mode.</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Hide scrollbar for the horizontal tabs */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<?php require_once 'includes/footer.php'; ?>