<?php
// Petite fonction pour attribuer un émoji selon le nom de la ressource
function getResourceIcon($nom, $type) {
    $nom = strtolower($nom);
    if (strpos($nom, 'piano') !== false) return '🎹';
    if (strpos($nom, 'batterie') !== false) return '🥁';
    if (strpos($nom, 'voix') !== false || strpos($nom, 'chant') !== false) return '🎤';
    if (strpos($nom, 'm.a.o') !== false || strpos($nom, 'mao') !== false) return '🎧';
    if (strpos($nom, 'guitare') !== false) return '🎸';
    if (strpos($nom, 'basse') !== false) return '🎸';
	if (strpos($nom, 'rock') !== false) return '🎸';
    if (strpos($nom, 'synthé') !== false || strpos($nom, 'clavier') !== false) return '🎹';
    if (strpos($nom, 'micro') !== false) return '🎙️';
    if (strpos($nom, 'carte son') !== false || strpos($nom, 'interface') !== false) return '🎚️';
    if (strpos($nom, 'casque') !== false) return '🎧';
    if (strpos($nom, 'enceinte') !== false) return '🔊';
    if (strpos($nom, 'câble') !== false) return '🔌';
    if (strpos($nom, 'dj') !== false) return '💿';
    
    // Par défaut si rien n'est trouvé, on utilise le type
    if ($type === 'Studio') return '🏠';
    if ($type === 'Instrument') return '🎻';
    return '📦';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Campus Melody - Réservations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#0B0B0F] text-[#F5F5F7] min-h-screen font-sans flex">

    <!-- SIDEBAR -->
    <aside class="w-64 shrink-0 bg-[#18181B] border-r border-[#27272A] flex flex-col h-screen sticky top-0 overflow-y-auto">
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
        <!-- CASE DE TOUR GRISE -->
        <div class="bg-[#18181B] rounded-3xl p-8 border border-[#27272A] max-w-5xl mx-auto shadow-xl">
            
            <!-- TopBar -->
            <div class="flex justify-between items-center mb-8 border-b border-[#27272A] pb-4">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight">Réservations</h3>
                    <p class="text-sm text-[#A1A1AA]">Planifiez vos répétitions et gérez votre matériel</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="notifications.php" class="w-9 h-9 bg-[#0B0B0F] border border-[#27272A] rounded-xl flex items-center justify-center text-sm hover:bg-[#27272A] transition">🔔</a>
                    <a href="b_profil.php" id="user-avatar-link" class="w-10 h-10 bg-[#A78BFA]/20 border border-[#A78BFA]/40 rounded-xl flex items-center justify-center text-[11px] font-bold text-[#A78BFA] hover:bg-[#A78BFA]/30 transition shadow-lg shadow-[#A78BFA]/5">
                        --
                    </a>
                </div>
            </div>

            <!-- Message de succès -->
            <?php if ($message_success): ?>
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm"><?= $message_success ?></div>
            <?php endif; ?>

            <!-- Sélecteur de Date -->
            <div class="flex gap-2 overflow-x-auto pb-6">
                <?php foreach ($dates as $d): ?>
                    <a href="?date=<?= $d['full'] ?>&tab=<?= $activeTab ?>" class="flex flex-col items-center px-4 py-3 rounded-xl border transition min-w-[75px] <?= ($selectedDate === $d['full'] ? 'bg-[#A78BFA] border-[#A78BFA] text-[#0B0B0F]' : 'bg-[#0B0B0F] border-[#27272A] text-[#A1A1AA] hover:border-[#A78BFA]/50') ?>">
                        <span class="text-[10px] font-bold uppercase"><?= $d['dayName'] ?></span>
                        <span class="text-lg font-bold"><?= $d['dayNum'] ?></span>
                        <span class="text-[10px]"><?= $d['month'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Onglets -->
            <div class="flex gap-0 mb-8 border-b border-[#27272A]">
                <a href="?date=<?= $selectedDate ?>&tab=salles" class="pb-2.5 px-5 text-sm font-bold <?= ($activeTab === 'salles' ? 'text-[#A78BFA] border-b-2 border-[#A78BFA]' : 'text-[#A1A1AA] hover:text-[#F5F5F7] transition') ?>">Salles</a>
                <a href="?date=<?= $selectedDate ?>&tab=materiel" class="pb-2.5 px-5 text-sm font-bold <?= ($activeTab === 'materiel' ? 'text-[#A78BFA] border-b-2 border-[#A78BFA]' : 'text-[#A1A1AA] hover:text-[#F5F5F7] transition') ?>">Matériel</a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">
                
                <!-- Liste des ressources -->
                <div class="space-y-2">
                    <?php foreach ($resources as $res): ?>
                        <a href="?date=<?= $selectedDate ?>&tab=<?= $activeTab ?>&id_ressource=<?= $res['id_resource'] ?>" class="flex items-center gap-3 px-4 py-4 rounded-xl border transition-all <?= ($selectedResourceId == $res['id_resource'] ? 'border-[#A78BFA] bg-[#A78BFA]/10' : 'bg-[#0B0B0F] border-[#27272A] hover:border-[#A78BFA]/50 hover:bg-[#A78BFA]/5') ?>">
                            <!-- APPEL DE LA FONCTION POUR L'EMOJI -->
                            <span class="text-xl"><?php echo getResourceIcon($res['nom'], $activeTab == 'salles' ? 'Studio' : 'Matériel'); ?></span>
                            <p class="text-sm font-semibold truncate"><?= htmlspecialchars($res['nom']) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Case des Créneaux -->
                <div class="bg-[#0B0B0F] border border-[#27272A] rounded-2xl p-6 h-fit">
                    <?php if (!$selectedResourceId): ?>
                        <p class="text-center text-[#A1A1AA] py-10 italic">Choisissez une ressource à gauche</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($slots as $slot): 
                                $time_start = substr($slot, 0, 5); 
                                $is_taken = in_array($time_start, $unavailableSlots);
                            ?>
                                <div class="p-4 rounded-xl border border-[#27272A] transition-all <?= $is_taken 
                                    ? 'opacity-40 bg-[#18181B] cursor-not-allowed' 
                                    : 'bg-[#18181B] hover:border-[#A78BFA]/60 hover:bg-[#A78BFA]/5 hover:shadow-lg hover:shadow-[#A78BFA]/5 cursor-pointer' ?>">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-bold"><?= $slot ?></p>
                                            <p class="text-[10px] <?= $is_taken ? 'text-red-500' : 'text-[#A78BFA]' ?>"><?= $is_taken ? 'Occupé' : 'Libre' ?></p>
                                        </div>
                                        <?php if (!$is_taken): ?>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="reserver">
                                                <input type="hidden" name="id_ressource" value="<?= $selectedResourceId ?>">
                                                <input type="hidden" name="selected_date" value="<?= $selectedDate ?>">
                                                <input type="hidden" name="slot" value="<?= $slot ?>">
                                                <button type="submit" class="text-xs bg-[#A78BFA] text-[#0B0B0F] px-4 py-2 rounded-lg font-bold hover:bg-white transition-colors">Réserver</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="navigation.js"></script>
</body>
</html>
