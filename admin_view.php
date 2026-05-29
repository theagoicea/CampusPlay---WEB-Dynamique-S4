<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Campus Melody - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Styles pour l'accordéon des demandes */
        .request-row.expanded .detail-section { display: block; }
        .detail-section { display: none; }
        .request-row.expanded .chevron-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-[#0B0B0F] text-[#F5F5F7] min-h-screen font-sans flex">

    <!-- SIDEBAR -->
    <aside class="w-64 shrink-0 bg-[#18181B] border-r border-[#27272A] flex flex-col h-screen sticky top-0">
        <div class="px-6 py-8 border-b border-[#27272A] flex items-center gap-3">
            <div class="w-9 h-9 bg-[#A78BFA]/10 rounded-xl border border-[#A78BFA]/30 flex items-center justify-center text-lg">🎵</div>
            <h1 class="text-sm font-bold leading-tight">Campus Melody</h1>
        </div>
        <nav class="flex-1 px-3 py-4">
            <p class="text-[10px] font-semibold text-[#A1A1AA] uppercase tracking-widest px-3 mb-3">Pages</p>
            <div id="sidebar-nav" class="space-y-1"></div>
        </nav>
    </aside>

    <main class="flex-1 overflow-y-auto px-8 py-10">
        <!-- Boîte Grise principale -->
        <div class="bg-[#18181B] rounded-3xl p-8 border border-[#27272A] max-w-6xl mx-auto shadow-xl">
            
            <!-- TOPBAR -->
            <div class="flex justify-between items-center mb-8 border-b border-[#27272A] pb-6">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight">Tableau de Bord Admin</h3>
                    <p class="text-sm text-[#A1A1AA]">Supervision globale</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="notifications.html" class="w-9 h-9 bg-[#0B0B0F] border border-[#27272A] rounded-xl flex items-center justify-center text-sm hover:bg-[#27272A] transition">🔔</a>
                    <a href="profil.html" id="user-avatar-link" class="w-10 h-10 bg-[#A78BFA]/20 border border-[#A78BFA]/40 rounded-xl flex items-center justify-center text-[11px] font-bold text-[#A78BFA] hover:bg-[#A78BFA]/30 transition shadow-lg shadow-[#A78BFA]/5">
                        --
                    </a>
                </div>
            </div>

            <!-- GRILLE KPI (Statistiques) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
                <div class="bg-[#0B0B0F] border border-[#27272A] p-6 rounded-2xl">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#A1A1AA] mb-2">Membres inscrits</div>
                    <div class="text-3xl font-bold text-purple-400"><?= $stats['membres'] ?></div>
                </div>
                <div class="bg-[#0B0B0F] border border-[#27272A] p-6 rounded-2xl">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#A1A1AA] mb-2">Inscriptions totales</div>
                    <div class="text-3xl font-bold text-indigo-400"><?= $stats['inscriptions'] ?></div>
                </div>
                <div class="bg-[#0B0B0F] border border-[#27272A] p-6 rounded-2xl">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#A1A1AA] mb-2">Demandes en attente</div>
                    <div class="text-3xl font-bold text-amber-400"><?= $stats['en_attente'] ?></div>
                </div>
            </div>

            <!-- SECTION DEMANDES -->
            <div class="bg-[#0B0B0F] rounded-2xl p-6 border border-[#27272A]">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#A1A1AA] mb-6">Gestion des demandes</h4>
                
                <div class="space-y-3">
                    <?php if (empty($requests)): ?>
                        <div class="py-12 text-center text-[#52525B] italic border border-dashed border-[#27272A] rounded-xl">
                            Aucune demande en attente pour le moment.
                        </div>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <div class="bg-[#18181B] border border-[#27272A] rounded-xl overflow-hidden request-row transition-all hover:border-[#A78BFA]/30" id="req-<?= $req['id'] ?>">
                                <!-- En-tête de ligne cliquable -->
                                <div class="flex justify-between items-center p-4 cursor-pointer" onclick="toggleRequest('<?= $req['id'] ?>')">
                                    <div class="flex items-center gap-4">
                                        <span class="text-[9px] px-2 py-1 rounded font-bold border 
                                            <?php 
                                                if($req['type'] === 'inscription') echo 'bg-violet-500/10 text-violet-400 border-violet-500/20';
                                                elseif($req['type'] === 'reservation') echo 'bg-pink-500/10 text-pink-400 border-pink-500/20';
                                                elseif($req['type'] === 'creation') echo 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                                            ?>">
                                            <?= strtoupper($req['type']) ?>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold"><?= htmlspecialchars($req['title']) ?></p>
                                            <p class="text-[11px] text-[#A1A1AA]">Par <?= htmlspecialchars($req['user']) ?></p>
                                        </div>
                                    </div>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#52525B] chevron-icon transition-transform duration-300"></i>
                                </div>

                                <!-- Détails Accordéon -->
                                <div class="detail-section px-4 pb-5 border-t border-[#27272A] pt-4 bg-[#0B0B0F]/30">
                                    <p class="text-xs text-[#A1A1AA] mb-5 leading-relaxed"><?= htmlspecialchars($req['detail']) ?></p>
                                    <form method="POST" class="flex gap-3">
                                        <input type="hidden" name="item_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="item_type" value="<?= $req['type'] ?>">
                                        
                                        <button name="action" value="approuver" class="px-5 py-2 bg-green-500/10 border border-green-500/20 text-green-400 text-[10px] font-bold rounded-lg hover:bg-green-500/20 transition">Approuver</button>
                                        <button name="action" value="refuser" class="px-5 py-2 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold rounded-lg hover:bg-rose-500/20 transition">Refuser</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?> 
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="navigation.js"></script>
    <script>
        // FONCTION POUR OUVRIR/FERMER LES DEMANDES
        function toggleRequest(id) {
            const row = document.getElementById('req-' + id);
            if(row) {
                row.classList.toggle('expanded');
            }
        }

        // Initialisation des icônes au chargement
        window.addEventListener('DOMContentLoaded', () => {
            if(window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
