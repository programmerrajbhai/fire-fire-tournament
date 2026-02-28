<?php
require_once 'admin_header.php';
$msg = '';

// ব্যালেন্স অ্যাড বা কমানোর লজিক
if (isset($_POST['update_balance'])) {
    $uid = intval($_POST['user_id']);
    $amount = intval($_POST['amount']);
    $action = $_POST['action_type']; // 'add' or 'deduct'

    if ($amount > 0) {
        if ($action == 'add') {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, deposit_balance = deposit_balance + ? WHERE id = ?");
            $stmt->execute([$amount, $amount, $uid]);
            
            // ট্রানজেকশন হিস্ট্রি অ্যাড করা
            $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, trx_id, status) VALUES (?, 'add_money', 'admin_added', ?, 'ADMIN', 'approved')")->execute([$uid, $amount]);
            
            $msg = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-xl mb-4 font-bold shadow-lg'>৳$amount সফলভাবে অ্যাড করা হয়েছে!</div>";
        } elseif ($action == 'deduct') {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $uid]);
            $msg = "<div class='bg-orange-500/20 border border-orange-500 text-orange-400 p-3 rounded-xl mb-4 font-bold shadow-lg'>৳$amount সফলভাবে কেটে নেওয়া হয়েছে!</div>";
        }
    }
}

// সব ইউজার আনা
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-6xl mx-auto pb-10">
    <h2 class="text-xl font-bold mb-6 uppercase tracking-wider"><i class="fa-solid fa-users text-indigo-500 mr-2"></i> User Wallets & Management</h2>
    <?= $msg ?>

    <div class="bg-[#1a1c29] rounded-3xl border border-gray-700 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-[#2d324a]/30 text-gray-400 uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="p-4 font-bold">ID</th>
                        <th class="p-4 font-bold">User Info</th>
                        <th class="p-4 font-bold">FF UID</th>
                        <th class="p-4 font-bold">Total Balance</th>
                        <th class="p-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php foreach($users as $u): ?>
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="p-4 font-black text-gray-500">#<?= $u['id'] ?></td>
                        <td class="p-4">
                            <p class="font-bold text-white"><?= htmlspecialchars($u['name']) ?></p>
                            <p class="text-[10px] text-gray-500"><?= htmlspecialchars($u['email']) ?></p>
                        </td>
                        <td class="p-4 font-black text-indigo-400"><?= htmlspecialchars($u['ff_uid']) ?></td>
                        <td class="p-4 font-black text-green-400 text-lg">৳ <?= $u['balance'] ?></td>
                        <td class="p-4 text-center">
                            <button onclick="openWalletModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', <?= $u['balance'] ?>)" class="bg-indigo-600/20 text-indigo-400 border border-indigo-500/50 px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-600 hover:text-white transition active:scale-95 shadow-lg">
                                <i class="fa-solid fa-wallet mr-1"></i> Manage Balance
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="walletModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-[#1a1c29] border border-gray-700 rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl relative">
        <button onclick="closeWalletModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
        
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-indigo-500/20 text-indigo-400 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 border border-indigo-500/30">
                <i class="fa-solid fa-coins"></i>
            </div>
            <h3 class="font-black text-white text-lg mb-1" id="modalUserName">User Name</h3>
            <p class="text-xs text-gray-400 mb-6 font-bold">Current Balance: <span class="text-green-400" id="modalUserBalance">৳0</span></p>

            <form method="POST">
                <input type="hidden" name="user_id" id="modalUserId">
                
                <div class="flex gap-2 mb-4">
                    <label class="flex-1 bg-gray-800 border border-gray-700 rounded-xl p-3 cursor-pointer hover:border-green-500 transition">
                        <input type="radio" name="action_type" value="add" checked class="accent-green-500"> 
                        <span class="text-xs font-bold text-gray-300 ml-1">Add (+)</span>
                    </label>
                    <label class="flex-1 bg-gray-800 border border-gray-700 rounded-xl p-3 cursor-pointer hover:border-orange-500 transition">
                        <input type="radio" name="action_type" value="deduct" class="accent-orange-500"> 
                        <span class="text-xs font-bold text-gray-300 ml-1">Deduct (-)</span>
                    </label>
                </div>

                <div class="bg-[#2d324a]/30 border border-gray-700 p-2 rounded-xl mb-6">
                    <input type="number" name="amount" required placeholder="Enter Amount (৳)" class="w-full bg-transparent text-center text-xl font-black text-white outline-none py-2 placeholder-gray-600">
                </div>

                <button type="submit" name="update_balance" class="w-full bg-indigo-600 text-white font-black py-3.5 rounded-xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg uppercase tracking-widest">
                    Confirm Update
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openWalletModal(id, name, balance) {
    document.getElementById('modalUserId').value = id;
    document.getElementById('modalUserName').innerText = name;
    document.getElementById('modalUserBalance').innerText = '৳' + balance;
    document.getElementById('walletModal').classList.remove('hidden');
    document.getElementById('walletModal').classList.add('flex');
}
function closeWalletModal() {
    document.getElementById('walletModal').classList.add('hidden');
    document.getElementById('walletModal').classList.remove('flex');
}
</script>

</div></div></body></html>