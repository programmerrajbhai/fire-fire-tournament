<?php
require_once 'admin_header.php';
$msg = '';

// 1. Delete Category Logic
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
    $msg = "<div class='bg-red-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-trash mr-2'></i> Category Deleted!</div>";
}

// 2. Add New Category Logic
if (isset($_POST['add_category'])) {
    $name = strtoupper(htmlspecialchars($_POST['name']));
    $image_type = $_POST['image_type'];
    $image_path = '';

    if ($image_type == 'url') {
        $image_path = $_POST['image_url'];
    } else {
        $upload_dir = "../assets/images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $new_name = "cat_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = $new_name;
            }
        }
    }

    if (!empty($name) && !empty($image_path)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, image_type, image_path) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $image_type, $image_path])) {
            $msg = "<div class='bg-green-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> Category Added Successfully!</div>";
        }
    } else {
        $msg = "<div class='bg-red-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-triangle-exclamation mr-2'></i> Please provide image URL or Upload an image.</div>";
    }
}

// 3. Update Category Logic
if (isset($_POST['update_category'])) {
    $cat_id = intval($_POST['cat_id']);
    $name = strtoupper(htmlspecialchars($_POST['name']));
    $image_type = $_POST['image_type'];
    
    // ডাটাবেস থেকে আগের ছবির তথ্য নিয়ে আসা
    $stmt_old = $pdo->prepare("SELECT image_type, image_path FROM categories WHERE id = ?");
    $stmt_old->execute([$cat_id]);
    $old_data = $stmt_old->fetch();
    
    $image_path = $old_data['image_path']; // ডিফল্টভাবে আগের ছবি থাকবে
    $final_image_type = $old_data['image_type'];

    // যদি নতুন URL দেয়
    if ($image_type == 'url' && !empty($_POST['image_url'])) {
        $image_path = $_POST['image_url'];
        $final_image_type = 'url';
    } 
    // যদি নতুন ছবি আপলোড করে
    elseif ($image_type == 'upload' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $upload_dir = "../assets/images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $new_name = "cat_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_name)) {
            $image_path = $new_name;
            $final_image_type = 'upload';
        }
    }

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, image_type = ?, image_path = ? WHERE id = ?");
    if ($stmt->execute([$name, $final_image_type, $image_path, $cat_id])) {
        $_SESSION['success_msg'] = "Category Updated Successfully!";
        header("Location: categories.php");
        exit;
    }
}

