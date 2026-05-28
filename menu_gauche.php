<aside class="w-64 shrink-0 bg-[#18181B] border-r border-[#27272A] flex flex-col sticky top-0 h-screen overflow-y-auto">
    <div class="px-6 py-8 border-b border-[#27272A]">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-[#A78BFA]/10 rounded-xl border border-[#A78BFA]/30 flex items-center justify-center text-lg">🎵</div>
            <h1 class="text-sm font-bold text-[#F5F5F7] leading-tight">Campus Melody</h1>
        </div>
    </div>
    
    <nav class="flex-1 px-3 py-4">
        <p class="text-[10px] font-semibold text-[#A1A1AA] uppercase tracking-widest px-3 mb-3">Pages</p>
        <div class="space-y-1">
            <?php
            // Fonction pour gérer la classe active
            function isActive($page) {
                return strpos($_SERVER['PHP_SELF'], $page) !== false ? 'bg-[#A78BFA] text-[#0B0B0F] font-bold' : 'text-[#A1A1AA] hover:text-[#F5F5F7] hover:bg-[#27272A]';
            }
            ?>
            <a href="accueil.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('accueil.php') ?>">
                <i data-lucide="home" class="w-4 h-4"></i><span class="text-sm font-medium">Accueil</span>
            </a>
            <a href="catalogue.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('catalogue.php') ?>">
                <i data-lucide="calendar" class="w-4 h-4"></i><span class="text-sm font-medium">Catalogue</span>
            </a>
            <a href="reservations.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('reservations.php') ?>">
                <i data-lucide="settings" class="w-4 h-4"></i><span class="text-sm font-medium">Réservations</span>
            </a>
            <a href="evenements.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('evenements.php') ?>">
                <i data-lucide="music" class="w-4 h-4"></i><span class="text-sm font-medium">Evénements</span>
            </a>
            <a href="profil.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('profil.php') ?>">
                <i data-lucide="user" class="w-4 h-4"></i><span class="text-sm font-medium">Profil</span>
            </a>
            <a href="notifications.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('notifications.php') ?>">
                <i data-lucide="bell" class="w-4 h-4"></i><span class="text-sm font-medium">Notifications</span>
            </a>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="tableau_bord_admin.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('tableau_bord_admin.php') ?>">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i><span class="text-sm font-medium">Admin</span>
                </a>
            <?php endif; ?>
            <a href="forum.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= isActive('forum.php') ?>">
                <i data-lucide="message-square" class="w-4 h-4"></i><span class="text-sm font-medium">Forums</span>
            </a>
        </div>
    </nav>
</aside>
