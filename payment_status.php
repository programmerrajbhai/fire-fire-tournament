<?php
session_start();
require_once 'includes/db.php';

// লগইন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_page = 'profile';
require_once 'includes/header.php';
?>

<div class="p-4 pb-24 flex items-center justify-center min-h-[80vh]">
    <div class="bg-[#1a1c29] border border-gray-700 rounded-3xl p-8 shadow-2xl text-center w-full max-w-sm">
        
        <?php if(isset($_GET['invoice_id'])): ?>
            <div class="w-20 h-20 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-5 border border-green-500/30 shadow-inner">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <h2 class="text-2xl font-black text-white uppercase tracking-wider mb-2">Payment Completed!</h2>
            <p class="text-xs text-gray-400 font-bold mb-6">আপনার পেমেন্ট সফল হয়েছে। ব্যালেন্স অটোমেটিক অ্যাড হয়ে গেছে।</p>
            
        <?php else: ?>
            <div class="w-20 h-20 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-5 border border-red-500/30 shadow-inner">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2 class="text-2xl font-black text-white uppercase tracking-wider mb-2">Payment Cancelled</h2>
            <p class="text-xs text-gray-400 font-bold mb-6">আপনি পেমেন্ট সম্পূর্ণ করেননি অথবা কোনো সমস্যা হয়েছে।</p>
        <?php endif; ?>

        <a href="profile.php" class="block w-full bg-indigo-600 text-white font-black text-sm py-4 rounded-xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg uppercase tracking-widest">
            Back to Profile
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>