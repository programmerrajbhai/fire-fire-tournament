<?php
session_start();
require_once 'includes/db.php';

$current_page = 'home';
require_once 'includes/header.php';

// ডাটাবেস থেকে ডায়নামিক কাউন্ট বের করা
$counts = ['BR MATCH' => 0, 'CLASH SQUAD' => 0, 'LONE WOLF' => 0];
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM matches WHERE status = 'upcoming' GROUP BY category");
while ($row = $stmt->fetch()) {
    $counts[$row['category']] = $row['count'];
}
?>

<div class="p-4 pb-24">
    <div class="w-full h-40 bg-gray-800 rounded-xl overflow-hidden relative mb-4">
        <img src="assets/images/banner.jpg" alt="Banner" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/600x200/1a1c29/FFFFFF?text=Promotional+Banner'">
        <div class="absolute bottom-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full font-bold">
            <i class="fa-solid fa-play"></i> WATCH NOW
        </div>
    </div>

    <div class="bg-indigo-600 text-white rounded-lg p-2 flex items-center mb-6 shadow-lg">
        <i class="fa-solid fa-circle-info mr-2"></i>
        <marquee behavior="scroll" direction="left" class="text-sm font-medium">
            যে কোনো প্রয়োজনে টেলিগ্রামে জয়েন করুন এবং এডমিনকে ইনবক্স করুন। ইনশাআল্লাহ সমাধান পাবেন।
        </marquee>
    </div>

    <h2 class="text-center text-xl font-bold mb-4 tracking-wider">FREE FIRE</h2>

    <div class="grid grid-cols-2 gap-4">
        
        <a href="matches.php?cat=BR MATCH" class="bg-[#1a1c29] rounded-xl overflow-hidden border border-gray-700 shadow-lg block active:scale-95 transition-transform">
            <img src="assets/images/br.jpg" alt="BR Match" class="w-full h-24 object-cover" onerror="this.src='https://via.placeholder.com/300x150/2d3748/FFFFFF?text=BR+MATCH'">
            <div class="p-3">
                <h3 class="font-bold text-white text-md">BR MATCH</h3>
                <p class="text-xs text-indigo-400 mt-1 font-bold"><?= $counts['BR MATCH'] ?> matches found</p>
            </div>
        </a>

        <a href="matches.php?cat=CLASH SQUAD" class="bg-[#1a1c29] rounded-xl overflow-hidden border border-gray-700 shadow-lg block active:scale-95 transition-transform">
            <img src="assets/images/cs.jpg" alt="Clash Squad" class="w-full h-24 object-cover" onerror="this.src='https://via.placeholder.com/300x150/2d3748/FFFFFF?text=CLASH+SQUAD'">
            <div class="p-3">
                <h3 class="font-bold text-white text-md">CLASH SQUAD</h3>
                <p class="text-xs text-indigo-400 mt-1 font-bold"><?= $counts['CLASH SQUAD'] ?> matches found</p>
            </div>
        </a>

        <a href="matches.php?cat=LONE WOLF" class="bg-[#1a1c29] rounded-xl overflow-hidden border border-gray-700 shadow-lg block active:scale-95 transition-transform">
            <img src="assets/images/lw.jpg" alt="Lone Wolf" class="w-full h-24 object-cover" onerror="this.src='https://via.placeholder.com/300x150/2d3748/FFFFFF?text=LONE+WOLF'">
            <div class="p-3">
                <h3 class="font-bold text-white text-md">LONE WOLF</h3>
                <p class="text-xs text-indigo-400 mt-1 font-bold"><?= $counts['LONE WOLF'] ?> matches found</p>
            </div>
        </a>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>