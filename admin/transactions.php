<?php
require_once 'admin_header.php';
$msg = '';

// Approve/Reject Logic
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    $trx_stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'pending'");
    $trx_stmt->execute([$id]);
    $trx = $trx_stmt->fetch();

    if ($trx) {
        $user_id = $trx['user_id'];
        $amount = $trx['amount'];

        if ($action == 'approve') {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE transactions SET status = 'approved' WHERE id = ?")->execute([$id]);
                
                if ($trx['type'] == 'add_money') {
                    // ব্যালেন্স অ্যাড করে দেওয়া
                    $pdo->prepare("UPDATE users SET balance = balance + ?, deposit_balance = deposit_balance + ? WHERE id = ?")->execute([$amount, $amount, $user_id]);
                }
                // Withdraw approve হলে ব্যালেন্স কাটার দরকার নাই, কারণ withdraw.php তে রিকোয়েস্ট দেওয়ার সময়ই টাকা কাটা হয়েছে। 
                
                $pdo->commit();
                $msg = "<div class='bg-green-600 p-3 rounded mb-4'>Transaction Approved Successfully!</div>";
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        } 
        elseif ($action == 'reject') {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE id = ?")->execute([$id]);
                
                if ($trx['type'] == 'withdraw') {
                    // রিজেক্ট হলে উইনিং ব্যালেন্স ফেরত দেওয়া
                    $pdo->prepare("UPDATE users SET winning_balance = winning_balance + ? WHERE id = ?")->execute([$amount, $user_id]);
                }
                $pdo->commit();
                $msg = "<div class='bg-red-600 p-3 rounded mb-4'>Transaction Rejected!</div>";
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }
    }
}

// পেন্ডিং ট্রানজেকশন আনা
$transactions = $pdo->query("SELECT t.*, u.name, u.email FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.status = 'pending' ORDER BY t.id DESC")->fetchAll();
?>

<h2 class="text-xl font-bold mb-4">Pending Transactions</h2>
<?= $msg ?>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden shadow-lg">
    <table class="w-full text-left text-sm">
        <thead class="bg-gray-900 text-gray-400">
            <tr>
                <th class="p-4">User</th>
                <th class="p-4">Type</th>
                <th class="p-4">Method & TrxID / Number</th>
                <th class="p-4">Amount</th>
                <th class="p-4 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($transactions) > 0): foreach($transactions as $t): ?>
            <tr class="border-b border-gray-700">
                <td class="p-4">
                    <p class="font-bold text-white"><?= htmlspecialchars($t['name']) ?></p>
                    <p class="text-xs text-gray-400"><?= htmlspecialchars($t['email']) ?></p>
                </td>
                <td class="p-4">
                    <?php if($t['type'] == 'add_money'): ?>
                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs font-bold uppercase">Add Money</span>
                    <?php else: ?>
                        <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded text-xs font-bold uppercase">Withdraw</span>
                    <?php endif; ?>
                </td>
                <td class="p-4">
                    <p class="font-bold text-indigo-300 uppercase"><?= htmlspecialchars($t['method']) ?></p>
                    <p class="text-xs text-gray-300"><?= htmlspecialchars($t['trx_id']) ?></p>
                </td>
                <td class="p-4 font-bold text-lg">৳ <?= $t['amount'] ?></td>
                <td class="p-4 text-right space-x-2">
                    <a href="?action=approve&id=<?= $t['id'] ?>" onclick="return confirm('Are you sure to APPROVE?')" class="bg-green-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-green-500">Approve</a>
                    <a href="?action=reject&id=<?= $t['id'] ?>" onclick="return confirm('Are you sure to REJECT?')" class="bg-red-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-red-500">Reject</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
                <td colspan="5" class="p-8 text-center text-gray-500">No pending transactions found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div></div></body></html>