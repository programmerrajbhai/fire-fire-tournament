<?php
require_once 'admin_header.php';
$msg = '';

$categories_list = $pdo->query("SELECT name FROM categories ORDER BY id ASC")->fetchAll();

if (isset($_POST['create'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $type = $_POST['type'];
    $map = $_POST['map'];
    $start_time = $_POST['start_time'];
    $entry_fee = $_POST['entry_fee'];
    $win_prize = $_POST['win_prize'];
    $per_kill = $_POST['per_kill'];
    $total_slots = $_POST['total_slots'];

    $stmt = $pdo->prepare("INSERT INTO matches (title, category, type, map, start_time, entry_fee, win_prize, per_kill, total_slots) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if($stmt->execute([$title, $category, $type, $map, $start_time, $entry_fee, $win_prize, $per_kill, $total_slots])) {
        $msg = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> নতুন ম্যাচ সফলভাবে তৈরি হয়েছে!</div>";
    } else {
        $msg = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-triangle-exclamation mr-2'></i> ম্যাচ তৈরি করতে সমস্যা হয়েছে।</div>";
    }
}
?>

<div class="max-w-4xl mx-auto pb-10 px-2 sm:px-0">
    <h2 class="text-xl font-bold mb-6 uppercase tracking-wider"><i class="fa-solid fa-square-plus text-indigo-500 mr-2"></i> নতুন ম্যাচ তৈরি করুন</h2>
    <?= $msg ?>

    <form method="POST" class="bg-[#1a1c29] p-6 sm:p-8 rounded-3xl border border-gray-700 shadow-2xl">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-[11px] text-gray-400 mb-1 font-bold uppercase tracking-wider">ম্যাচের নাম (Title)</label>
                <input type="text" name="title" required placeholder="যেমন: Solo Time | Mobile" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3.5 rounded-xl text-white outline-none focus:border-indigo-500 transition font-bold">
            </div>
            <div>
                <label class="block text-[11px] text-gray-400 mb-1 font-bold uppercase tracking-wider">গেম ক্যাটাগরি</label>
                <select name="category" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3.5 rounded-xl text-white uppercase outline-none focus:border-indigo-500 transition cursor-pointer font-bold appearance-none">
                    <?php if(count($categories_list) > 0): ?>
                        <?php foreach($categories_list as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="BR MATCH">BR MATCH (Default)</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
            <div>
                <label class="block text-[11px] text-gray-400 mb-1 font-bold uppercase tracking-wider">ম্যাচের ধরণ (Type)</label>
                <select name="type" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3.5 rounded-xl text-white outline-none focus:border-indigo-500 transition cursor-pointer font-bold appearance-none">
                    <option value="Solo">Solo</option>
                    <option value="Duo">Duo</option>
                    <option value="Squad">Squad</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] text-gray-400 mb-1 font-bold uppercase tracking-wider">ম্যাপ (Map)</label>
                <input type="text" name="map" value="Bermuda" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3.5 rounded-xl text-white outline-none focus:border-indigo-500 transition font-bold">
            </div>
            <div>
                <label class="block text-[11px] text-gray-400 mb-1 font-bold uppercase tracking-wider">শুরুর সময়</label>
                <input type="datetime-local" name="start_time" required class="w-full bg-[#2d324a]/30 border border-gray-700 p-3.5 rounded-xl text-white outline-none focus:border-indigo-500 transition cursor-pointer text-sm font-bold">
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#2d324a]/20 p-3 rounded-xl border border-indigo-500/30">
                <label class="block text-[10px] text-indigo-300 mb-1 font-black uppercase tracking-widest text-center">এন্ট্রি ফি (৳)</label>
                <input type="number" name="entry_fee" required placeholder="0" class="w-full bg-transparent text-center text-xl font-black text-white outline-none placeholder-gray-600">
            </div>
            <div class="bg-[#2d324a]/20 p-3 rounded-xl border border-yellow-500/30">
                <label class="block text-[10px] text-yellow-500 mb-1 font-black uppercase tracking-widest text-center">বিজয়ী প্রাইজ (৳)</label>
                <input type="number" name="win_prize" required placeholder="0" class="w-full bg-transparent text-center text-xl font-black text-white outline-none placeholder-gray-600">
            </div>
            <div class="bg-[#2d324a]/20 p-3 rounded-xl border border-pink-500/30">
                <label class="block text-[10px] text-pink-400 mb-1 font-black uppercase tracking-widest text-center">প্রতি কিলে (৳)</label>
                <input type="number" name="per_kill" required placeholder="0" class="w-full bg-transparent text-center text-xl font-black text-white outline-none placeholder-gray-600">
            </div>
            <div class="bg-[#2d324a]/20 p-3 rounded-xl border border-emerald-500/30">
                <label class="block text-[10px] text-emerald-400 mb-1 font-black uppercase tracking-widest text-center">মোট প্লেয়ার</label>
                <input type="number" name="total_slots" value="48" required class="w-full bg-transparent text-center text-xl font-black text-white outline-none placeholder-gray-600">
            </div>
        </div>

        <button type="submit" name="create" class="w-full bg-indigo-600 text-white font-black text-lg py-4 rounded-2xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg shadow-indigo-600/40 uppercase tracking-widest">
            <i class="fa-solid fa-paper-plane mr-2"></i> ম্যাচ পাবলিশ করুন
        </button>
    </form>
</div>

</div></div></body></html>