<?php
session_start();
require_once 'includes/db.php';

// 🚨 রিয়েল লগইন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// ডাটাবেস থেকে ডায়নামিক ক্যাটাগরিগুলো আনা
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// ডিফল্ট ক্যাটাগরি নির্ধারণ (যদি URL এ কোনো ক্যাটাগরি না থাকে, তাহলে প্রথমটি দেখাবে)
$default_cat = (count($categories) > 0) ? $categories[0]['name'] : 'BR MATCH';
$category = isset($_GET['cat']) ? $_GET['cat'] : $default_cat;

// নির্দিষ্ট ক্যাটাগরির Completed ম্যাচগুলো এবং তাদের ইমেজ আনা
$stmt = $pdo->prepare("SELECT m.*, c.image_type, c.image_path FROM matches m 
                       LEFT JOIN categories c ON m.category = c.name 
                       WHERE m.category = ? AND m.status = 'completed' 
                       ORDER BY m.start_time DESC");
$stmt->execute([$category]);
$matches = $stmt->fetchAll();

$current_page = 'result';
require_once 'includes/header.php';
?>

<div class="p-4 pb-24">
    <h2 class="text-2xl font-black mb-6 tracking-wide uppercase text-white drop-shadow-md">Match Results</h2>

    <div class="flex overflow-x-auto gap-3 pb-2 mb-6 custom-scrollbar">
        <?php foreach($categories as $cat): ?>
            <a href="?cat=<?= urlencode($cat['name']) ?>" class="whitespace-nowrap px-5 py-2.5 rounded-xl text-[12px] font-black tracking-widest uppercase transition-all duration-300 border <?= $category == $cat['name'] ? 'bg-indigo-600 text-white border-indigo-500 shadow-lg shadow-indigo-500/30' : 'bg-[#1a1c29] text-gray-500 border-gray-800 hover:text-gray-300 hover:border-gray-600' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
        
        <?php if(count($categories) == 0): ?>
            <p class="text-xs text-gray-500">No categories found.</p>
        <?php endif; ?>
    </div>

    <?php if (count($matches) > 0): ?>
        <div class="space-y-6">
            <?php foreach($matches as $match): 
                // ক্যাটাগরি ইমেজ সোর্স লজিক
                $img_src = 'assets/images/br.jpg'; 
                if(!empty($match['image_path'])) {
                    $img_src = ($match['image_type'] == 'url') ? $match['image_path'] : 'assets/images/' . $match['image_path'];
                }
            ?>
            <div class="bg-[#1a1c29] rounded-3xl overflow-hidden border border-gray-800 shadow-xl relative">
                
                <div class="absolute top-0 right-0 bg-green-600/90 backdrop-blur-sm text-white text-[10px] font-black px-4 py-1.5 rounded-bl-2xl z-10 shadow-md uppercase tracking-widest border-b border-l border-green-500">
                    Completed
                </div>

                <div class="p-5">
                    
                    <div class="flex items-center gap-4 mb-6 mt-2">
                        <div class="w-14 h-14 rounded-xl overflow-hidden border border-gray-700 bg-gray-800 shrink-0 shadow-inner">
                            <img src="<?= htmlspecialchars($img_src) ?>" class="w-full h-full object-cover p-0.5 rounded-xl" onerror="this.src='https://via.placeholder.com/150/1a1c29/FFFFFF?text=G'">
                        </div>
                        <div class="flex-1 pr-10">
                            <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest mb-0.5">Match #<?= $match['id'] ?></p>
                            <h3 class="font-bold text-[15px] text-white leading-tight mb-1"><?= htmlspecialchars($match['title']) ?></h3>
                            <p class="text-[11px] text-gray-400 font-semibold flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar-check text-green-500"></i> <?= date('d M, Y - h:i A', strtotime($match['start_time'])) ?>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-center mb-2">
                        <div class="bg-[#2d324a]/30 p-2.5 rounded-2xl border border-gray-800">
                            <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-1">Win Prize</p>
                            <p class="font-black text-lg text-white">৳<?= $match['win_prize'] ?></p>
                        </div>
                        <div class="bg-[#2d324a]/30 p-2.5 rounded-2xl border border-gray-800">
                            <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-1">Per Kill</p>
                            <p class="font-black text-lg text-white">৳<?= $match['per_kill'] ?></p>
                        </div>
                        <div class="bg-[#2d324a]/30 p-2.5 rounded-2xl border border-gray-800">
                            <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-1">Entry Fee</p>
                            <p class="font-black text-lg text-white">৳<?= $match['entry_fee'] ?></p>
                        </div>
                    </div>

                </div>

                <a href="match_leaderboard.php?id=<?= $match['id'] ?>" class="block text-center bg-indigo-600/10 border-t border-indigo-500/20 text-indigo-400 font-black text-xs py-4 hover:bg-indigo-600 hover:text-white transition-all uppercase tracking-widest">
                    View Leaderboard & Results <i class="fa-solid fa-chevron-right ml-1"></i>
                </a>

            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center text-gray-500 mt-24">
            <div class="w-20 h-20 bg-[#1a1c29] border border-gray-800 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fa-solid fa-trophy text-3xl opacity-50"></i>
            </div>
            <p class="font-black text-sm tracking-widest uppercase text-gray-400">No Results Yet</p>
            <p class="text-xs mt-2">এই ক্যাটাগরিতে এখনও কোনো ম্যাচ শেষ হয়নি।</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Custom Scrollbar for the horizontal tabs */
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
</style>

<?php require_once 'includes/footer.php'; ?>