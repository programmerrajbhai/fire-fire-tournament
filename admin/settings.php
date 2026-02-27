<?php
require_once 'admin_header.php';
$msg = '';

// 1. Delete Slider Logic
if (isset($_GET['del_slider'])) {
    $del_id = intval($_GET['del_slider']);
    $pdo->prepare("DELETE FROM sliders WHERE id = ?")->execute([$del_id]);
    $msg = "<div class='bg-red-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-trash mr-2'></i> Slider Deleted!</div>";
}

// 2. Add New Slider Logic
if (isset($_POST['add_slider'])) {
    $image_type = $_POST['slider_type'];
    $image_path = '';

    if ($image_type == 'url' && !empty($_POST['slider_url'])) {
        $image_path = $_POST['slider_url'];
    } elseif ($image_type == 'upload' && isset($_FILES['slider_file']) && $_FILES['slider_file']['error'] == 0) {
        $upload_dir = "../assets/images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['slider_file']["name"], PATHINFO_EXTENSION);
        $new_name = "slider_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['slider_file']["tmp_name"], $upload_dir . $new_name)) {
            $image_path = $new_name;
        }
    }

    if (!empty($image_path)) {
        $stmt = $pdo->prepare("INSERT INTO sliders (image_type, image_path) VALUES (?, ?)");
        $stmt->execute([$image_type, $image_path]);
        $msg = "<div class='bg-green-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> New Slider Added Successfully!</div>";
    } else {
        $msg = "<div class='bg-red-600 p-3 rounded mb-4 font-bold shadow-lg'>Please provide image URL or Upload an image.</div>";
    }
}

// 3. Update General Settings (Logo & Notice)
if (isset($_POST['update_settings'])) {
    $notice = $_POST['notice_text'];
    
    $stmt_old = $pdo->query("SELECT logo FROM settings WHERE id = 1");
    $old_data = $stmt_old->fetch();
    $final_logo = $old_data['logo'];

    $logo_type = $_POST['logo_type'];
    if ($logo_type == 'url' && !empty($_POST['logo_url'])) {
        $final_logo = $_POST['logo_url'];
    } elseif ($logo_type == 'upload' && isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] == 0) {
        $upload_dir = "../assets/images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['logo_file']["name"], PATHINFO_EXTENSION);
        $new_name = "logo_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['logo_file']["tmp_name"], $upload_dir . $new_name)) {
            $final_logo = $new_name;
        }
    }

    $stmt = $pdo->prepare("UPDATE settings SET notice_text = ?, logo = ? WHERE id = 1");
    if ($stmt->execute([$notice, $final_logo])) {
        $msg = "<div class='bg-green-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> Settings updated successfully!</div>";
    }
}

