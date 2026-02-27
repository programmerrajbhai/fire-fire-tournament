<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
}
$user_id = $_SESSION['user_id'];

// AJAX API HANDLER
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $match_id = intval($_POST['match_id']);
    
    if ($_POST['action'] == 'get_room_details') {
        $check_join = $pdo->prepare("SELECT id FROM joined_matches WHERE user_id = ? AND match_id = ?");
        $check_join->execute([$user_id, $match_id]);
        
        if ($check_join->rowCount() > 0) {
            $match_data = $pdo->prepare("SELECT room_id, room_pass FROM matches WHERE id = ?");
            $match_data->execute([$match_id]);
            $room = $match_data->fetch();
            echo json_encode(['status' => 'success', 'room_id' => $room['room_id'], 'room_pass' => $room['room_pass']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "You didn't join this match."]);
        }
        exit;
    }

    if ($_POST['action'] == 'join_match') {
        $match_data = $pdo->prepare("SELECT entry_fee, total_slots, joined FROM matches WHERE id = ?");
        $match_data->execute([$match_id]);
        $match = $match_data->fetch();

        $user_data = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $user_data->execute([$user_id]);
        $user = $user_data->fetch();

        $check_exist = $pdo->prepare("SELECT id FROM joined_matches WHERE user_id = ? AND match_id = ?");
        $check_exist->execute([$user_id, $match_id]);

        if ($check_exist->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'You have already joined!']);
        } elseif ($match['joined'] >= $match['total_slots']) {
            echo json_encode(['status' => 'error', 'message' => 'Match is already full!']);
        } elseif ($user['balance'] < $match['entry_fee']) {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient balance!']);
        } else {
            try {
                $pdo->beginTransaction();
                $new_balance = $user['balance'] - $match['entry_fee'];
                $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_balance, $user_id]);
                $pdo->prepare("INSERT INTO joined_matches (user_id, match_id) VALUES (?, ?)")->execute([$user_id, $match_id]);
                $pdo->prepare("UPDATE matches SET joined = joined + 1 WHERE id = ?")->execute([$match_id]);
                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Successfully joined the match!']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Something went wrong!']);
            }
        }
        exit;
    }
}

$current_page = 'home';
require_once 'includes/header.php';

$category = isset($_GET['cat']) ? $_GET['cat'] : 'BR MATCH';

$stmt = $pdo->prepare("SELECT * FROM matches WHERE category = ? AND status = 'upcoming' ORDER BY start_time ASC");
$stmt->execute([$category]);
$matches = $stmt->fetchAll();
?>

