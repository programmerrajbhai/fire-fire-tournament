<?php
session_start();
require_once 'includes/db.php';

// যদি আগে থেকেই লগইন থাকে, হোমপেজে পাঠিয়ে দেবে
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$msg = '';

if (isset($_POST['register'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $ff_uid = htmlspecialchars($_POST['ff_uid']);
    $password = $_POST['password'];
    $c_password = $_POST['c_password'];
    $refer_code_input = htmlspecialchars($_POST['refer_code']); // কে রেফার করেছে তার কোড

    // ইমেইল বা UID আগে থেকেই আছে কিনা চেক করা
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR ff_uid = ?");
    $stmt->execute([$email, $ff_uid]);
    
    if ($stmt->rowCount() > 0) {
        $msg = "<div class='bg-red-500/20 text-red-500 p-3 rounded-lg text-sm font-bold text-center mb-4'>এই ইমেইল বা UID দিয়ে ইতিমধ্যে অ্যাকাউন্ট খোলা আছে!</div>";
    } elseif ($password !== $c_password) {
        $msg = "<div class='bg-red-500/20 text-red-500 p-3 rounded-lg text-sm font-bold text-center mb-4'>পাসওয়ার্ড দুটি মিলেনি!</div>";
    } else {
        // পাসওয়ার্ড এনক্রিপ্ট করা
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // নিজের জন্য একটি ইউনিক রেফার কোড তৈরি করা (যেমন: REF-83492)
        $my_refer_code = 'REF-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $referred_by_id = NULL;

        try {
            $pdo->beginTransaction();

            // যদি সে কারো রেফার কোড দিয়ে থাকে
            if (!empty($refer_code_input)) {
                $ref_check = $pdo->prepare("SELECT id FROM users WHERE refer_code = ?");
                $ref_check->execute([$refer_code_input]);
                if ($ref_check->rowCount() > 0) {
                    $referrer = $ref_check->fetch();
                    $referred_by_id = $referrer['id'];

                    // সেটিংস থেকে রেফার বোনাস কত তা আনা
                    $set_stmt = $pdo->query("SELECT refer_bonus FROM settings WHERE id = 1");
                    $bonus_amount = $set_stmt->fetchColumn();

                    // যে রেফার করেছে, তার ব্যালেন্সে টাকা অ্যাড করা
                    $add_bonus = $pdo->prepare("UPDATE users SET balance = balance + ?, deposit_balance = deposit_balance + ? WHERE id = ?");
                    $add_bonus->execute([$bonus_amount, $bonus_amount, $referred_by_id]);

                    // ট্রানজেকশন হিস্ট্রি রাখা
                    $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, trx_id, status) VALUES (?, 'refer_bonus', 'system', ?, 'REFERRAL', 'approved')")->execute([$referred_by_id, $bonus_amount]);
                }
            }

            // নতুন ইউজার ডাটাবেসে সেভ করা
            $insert = $pdo->prepare("INSERT INTO users (name, email, ff_uid, password, refer_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$name, $email, $ff_uid, $hashed_password, $my_refer_code, $referred_by_id]);
            
            $pdo->commit();

            $_SESSION['success_msg'] = "অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে! এখন লগইন করুন।";
            header("Location: login.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "<div class='bg-red-500/20 text-red-500 p-3 rounded-lg text-sm font-bold text-center mb-4'>কোথাও কোনো সমস্যা হয়েছে!</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KheloFreeFire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#0f111a] text-white flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-[#1a1c29] p-8 rounded-3xl border border-gray-800 shadow-2xl">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-white uppercase tracking-wider">Create Account</h2>
            <p class="text-xs text-gray-400 mt-1">Join the ultimate Free Fire tournament.</p>
        </div>

        <?= $msg ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Free Fire UID</label>
                <input type="number" name="ff_uid" required class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Confirm</label>
                    <input type="password" name="c_password" required minlength="6" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-indigo-400 uppercase mb-1">Refer Code (Optional)</label>
                <input type="text" name="refer_code" placeholder="e.g. REF-123456" class="w-full bg-[#2d324a]/30 border border-indigo-500/30 p-3 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
            
            <button type="submit" name="register" class="w-full bg-indigo-600 text-white font-black py-3.5 rounded-xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg shadow-indigo-600/30 uppercase mt-4">
                Sign Up
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            Already have an account? <a href="login.php" class="text-indigo-400 font-bold hover:underline">Login here</a>
        </p>
    </div>

</body>
</html>