<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// ==========================================
// ⚙️ BACKEND: FETCH JOINED PLAYERS (AJAX)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'get_joined_players') {
    header('Content-Type: application/json');
    $match_id = intval($_POST['match_id']);
    
    // ডাটাবেস থেকে ওই ম্যাচের সব প্লেয়ারের তথ্য আনা
    $stmt = $pdo->prepare("
        SELECT u.name, u.ff_uid 
        FROM joined_matches jm 
        JOIN users u ON jm.user_id = u.id 
        WHERE jm.match_id = ?
        ORDER BY jm.id ASC
    ");
    $stmt->execute([$match_id]);
    $players = $stmt->fetchAll();
    
    if ($players) {
        echo json_encode(['status' => 'success', 'players' => $players]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No players have joined yet.']);
    }
    exit;
}

// ==========================================
// 🎨 FRONTEND UI
// ==========================================
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
    <h2 class="text-2xl font-black mb-6 tracking-wide uppercase text-white drop-shadow-md">My Matches</h2>

    <div class="flex bg-[#1a1c29] rounded-xl p-1.5 mb-6 border border-gray-800 shadow-lg">
        <a href="?tab=upcoming" class="flex-1 text-center py-3 rounded-lg text-sm font-bold transition-all duration-300 <?= $tab == 'upcoming' ? 'bg-[#5a4bda] text-white shadow-md' : 'text-gray-500 hover:text-gray-300' ?>">
            Upcoming
        </a>
        <a href="?tab=past" class="flex-1 text-center py-3 rounded-lg text-sm font-bold transition-all duration-300 <?= $tab == 'past' ? 'bg-gray-700 text-white shadow-md' : 'text-gray-500 hover:text-gray-300' ?>">
            Past Results
        </a>
    </div>

    <?php if (count($my_matches) > 0): ?>
        <div class="space-y-5">
            <?php foreach($my_matches as $match): ?>
                <div class="bg-[#1a1c29] border border-gray-800 rounded-2xl p-5 shadow-xl relative overflow-hidden">
                    
                    <a href="match_details.php?id=<?= $match['id'] ?>" class="flex items-center gap-4 mb-4 group block">
                        <div class="w-14 h-14 bg-[#232736] rounded-xl flex items-center justify-center border border-gray-700 shrink-0 group-hover:border-indigo-500 transition">
                            <i class="fa-solid fa-gamepad text-indigo-400 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-[15px] text-white leading-tight mb-1 group-hover:text-indigo-400 transition"><?= htmlspecialchars($match['title']) ?></h3>
                            <p class="text-xs text-orange-400 font-semibold"><i class="fa-regular fa-clock mr-1"></i> <?= date('d M, Y - h:i A', strtotime($match['start_time'])) ?></p>
                        </div>
                    </a>
                    
                    <div class="grid grid-cols-2 gap-3 border-t border-gray-800 pt-4">
                        
                        <button onclick="viewPlayers(<?= $match['id'] ?>)" class="bg-[#2d324a]/50 text-gray-300 text-[11px] py-2.5 rounded-xl font-bold flex justify-center items-center gap-2 hover:bg-gray-700 transition border border-gray-700">
                            <i class="fa-solid fa-users text-indigo-400"></i> View Players
                        </button>

                        <?php if($tab == 'upcoming'): ?>
                            <button onclick="checkRoom(<?= $match['id'] ?>)" class="bg-indigo-600/20 border border-indigo-500/40 text-indigo-400 text-[11px] py-2.5 rounded-xl font-bold flex justify-center items-center gap-2 hover:bg-indigo-600 hover:text-white transition shadow-sm">
                                <i class="fa-solid fa-key"></i> Room Info
                            </button>
                        <?php else: ?>
                            <a href="result.php?id=<?= $match['id'] ?>" class="bg-gray-700 text-white text-[11px] py-2.5 rounded-xl font-bold flex justify-center items-center gap-2 hover:bg-gray-600 transition shadow-sm">
                                <i class="fa-solid fa-trophy text-yellow-500"></i> View Result
                            </a>
                        <?php endif; ?>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="flex flex-col items-center justify-center mt-24 text-gray-500">
            <div class="w-20 h-20 bg-[#1a1c29] border border-gray-800 rounded-full flex items-center justify-center mb-5 shadow-inner">
                <i class="fa-regular fa-calendar-xmark text-3xl opacity-50"></i>
            </div>
            <p class="font-black text-sm tracking-widest uppercase">No <?= $tab ?> matches</p>
            <p class="text-xs mt-2 text-gray-600">আপনি এখনও কোনো ম্যাচে জয়েন করেননি।</p>
        </div>
    <?php endif; ?>
</div>

<div id="customModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-[100] hidden items-center justify-center p-6 transition-all">
    <div class="bg-[#1a1c29] border border-gray-800 rounded-[2rem] w-full max-w-sm overflow-hidden shadow-2xl">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center bg-[#2d324a]/20">
            <h3 id="modalTitle" class="font-black text-white flex items-center gap-2 tracking-wide text-sm uppercase">
                </h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-white transition"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        
        <div id="modalContent" class="p-6 text-center">
            </div>
        
        <div class="p-5 bg-[#2d324a]/10">
            <button onclick="closeModal()" class="w-full bg-white text-black font-black py-3.5 rounded-xl active:scale-95 transition uppercase shadow-lg tracking-widest text-sm">Close</button>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('customModal');
const modalTitle = document.getElementById('modalTitle');
const modalContent = document.getElementById('modalContent');

function showModal(titleHTML, bodyHTML) { 
    modalTitle.innerHTML = titleHTML;
    modalContent.innerHTML = bodyHTML; 
    modal.classList.remove('hidden'); 
    modal.classList.add('flex'); 
}
function closeModal() { 
    modal.classList.add('hidden'); 
    modal.classList.remove('flex'); 
}

// ১. Room Check (আগের ফাংশন)
function checkRoom(matchId) {
    const fd = new FormData(); 
    fd.append('action', 'get_room_details'); 
    fd.append('match_id', matchId);
    
    fetch('matches.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        const title = '<i class="fa-solid fa-shield-halved text-indigo-500"></i> রুম ইনফরমেশন';
        if(data.status === 'error') {
            showModal(title, `<div class="bg-gray-800/50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner"><i class="fa-solid fa-lock text-gray-500 text-3xl"></i></div><p class="text-gray-300 font-bold tracking-wide">${data.message}</p>`);
        } else {
            showModal(title, `
                <div class="text-left space-y-4 font-black">
                    <div class="bg-gray-800/50 p-4 rounded-2xl border border-gray-700 text-center">
                        <p class="text-[11px] text-gray-400 mb-1 uppercase tracking-widest">Room ID</p>
                        <p class="text-2xl text-white tracking-widest select-all">${data.room_id || 'Not Set'}</p>
                    </div>
                    <div class="bg-gray-800/50 p-4 rounded-2xl border border-gray-700 text-center">
                        <p class="text-[11px] text-gray-400 mb-1 uppercase tracking-widest">Password</p>
                        <p class="text-2xl text-white tracking-widest select-all">${data.room_pass || 'Not Set'}</p>
                    </div>
                </div>
            `);
        }
    });
}

// ২. View Joined Players (নতুন ফাংশন)
function viewPlayers(matchId) {
    const fd = new FormData(); 
    fd.append('action', 'get_joined_players'); 
    fd.append('match_id', matchId);
    
    // Showing loading state
    const title = '<i class="fa-solid fa-users text-indigo-500"></i> Joined Players';
    showModal(title, '<i class="fa-solid fa-spinner fa-spin text-3xl text-indigo-500"></i>');

    fetch('my_matches.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            let html = '<div class="max-h-72 overflow-y-auto space-y-3 pr-2 custom-scrollbar text-left">';
            data.players.forEach((player, index) => {
                html += `
                    <div class="flex items-center gap-4 bg-gray-800/40 p-3 rounded-2xl border border-gray-700/50 hover:bg-gray-800 transition">
                        <div class="w-10 h-10 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-black text-sm shadow-inner">
                            ${index + 1}
                        </div>
                        <div>
                            <p class="text-[14px] font-bold text-white mb-0.5">${player.name}</p>
                            <p class="text-[10px] text-gray-400 font-semibold tracking-wider">UID: ${player.ff_uid}</p>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            showModal(title, html);
        } else {
            showModal(title, `<p class="text-gray-400 font-bold">${data.message}</p>`);
        }
    });
}
</script>

<style>
/* Custom Scrollbar for Player List */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
</style>

<?php require_once 'includes/footer.php'; ?>