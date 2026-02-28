<?php
require_once 'admin_header.php';
$msg = '';

$cat_name = isset($_GET['cat_name']) ? $_GET['cat_name'] : '';
$match_id = isset($_GET['match_id']) ? intval($_GET['match_id']) : 0;

// ==========================================
// 🚀 AUTO-PATCH DATABASE (No need for phpMyAdmin)
// ==========================================
try {
    $pdo->query("SELECT image_type FROM matches LIMIT 1");
} catch (PDOException $e) {
    // যদি matches টেবিলে ছবির কলাম না থাকে, তবে অটোমেটিক তৈরি করে নেবে
    $pdo->exec("ALTER TABLE `matches` ADD `image_type` varchar(20) NOT NULL DEFAULT 'category' AFTER `category`");
    $pdo->exec("ALTER TABLE `matches` ADD `image_path` text NULL AFTER `image_type`");
}

// ==========================================
// ⚙️ BACKEND: MATCH INFO, STATUS & LOGO UPDATE
// ==========================================
if (isset($_POST['update_match_settings'])) {
    $m_id = intval($_POST['m_id']);
    $title = htmlspecialchars($_POST['title']);
    $status = $_POST['status'];
    $image_type = $_POST['image_type']; // 'category', 'upload', 'url'
    
    // ডাটাবেস থেকে বর্তমান তথ্য আনা
    $stmt_old = $pdo->prepare("SELECT image_type, image_path FROM matches WHERE id = ?");
    $stmt_old->execute([$m_id]);
    $old_data = $stmt_old->fetch();
    
    $image_path = $old_data['image_path'];
    $final_image_type = $image_type;

    if ($image_type == 'url' && !empty($_POST['image_url'])) {
        $image_path = $_POST['image_url'];
    } elseif ($image_type == 'upload' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $upload_dir = __DIR__ . "/../assets/images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $new_name = "match_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_name)) {
            $image_path = $new_name;
        } else {
            $final_image_type = $old_data['image_type']; // Fallback
        }
    } elseif ($image_type == 'category') {
        $image_path = NULL; // ডিফল্ট ক্যাটাগরির ছবি নেবে
    }

    $stmt = $pdo->prepare("UPDATE matches SET title = ?, status = ?, image_type = ?, image_path = ? WHERE id = ?");
    if($stmt->execute([$title, $status, $final_image_type, $image_path, $m_id])) {
        $msg = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> ম্যাচ এবং লোগো সফলভাবে আপডেট হয়েছে!</div>";
    }
}

// ==========================================
// ⚙️ BACKEND: PLAYER RESULT & WALLET UPDATE
// ==========================================
if (isset($_POST['update_player_result'])) {
    $jm_id = intval($_POST['jm_id']); 
    $u_id = intval($_POST['user_id']); 
    $kills = intval($_POST['kills']);
    $prize = intval($_POST['prize_won']);
    $add_to_wallet = isset($_POST['add_to_wallet']) ? true : false;

    try {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE joined_matches SET kills = ?, prize_won = ? WHERE id = ?")->execute([$kills, $prize, $jm_id]);

        if ($add_to_wallet && $prize > 0) {
            $pdo->prepare("UPDATE users SET balance = balance + ?, winning_balance = winning_balance + ? WHERE id = ?")->execute([$prize, $prize, $u_id]);
            $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, trx_id, status) VALUES (?, 'prize_won', 'system', ?, 'MATCH_WIN', 'approved')")->execute([$u_id, $prize]);
            $msg = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-trophy mr-2'></i> রেজাল্ট সেভ হয়েছে এবং ৳$prize ওয়ালেটে অ্যাড হয়েছে!</div>";
        } else {
            $msg = "<div class='bg-indigo-500/20 border border-indigo-500 text-indigo-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-check mr-2'></i> রেজাল্ট আপডেট হয়েছে (ওয়ালেটে অ্যাড হয়নি)।</div>";
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-xl mb-6 font-bold shadow-lg'><i class='fa-solid fa-triangle-exclamation mr-2'></i> আপডেট করতে সমস্যা হয়েছে!</div>";
    }
}
?>