// Fetch current data
$stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt->fetch();
$logo_src = (strpos($settings['logo'], 'http') === 0) ? $settings['logo'] : '../assets/images/' . $settings['logo'];
$sliders = $pdo->query("SELECT * FROM sliders ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-6xl pb-10">
    <h2 class="text-xl font-bold mb-4">App Settings & Sliders</h2>
    <?= $msg ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="bg-[#1a1c29] p-6 rounded-xl border border-gray-700 shadow-xl">
                <h3 class="font-bold mb-4 text-indigo-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-bullhorn mr-2"></i> Notice Text</h3>
                <textarea name="notice_text" rows="2" class="w-full bg-gray-800 border border-gray-700 p-3 rounded-lg text-white outline-none focus:border-indigo-500" required><?= htmlspecialchars($settings['notice_text']) ?></textarea>
            </div>

            <div class="bg-[#1a1c29] p-6 rounded-xl border border-gray-700 shadow-xl">
                <h3 class="font-bold mb-4 text-indigo-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-circle-user mr-2"></i> Main Logo</h3>
                <div class="flex items-center gap-4 mb-4">
                    <img src="<?= htmlspecialchars($logo_src) ?>" class="h-16 w-16 object-cover rounded-full bg-gray-800 border border-gray-600">
                    <div class="flex gap-4">
                        <label class="cursor-pointer text-sm font-bold text-gray-400"><input type="radio" name="logo_type" value="upload" checked onchange="toggleInput('logo', 'upload')" class="mr-1 accent-indigo-500"> Upload PC</label>
                        <label class="cursor-pointer text-sm font-bold text-gray-400"><input type="radio" name="logo_type" value="url" onchange="toggleInput('logo', 'url')" class="mr-1 accent-indigo-500"> Use URL</label>
                    </div>
                </div>
                <div id="logo_upload_div"><input type="file" name="logo_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-800 file:text-white cursor-pointer"></div>
                <div id="logo_url_div" class="hidden"><input type="url" name="logo_url" placeholder="Paste Logo Image URL..." class="w-full bg-gray-800 border border-gray-700 p-2 rounded text-white text-sm outline-none focus:border-indigo-500"></div>
            </div>
            
            <button type="submit" name="update_settings" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-500 active:scale-95 transition shadow-lg text-lg">
                <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Update Settings
            </button>
        </form>


        <div>
            <form method="POST" enctype="multipart/form-data" class="bg-[#1a1c29] p-6 rounded-xl border border-gray-700 shadow-xl mb-6">
                <h3 class="font-bold mb-4 text-emerald-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-images mr-2"></i> Add New Slider</h3>
                <div class="flex gap-4 mb-4">
                    <label class="cursor-pointer text-sm font-bold text-gray-400"><input type="radio" name="slider_type" value="upload" checked onchange="toggleInput('slider', 'upload')" class="mr-1 accent-emerald-500"> Upload PC</label>
                    <label class="cursor-pointer text-sm font-bold text-gray-400"><input type="radio" name="slider_type" value="url" onchange="toggleInput('slider', 'url')" class="mr-1 accent-emerald-500"> Image URL</label>
                </div>
                <div id="slider_upload_div"><input type="file" name="slider_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-800 file:text-white cursor-pointer"></div>
                <div id="slider_url_div" class="hidden"><input type="url" name="slider_url" placeholder="Paste Banner Image URL..." class="w-full bg-gray-800 border border-gray-700 p-2 rounded text-white text-sm outline-none focus:border-emerald-500"></div>
                
                <button type="submit" name="add_slider" class="w-full bg-emerald-600 text-white font-bold py-2 rounded-lg hover:bg-emerald-500 active:scale-95 transition mt-4">
                    <i class="fa-solid fa-plus mr-1"></i> Add to Slider
                </button>
            </form>

            <div class="bg-[#1a1c29] p-6 rounded-xl border border-gray-700 shadow-xl">
                <h3 class="font-bold mb-4 text-gray-300 border-b border-gray-700 pb-2">Active Sliders (<?= count($sliders) ?>)</h3>
                <div class="space-y-3">
                    <?php if(count($sliders) > 0): ?>
                        <?php foreach($sliders as $slide): 
                            $slide_src = ($slide['image_type'] == 'url') ? $slide['image_path'] : '../assets/images/' . $slide['image_path'];
                        ?>
                        <div class="flex items-center gap-3 bg-gray-800 p-2 rounded-lg border border-gray-700">
                            <img src="<?= htmlspecialchars($slide_src) ?>" class="w-20 h-10 object-cover rounded bg-gray-900" onerror="this.src='https://via.placeholder.com/150'">
                            <div class="flex-1 text-[10px] text-gray-400 truncate"><?= htmlspecialchars($slide_src) ?></div>
                            <a href="?del_slider=<?= $slide['id'] ?>" onclick="return confirm('Delete this slider?')" class="bg-red-500/10 text-red-500 p-2 rounded hover:bg-red-500 hover:text-white transition">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 text-center py-4">No sliders added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function toggleInput(field, type) {
    if(type === 'upload') {
        document.getElementById(field + '_upload_div').classList.remove('hidden');
        document.getElementById(field + '_url_div').classList.add('hidden');
    } else {
        document.getElementById(field + '_upload_div').classList.add('hidden');
        document.getElementById(field + '_url_div').classList.remove('hidden');
    }
}
</script>

</div></div></body></html>