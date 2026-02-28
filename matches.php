<?php
session_start();
require_once 'includes/db.php';

// অটো লগইন চেক
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
}
$user_id = $_SESSION['user_id'];

// ==========================================
// ⚙️ BACKEND LOGIC (AJAX API)
// ==========================================
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
            echo json_encode(['status' => 'error', 'message' => "আপনি এই ম্যাচে এখনও জয়েন করেননি!"]);
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
            echo json_encode(['status' => 'error', 'message' => 'আপনি ইতিমধ্যে জয়েন করেছেন!']);
        } elseif ($match['joined'] >= $match['total_slots']) {
            echo json_encode(['status' => 'error', 'message' => 'ম্যাচটি ফুল হয়ে গেছে!']);
        } elseif ($user['balance'] < $match['entry_fee']) {
            echo json_encode(['status' => 'error', 'message' => 'আপনার ব্যালেন্স অপর্যাপ্ত!']);
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$match['entry_fee'], $user_id]);
                $pdo->prepare("INSERT INTO joined_matches (user_id, match_id) VALUES (?, ?)")->execute([$user_id, $match_id]);
                $pdo->prepare("UPDATE matches SET joined = joined + 1 WHERE id = ?")->execute([$match_id]);
                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'সফলভাবে জয়েন করেছেন!']);
            } catch (Exception $e) { $pdo->rollBack(); }
        }
        exit;
    }
}

$current_page = 'home';
require_once 'includes/header.php';

$category_name = isset($_GET['cat']) ? $_GET['cat'] : 'BR MATCH';

