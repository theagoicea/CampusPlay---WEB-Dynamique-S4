<?php
function getNotifStyle($type) {
    switch ($type) {
        case 'rappel-evenement':
            return ['dot' => 'bg-blue-400', 'badge' => 'bg-blue-500/10 text-blue-400 border-blue-500/20', 'label' => 'Visiteur · rappel-événement'];
        case 'forum':
            return ['dot' => 'bg-cyan-400', 'badge' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20', 'label' => 'Visiteur · forum'];
        case 'rappel-materiel':
            return ['dot' => 'bg-orange-400', 'badge' => 'bg-orange-500/10 text-orange-400 border-orange-500/20', 'label' => 'Membre · rappel-matériel'];
        case 'inscription-evenement':
            return ['dot' => 'bg-violet-400', 'badge' => 'bg-violet-500/10 text-violet-400 border-violet-500/20', 'label' => 'Organisateur · inscription-événement'];
        case 'adhesion-association':
            return ['dot' => 'bg-pink-400', 'badge' => 'bg-pink-500/10 text-pink-400 border-pink-500/20', 'label' => 'Admin · adhésion-association'];
        case 'creation-evenement':
            return ['dot' => 'bg-amber-400', 'badge' => 'bg-amber-500/10 text-amber-400 border-amber-500/20', 'label' => 'Admin · création-événement'];
        case 'signalement':
            return ['dot' => 'bg-amber-400', 'badge' => 'bg-amber-500/10 text-amber-400 border-amber-500/20', 'label' => 'Admin · signalement'];
        default:
            return ['dot' => 'bg-gray-400', 'badge' => 'bg-gray-500/10 text-gray-400 border-gray-500/20', 'label' => 'Notification'];
    }
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array('y' => 'an', 'm' => 'mois', 'w' => 'semaine', 'd' => 'jour', 'h' => 'h', 'i' => 'min', 's' => 'sec');
    foreach ($string as $k => &$v) {
        if ($diff->$k) { $v = $diff->$k . ' ' . $v . ($diff->$k > 1 && $k != 'm' && $k != 'h' && $k != 'i' ? 's' : ''); } 
        else { unset($string[$k]); }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'Il y a ' . implode(', ', $string) : 'À l\'instant';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Campus Melody - Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#0B0B0F] text-[#F5F5F7] min-h-screen font-sans flex">

    <!-- SIDEBAR -->
    <aside class="w-64 shrink-0 bg-[#18181B] border-r border-[#27272A] flex flex-col h-screen sticky top-0">
        <div class="px-6 py-8 border-b border-[#27272A]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#A78BFA]/10 rounded-xl border border-[#A78BFA]/30 flex items-center justify-center text-lg">🎵</div>
                <h1 class="text-sm font-bold text-[#F5F5F7] leading-tight">Campus Melody</h1>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4">
            <p class="text-[10px] font-semibold text-[#A1A1AA] uppercase tracking-widest px-3 mb-3">Pages</p>
            <div id="sidebar-nav" class="space-y-1"></div>
        </nav>
    </aside>

    <main class="flex-1 overflow-y-auto px-8 py-10">
        <!-- TOUR GRIS (Conteneur) -->
        <div class="bg-[#18181B] rounded-3xl p-8 border border-[#27272A] max-w-5xl mx-auto shadow-xl">
            
            <!-- TopBar -->
            <div class="flex justify-between items-center mb-10 border-b border-[#27272A] pb-6">
                <div>
                    <h3 class="text-3xl font-bold tracking-tight">Notifications</h3>
                    <p class="text-sm text-[#A1A1AA] mt-1">Suivi de vos demandes et activités</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="profil.html" id="user-avatar-link" class="w-10 h-10 bg-[#A78BFA]/20 border border-[#A78BFA]/40 rounded-xl flex items-center justify-center text-[11px] font-bold text-[#A78BFA] hover:bg-[#A78BFA]/30 transition shadow-lg shadow-[#A78BFA]/5">
                        --
                    </a>
                </div>
            </div>

            <!-- LISTE DES NOTIFICATIONS (Style Image) -->
            <div class="space-y-4">
                <?php if (empty($notifications)): ?>
                    <p class="text-center text-[#A1A1AA] py-10 italic">Aucune notification.</p>
                <?php endif; ?>

                <?php foreach ($notifications as $notif): 
                    $style = getNotifStyle($notif['type_notification']);
                ?>
                    <div class="relative group bg-[#0B0B0F] border border-[#1f1f23] rounded-[24px] p-6 transition-all hover:border-[#27272A]">
                        <div class="flex items-start gap-6">
                            <!-- Point de couleur -->
                            <div class="mt-2 w-2.5 h-2.5 rounded-full shrink-0 <?= $style['dot'] ?> shadow-[0_0_8px_rgba(0,0,0,0.5)]"></div>
                            
                            <!-- Contenu -->
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <h4 class="text-[15px] font-medium leading-relaxed max-w-[80%] text-[#F5F5F7]">
                                        <?= htmlspecialchars($notif['message']) ?>
                                    </h4>
                                    <span class="text-[11px] text-[#52525B] font-medium">
                                        <?= time_elapsed_string($notif['date_envoi']) ?>
                                    </span>
                                </div>
                                
                                <!-- Badge Rôle · Type -->
                                <div class="mt-3 inline-flex items-center px-3 py-1 rounded-lg border text-[10px] font-bold tracking-wide uppercase <?= $style['badge'] ?>">
                                    <?= $style['label'] ?>
                                </div>
                            </div>

                            <!-- Action supprimer au survol -->
                            <form method="POST" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_notif" value="<?= $notif['id_notification'] ?>">
                                <button type="submit" class="p-1.5 text-[#52525B] hover:text-rose-500 transition">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Ton navigation.js existant s'occupe du sidebar-nav -->
    <script src="navigation.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
