<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = $_SESSION['user_id'];
$message = '';

// ফর্ম সাবমিট হলে ডাটাবেসে ইনসার্ট হবে
if (isset($_POST['add_money'])) {
    $method = $_POST['method'];
    $amount = intval($_POST['amount']);
    $trx_id = htmlspecialchars($_POST['trx_id']);

    if ($amount >= 10 && !empty($trx_id)) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, trx_id) VALUES (?, 'add_money', ?, ?, ?)");
        if ($stmt->execute([$user_id, $method, $amount, $trx_id])) {
            $message = '<div class="bg-green-600 text-white p-3 rounded-lg text-sm text-center mb-4">Request sent! Admin will verify and add balance soon.</div>';
        } else {
            $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Failed to send request!</div>';
        }
    } else {
        $message = '<div class="bg-red-600 text-white p-3 rounded-lg text-sm text-center mb-4">Minimum add money is 10 Taka and TrxID is required!</div>';
    }
}

$current_page = 'profile'; // প্রোফাইল অ্যাক্টিভ দেখাবে
require_once 'includes/header.php';
?>

<div class="p-4 pb-24">
    <div class="flex items-center mb-6">
        <a href="profile.php" class="text-white mr-4 bg-gray-800 p-2 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-bold tracking-wide">Add Money</h2>
    </div>

    <?= $message; ?>

    <div class="bg-[#1a1c29] border border-gray-700 rounded-2xl p-5 mb-6 text-center">
        <h3 class="text-gray-400 text-sm font-bold mb-3 uppercase tracking-widest">Send Money To</h3>
        
        <div class="flex justify-center gap-4 mb-3">
            <div class="bg-gray-800 p-3 rounded-xl border border-gray-700 text-center w-full">
                <img src="https://seeklogo.com/images/B/bkash-logo-0C1572FBB4-seeklogo.com.png" class="h-6 mx-auto mb-2" alt="bKash">
                <p class="font-bold text-sm" id="bkashNum">017XXXXXXXX</p>
                <button onclick="copyNum('bkashNum')" class="text-indigo-400 text-xs mt-1">Copy</button>
            </div>
            <div class="bg-gray-800 p-3 rounded-xl border border-gray-700 text-center w-full">
                <img src="https://download.logo.wine/logo/Nagad/Nagad-Logo.wine.png" class="h-6 mx-auto mb-2" alt="Nagad">
                <p class="font-bold text-sm" id="nagadNum">019XXXXXXXX</p>
                <button onclick="copyNum('nagadNum')" class="text-indigo-400 text-xs mt-1">Copy</button>
            </div>
        </div>
        <p class="text-[10px] text-orange-400">* Minimum send money amount is ৳10.</p>
    </div>

    <form action="" method="POST" class="bg-[#1a1c29] border border-gray-700 rounded-2xl p-5">
        
        <div class="mb-4">
            <label class="block text-gray-400 text-xs font-bold mb-2">Select Method</label>
            <select name="method" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 outline-none focus:border-indigo-500">
                <option value="bkash">bKash (Send Money)</option>
                <option value="nagad">Nagad (Send Money)</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-400 text-xs font-bold mb-2">Amount (৳)</label>
            <input type="number" name="amount" placeholder="e.g. 50" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 outline-none focus:border-indigo-500">
        </div>

        <div class="mb-6">
            <label class="block text-gray-400 text-xs font-bold mb-2">Transaction ID (TrxID)</label>
            <input type="text" name="trx_id" placeholder="8AN7GXX..." required class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 outline-none focus:border-indigo-500 uppercase">
        </div>

        <button type="submit" name="add_money" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-700 active:scale-95 transition">
            Verify Payment
        </button>
    </form>
</div>

<script>
function copyNum(id) {
    var num = document.getElementById(id).innerText;
    navigator.clipboard.writeText(num);
    alert("Number Copied: " + num);
}
</script>

<?php require_once 'includes/footer.php'; ?>