<div class="max-w-7xl mx-auto pb-10 px-2 sm:px-0">
    <?= $msg ?>

    <?php 
    // =========================================================================
    // 🟢 LEVEL 3: MANAGE SPECIFIC MATCH (PLAYER LIST & SETTINGS)
    // =========================================================================
    if ($match_id > 0): 
        $stmt = $pdo->prepare("SELECT m.*, m.image_type as m_img_type, m.image_path as m_img_path, c.image_type as c_img_type, c.image_path as c_img_path FROM matches m LEFT JOIN categories c ON m.category = c.name WHERE m.id = ?");
        $stmt->execute([$match_id]);
        $match_info = $stmt->fetch();

        if($match_info):
            // 🖼️ Smart Image Logic (Match Logo vs Category Logo)
            $img_src = 'https://via.placeholder.com/150';
            if ($match_info['m_img_type'] == 'url' || $match_info['m_img_type'] == 'upload') {
                $img_src = ($match_info['m_img_type'] == 'url') ? $match_info['m_img_path'] : '../assets/images/' . $match_info['m_img_path'];
            } elseif (!empty($match_info['c_img_path'])) {
                $img_src = ($match_info['c_img_type'] == 'url') ? $match_info['c_img_path'] : '../assets/images/' . $match_info['c_img_path'];
            }

            $p_stmt = $pdo->prepare("SELECT u.id as user_id, u.name, u.ff_uid, jm.id as jm_id, jm.join_time, jm.kills, jm.prize_won FROM joined_matches jm JOIN users u ON jm.user_id = u.id WHERE jm.match_id = ? ORDER BY jm.prize_won DESC, jm.id ASC");
            $p_stmt->execute([$match_id]);
            $participants = $p_stmt->fetchAll();
    ?>
        
        <div class="flex items-center mb-6">
            <a href="?cat_name=<?= urlencode($match_info['category']) ?>" class="text-white bg-[#1a1c29] border border-gray-700 p-2.5 rounded-xl w-10 h-10 flex items-center justify-center hover:bg-gray-800 transition mr-4 shadow-lg">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold uppercase tracking-wider text-white">ম্যাচ ম্যানেজমেন্ট</h2>
                <p class="text-xs text-indigo-400 font-bold uppercase tracking-widest mt-0.5">ম্যাচ আইডি: #<?= $match_info['id'] ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-[#1a1c29] border border-gray-700 rounded-3xl p-6 shadow-xl flex flex-col sm:flex-row items-center gap-5 relative overflow-hidden text-center sm:text-left">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-600/20 blur-3xl rounded-full"></div>
                
                <div class="w-28 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border border-gray-600 bg-gray-900 shrink-0 shadow-inner z-10 mx-auto sm:mx-0">
                    <img src="<?= htmlspecialchars($img_src) ?>" class="w-full h-full object-cover p-1 rounded-2xl" onerror="this.src='https://via.placeholder.com/150'">
                </div>
                <div class="flex-1 z-10">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1"><?= htmlspecialchars($match_info['category']) ?></p>
                    <h3 class="font-black text-xl text-white leading-tight mb-3"><?= htmlspecialchars($match_info['title']) ?></h3>
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 sm:gap-3 text-[11px] font-bold">
                        <span class="bg-[#2d324a]/50 text-gray-300 px-3 py-1.5 rounded-lg border border-gray-700"><i class="fa-regular fa-clock text-orange-400 mr-1"></i> <?= date('d M, h:i A', strtotime($match_info['start_time'])) ?></span>
                        <span class="bg-[#2d324a]/50 text-gray-300 px-3 py-1.5 rounded-lg border border-gray-700"><i class="fa-solid fa-users text-indigo-400 mr-1"></i> <?= $match_info['joined'] ?>/<?= $match_info['total_slots'] ?></span>
                        <span class="bg-[#2d324a]/50 text-gray-300 px-3 py-1.5 rounded-lg border border-gray-700"><i class="fa-solid fa-trophy text-yellow-500 mr-1"></i> ৳<?= $match_info['win_prize'] ?></span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 bg-[#1a1c29] border border-gray-700 rounded-3xl p-6 shadow-xl h-fit">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-3"><i class="fa-solid fa-pen-to-square text-indigo-400 mr-2"></i> এডিট ম্যাচ ও লোগো</h3>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="m_id" value="<?= $match_info['id'] ?>">
                    
                    <div>
                        <label class="block text-[10px] text-gray-500 font-bold mb-1 uppercase tracking-widest">ম্যাচের নাম</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($match_info['title']) ?>" required class="w-full bg-[#2d324a]/50 border border-gray-700 p-2.5 rounded-xl text-white outline-none focus:border-indigo-500 font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-[10px] text-gray-500 font-bold mb-1 uppercase tracking-widest">স্ট্যাটাস</label>
                        <select name="status" class="w-full bg-[#2d324a]/50 border border-gray-700 p-2.5 rounded-xl text-white outline-none focus:border-indigo-500 font-bold text-sm appearance-none cursor-pointer">
                            <option value="upcoming" <?= ($match_info['status'] == 'upcoming') ? 'selected' : '' ?>>Upcoming (আপকামিং)</option>
                            <option value="running" <?= ($match_info['status'] == 'running') ? 'selected' : '' ?>>Running (চলছে)</option>
                            <option value="completed" <?= ($match_info['status'] == 'completed') ? 'selected' : '' ?>>Completed (শেষ হয়েছে)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] text-gray-500 font-bold mb-2 uppercase tracking-widest">ম্যাচ লোগো / ছবি</label>
                        <div class="grid grid-cols-1 gap-2 bg-[#2d324a]/30 p-2 rounded-xl border border-gray-700">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                                <input type="radio" name="image_type" value="category" <?= (!isset($match_info['m_img_type']) || $match_info['m_img_type'] == 'category' || empty($match_info['m_img_type'])) ? 'checked' : '' ?> onchange="toggleMatchImage('category')" class="accent-indigo-500 w-3 h-3">
                                ক্যাটাগরির ডিফল্ট ছবি
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                                <input type="radio" name="image_type" value="upload" <?= ($match_info['m_img_type'] == 'upload') ? 'checked' : '' ?> onchange="toggleMatchImage('upload')" class="accent-indigo-500 w-3 h-3">
                                নতুন আপলোড করুন (PC)
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                                <input type="radio" name="image_type" value="url" <?= ($match_info['m_img_type'] == 'url') ? 'checked' : '' ?> onchange="toggleMatchImage('url')" class="accent-indigo-500 w-3 h-3">
                                ছবির লিংক (URL)
                            </label>
                        </div>
                    </div>

                    <div id="match_upload_div" class="<?= ($match_info['m_img_type'] == 'upload') ? '' : 'hidden' ?>">
                        <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-bold hover:file:bg-indigo-500 cursor-pointer bg-[#2d324a]/50 border border-gray-700 rounded-xl p-1">
                    </div>

                    <div id="match_url_div" class="<?= ($match_info['m_img_type'] == 'url') ? '' : 'hidden' ?>">
                        <input type="url" name="image_url" value="<?= ($match_info['m_img_type'] == 'url') ? htmlspecialchars($match_info['m_img_path']) : '' ?>" placeholder="https://example.com/image.jpg" class="w-full bg-[#2d324a]/50 border border-gray-700 p-2.5 rounded-xl text-white outline-none focus:border-indigo-500 text-xs">
                    </div>

                    <button type="submit" name="update_match_settings" class="w-full bg-indigo-600 text-white font-black py-3 rounded-xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg uppercase tracking-widest text-xs mt-2">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> আপডেট করুন
                    </button>
                </form>
            </div>
        </div>

        <script>
        function toggleMatchImage(type) {
            document.getElementById('match_upload_div').classList.add('hidden');
            document.getElementById('match_url_div').classList.add('hidden');
            if(type === 'upload') document.getElementById('match_upload_div').classList.remove('hidden');
            if(type === 'url') document.getElementById('match_url_div').classList.remove('hidden');
        }
        </script>

        <div class="bg-[#1a1c29] rounded-3xl border border-gray-700 shadow-xl overflow-hidden">
            <div class="p-5 border-b border-gray-800 flex justify-between items-center bg-[#2d324a]/20">
                <h3 class="font-black text-white flex items-center gap-2 tracking-wide uppercase"><i class="fa-solid fa-users-viewfinder text-indigo-500 text-lg"></i> প্লেয়ার লিস্ট ও রেজাল্ট</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-[#2d324a]/30 text-gray-400 uppercase tracking-widest text-[10px]">
                        <tr>
                            <th class="p-4 font-bold w-12 text-center">#</th>
                            <th class="p-4 font-bold">Player Name</th>
                            <th class="p-4 font-bold text-center border-l border-gray-800">In-Game UID</th>
                            <th class="p-4 font-bold text-center border-l border-gray-800">Kills</th>
                            <th class="p-4 font-bold text-center text-green-400">Prize Won</th>
                            <th class="p-4 font-bold text-right border-l border-gray-800">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php if(count($participants) > 0): ?>
                            <?php foreach($participants as $index => $p): ?>
                            <tr class="hover:bg-gray-800/30 transition">
                                <td class="p-4 text-center">
                                    <div class="w-7 h-7 rounded-lg bg-gray-800 text-gray-400 border border-gray-700 flex items-center justify-center font-black mx-auto text-xs"><?= $index + 1 ?></div>
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-white text-[13px]"><?= htmlspecialchars($p['name']) ?></p>
                                </td>
                                <td class="p-4 text-center border-l border-gray-800">
                                    <span class="font-black text-orange-400 select-all bg-orange-500/10 px-2 py-1 rounded border border-orange-500/20"><?= htmlspecialchars($p['ff_uid']) ?></span>
                                </td>
                                <td class="p-4 text-center border-l border-gray-800 font-black text-indigo-400 text-lg"><?= $p['kills'] ?></td>
                                <td class="p-4 text-center font-black text-green-400 text-lg">৳<?= $p['prize_won'] ?></td>
                                <td class="p-4 text-right border-l border-gray-800">
                                    <button onclick="openResultModal(<?= $p['jm_id'] ?>, <?= $p['user_id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>', <?= $p['kills'] ?>, <?= $p['prize_won'] ?>)" class="bg-indigo-600/20 text-indigo-400 border border-indigo-500/50 px-4 py-2 rounded-xl text-[11px] font-bold hover:bg-indigo-600 hover:text-white transition active:scale-95 shadow-lg flex items-center gap-2 ml-auto">
                                        <i class="fa-solid fa-gift"></i> Update Result
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="p-12 text-center text-gray-500"><i class="fa-solid fa-users-slash text-5xl mb-4 opacity-30"></i><p class="font-bold text-sm tracking-widest uppercase">কোনো প্লেয়ার জয়েন করেনি</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="resultModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-[100] hidden items-center justify-center p-4 transition-all">
            <div class="bg-[#1a1c29] border border-gray-700 rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl relative">
                <button onclick="closeResultModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 bg-indigo-500/20 text-indigo-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 border border-indigo-500/30"><i class="fa-solid fa-user-astronaut"></i></div>
                        <h3 class="font-black text-white text-lg mb-1" id="modalPlayerName">Player Name</h3>
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Set Kills & Prize</p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="jm_id" id="modalJmId">
                        <input type="hidden" name="user_id" id="modalUserId">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-center focus-within:border-indigo-500 transition">
                                <label class="block text-[10px] text-indigo-400 font-bold uppercase tracking-widest mb-1">Total Kills</label>
                                <input type="number" name="kills" id="modalKills" required class="w-full bg-transparent text-center text-2xl font-black text-white outline-none">
                            </div>
                            <div class="bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-center focus-within:border-green-500 transition">
                                <label class="block text-[10px] text-green-400 font-bold uppercase tracking-widest mb-1">Prize (৳)</label>
                                <input type="number" name="prize_won" id="modalPrize" required class="w-full bg-transparent text-center text-2xl font-black text-white outline-none">
                            </div>
                        </div>
                        <div class="mb-6 bg-gray-800/50 p-3 rounded-xl border border-gray-700">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" name="add_to_wallet" value="1" class="w-5 h-5 mt-0.5 accent-green-500">
                                <span class="text-xs text-gray-300 font-medium group-hover:text-white transition">
                                    <strong class="block text-green-400 mb-0.5">Add to User's Wallet</strong>
                                    টিক দিলে প্রাইজ মানি সরাসরি ইউজারের ওয়ালেটে অ্যাড হয়ে যাবে।
                                </span>
                            </label>
                        </div>
                        <button type="submit" name="update_player_result" class="w-full bg-indigo-600 text-white font-black py-4 rounded-xl hover:bg-indigo-500 active:scale-95 transition-all shadow-lg shadow-indigo-600/30 uppercase tracking-widest text-sm">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Save Result
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function openResultModal(jmId, userId, name, kills, prize) {
                document.getElementById('modalJmId').value = jmId;
                document.getElementById('modalUserId').value = userId;
                document.getElementById('modalPlayerName').innerText = name;
                document.getElementById('modalKills').value = kills;
                document.getElementById('modalPrize').value = prize;
                document.getElementById('resultModal').classList.remove('hidden');
                document.getElementById('resultModal').classList.add('flex');
            }
            function closeResultModal() {
                document.getElementById('resultModal').classList.add('hidden');
                document.getElementById('resultModal').classList.remove('flex');
            }
        </script>

    <?php 
        endif; 
    // =========================================================================
    // 🟡 LEVEL 2: SHOW MATCHES FOR A SPECIFIC CATEGORY
    // =========================================================================
    elseif ($cat_name != ''): 
        $stmt = $pdo->prepare("SELECT m.*, m.image_type as m_img_type, m.image_path as m_img_path, c.image_type as c_img_type, c.image_path as c_img_path FROM matches m LEFT JOIN categories c ON m.category = c.name WHERE m.category = ? ORDER BY m.id DESC");
        $stmt->execute([$cat_name]);
        $matches = $stmt->fetchAll();
    ?>

        <div class="flex items-center mb-6">
            <a href="match_participants.php" class="text-white bg-[#1a1c29] border border-gray-700 p-2.5 rounded-xl w-10 h-10 flex items-center justify-center hover:bg-gray-800 transition mr-4 shadow-lg">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold uppercase tracking-wider text-white">ম্যাচ লিস্ট</h2>
                <p class="text-xs text-indigo-400 font-bold uppercase tracking-widest mt-0.5">ক্যাটাগরি: <?= htmlspecialchars($cat_name) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <?php if(count($matches) > 0): ?>
                <?php foreach($matches as $m): 
                    $img_src = 'https://via.placeholder.com/150';
                    if ($m['m_img_type'] == 'url' || $m['m_img_type'] == 'upload') {
                        $img_src = ($m['m_img_type'] == 'url') ? $m['m_img_path'] : '../assets/images/' . $m['m_img_path'];
                    } elseif (!empty($m['c_img_path'])) {
                        $img_src = ($m['c_img_type'] == 'url') ? $m['c_img_path'] : '../assets/images/' . $m['c_img_path'];
                    }
                ?>
                <div class="bg-[#1a1c29] border border-gray-700 rounded-3xl p-5 shadow-xl relative overflow-hidden group hover:border-indigo-500 transition flex flex-col justify-between">
                    
                    <div class="absolute top-0 right-0 bg-gray-800 text-gray-300 border-b border-l border-gray-700 text-[10px] font-black px-4 py-1.5 rounded-bl-2xl shadow-md uppercase tracking-widest">
                        Status: <span class="<?= $m['status'] == 'completed' ? 'text-green-400' : ($m['status'] == 'running' ? 'text-orange-400' : 'text-indigo-400') ?>"><?= $m['status'] ?></span>
                    </div>

                    <div class="flex gap-4 mb-4 mt-2">
                        <div class="w-16 h-16 rounded-xl overflow-hidden border border-gray-600 bg-gray-900 shrink-0 shadow-inner">
                            <img src="<?= htmlspecialchars($img_src) ?>" class="w-full h-full object-cover p-0.5 rounded-xl" onerror="this.src='https://via.placeholder.com/150'">
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest mb-1 mt-1">Match #<?= $m['id'] ?></p>
                            <h3 class="font-bold text-sm text-white leading-tight pr-10"><?= htmlspecialchars($m['title']) ?></h3>
                        </div>
                    </div>

                    <div class="flex gap-4 mb-5">
                        <div class="bg-gray-800/50 px-3 py-1.5 rounded-lg border border-gray-700 text-xs font-bold text-gray-300"><i class="fa-regular fa-clock text-orange-400 mr-1"></i> <?= date('d M, h:i A', strtotime($m['start_time'])) ?></div>
                        <div class="bg-gray-800/50 px-3 py-1.5 rounded-lg border border-gray-700 text-xs font-bold text-gray-300"><i class="fa-solid fa-users text-indigo-400 mr-1"></i> <?= $m['joined'] ?>/<?= $m['total_slots'] ?></div>
                    </div>
                    
                    <a href="?match_id=<?= $m['id'] ?>" class="block text-center bg-indigo-600/10 border border-indigo-500/30 text-indigo-400 font-black py-3 rounded-xl hover:bg-indigo-600 hover:text-white transition uppercase tracking-widest text-xs">
                        <i class="fa-solid fa-list-check mr-1"></i> Manage Match
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-gray-500 py-16">
                    <i class="fa-solid fa-ghost text-5xl mb-4 opacity-30"></i>
                    <p class="font-bold text-sm tracking-widest uppercase">এই ক্যাটাগরিতে কোনো ম্যাচ নেই</p>
                </div>
            <?php endif; ?>
        </div>

    <?php 
    // =========================================================================
    // 🔴 LEVEL 1: SHOW ALL CATEGORIES (DEFAULT VIEW)
    // =========================================================================
    else: 
        $categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
        $counts = [];
        $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM matches GROUP BY category");
        while ($row = $stmt->fetch()) { $counts[$row['category']] = $row['count']; }
    ?>

        <h2 class="text-2xl font-black mb-6 uppercase tracking-wider text-white border-b border-gray-800 pb-3">
            <i class="fa-solid fa-layer-group text-indigo-500 mr-2"></i> সিলেক্ট ক্যাটাগরি
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($categories as $cat): 
                $img_src = 'https://via.placeholder.com/300x150';
                if(!empty($cat['image_path'])) {
                    $img_src = ($cat['image_type'] == 'url') ? $cat['image_path'] : '../assets/images/' . $cat['image_path'];
                }
                $match_count = isset($counts[$cat['name']]) ? $counts[$cat['name']] : 0;
            ?>
            <a href="?cat_name=<?= urlencode($cat['name']) ?>" class="bg-[#1a1c29] border border-gray-700/60 rounded-3xl overflow-hidden shadow-xl hover:shadow-indigo-500/20 hover:border-indigo-500 transition-all group block active:scale-95 flex flex-col h-full">
                <div class="aspect-video w-full bg-gray-900 relative overflow-hidden">
                    <img src="<?= htmlspecialchars($img_src) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500 opacity-80 group-hover:opacity-100" onerror="this.src='https://via.placeholder.com/300x150'">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1a1c29] via-transparent to-transparent"></div>
                </div>
                <div class="p-4 text-center -mt-4 relative z-10 flex-1 flex flex-col justify-end">
                    <h3 class="font-black text-[13px] md:text-sm text-white uppercase tracking-widest drop-shadow-md mb-2 truncate"><?= htmlspecialchars($cat['name']) ?></h3>
                    <span class="inline-block bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase shadow-inner">
                        <?= $match_count ?> Matches
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
            
            <?php if(count($categories) == 0): ?>
                <div class="col-span-full text-center text-gray-500 py-16">
                    <i class="fa-solid fa-folder-open text-5xl mb-4 opacity-30"></i>
                    <p class="font-bold text-sm tracking-widest uppercase">কোনো ক্যাটাগরি নেই</p>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>
</div></div></body></html>