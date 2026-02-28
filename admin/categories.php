<?php
require_once 'admin_header.php';
$msg = '';

// Delete Category Logic
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
    $msg = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-xl mb-4 font-bold shadow-lg'><i class='fa-solid fa-trash mr-2'></i> ক্যাটাগরি ডিলিট করা হয়েছে!</div>";
}

// ==========================================
// ➕ ADD NEW CATEGORY LOGIC
// ==========================================
if (isset($_POST['add_category'])) {
    $name = strtoupper(htmlspecialchars($_POST['name']));
    $image_type = $_POST['image_type'];
    $image_path = '';

    if ($image_type == 'url') {
        $image_path = $_POST['image_url'];
    } else {
        $upload_dir = __DIR__ . "/../assets/images/";
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $new_name = "cat_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = $new_name;
            }
        }
    }

    if (!empty($name) && !empty($image_path)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, image_type, image_path) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $image_type, $image_path])) {
            $msg = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-xl mb-4 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> নতুন ক্যাটাগরি সফলভাবে যোগ করা হয়েছে!</div>";
        }
    } else {
        $msg = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-xl mb-4 font-bold shadow-lg'><i class='fa-solid fa-triangle-exclamation mr-2'></i> অনুগ্রহ করে ছবির URL দিন অথবা ছবি আপলোড করুন।</div>";
    }
}

// ==========================================
// ✏️ UPDATE EXISTING CATEGORY LOGIC
// ==========================================
if (isset($_POST['update_category'])) {
    $cat_id = intval($_POST['cat_id']);
    $name = strtoupper(htmlspecialchars($_POST['name']));
    $image_type = $_POST['image_type'];
    
    // ডাটাবেস থেকে আগের ছবির তথ্য নিয়ে আসা
    $stmt_old = $pdo->prepare("SELECT image_type, image_path FROM categories WHERE id = ?");
    $stmt_old->execute([$cat_id]);
    $old_data = $stmt_old->fetch();
    
    $image_path = $old_data['image_path']; // ডিফল্টভাবে আগের ছবি থাকবে
    $final_image_type = $old_data['image_type'];

    // যদি নতুন URL দেয়
    if ($image_type == 'url' && !empty($_POST['image_url'])) {
        $image_path = $_POST['image_url'];
        $final_image_type = 'url';
    } 
    // যদি নতুন ছবি আপলোড করে
    elseif ($image_type == 'upload' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $upload_dir = __DIR__ . "/../assets/images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $new_name = "cat_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_name)) {
            $image_path = $new_name;
            $final_image_type = 'upload';
        }
    }

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, image_type = ?, image_path = ? WHERE id = ?");
    if ($stmt->execute([$name, $final_image_type, $image_path, $cat_id])) {
        $_SESSION['success_msg'] = "ক্যাটাগরি সফলভাবে আপডেট হয়েছে!";
        // রিলোড এড়ানোর জন্য রিডাইরেক্ট
        echo "<script>window.location.href='categories.php';</script>";
        exit;
    } else {
        $msg = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-xl mb-4 font-bold'><i class='fa-solid fa-triangle-exclamation mr-2'></i> আপডেট করতে সমস্যা হয়েছে!</div>";
    }
}

// সেশন থেকে সাকসেস মেসেজ দেখানো
if (isset($_SESSION['success_msg'])) {
    $msg = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-xl mb-4 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> " . $_SESSION['success_msg'] . "</div>";
    unset($_SESSION['success_msg']);
}