// সেশন থেকে সাকসেস মেসেজ দেখানো (Update করার পর রিলোড এড়ানোর জন্য)
if (isset($_SESSION['success_msg'])) {
    $msg = "<div class='bg-green-600 p-3 rounded mb-4 font-bold shadow-lg'><i class='fa-solid fa-circle-check mr-2'></i> " . $_SESSION['success_msg'] . "</div>";
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

<div class="max-w-6xl mx-auto pb-10">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Manage Categories</h2>
        <?php if($edit_mode): ?>
            <a href="categories.php" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-600 transition"><i class="fa-solid fa-plus mr-1"></i> Add New Category</a>
        <?php endif; ?>
    </div>
    
    <?= $msg ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="md:col-span-1 bg-[#1a1c29] p-6 rounded-xl border <?= $edit_mode ? 'border-indigo-500 shadow-indigo-500/20' : 'border-gray-700 shadow-xl' ?> h-fit relative">
            
            <?php if($edit_mode): ?>
                <div class="absolute -top-3 right-4 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded">EDITING MODE</div>
            <?php endif; ?>

            <h3 class="font-bold mb-4 text-indigo-400 border-b border-gray-700 pb-2">
                <?= $edit_mode ? '<i class="fa-solid fa-pen-to-square mr-2"></i> Update Category' : '<i class="fa-solid fa-plus mr-2"></i> Add New Category' ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="cat_id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-xs text-gray-400 font-bold mb-1">Category Name</label>
                    <input type="text" name="name" value="<?= $edit_mode ? htmlspecialchars($edit_data['name']) : '' ?>" required placeholder="e.g. LUDO KING" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white uppercase outline-none focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 font-bold mb-2">Image Source</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" name="image_type" value="upload" <?= (!$edit_mode || $edit_data['image_type'] == 'upload') ? 'checked' : '' ?> onchange="toggleImageInput('upload')" class="accent-indigo-500 w-4 h-4">
                            Upload PC
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" name="image_type" value="url" <?= ($edit_mode && $edit_data['image_type'] == 'url') ? 'checked' : '' ?> onchange="toggleImageInput('url')" class="accent-indigo-500 w-4 h-4">
                            Image URL
                        </label>
                    </div>
                </div>

                <div id="upload_div" class="<?= ($edit_mode && $edit_data['image_type'] == 'url') ? 'hidden' : '' ?>">
                    <label class="block text-xs text-gray-400 font-bold mb-1">Select Image <?= $edit_mode ? '(Leave empty to keep current)' : '' ?></label>
                    <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-700 file:text-white hover:file:bg-gray-600 cursor-pointer">
                </div>

                <div id="url_div" class="<?= (!$edit_mode || $edit_data['image_type'] == 'upload') ? 'hidden' : '' ?>">
                    <label class="block text-xs text-gray-400 font-bold mb-1">Paste Image URL <?= $edit_mode ? '(Leave empty to keep current)' : '' ?></label>
                    <input type="url" name="image_url" value="<?= ($edit_mode && $edit_data['image_type'] == 'url') ? htmlspecialchars($edit_data['image_path']) : '' ?>" placeholder="https://example.com/image.jpg" class="w-full bg-gray-800 border border-gray-700 p-2.5 rounded-lg text-white outline-none focus:border-indigo-500 transition">
                </div>
                
                <?php if($edit_mode): ?>
                    <button type="submit" name="update_category" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-500 active:scale-95 transition shadow-lg mt-2">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Save Changes
                    </button>
                    <a href="categories.php" class="block text-center w-full bg-gray-700 text-white font-bold py-3 rounded-lg hover:bg-gray-600 transition mt-2">Cancel Edit</a>
                <?php else: ?>
                    <button type="submit" name="add_category" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-500 active:scale-95 transition shadow-lg mt-2">
                        <i class="fa-solid fa-plus mr-1"></i> Add Category
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <div class="md:col-span-2 bg-[#1a1c29] p-6 rounded-xl border border-gray-700 shadow-xl">
            <h3 class="font-bold mb-4 text-indigo-400 border-b border-gray-700 pb-2"><i class="fa-solid fa-list mr-2"></i> Category List</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach($categories as $cat): 
                    $img_src = ($cat['image_type'] == 'url') ? $cat['image_path'] : '../assets/images/' . $cat['image_path'];
                ?>
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-3 flex items-center gap-4 relative overflow-hidden group hover:border-indigo-500 transition">
                    
                    <img src="<?= htmlspecialchars($img_src) ?>" class="w-16 h-12 object-cover rounded bg-gray-900 border border-gray-700" onerror="this.src='https://via.placeholder.com/150'">
                    
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-white"><?= htmlspecialchars($cat['name']) ?></h4>
                        <span class="text-[9px] bg-gray-700 px-2 py-0.5 rounded text-gray-300 uppercase"><?= $cat['image_type'] ?></span>
                    </div>

                    <div class="flex gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="?edit=<?= $cat['id'] ?>" class="bg-blue-500/20 text-blue-400 p-2.5 rounded-lg hover:bg-blue-500 hover:text-white transition" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('Delete this category?')" class="bg-red-500/20 text-red-400 p-2.5 rounded-lg hover:bg-red-500 hover:text-white transition" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if(count($categories) == 0): ?>
                <div class="text-center text-gray-500 py-10">
                    <i class="fa-solid fa-folder-open text-4xl mb-3"></i>
                    <p>No categories found.</p>
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