// ম্যাচ এবং ক্যাটাগরি ইমেজ ফেচ করা
$stmt = $pdo->prepare("SELECT m.*, c.image_type, c.image_path FROM matches m 
                       LEFT JOIN categories c ON m.category = c.name 
                       WHERE m.category = ? AND m.status = 'upcoming' 
                       ORDER BY m.start_time ASC");
$stmt->execute([$category_name]);
$matches = $stmt->fetchAll();
?>

<div class="p-4 pb-24">
    
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="index.php" class="text-white bg-[#1a1c29] border border-gray-700 p-2 rounded-xl w-10 h-10 flex items-center justify-center active:scale-90 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-lg font-black text-white uppercase tracking-wide"><?= htmlspecialchars($category_name) ?></h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5"><?= count($matches) ?>টি আপকামিং ম্যাচ</p>
            </div>
        </div>
        <button class="bg-indigo-600/20 text-indigo-400 border border-indigo-500/50 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase shadow-lg flex items-center gap-1">
            <i class="fa-solid fa-circle-info"></i> Rules
        </button>
    </div>

    <?php if (count($matches) > 0): ?>
        <?php foreach($matches as $match): 
            $progress = ($match['joined'] / $match['total_slots']) * 100;
            $is_full = $match['joined'] >= $match['total_slots'];
            $start_ts = strtotime($match['start_time']);
            
            // ক্যাটাগরি ইমেজ লজিক
            $img_src = 'assets/images/br.jpg'; 
            if(!empty($match['image_path'])) {
                $img_src = ($match['image_type'] == 'url') ? $match['image_path'] : 'assets/images/' . $match['image_path'];
            }
        ?>
        
        <div class="bg-[#1b1f2d] rounded-2xl border border-gray-800 mb-6 relative shadow-xl">
            
            <div class="absolute top-0 right-0 bg-indigo-600 text-white text-[11px] font-black px-4 py-1.5 rounded-bl-2xl rounded-tr-2xl z-10 shadow-md tracking-wider">
                ম্যাচ #<?= $match['id'] ?>
            </div>

            <div class="p-4 pt-5">
                
                <div class="flex items-center gap-3 mb-5 mt-1">
                    <div class="w-12 h-12 rounded-xl overflow-hidden border border-gray-600/50 bg-[#1a1c29] shrink-0 shadow-inner">
                        <img src="<?= htmlspecialchars($img_src) ?>" alt="Icon" class="w-full h-full object-cover p-0.5 rounded-xl">
                    </div>
                    
                    <div class="flex-1 pr-16">
                        <h3 class="font-bold text-white text-[14px] leading-tight mb-1"><?= htmlspecialchars($match['title']) ?></h3>
                        <p class="text-[11px] text-orange-400 font-bold flex items-center gap-1">
                            <i class="fa-regular fa-clock"></i> <?= date('d M, Y - h:i A', $start_ts) ?>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center mb-5">
                    <div class="border border-gray-700/60 rounded-xl py-2 bg-[#232736]/50">
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-1">বিজয়ী</p>
                        <p class="font-black text-lg text-white">৳<?= $match['win_prize'] ?></p>
                    </div>
                    <div class="border border-gray-700/60 rounded-xl py-2 bg-[#232736]/50">
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-1">প্রতি কিলে</p>
                        <p class="font-black text-lg text-white">৳<?= $match['per_kill'] ?></p>
                    </div>
                    <div class="border border-indigo-500/30 rounded-xl py-2 bg-[#232736]/50">
                        <p class="text-[9px] text-indigo-300 font-bold uppercase tracking-wider mb-1">এন্ট্রি ফি</p>
                        <p class="font-black text-lg text-white">৳<?= $match['entry_fee'] ?></p>
                    </div>
                </div>

                <div class="relative w-full h-2.5 bg-[#2a2f45] rounded-full overflow-hidden mb-3 border border-gray-800">
                    <div class="absolute h-full bg-gradient-to-r from-orange-600 to-yellow-500 rounded-full transition-all duration-1000" style="width: <?= $progress ?>%;"></div>
                </div>
                
                <div class="flex justify-between items-center mb-5">
                    <span class="text-[11px] font-bold text-gray-400">আর মাত্র <span class="text-orange-400"><?= $match['total_slots'] - $match['joined'] ?></span> টি সিট খালি</span>
                    <span class="text-[11px] font-bold text-white bg-gray-800 px-2 py-0.5 rounded"><?= $match['joined'] ?>/<?= $match['total_slots'] ?></span>
                    
                    <?php if($is_full): ?>
                        <button class="bg-red-600/20 text-red-500 border border-red-500/50 px-5 py-2 rounded-xl text-xs font-bold cursor-not-allowed">FULL</button>
                    <?php else: ?>
                        <button onclick="joinMatch(<?= $match['id'] ?>, <?= $match['entry_fee'] ?>)" class="bg-[#5a4bda] text-white px-6 py-2.5 rounded-xl text-xs font-bold active:scale-95 transition-transform uppercase shadow-lg shadow-indigo-600/30 tracking-widest">Join</button>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button onclick="checkRoom(<?= $match['id'] ?>)" class="bg-[#2a2f45] py-3 rounded-xl text-[11px] text-indigo-300 font-black flex items-center justify-center gap-2 hover:bg-gray-700 transition">
                        <i class="fa-solid fa-key"></i> রুম আইডি
                    </button>
                    <a href="match_details.php?id=<?= $match['id'] ?>" class="bg-[#2a2f45] py-3 rounded-xl text-[11px] text-indigo-300 font-black flex items-center justify-center gap-2 hover:bg-gray-700 transition">
                        <i class="fa-solid fa-trophy"></i> পুরস্কার ও নিয়ম
                    </a>
                </div>
            </div>

            <div class="bg-[#84cc16] text-black text-center py-2.5 font-black text-[11px] tracking-widest timer-bar uppercase rounded-b-2xl shadow-inner" data-time="<?= $start_ts ?>">
                <i class="fa-regular fa-clock mr-1"></i> Starts in <span class="countdown ml-1">...</span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-gray-500 mt-28">
            <i class="fa-solid fa-ghost text-6xl mb-4 opacity-30"></i>
            <p class="font-black text-sm tracking-widest uppercase">এই মুহূর্তে কোনো ম্যাচ নেই</p>
            <p class="text-xs mt-2">অ্যাডমিন নতুন ম্যাচ দিলে এখানে শো করবে।</p>
        </div>
    <?php endif; ?>
</div>

<div id="customModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-[100] hidden items-center justify-center p-6 transition-all">
    <div class="bg-[#1a1c29] border border-gray-800 rounded-[2rem] w-full max-w-sm overflow-hidden shadow-2xl">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center bg-[#2d324a]/20">
            <h3 class="font-black text-white flex items-center gap-2 tracking-wide"><i class="fa-solid fa-shield-halved text-indigo-500"></i> রুম ইনফরমেশন</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-white transition"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div id="modalContent" class="p-8 text-center"></div>
        <div class="p-5 bg-[#2d324a]/10">
            <button onclick="closeModal()" class="w-full bg-white text-black font-black py-3.5 rounded-xl active:bg-gray-200 transition uppercase shadow-lg tracking-widest">Close</button>
        </div>
    </div>
</div>

<script>
// Live Timer JS
function initTimers() {
    const bars = document.querySelectorAll('.timer-bar');
    setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        bars.forEach(bar => {
            const start = parseInt(bar.dataset.time);
            const diff = start - now;
            const display = bar.querySelector('.countdown');
            if (diff > 0) {
                const h = Math.floor(diff / 3600);
                const m = Math.floor((diff % 3600) / 60);
                const s = diff % 60;
                display.innerText = `${h}h : ${m}m : ${s}s`;
            } else {
                display.innerText = "MATCH IN PROGRESS";
                bar.classList.replace('bg-[#84cc16]', 'bg-red-600');
                bar.classList.add('text-white');
            }
        });
    }, 1000);
}
initTimers();

// Modal JS
const modal = document.getElementById('customModal');
const modalContent = document.getElementById('modalContent');
function showModal(html) { modalContent.innerHTML = html; modal.classList.remove('hidden'); modal.classList.add('flex'); }
function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

// Room Check AJAX
function checkRoom(matchId) {
    const fd = new FormData(); fd.append('action', 'get_room_details'); fd.append('match_id', matchId);
    fetch('matches.php', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
        if(data.status === 'error') {
            showModal(`<div class="bg-gray-800/50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner"><i class="fa-solid fa-lock text-gray-500 text-3xl"></i></div><p class="text-gray-300 font-bold tracking-wide">${data.message}</p>`);
        } else {
            showModal(`
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
                <p class="text-[10px] text-orange-400 mt-4">* উপরে ক্লিক করে লেখাটি কপি করতে পারবেন।</p>
            `);
        }
    });
}

// Join Match AJAX
function joinMatch(matchId, fee) {
    if(confirm(`আপনি কি জয়েন করতে চান? আপনার অ্যাকাউন্ট থেকে ৳${fee} কেটে নেওয়া হবে।`)) {
        const fd = new FormData(); fd.append('action', 'join_match'); fd.append('match_id', matchId);
        fetch('matches.php', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
            alert(data.message); if(data.status === 'success') location.reload();
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>