<?php
session_start();
require_once 'includes/db.php';

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
        $message = '<div class="bg-blue-600 text-white p-3 rounded-lg text-sm text-center mb-4">You have already joined this match!</div>';
    } elseif ($match['joined'] >= $match['total_slots']) {
        $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Match is full!</div>';
    } elseif ($user['balance'] < $match['entry_fee']) {
        $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Insufficient balance! <a href="add_money.php" class="underline font-bold">Add Money</a></div>';
    } else {
        try {
            $pdo->beginTransaction();
            $new_balance = $user['balance'] - $match['entry_fee'];
            $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_balance, $user_id]);
            $pdo->prepare("INSERT INTO joined_matches (user_id, match_id) VALUES (?, ?)")->execute([$user_id, $match_id]);
            $pdo->prepare("UPDATE matches SET joined = joined + 1 WHERE id = ?")->execute([$match_id]);
            $pdo->commit();
            $message = '<div class="bg-green-600 text-white p-3 rounded-lg text-sm text-center mb-4">Successfully joined the match!</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Something went wrong!</div>';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch();

if (!$match) {
    die("<div class='p-10 text-center text-white'>Match not found!</div>");
}

$check_joined = $pdo->prepare("SELECT id FROM joined_matches WHERE user_id = ? AND match_id = ?");
$check_joined->execute([$user_id, $match_id]);
$has_joined = $check_joined->rowCount() > 0;

$progress = ($match['joined'] / $match['total_slots']) * 100;

$current_page = 'home';
require_once 'includes/header.php';
?>

<div class="p-4 pb-24">
    <div class="flex items-center mb-4">
        <a href="javascript:history.back()" class="text-white mr-4 bg-gray-800 p-2 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-bold tracking-wide">Match Details</h2>
    </div>

    <?= $message; ?>

    <div class="bg-[#1a1c29] rounded-2xl overflow-hidden border border-gray-700 shadow-lg mb-6">
        <img src="assets/images/br.jpg" class="w-full h-32 object-cover opacity-80" onerror="this.src='https://via.placeholder.com/400x150/2d3748/FFFFFF'">
        
        <div class="p-5">
            <h3 class="font-bold text-xl text-white mb-1"><?= htmlspecialchars($match['title']) ?></h3>
            <p class="text-xs text-orange-400 font-semibold mb-4"><i class="fa-regular fa-clock"></i> <?= date('d M, Y - h:i A', strtotime($match['start_time'])) ?></p>

            <div class="grid grid-cols-3 gap-2 text-center mb-5">
                <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                    <p class="text-[9px] text-gray-400">WIN PRIZE</p>
                    <p class="font-bold text-md text-white">৳ <?= $match['win_prize'] ?></p>
                </div>
                <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                    <p class="text-[9px] text-gray-400">PER KILL</p>
                    <p class="font-bold text-md text-white">৳ <?= $match['per_kill'] ?></p>
                </div>
                <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                    <p class="text-[9px] text-gray-400">ENTRY FEE</p>
                    <p class="font-bold text-md text-white">৳ <?= $match['entry_fee'] ?></p>
                </div>
            </div>

            <div class="mb-2 relative w-full bg-gray-800 h-2.5 rounded-full overflow-hidden">
                <div class="absolute top-0 left-0 h-full bg-[#f97316] rounded-full" style="width: <?= $progress ?>%;"></div>
            </div>
            <div class="flex justify-between items-center text-[11px] font-semibold text-gray-400">
                <span>Only <?= $match['total_slots'] - $match['joined'] ?> spots left</span>
                <span class="text-white"><?= $match['joined'] ?>/<?= $match['total_slots'] ?></span>
            </div>
        </div>
    </div>

    <div class="bg-[#1a1c29] rounded-2xl border border-gray-700 shadow-lg p-5 mb-6">
        <h3 class="font-bold text-md mb-3 border-b border-gray-700 pb-2"><i class="fa-solid fa-trophy text-yellow-500 mr-2"></i> Prize Pool Details</h3>
        <ul class="text-sm text-gray-300 space-y-2">
            <li class="flex justify-between"><span>Winner (Booyah)</span> <span class="font-bold text-white">৳ <?= $match['win_prize'] ?></span></li>
            <li class="flex justify-between"><span>Per Kill</span> <span class="font-bold text-white">৳ <?= $match['per_kill'] ?></span></li>
        </ul>
    </div>

    <div class="bg-[#1a1c29] rounded-2xl border border-gray-700 shadow-lg p-5 mb-6">
        <h3 class="font-bold text-md mb-3 border-b border-gray-700 pb-2 text-red-400"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Game Rules</h3>
        <ul class="text-xs text-gray-400 space-y-2 list-disc pl-4">
            <li>ম্যাচ শুরুর অন্তত ১০ মিনিট আগে রুমে জয়েন করতে হবে।</li>
            <li>কোনো ধরনের হ্যাক বা গ্লিচ ব্যবহার করলে সাথে সাথে ব্যান করা হবে।</li>
            <li>টিম আপ করা সম্পূর্ণ নিষেধ। প্রমাণ পেলে প্রাইজ দেওয়া হবে না।</li>
        </ul>
    </div>

    <div class="fixed bottom-16 left-0 w-full p-4 bg-[#1a1c29] border-t border-gray-700 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.5)] z-40">
        <?php if ($has_joined): ?>
            <button class="w-full bg-gray-600 text-white font-bold py-3.5 rounded-xl cursor-not-allowed">
                <i class="fa-solid fa-circle-check mr-2"></i> Already Joined
            </button>
        <?php elseif ($match['joined'] >= $match['total_slots']): ?>
            <button class="w-full bg-red-600 text-white font-bold py-3.5 rounded-xl cursor-not-allowed">
                Match Full
            </button>
        <?php else: ?>
            <form method="POST">
                <button type="submit" name="join_match" onclick="return confirm('Are you sure? ৳<?= $match['entry_fee'] ?> will be deducted.')" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-700 active:scale-95 transition">
                    Join Match Now (৳ <?= $match['entry_fee'] ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>