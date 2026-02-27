<?php
require_once 'admin_header.php';
$msg = '';

// ডাটাবেস থেকে ডায়নামিক ক্যাটাগরিগুলো আনা হচ্ছে
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
        $msg = "<div class='bg-green-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i>Match Created Successfully!</div>";
    } else {
        $msg = "<div class='bg-red-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-triangle-exclamation mr-2'></i>Failed to create match.</div>";
    }
}
?>

<h2 class="text-xl font-bold mb-4">Create New Match</h2>
<?= $msg ?>

<form method="POST" class="bg-[#1a1c29] p-6 rounded-xl border border-gray-700 max-w-2xl shadow-xl">
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Match Title</label>
            <input type="text" name="title" required placeholder="e.g. Solo Time | Mobile" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Category</label>
            <select name="category" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white uppercase outline-none focus:border-indigo-500 transition cursor-pointer">
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

    <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Type</label>
            <select name="type" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition cursor-pointer">
                <option value="Solo">Solo</option>
                <option value="Duo">Duo</option>
                <option value="Squad">Squad</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Map</label>
            <input type="text" name="map" value="Bermuda" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Start Time</label>
            <input type="datetime-local" name="start_time" required class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition cursor-pointer">
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Entry Fee (৳)</label>
            <input type="number" name="entry_fee" required placeholder="0" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Win Prize (৳)</label>
            <input type="number" name="win_prize" required placeholder="0" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Per Kill (৳)</label>
            <input type="number" name="per_kill" required placeholder="0" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1 font-bold">Total Slots</label>
            <input type="number" name="total_slots" value="48" required class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition">
        </div>
    </div>

    <button type="submit" name="create" class="w-full bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-500 active:scale-95 transition-transform shadow-lg shadow-indigo-500/30">
        <i class="fa-solid fa-plus mr-2"></i> Create Match
    </button>
</form>

</div></div></body></html>