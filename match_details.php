<?php
session_start();
require_once 'includes/db.php';

// অটো লগইন চেক
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = $_SESSION['user_id'];
$message = '';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$match_id = intval($_GET['id']);

// ==========================================
// ⚙️ BACKEND: JOIN MATCH LOGIC
// ==========================================
if (isset($_POST['join_match'])) {
    $match_data = $pdo->prepare("SELECT entry_fee, total_slots, joined FROM matches WHERE id = ?");
    $match_data->execute([$match_id]);
    $match = $match_data->fetch();

    $user_data = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $user_data->execute([$user_id]);
    $user = $user_data->fetch();

    $check_exist = $pdo->prepare("SELECT id FROM joined_matches WHERE user_id = ? AND match_id = ?");
    $check_exist->execute([$user_id, $match_id]);

    if ($check_exist->rowCount() > 0) {
        $message = '<div class="bg-blue-600/20 border border-blue-600 text-blue-400 p-3 rounded-xl text-sm text-center mb-4 font-bold">You have already joined this match!</div>';
    } elseif ($match['joined'] >= $match['total_slots']) {
        $message = '<div class="bg-red-600/20 border border-red-600 text-red-400 p-3 rounded-xl text-sm text-center mb-4 font-bold">Match is full!</div>';
    } elseif ($user['balance'] < $match['entry_fee']) {
        $message = '<div class="bg-red-600/20 border border-red-600 text-red-400 p-3 rounded-xl text-sm text-center mb-4 font-bold">Insufficient balance! <a href="add_money.php" class="underline ml-1">Add Money</a></div>';
    } else {
        try {
            $pdo->beginTransaction();
            $new_balance = $user['balance'] - $match['entry_fee'];
            $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_balance, $user_id]);
            $pdo->prepare("INSERT INTO joined_matches (user_id, match_id) VALUES (?, ?)")->execute([$user_id, $match_id]);
            $pdo->prepare("UPDATE matches SET joined = joined + 1 WHERE id = ?")->execute([$match_id]);
            $pdo->commit();
            $message = '<div class="bg-green-600/20 border border-green-600 text-green-400 p-3 rounded-xl text-sm text-center mb-4 font-bold">Successfully joined the match!</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="bg-red-600/20 border border-red-600 text-red-400 p-3 rounded-xl text-sm text-center mb-4 font-bold">Something went wrong!</div>';
        }
    }
}

