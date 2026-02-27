<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = $_SESSION['user_id'];

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
$status_condition = ($tab == 'upcoming') ? "IN ('upcoming', 'running')" : "= 'completed'";

$query = "SELECT m.* FROM matches m 
          JOIN joined_matches jm ON m.id = jm.match_id 
          WHERE jm.user_id = ? AND m.status $status_condition 
          ORDER BY m.start_time ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$my_matches = $stmt->fetchAll();

$current_page = 'my_matches';
require_once 'includes/header.php';
?>

<div class="p-4 pb-24">
    <h2 class="text-xl font-bold mb-4">My Matches</h2>

    <div class="flex bg-[#1a1c29] rounded-xl p-1 mb-6 border border-gray-700">
        <a href="?tab=upcoming" class="flex-1 text-center py-3 rounded-lg text-sm font-bold transition <?= $tab == 'upcoming' ? 'bg-[#2d3748] text-white' : 'text-gray-400 hover:text-white' ?>">
            Upcoming
        </a>
        <a href="?tab=past" class="flex-1 text-center py-3 rounded-lg text-sm font-bold transition <?= $tab == 'past' ? 'bg-[#2d3748] text-white' : 'text-gray-400 hover:text-white' ?>">
            Past
        </a>
    </div>

    <?php if (count($my_matches) > 0): ?>
        <div class="space-y-4">
            <?php foreach($my_matches as $match): ?>
                <div class="bg-[#1a1c29] border border-gray-700 rounded-xl p-4 flex items-center justify-between shadow-lg">
                    <a href="match_details.php?id=<?= $match['id'] ?>" class="flex items-center gap-4 flex-1">
                        <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center border border-gray-600">
                            <i class="fa-solid fa-gamepad text-indigo-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-white"><?= htmlspecialchars($match['title']) ?></h3>
                            <p class="text-xs text-gray-400 mt-1"><?= date('d M, Y h:i A', strtotime($match['start_time'])) ?></p>
                        </div>
                    </a>
                    
                    <?php if($tab == 'upcoming'): ?>
                        <button onclick="checkRoom(<?= $match['id'] ?>)" class="bg-indigo-600 text-white text-xs px-3 py-2 rounded-lg font-bold ml-2 active:scale-95">Room Info</button>
                    <?php else: ?>
                        <a href="result.php?id=<?= $match['id'] ?>" class="bg-gray-700 text-white text-xs px-3 py-2 rounded-lg font-bold ml-2">Result</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="flex flex-col items-center justify-center mt-20 text-gray-500">
            <div class="w-16 h-16 bg-[#1a1c29] border border-gray-700 rounded-full flex items-center justify-center mb-4 shadow-lg">
                <i class="fa-regular fa-calendar-xmark text-2xl"></i>
            </div>
            <p class="font-medium text-sm">No <?= $tab ?> matches found.</p>
        </div>
    <?php endif; ?>
</div>

<div id="customModal" class="fixed inset-0 bg-black bg-opacity-80 z-[100] hidden items-center justify-center p-5 transition-opacity">
    <div class="bg-[#1a1c29] border border-gray-700 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl">
        <div class="p-4 border-b border-gray-700 flex justify-between items-center">
            <h3 class="font-bold flex items-center gap-2"><i class="fa-solid fa-key text-indigo-500"></i> Room Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div id="modalContent" class="p-6 text-center"></div>
        <div class="p-4">
            <button onclick="closeModal()" class="w-full bg-gray-700 text-white font-bold py-3 rounded-xl active:bg-gray-600">Close</button>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('customModal');
const modalContent = document.getElementById('modalContent');
function showModal(html) { modalContent.innerHTML = html; modal.classList.remove('hidden'); modal.classList.add('flex'); }
function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

// Fetch API calling matches.php to get room details securely
function checkRoom(matchId) {
    const formData = new FormData();
    formData.append('action', 'get_room_details');
    formData.append('match_id', matchId);
    
    // We send request to matches.php because the API logic is stored there
    fetch('matches.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'error') {
            showModal(`<div class="bg-gray-800 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-lock text-gray-500 text-2xl"></i></div><p class="text-gray-300 font-medium">${data.message}</p>`);
        } else {
            let r_id = data.room_id ? data.room_id : 'Will be given soon';
            let r_pass = data.room_pass ? data.room_pass : 'Will be given soon';
            showModal(`<div class="text-left bg-gray-800 p-4 rounded-xl border border-gray-700"><p class="text-xs text-gray-400 mb-1">ROOM ID</p><p class="font-bold text-lg text-white mb-3">${r_id}</p><p class="text-xs text-gray-400 mb-1">PASSWORD</p><p class="font-bold text-lg text-white">${r_pass}</p></div>`);
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>