<div class="p-4 pb-24">
    <div class="flex items-center mb-6">
        <a href="index.php" class="text-white mr-4 bg-gray-800 p-2 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <p class="text-[10px] text-gray-400">Mode</p>
            <h2 class="text-lg font-bold tracking-wide uppercase"><?= htmlspecialchars($category) ?></h2>
        </div>
    </div>

    <?php if (count($matches) > 0): ?>
        <?php foreach($matches as $match): 
            $progress = ($match['joined'] / $match['total_slots']) * 100;
            $is_full = $match['joined'] >= $match['total_slots'];
            $start_time = strtotime($match['start_time']);
        ?>
        <div class="bg-[#1a1c29] rounded-2xl overflow-hidden border border-gray-700 mb-6 shadow-xl relative">
            
            <div class="absolute top-0 right-0 bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl z-10">
                #<?= $match['id'] ?>
            </div>

            <div class="p-4 pt-6">
                <a href="match_details.php?id=<?= $match['id'] ?>" class="flex items-center gap-3 mb-5 block">
                    <img src="assets/images/br.jpg" class="w-14 h-14 rounded-xl object-cover border border-gray-600" onerror="this.src='https://via.placeholder.com/150'">
                    <div>
                        <h3 class="font-bold text-[15px] text-white hover:text-indigo-400"><?= htmlspecialchars($match['title']) ?></h3>
                        <p class="text-[11px] text-orange-400 font-semibold mt-1"><i class="fa-regular fa-clock"></i> <?= date('d M, h:i A', $start_time) ?></p>
                    </div>
                </a>

                <div class="grid grid-cols-3 gap-2 text-center mb-5">
                    <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                        <p class="text-[9px] text-gray-400">WIN PRIZE</p>
                        <p class="font-bold text-lg text-white"><?= $match['win_prize'] ?></p>
                    </div>
                    <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                        <p class="text-[9px] text-gray-400">PER KILL</p>
                        <p class="font-bold text-lg text-white"><?= $match['per_kill'] ?></p>
                    </div>
                    <div class="bg-gray-800 p-2 rounded-xl border border-gray-700">
                        <p class="text-[9px] text-gray-400">ENTRY FEE</p>
                        <p class="font-bold text-lg text-white"><?= $match['entry_fee'] ?></p>
                    </div>
                </div>

                <div class="mb-2 relative w-full bg-gray-800 h-2.5 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full bg-[#f97316] rounded-full" style="width: <?= $progress ?>%;"></div>
                </div>
                
                <div class="flex justify-between items-center text-[11px] font-semibold text-gray-400 mb-5">
                    <span>Only <?= $match['total_slots'] - $match['joined'] ?> spots left</span>
                    <span class="text-white"><?= $match['joined'] ?>/<?= $match['total_slots'] ?></span>
                    
                    <?php if($is_full): ?>
                        <span class="bg-gray-600 text-white px-4 py-1.5 rounded-lg">FULL</span>
                    <?php else: ?>
                        <button onclick="joinMatch(<?= $match['id'] ?>)" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg active:scale-95 transition-transform">JOIN</button>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-2">
                    <button onclick="checkRoom(<?= $match['id'] ?>)" class="bg-[#2d3748] py-2.5 rounded-xl text-xs text-indigo-300 font-bold border border-gray-700 flex items-center justify-center gap-2 active:bg-gray-700 transition">
                        <i class="fa-solid fa-key"></i> Room Details
                    </button>
                    <a href="match_details.php?id=<?= $match['id'] ?>" class="bg-[#2d3748] py-2.5 rounded-xl text-xs text-indigo-300 font-bold border border-gray-700 flex items-center justify-center gap-2 active:bg-gray-700 transition">
                        <i class="fa-solid fa-trophy"></i> Prize Pool
                    </a>
                </div>
            </div>

            <div class="bg-[#84cc16] text-black text-center py-2.5 font-bold text-xs tracking-wider timer-bar" data-time="<?= $start_time ?>">
                <i class="fa-regular fa-clock"></i> STARTS IN <span class="countdown ml-1">Loading...</span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-gray-500 mt-20">
            <i class="fa-regular fa-calendar-xmark text-4xl mb-3"></i>
            <p>No upcoming matches found for this category.</p>
        </div>
    <?php endif; ?>
</div>

<div id="customModal" class="fixed inset-0 bg-black bg-opacity-80 z-[100] hidden items-center justify-center p-5 transition-opacity">
    <div class="bg-[#1a1c29] border border-gray-700 rounded-2xl w-full max-w-sm overflow-hidden">
        <div class="p-4 border-b border-gray-700 flex justify-between items-center">
            <h3 class="font-bold flex items-center gap-2"><i class="fa-solid fa-key text-indigo-500"></i> Room Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div id="modalContent" class="p-6 text-center"></div>
        <div class="p-4">
            <button onclick="closeModal()" class="w-full bg-white text-black font-bold py-3 rounded-xl active:bg-gray-200">Close</button>
        </div>
    </div>
</div>

<script>
function startTimers() {
    const timers = document.querySelectorAll('.timer-bar');
    setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        timers.forEach(timer => {
            const startTime = parseInt(timer.getAttribute('data-time'));
            const diff = startTime - now;
            const countdownEl = timer.querySelector('.countdown');
            if (diff > 0) {
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;
                let timeString = '';
                if(hours > 0) timeString += hours + 'h : ';
                timeString += minutes + 'm : ' + seconds + 's';
                countdownEl.innerText = timeString;
            } else {
                countdownEl.innerText = "MATCH STARTED";
                timer.classList.remove('bg-[#84cc16]', 'text-black');
                timer.classList.add('bg-red-600', 'text-white');
            }
        });
    }, 1000);
}
startTimers();

const modal = document.getElementById('customModal');
const modalContent = document.getElementById('modalContent');
function showModal(html) { modalContent.innerHTML = html; modal.classList.remove('hidden'); modal.classList.add('flex'); }
function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

function checkRoom(matchId) {
    const formData = new FormData();
    formData.append('action', 'get_room_details');
    formData.append('match_id', matchId);
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

function joinMatch(matchId) {
    if(confirm("Are you sure you want to join this match? Entry fee will be deducted.")) {
        const formData = new FormData();
        formData.append('action', 'join_match');
        formData.append('match_id', matchId);
        fetch('matches.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') { alert(data.message); location.reload(); } 
            else { alert("Failed: " + data.message); }
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>