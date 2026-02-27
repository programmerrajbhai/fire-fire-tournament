<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = $_SESSION['user_id'];
$message = '';

// ইউজারের বর্তমান উইনিং ব্যালেন্স চেক
$stmt = $pdo->prepare("SELECT winning_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (isset($_POST['withdraw'])) {
    $method = $_POST['method'];
    $account_no = htmlspecialchars($_POST['account_no']);
    $amount = intval($_POST['amount']);

    if ($amount < 50) {
        $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Minimum withdrawal amount is ৳50.</div>';
    } elseif ($amount > $user['winning_balance']) {
        $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Insufficient Winning Balance!</div>';
    } else {
        try {
            $pdo->beginTransaction();
            
            // উইনিং ব্যালেন্স থেকে টাকা কাটা
            $new_winning_balance = $user['winning_balance'] - $amount;
            $pdo->prepare("UPDATE users SET winning_balance = ? WHERE id = ?")->execute([$new_winning_balance, $user_id]);
            
            // ট্রানজেকশন রেকর্ড রাখা (trx_id এর জায়গায় আমরা account_no রাখছি যাতে অ্যাডমিন এই নাম্বারে টাকা পাঠাতে পারে)
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, trx_id, status) VALUES (?, 'withdraw', ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $method, $amount, $account_no]);
            
            $pdo->commit();
            $message = '<div class="bg-green-600 text-white p-3 rounded-lg text-sm text-center mb-4">Withdrawal request sent successfully! Admin will send money soon.</div>';
            
            // ব্যালেন্স আপডেট করার জন্য পেজ রিফ্রেশের ভ্যালু আপডেট করা
            $user['winning_balance'] = $new_winning_balance;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Something went wrong! Try again later.</div>';
        }
    }
}

$current_page = 'profile'; 
require_once 'includes/header.php';
?>

<div class="p-4 pb-24">
    <div class="flex items-center mb-6">
        <a href="profile.php" class="text-white mr-4 bg-gray-800 p-2 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-bold tracking-wide">Withdraw Money</h2>
    </div>

    <?= $message; ?>

    <div class="bg-gradient-to-r from-green-600 to-teal-500 rounded-2xl p-6 mb-6 text-center shadow-lg text-white">
        <p class="text-xs font-bold uppercase tracking-widest opacity-80 mb-1">Withdrawable Balance</p>
        <h1 class="text-4xl font-bold">৳ <?= $user['winning_balance'] ?></h1>
        <p class="text-[10px] opacity-80 mt-2">* You can only withdraw from your winnings.</p>
    </div>

    <form action="" method="POST" class="bg-[#1a1c29] border border-gray-700 rounded-2xl p-5 shadow-lg">
        
        <div class="mb-4">
            <label class="block text-gray-400 text-xs font-bold mb-2">Select Method</label>
            <div class="flex gap-4">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="method" value="bkash" class="peer hidden" checked>
                    <div class="bg-gray-800 border border-gray-700 peer-checked:border-pink-500 peer-checked:bg-pink-500/10 rounded-xl p-3 text-center transition">
                        <img src="https://seeklogo.com/images/B/bkash-logo-0C1572FBB4-seeklogo.com.png" class="h-6 mx-auto mb-1">
                        <span class="text-xs font-bold text-white">bKash</span>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="method" value="nagad" class="peer hidden">
                    <div class="bg-gray-800 border border-gray-700 peer-checked:border-orange-500 peer-checked:bg-orange-500/10 rounded-xl p-3 text-center transition">
                        <img src="https://download.logo.wine/logo/Nagad/Nagad-Logo.wine.png" class="h-6 mx-auto mb-1">
                        <span class="text-xs font-bold text-white">Nagad</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-400 text-xs font-bold mb-2">Account Number (Personal)</label>
            <input type="text" name="account_no" placeholder="01XXXXXXXXX" required pattern="[0-9]{11}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 outline-none focus:border-indigo-500">
        </div>

        <div class="mb-6">
            <label class="block text-gray-400 text-xs font-bold mb-2">Amount to Withdraw (৳)</label>
            <input type="number" name="amount" placeholder="Min. 50" min="50" max="<?= $user['winning_balance'] ?>" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 outline-none focus:border-indigo-500">
            <p class="text-[10px] text-gray-500 mt-1">Minimum withdrawal is ৳50</p>
        </div>

        <button type="submit" name="withdraw" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-700 active:scale-95 transition">
            Submit Request
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>