<?php
require_once 'admin_header.php';
$msg = '';

if (isset($_POST['update_room'])) {
    $match_id = $_POST['match_id'];
    $room_id = $_POST['room_id'];
    $room_pass = $_POST['room_pass'];

    $stmt = $pdo->prepare("UPDATE matches SET room_id = ?, room_pass = ? WHERE id = ?");
    if($stmt->execute([$room_id, $room_pass, $match_id])) {
        $msg = "<div class='bg-green-600 p-3 rounded mb-4'>Room Credentials Updated for Match #$match_id</div>";
    }
}

// আপকামিং ম্যাচগুলো আনা
$matches = $pdo->query("SELECT id, title, start_time, room_id, room_pass FROM matches WHERE status='upcoming' ORDER BY start_time ASC")->fetchAll();
?>

<h2 class="text-xl font-bold mb-4">Manage Room ID & Password</h2>
<?= $msg ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach($matches as $match): ?>
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow-lg">
        <h3 class="font-bold text-lg text-indigo-400 mb-1">#<?= $match['id'] ?> - <?= htmlspecialchars($match['title']) ?></h3>
        <p class="text-xs text-gray-400 mb-4"><i class="fa-regular fa-clock"></i> <?= date('d M, Y h:i A', strtotime($match['start_time'])) ?></p>
        
        <form method="POST" class="flex gap-2">
            <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
            <input type="text" name="room_id" placeholder="Room ID" value="<?= $match['room_id'] ?>" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm text-white" required>
            <input type="text" name="room_pass" placeholder="Password" value="<?= $match['room_pass'] ?>" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm text-white" required>
            <button type="submit" name="update_room" class="bg-green-600 px-4 py-2 rounded text-sm font-bold hover:bg-green-500">Update</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

</div></div></body></html>