// Edit Mode চেক করা
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch();
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-6xl mx-auto pb-10 px-2 sm:px-0">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
        <h2 class="text-xl font-bold uppercase tracking-wider"><i class="fa-solid fa-layer-group text-indigo-500 mr-2"></i> ক্যাটাগরি ম্যানেজমেন্ট</h2>
        <?php if($edit_mode): ?>
            <a href="categories.php" class="bg-gray-700 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-gray-600 transition shadow-lg w-full sm:w-auto text-center"><i class="fa-solid fa-plus mr-1"></i> নতুন যোগ করুন</a>
        <?php endif; ?>
    </div>
    
    <?= $msg ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 bg-[#1a1c29] p-6 rounded-3xl border <?= $edit_mode ? 'border-indigo-500 shadow-indigo-500/20' : 'border-gray-700 shadow-xl' ?> h-fit relative">
            <?php if($edit_mode): ?>
                <div class="absolute -top-3 right-6 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-lg">এডিটিং মোড</div>
            <?php endif; ?>

            <h3 class="font-bold mb-5 text-indigo-400 border-b border-gray-700 pb-3">
                <?= $edit_mode ? '<i class="fa-solid fa-pen-to-square mr-2"></i> ক্যাটাগরি আপডেট করুন' : '<i class="fa-solid fa-square-plus mr-2"></i> নতুন ক্যাটাগরি' ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="cat_id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-[11px] text-gray-400 font-bold mb-1 uppercase">গেমের নাম / ক্যাটাগরি</label>
                    <input type="text" name="name" value="<?= $edit_mode ? htmlspecialchars($edit_data['name']) : '' ?>" required placeholder="যেমন: LUDO KING" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-white uppercase outline-none focus:border-indigo-500 transition font-bold">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-400 font-bold mb-2 uppercase">ছবির ধরণ সিলেক্ট করুন</label>
                    <div class="flex gap-4 bg-[#2d324a]/30 p-2 rounded-xl border border-gray-700">
                        <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer text-sm font-bold text-gray-300">
                            <input type="radio" name="image_type" value="upload" <?= (!$edit_mode || $edit_data['image_type'] == 'upload') ? 'checked' : '' ?> onchange="toggleImageInput('upload')" class="accent-indigo-500 w-4 h-4">
                            Upload (PC)
                        </label>
                        <div class="w-px bg-gray-600"></div>
                        <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer text-sm font-bold text-gray-300">
                            <input type="radio" name="image_type" value="url" <?= ($edit_mode && $edit_data['image_type'] == 'url') ? 'checked' : '' ?> onchange="toggleImageInput('url')" class="accent-indigo-500 w-4 h-4">
                            Image URL
                        </label>
                    </div>
                </div>

                <div id="upload_div" class="<?= ($edit_mode && $edit_data['image_type'] == 'url') ? 'hidden' : '' ?>">
                    <label class="block text-[11px] text-gray-400 font-bold mb-1 uppercase">ছবি আপলোড করুন <?= $edit_mode ? '(না দিলে আগেরটাই থাকবে)' : '' ?></label>
                    <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-bold hover:file:bg-indigo-500 cursor-pointer bg-[#2d324a]/30 border border-gray-700 rounded-xl p-1">
                </div>

                <div id="url_div" class="<?= (!$edit_mode || $edit_data['image_type'] == 'upload') ? 'hidden' : '' ?>">
                    <label class="block text-[11px] text-gray-400 font-bold mb-1 uppercase">ছবির লিংক (URL) দিন <?= $edit_mode ? '(না দিলে আগেরটাই থাকবে)' : '' ?></label>
                    <input type="url" name="image_url" value="<?= ($edit_mode && $edit_data['image_type'] == 'url') ? htmlspecialchars($edit_data['image_path']) : '' ?>" placeholder="https://example.com/image.jpg" class="w-full bg-[#2d324a]/30 border border-gray-700 p-3 rounded-xl text-white outline-none focus:border-indigo-500 transition text-sm">
                </div>
                
                <?php if($edit_mode): ?>
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <a href="categories.php" class="text-center w-full bg-gray-700 text-white font-bold py-3.5 rounded-xl hover:bg-gray-600 transition">ক্যানসেল</a>
                        <button type="submit" name="update_category" class="w-full bg-green-600 text-white font-bold py-3.5 rounded-xl hover:bg-green-500 active:scale-95 transition shadow-lg shadow-green-600/30">সেভ করুন</button>
                    </div>
                <?php else: ?>
                    <button type="submit" name="add_category" class="w-full bg-indigo-600 text-white font-black py-3.5 rounded-xl hover:bg-indigo-500 active:scale-95 transition shadow-lg shadow-indigo-600/30 tracking-widest uppercase mt-4">
                        যোগ করুন
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <div class="lg:col-span-2 bg-[#1a1c29] p-6 rounded-3xl border border-gray-700 shadow-xl">
            <h3 class="font-bold mb-5 text-indigo-400 border-b border-gray-700 pb-3"><i class="fa-solid fa-list-ul mr-2"></i> সব ক্যাটাগরি লিস্ট</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach($categories as $cat): 
                    $img_src = ($cat['image_type'] == 'url') ? $cat['image_path'] : '../assets/images/' . $cat['image_path'];
                ?>
                <div class="bg-[#2d324a]/30 border border-gray-700 rounded-2xl p-3 flex items-center gap-4 relative overflow-hidden group hover:border-indigo-500 transition shadow-sm hover:shadow-md hover:shadow-indigo-500/10">
                    
                    <div class="w-16 h-12 rounded-lg overflow-hidden shrink-0 shadow-inner bg-gray-900 border border-gray-700">
                        <img src="<?= htmlspecialchars($img_src) ?>" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/150'">
                    </div>
                    
                    <div class="flex-1 pr-14">
                        <h4 class="font-black text-sm text-white uppercase tracking-wide leading-tight"><?= htmlspecialchars($cat['name']) ?></h4>
                        <span class="text-[9px] bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 px-2 py-0.5 rounded-md uppercase font-bold mt-1 inline-block"><?= $cat['image_type'] ?></span>
                    </div>

                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex gap-1.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity bg-[#1a1c29] p-1 rounded-xl shadow-lg border border-gray-700">
                        <a href="?edit=<?= $cat['id'] ?>" class="bg-blue-500/20 text-blue-400 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-blue-500 hover:text-white transition" title="এডিট করুন">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </a>
                        <a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('আপনি কি এই ক্যাটাগরি ডিলিট করতে চান?')" class="bg-red-500/20 text-red-400 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-500 hover:text-white transition" title="ডিলিট করুন">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if(count($categories) == 0): ?>
                <div class="text-center text-gray-500 py-12">
                    <i class="fa-solid fa-folder-open text-5xl mb-3 opacity-30"></i>
                    <p class="font-bold text-sm tracking-widest uppercase">কোনো ক্যাটাগরি নেই</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function toggleImageInput(type) {
    if(type === 'upload') {
        document.getElementById('upload_div').classList.remove('hidden');
        document.getElementById('url_div').classList.add('hidden');
    } else {
        document.getElementById('upload_div').classList.add('hidden');
        document.getElementById('url_div').classList.remove('hidden');
    }
}
</script>

</div></div></body></html>