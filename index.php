<?php
session_start();
require_once 'includes/db.php';

$current_page = 'home';
require_once 'includes/header.php';

// ১. প্রতিটি ক্যাটাগরিতে কয়টি ম্যাচ আছে তার লজিক
$counts = [];
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM matches WHERE status = 'upcoming' GROUP BY category");
while ($row = $stmt->fetch()) {
    $counts[$row['category']] = $row['count'];
}

// ২. ডাটাবেস থেকে সেটিংস (লোগো ও নোটিশের জন্য) আনা
$set_stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $set_stmt->fetch();

// ৩. আনলিমিটেড স্লাইডার ফেচ করা
$sliders = $pdo->query("SELECT * FROM sliders ORDER BY id DESC")->fetchAll();

// ৪. ডায়নামিক ক্যাটাগরি আনা
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<div class="p-4 pb-24">
    
    <div class="swiper mySwiper w-full h-44 rounded-xl overflow-hidden mb-5 shadow-lg border border-gray-700/50">
        <div class="swiper-wrapper">
            
            <?php if(count($sliders) > 0): ?>
                <?php foreach($sliders as $slider): 
                    $slide_img = ($slider['image_type'] == 'url') ? $slider['image_path'] : 'assets/images/' . $slider['image_path'];
                ?>
                <div class="swiper-slide relative">
                    <img src="<?= htmlspecialchars($slide_img) ?>" alt="Banner" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/600x200/1a1c29/FFFFFF?text=Banner'">
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="swiper-slide relative bg-gray-800 flex items-center justify-center">
                    <p class="text-gray-400 font-bold text-sm">Add sliders from Admin Panel</p>
                </div>
            <?php endif; ?>

        </div>
        <div class="swiper-pagination"></div>
    </div>

    <div class="bg-[#5a4bda] text-white rounded-xl p-2 flex items-center mb-6 shadow-md">
        <div class="bg-white/20 w-7 h-7 rounded-full flex items-center justify-center mr-3 shrink-0">
            <i class="fa-solid fa-info text-xs"></i>
        </div>
        <marquee behavior="scroll" direction="left" class="text-[13px] font-medium mt-0.5">
            <?= htmlspecialchars($settings['notice_text']) ?>
        </marquee>
    </div>

    <h2 class="text-center text-lg font-extrabold mb-4 tracking-wide text-white uppercase">FREE FIRE</h2>

    <div class="grid grid-cols-2 gap-3 md:gap-4">
        
        <?php foreach($categories as $cat): 
            $img_src = ($cat['image_type'] == 'url') ? $cat['image_path'] : 'assets/images/' . $cat['image_path'];
            $match_count = isset($counts[$cat['name']]) ? $counts[$cat['name']] : 0;
        ?>
        
        <a href="matches.php?cat=<?= urlencode($cat['name']) ?>" class="bg-[#1a1c29] rounded-xl overflow-hidden border border-gray-700/60 shadow-lg block active:scale-95 transition-transform group">
            <div class="aspect-video w-full overflow-hidden bg-gray-800">
                <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.src='https://via.placeholder.com/300x150/2d3748/FFFFFF?text=<?= urlencode($cat['name']) ?>'">
            </div>
            
            <div class="p-3 bg-[#1a1c29]">
                <h3 class="font-bold text-white text-[14px] uppercase truncate leading-tight"><?= htmlspecialchars($cat['name']) ?></h3>
                <p class="text-[11px] text-gray-400 mt-1"><?= $match_count ?> matches found</p>
            </div>
        </a>
        
        <?php endforeach; ?>

        <?php if(count($categories) == 0): ?>
            <div class="col-span-2 text-center text-gray-500 py-10 border border-dashed border-gray-700 rounded-xl">
                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                <p>No categories found.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
  var swiper = new Swiper(".mySwiper", {
    loop: true,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },
    pagination: {
      el: ".swiper-pagination",
      dynamicBullets: true,
    },
  });
</script>

<style>
/* Swiper Custom Dot Colors */
.swiper-pagination-bullet { background: #fff; opacity: 0.5; }
.swiper-pagination-bullet-active { background: #4f46e5; opacity: 1; }
</style>

<?php require_once 'includes/footer.php'; ?>