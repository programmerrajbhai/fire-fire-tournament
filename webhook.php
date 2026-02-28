<?php
// webhook.php - Auto Balance Updater
require_once 'includes/db.php';

// আপনার UddoktaPay API KEY এখানে দিন (সিকিউরিটি ভেরিফিকেশনের জন্য)
$apiKey = "YOUR_API_KEY"; 

// API Key চেক করা
$headerApi = isset($_SERVER['HTTP_RT_UDDOKTAPAY_API_KEY']) ? $_SERVER['HTTP_RT_UDDOKTAPAY_API_KEY'] : '';

if ($headerApi !== $apiKey) {
    http_response_code(403);
    die("Unauthorized Action");
}

// UddoktaPay থেকে আসা ডাটা রিসিভ করা
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (isset($data['status']) && $data['status'] === 'COMPLETED') {
    
    $amount = intval($data['amount']);
    $trx_id = htmlspecialchars($data['transaction_id']);
    $user_id = intval($data['metadata']['user_id']); // add_money.php থেকে পাঠানো user_id
    $method = htmlspecialchars($data['payment_method']); // bKash, Nagad etc.

    // চেক করা এই TrxID আগে ডাটাবেসে অ্যাড হয়েছে কিনা (ডাবল অ্যাড হওয়া রোধ করতে)
    $check_trx = $pdo->prepare("SELECT id FROM transactions WHERE trx_id = ?");
    $check_trx->execute([$trx_id]);

    if ($check_trx->rowCount() == 0) {
        try {
            $pdo->beginTransaction();

            // ১. ইউজারের ব্যালেন্স আপডেট করা (Deposit Balance ও Total Balance)
            $update_user = $pdo->prepare("UPDATE users SET balance = balance + ?, deposit_balance = deposit_balance + ? WHERE id = ?");
            $update_user->execute([$amount, $amount, $user_id]);

            // ২. ট্রানজেকশন হিস্ট্রিতে রেকর্ড রাখা (approved হিসেবে)
            $insert_trx = $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, trx_id, status) VALUES (?, 'add_money', ?, ?, ?, 'approved')");
            $insert_trx->execute([$user_id, $method, $amount, $trx_id]);

            $pdo->commit();
            http_response_code(200);
            echo "Success";
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo "Database Error";
        }
    } else {
        http_response_code(200);
        echo "Already Processed";
    }
} else {
    http_response_code(400);
    echo "Invalid Status";
}
?>