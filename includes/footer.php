<div class="bottom-nav fixed bottom-0 w-full flex justify-around items-center py-2 z-50">
    <a href="index.php" class="nav-item <?= ($current_page == 'home') ? 'active' : 'text-gray-400' ?> flex flex-col items-center p-2">
        <i class="fa-solid fa-play mb-1"></i>
        <span class="text-[10px] font-semibold">Home</span>
    </a>
    <a href="my_matches.php" class="nav-item <?= ($current_page == 'my_matches') ? 'active' : 'text-gray-400' ?> flex flex-col items-center p-2">
        <i class="fa-regular fa-calendar-days mb-1"></i>
        <span class="text-[10px] font-semibold">My Matches</span>
    </a>
    <a href="result.php" class="nav-item <?= ($current_page == 'result') ? 'active' : 'text-gray-400' ?> flex flex-col items-center p-2">
        <i class="fa-solid fa-chart-simple mb-1"></i>
        <span class="text-[10px] font-semibold">Result</span>
    </a>
    <a href="profile.php" class="nav-item <?= ($current_page == 'profile') ? 'active' : 'text-gray-400' ?> flex flex-col items-center p-2">
        <i class="fa-solid fa-user mb-1"></i>
        <span class="text-[10px] font-semibold">Profile</span>
    </a>
</div>

<a href="#" class="fixed bottom-20 right-4 bg-green-500 text-white w-12 h-12 rounded-full flex justify-center items-center shadow-lg hover:bg-green-600 transition z-50">
    <i class="fa-solid fa-comment-dots text-xl"></i>
</a>

<script src="assets/js/main.js"></script>
</body>
</html>