// ==========================================
// 🎨 FRONTEND: FETCH DATA (With Category Logo)
// ==========================================
// ম্যাচ এবং তার ক্যাটাগরির লোগো তথ্য জয়েন কুয়েরি দিয়ে আনা হচ্ছে
$stmt = $pdo->prepare("SELECT m.*, c.image_type, c.image_path FROM matches m 
                       LEFT JOIN categories c ON m.category = c.name 
                       WHERE m.id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch();

if (!$match) {
    die("<div class='p-10 text-center text-white font-bold'>Match not found!</div>");
}

// ক্যাটাগরি লোগো নির্ধারণ (URL নাকি Upload করা ফাইল)
$cat_logo_src = 'assets/images/br.jpg'; // ডিফল্ট ছবি
if (!empty($match['image_path'])) {
    $cat_logo_src = ($match['image_type'] == 'url') ? $match['image_path'] : 'assets/images/' . $match['image_path'];
}

$check_joined = $pdo->prepare("SELECT id FROM joined_matches WHERE user_id = ? AND match_id = ?");
$check_joined->execute([$user_id, $match_id]);
$has_joined = $check_joined->rowCount() > 0;

$progress = ($match['joined'] / $match['total_slots']) * 100;

$current_page = 'home';
require_once 'includes/header.php';
?>

<div class="p-4 pb-32">
    <div class="flex items-center mb-6">
        <a href="javascript:history.back()" class="text-white mr-4 bg-[#1a1c29] border border-gray-700 p-2 rounded-full w-10 h-10 flex items-center justify-center active:scale-90 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-extrabold tracking-wide text-white uppercase">Match Details</h2>
    </div>

    <?= $message; ?>

    <div class="bg-[#1a1c29] rounded-3xl overflow-hidden border border-gray-800 shadow-2xl mb-6 relative">
        <div class="absolute top-4 right-4 bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-full z-10 shadow-lg">
            #<?= $match['id'] ?>
        </div>
        
        <div class="aspect-video w-full overflow-hidden relative bg-gray-900">
            <img src="<?= htmlspecialchars($cat_logo_src) ?>" class="w-full h-full object-cover opacity-60" onerror="this.src='https://via.placeholder.com/400x225/1a1c29/FFFFFF?text=<?= urlencode($match['category']) ?>'">
            <div class="absolute inset-0 bg-gradient-to-t from-[#1a1c29] via-transparent to-transparent"></div>
        </div>
        
        <div class="p-6 -mt-12 relative z-10">
            <h3 class="font-black text-2xl text-white mb-1 drop-shadow-md"><?= htmlspecialchars($match['title']) ?></h3>
            <p class="text-xs text-orange-400 font-bold mb-6 flex items-center gap-2">
                <i class="fa-regular fa-calendar-check"></i> <?= date('d M, Y - h:i A', strtotime($match['start_time'])) ?>
            </p>

            <div class="grid grid-cols-3 gap-3 text-center mb-6">
                <div class="bg-[#2d324a]/50 backdrop-blur-sm p-3 rounded-2xl border border-gray-700">
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">WIN PRIZE</p>
                    <p class="font-black text-lg text-white">৳<?= $match['win_prize'] ?></p>
                </div>
                <div class="bg-[#2d324a]/50 backdrop-blur-sm p-3 rounded-2xl border border-gray-700">
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">PER KILL</p>
                    <p class="font-black text-lg text-white">৳<?= $match['per_kill'] ?></p>
                </div>
                <div class="bg-[#2d324a]/50 backdrop-blur-sm p-3 rounded-2xl border border-gray-700">
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">ENTRY FEE</p>
                    <p class="font-black text-lg text-white">৳<?= $match['entry_fee'] ?></p>
                </div>
            </div>

            <div class="mb-2 relative w-full bg-gray-800 h-3 rounded-full overflow-hidden border border-gray-700">
                <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-orange-600 to-yellow-500 rounded-full transition-all duration-1000" style="width: <?= $progress ?>%;"></div>
            </div>
            <div class="flex justify-between items-center text-[11px] font-black text-gray-400">
                <span class="flex items-center gap-1"><i class="fa-solid fa-users text-orange-500"></i> ONLY <?= $match['total_slots'] - $match['joined'] ?> SLOTS LEFT</span>
                <span class="text-white bg-gray-800 px-2 py-0.5 rounded-md"><?= $match['joined'] ?> / <?= $match['total_slots'] ?></span>
            </div>
        </div>
    </div>

    <div class="bg-[#1a1c29] rounded-2xl border border-gray-800 shadow-xl p-5 mb-6">
        <h3 class="font-black text-sm mb-4 border-b border-gray-800 pb-3 flex items-center gap-2">
            <i class="fa-solid fa-award text-yellow-500 text-lg"></i> PRIZE POOL DETAILS
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center bg-gray-800/40 p-3 rounded-xl border border-gray-800">
                <span class="text-sm font-bold text-gray-300">Winner (Booyah!)</span> 
                <span class="font-black text-green-400">৳<?= $match['win_prize'] ?></span>
            </div>
            <div class="flex justify-between items-center bg-gray-800/40 p-3 rounded-xl border border-gray-800">
                <span class="text-sm font-bold text-gray-300">Every Single Kill</span> 
                <span class="font-black text-indigo-400">৳<?= $match['per_kill'] ?></span>
            </div>
        </div>
    </div>

    <div class="bg-[#1a1c29] rounded-2xl border border-gray-800 shadow-xl p-5 mb-6">
        <h3 class="font-black text-sm mb-4 border-b border-gray-800 pb-3 text-red-500 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-lg"></i> GAMEPLAY RULES
        </h3>
        <ul class="text-xs text-gray-400 space-y-3 pl-1">
            <li class="flex gap-2"><span class="text-indigo-500 font-bold">1.</span> ম্যাচ শুরুর অন্তত ১০ মিনিট আগে রুমে জয়েন করতে হবে।</li>
            <li class="flex gap-2"><span class="text-indigo-500 font-bold">2.</span> কোনো ধরনের হ্যাক বা গ্লিচ ব্যবহার করলে সাথে সাথে অ্যাকাউন্ট ব্যান করা হবে।</li>
            <li class="flex gap-2"><span class="text-indigo-500 font-bold">3.</span> টিম আপ করা সম্পূর্ণ নিষেধ। প্রমাণ পেলে প্রাইজ দেওয়া হবে না।</li>
            <li class="flex gap-2"><span class="text-indigo-500 font-bold">4.</span> ম্যাচ শেষে আপনার স্ক্রিনশট এবং কিল প্রমাণ দিতে হতে পারে।</li>
        </ul>
    </div>

    <div class="fixed bottom-16 left-0 w-full p-4 bg-[#1a1c29]/80 backdrop-blur-md border-t border-gray-800 shadow-2xl z-40">
        <?php if ($has_joined): ?>
            <button class="w-full bg-gray-700 text-gray-400 font-black py-4 rounded-2xl cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check text-green-500"></i> YOU ALREADY JOINED
            </button>
        <?php elseif ($match['joined'] >= $match['total_slots']): ?>
            <button class="w-full bg-red-600/30 text-red-500 border border-red-600/50 font-black py-4 rounded-2xl cursor-not-allowed uppercase">
                Match Slot is Full
            </button>
        <?php else: ?>
            <form method="POST">
                <button type="submit" name="join_match" onclick="return confirm('Are you sure you want to join? ৳<?= $match['entry_fee'] ?> will be deducted from your wallet.')" class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg shadow-indigo-500/40 uppercase tracking-widest">
                    Join Match Now (৳<?= $match['entry_fee'] ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>