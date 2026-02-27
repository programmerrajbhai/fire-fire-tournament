<?php
require_once 'admin_header.php';
$msg = '';

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
        $msg = "<div class='bg-green-600 p-3 rounded mb-4'>Match Created Successfully!</div>";
    } else {
        $msg = "<div class='bg-red-600 p-3 rounded mb-4'>Failed to create match.</div>";
    }
}
?>

<h2 class="text-xl font-bold mb-4">Create New Match</h2>
<?= $msg ?>

<form method="POST" class="bg-gray-800 p-6 rounded-xl border border-gray-700 max-w-2xl">
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Match Title</label>
            <input type="text" name="title" required placeholder="e.g. Solo Time | Mobile" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Category</label>
            <select name="category" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
                <option value="BR MATCH">BR MATCH</option>
                <option value="CLASH SQUAD">CLASH SQUAD</option>
                <option value="LONE WOLF">LONE WOLF</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Type</label>
            <select name="type" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
                <option value="Solo">Solo</option>
                <option value="Duo">Duo</option>
                <option value="Squad">Squad</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Map</label>
            <input type="text" name="map" value="Bermuda" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Start Time</label>
            <input type="datetime-local" name="start_time" required class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Entry Fee (৳)</label>
            <input type="number" name="entry_fee" required class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Win Prize (৳)</label>
            <input type="number" name="win_prize" required class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Per Kill (৳)</label>
            <input type="number" name="per_kill" required class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Total Slots</label>
            <input type="number" name="total_slots" value="48" required class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white">
        </div>
    </div>

    <button type="submit" name="create" class="bg-indigo-600 text-white font-bold py-2 px-6 rounded hover:bg-indigo-500">Create Match</button>
</form>

</div></div></body></html>