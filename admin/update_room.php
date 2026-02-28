<?php
require_once 'admin_header.php';
$msg = '';

// ==========================================
// 🗑️ DELETE MATCH LOGIC
// ==========================================
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    
    // প্রথমে এই ম্যাচের সাথে যুক্ত জয়েন করা প্লেয়ারদের ডাটা ডিলিট করা (Foreign Key এরর এড়াতে)
    $pdo->prepare("DELETE FROM joined_matches WHERE match_id = ?")->execute([$del_id]);
    
    // এরপর মূল ম্যাচটি ডিলিট করা
    $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
    if($stmt->execute([$del_id])) {
        $_SESSION['success_msg'] = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-trash-can mr-2'></i> ম্যাচ #$del_id সফলভাবে ডিলিট করা হয়েছে!</div>";
        echo "<script>window.location.href='update_room.php';</script>";
        exit;
    }
}

// ==========================================
// ✏️ UPDATE ROOM LOGIC
// ==========================================
if (isset($_POST['update_room'])) {
    $match_id = intval($_POST['match_id']);
    $room_id = htmlspecialchars($_POST['room_id']);
    $room_pass = htmlspecialchars($_POST['room_pass']);

    $stmt = $pdo->prepare("UPDATE matches SET room_id = ?, room_pass = ? WHERE id = ?");
    if($stmt->execute([$room_id, $room_pass, $match_id])) {
        $msg = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> ম্যাচ #$match_id এর রুম আইডি আপডেট হয়েছে!</div>";
    }
}

// সেশন মেসেজ শো করা (ডিলিট করার পর)
if (isset($_SESSION['success_msg'])) {
    $msg .= $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// আপকামিং ম্যাচগুলো আনা (joined এবং total_slots সহ)
$matches = $pdo->query("SELECT id, title, start_time, room_id, room_pass, joined, total_slots FROM matches WHERE status='upcoming' ORDER BY start_time ASC")->fetchAll();
?>

<div class="max-w-5xl mx-auto pb-10 px-2 sm:px-0">
    <h2 class="text-xl font-bold mb-6 uppercase tracking-wider"><i class="fa-solid fa-key text-orange-500 mr-2"></i> রুম আইডি ও পাসওয়ার্ড</h2>
    <?= $msg ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php if(count($matches) > 0): ?>
            <?php foreach($matches as $match): 
                $is_full = $match['joined'] >= $match['total_slots'];
            ?>
            <div class="bg-[#1a1c29] border border-gray-700 rounded-3xl p-5 shadow-xl relative overflow-hidden group hover:border-indigo-500 transition-colors">
                
                <div class="absolute top-0 right-0 bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-bl-2xl shadow-md">
                    #<?= $match['id'] ?>
                </div>

                <h3 class="font-bold text-[15px] text-white leading-tight mb-2 pr-6"><?= htmlspecialchars($match['title']) ?></h3>
                
                <div class="flex items-center justify-between mb-4 border-b border-gray-800 pb-3">
                    <p class="text-[11px] text-gray-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-regular fa-clock text-orange-400"></i> <?= date('d M, Y', strtotime($match['start_time'])) ?>
                    </p>
                    <span class="text-[10px] font-black tracking-widest uppercase px-2 py-1 rounded border <?= $is_full ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' ?>">
                        <i class="fa-solid fa-users mr-1"></i> <?= $match['joined'] ?>/<?= $match['total_slots'] ?>
                    </span>
                </div>
                
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                    
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">রুম আইডি (Room ID)</label>
                        <input type="text" name="room_id" placeholder="এখানে আইডি দিন" value="<?= htmlspecialchars($match['room_id']) ?>" class="w-full bg-[#2d324a]/30 border border-gray-700 p-2.5 rounded-xl text-sm text-white font-bold outline-none focus:border-indigo-500 transition" required>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">পাসওয়ার্ড (Password)</label>
                        <input type="text" name="room_pass" placeholder="এখানে পাসওয়ার্ড দিন" value="<?= htmlspecialchars($match['room_pass']) ?>" class="w-full bg-[#2d324a]/30 border border-gray-700 p-2.5 rounded-xl text-sm text-white font-bold outline-none focus:border-indigo-500 transition" required>
                    </div>

                    <div class="flex gap-2 mt-2 pt-1">
                        <button type="submit" name="update_room" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-xs font-black hover:bg-indigo-500 active:scale-95 transition-transform shadow-lg uppercase tracking-widest">আপডেট করুন</button>
                        
                        <a href="?delete=<?= $match['id'] ?>" onclick="return confirm('আপনি কি নিশ্চিত যে এই ম্যাচটি সম্পূর্ণ ডিলিট করতে চান?')" class="bg-red-500/10 border border-red-500/30 text-red-500 px-4 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition active:scale-95 shadow-lg" title="ম্যাচ ডিলিট করুন">
                            <i class="fa-solid fa-trash-can text-lg"></i>
                        </a>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center text-gray-500 py-16">
                <i class="fa-solid fa-ghost text-5xl mb-4 opacity-30"></i>
                <p class="font-bold text-sm uppercase tracking-widest">কোনো আপকামিং ম্যাচ নেই</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</div></div></body></html>