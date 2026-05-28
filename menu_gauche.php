<?php
// On récupère le nom du fichier actuel 
$page_actuelle = basename($_SERVER['PHP_SELF']);

// On crée une fonction pour éviter de répéter le code des classes CSS
function base_class($nom_page, $page_actuelle) {
    $common = "w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ";
    
    if ($nom_page == $page_actuelle) {
        // Style VIOLET (Actif)
        return $common . "bg-[#A78BFA] text-[#0B0B0F] font-bold shadow-lg shadow-[#A78BFA]/10";
    } else {
        // Style GRIS (Inactif)
        return $common . "text-[#A1A1AA] hover:text-[#F5F5F7] hover:bg-[#27272A]";
    }
}
?>

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
            
            <a href="accueil.php" class="<?php echo base_class('accueil.php', $page_actuelle); ?>">
                <i data-lucide="home" class="w-4 h-4"></i><span class="text-sm font-medium">Accueil</span>
            </a>

            <a href="catalogue.php" class="<?php echo base_class('catalogue.php', $page_actuelle); ?>">
                <i data-lucide="calendar" class="w-4 h-4"></i><span class="text-sm font-medium">Catalogue</span>
            </a>

            <a href="reservations.php" class="<?php echo base_class('reservations.php', $page_actuelle); ?>">
                <i data-lucide="settings" class="w-4 h-4"></i><span class="text-sm font-medium">Réservations</span>
            </a>

            <a href="evenements.php" class="<?php echo base_class('evenements.php', $page_actuelle); ?>">
                <i data-lucide="music" class="w-4 h-4"></i><span class="text-sm font-medium">Evénements</span>
            </a>

            <a href="profil.php" class="<?php echo base_class('profil.php', $page_actuelle); ?>">
                <i data-lucide="user" class="w-4 h-4"></i><span class="text-sm font-medium">Profil</span>
            </a>

            <a href="notifications.php" class="<?php echo base_class('notifications.php', $page_actuelle); ?>">
                <i data-lucide="bell" class="w-4 h-4"></i><span class="text-sm font-medium">Notifications</span>
            </a>

            <a href="forum.php" class="<?php echo base_class('forum.php', $page_actuelle); ?>">
                <i data-lucide="message-square" class="w-4 h-4"></i><span class="text-sm font-medium">Forums</span>
            </a>

        </div>
    </nav>
</aside>
