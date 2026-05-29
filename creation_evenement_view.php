<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Campus Melody - Créer un événement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Transition fluide pour le changement de format */
        #previewContainer {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="bg-[#0B0B0F] text-[#F5F5F7] min-h-screen font-sans flex">

    <!-- SIDEBAR (Remplie par navigation.js) -->
    <aside class="w-64 shrink-0 bg-[#18181B] border-r border-[#27272A] flex flex-col h-screen sticky top-0">
        <div class="px-6 py-8 border-b border-[#27272A]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#A78BFA]/10 rounded-xl border border-[#A78BFA]/30 flex items-center justify-center text-lg">🎵</div>
                <h1 class="text-sm font-bold text-[#F5F5F7] leading-tight">Campus Melody</h1>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4">
            <p class="text-[10px] text-[#A1A1AA] uppercase px-3 mb-3 tracking-widest font-semibold">Pages</p>
            <div id="sidebar-nav" class="space-y-1"></div>
        </nav>
    </aside>

    <main class="flex-1 overflow-y-auto px-8 py-10">
        <!-- Boîte Grise principale (Style Profil) -->
        <div class="bg-[#18181B] rounded-3xl p-8 border border-[#27272A] max-w-5xl mx-auto shadow-xl">
            
            <!-- TOPBAR IDENTIQUE AUX AUTRES PAGES -->
            <div class="flex justify-between items-center mb-8 border-b border-[#27272A] pb-6">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight">Proposer un événement</h3>
                    <p class="text-sm text-[#A1A1AA]">Remplissez les détails pour validation</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="notifications.html" class="w-10 h-10 bg-[#0B0B0F] border border-[#27272A] rounded-xl flex items-center justify-center hover:bg-[#27272A] transition">🔔</a>
                    <a href="profil.html" class="w-10 h-10 bg-[#A78BFA]/20 border border-[#A78BFA]/40 rounded-xl flex items-center justify-center text-[11px] font-bold text-[#A78BFA] hover:bg-[#A78BFA]/30 transition shadow-lg shadow-[#A78BFA]/5">
                        <?= $initiales ?>
                    </a>
                </div>
            </div>

            <?php if ($message_success): ?>
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> <?= $message_success ?>
                </div>
            <?php endif; ?>

            <form action="creation_evenement.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                
                <!-- SECTION IMAGE : CARRÉ OU BANNIÈRE -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Visuel de l'événement</label>
                        
                        <!-- Sélecteur de format -->
                        <div class="flex bg-[#0B0B0F] p-1 rounded-lg border border-[#27272A] gap-1">
                            <button type="button" id="btn-square" onclick="setFormat('square')" class="format-btn px-4 py-1 text-[10px] rounded-md bg-[#27272A] text-white transition">Carré</button>
                            <button type="button" id="btn-landscape" onclick="setFormat('landscape')" class="format-btn px-4 py-1 text-[10px] rounded-md text-[#A1A1AA] hover:text-white transition">Bannière</button>
                        </div>
                    </div>

                    <!-- Zone d'aperçu dynamique -->
                    <div id="previewContainer" class="relative w-full max-w-md mx-auto border-2 border-dashed border-[#27272A] rounded-2xl overflow-hidden bg-[#0B0B0F] hover:border-[#A78BFA]/50 transition-all aspect-square">
                        <input type="file" name="image_event" id="imageInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30" onchange="previewImage(event)">
                        
                        <div id="placeholder" class="absolute inset-0 flex flex-col items-center justify-center z-10">
                            <i data-lucide="image" class="w-8 h-8 text-[#52525B] mb-2"></i>
                            <p class="text-xs text-[#52525B]">Importer une image</p>
                        </div>

                        <img id="imagePreview" src="#" class="absolute inset-0 w-full h-full object-cover hidden z-20">
                    </div>
                </div>

                <!-- INFOS GÉNÉRALES -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Titre de l'événement</label>
                        <input type="text" name="titre" required class="w-full bg-[#0B0B0F] border border-[#27272A] rounded-xl py-3 px-4 text-sm focus:border-[#A78BFA] outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Catégorie</label>
                        <select name="categorie" required class="w-full bg-[#0B0B0F] border border-[#27272A] rounded-xl py-3 px-4 text-sm focus:border-[#A78BFA] outline-none cursor-pointer">
                            <option value="Concert">🎸 Concert</option>
                            <option value="Open Mic">🎤 Open Mic</option>
                            <option value="Jam Session">🎷 Jam Session</option>
                            <option value="DJ Night">💿 DJ Night</option>
                            <option value="Workshop">🎧 Workshop</option>
                            <option value="Audition">🎵 Audition</option>
                            <option value="Répétition">🥁 Répétition</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Description</label>
                    <textarea name="description" rows="3" required class="w-full bg-[#0B0B0F] border border-[#27272A] rounded-xl py-3 px-4 text-sm focus:border-[#A78BFA] outline-none" placeholder="Présentez votre événement..."></textarea>
                </div>

                <!-- LOGISTIQUE -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Lieu / Salle</label>
                        <select name="lieu" required class="w-full bg-[#0B0B0F] border border-[#27272A] rounded-xl py-3 px-4 text-sm focus:border-[#A78BFA] outline-none">
                            <option value="" disabled selected>Sélectionner une salle...</option>
                            <?php foreach ($salles as $salle): ?>
                                <option value="<?= htmlspecialchars($salle['nom']) ?>"><?= htmlspecialchars($salle['nom']) ?></option>
                            <?php endforeach; ?>
                            <option value="Autre">Autre lieu</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Capacité maximale</label>
                        <input type="number" name="capacite" required class="w-full bg-[#0B0B0F] border border-[#27272A] rounded-xl py-3 px-4 text-sm focus:border-[#A78BFA] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Date et heure de début</label>
                        <input type="datetime-local" name="date_debut" required class="w-full bg-[#0B0B0F] border border-[#27272A] rounded-xl py-3 px-4 text-sm text-[#F5F5F7] focus:border-[#A78BFA] outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#A1A1AA] uppercase tracking-widest">Date et heure de fin</label>
                        <input type="datetime-local" name="date_fin" required class="w-full bg-[#0B0B0F] border border-[#27272A] rounded-xl py-3 px-4 text-sm text-[#F5F5F7] focus:border-[#A78BFA] outline-none">
                    </div>
                </div>

                <!-- OPTIONS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="p-4 bg-[#0B0B0F] border border-[#27272A] rounded-2xl flex items-center justify-between cursor-pointer hover:border-[#A78BFA]/30 transition">
                        <span class="text-xs font-bold text-[#A1A1AA]">Réservé aux membres asso</span>
                        <input type="checkbox" name="reserve_membres" class="w-5 h-5 accent-[#A78BFA]">
                    </label>
                    <label class="p-4 bg-[#0B0B0F] border border-[#27272A] rounded-2xl flex items-center justify-between cursor-pointer hover:border-[#A78BFA]/30 transition">
                        <span class="text-xs font-bold text-[#A1A1AA]">Validation des inscriptions</span>
                        <input type="checkbox" name="besoin_validation" class="w-5 h-5 accent-[#A78BFA]">
                    </label>
                </div>

                <button type="submit" class="w-full py-4 bg-[#A78BFA] text-[#0B0B0F] font-bold rounded-2xl hover:scale-[1.01] transition transform active:scale-95 shadow-lg shadow-[#A78BFA]/20">
                    Envoyer la demande 
                </button>
            </form>
        </div>
    </main>

    <script src="navigation.js"></script>
    <script>
        lucide.createIcons();

        // APERÇU IMAGE
        function previewImage(event) {
            const reader = new FileReader();
            const imagePreview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('placeholder');
            
            reader.onload = function() {
                if (reader.readyState === 2) {
                    imagePreview.src = reader.result;
                    imagePreview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        // GESTION FORMAT (CARRÉ / BANNIÈRE)
        function setFormat(ratio) {
            const container = document.getElementById('previewContainer');
            const btnSquare = document.getElementById('btn-square');
            const btnLandscape = document.getElementById('btn-landscape');
            
            // Réinitialiser les styles
            container.classList.remove('aspect-square', 'aspect-video');
            btnSquare.classList.remove('bg-[#27272A]', 'text-white');
            btnSquare.classList.add('text-[#A1A1AA]');
            btnLandscape.classList.remove('bg-[#27272A]', 'text-white');
            btnLandscape.classList.add('text-[#A1A1AA]');

            // Appliquer le nouveau ratio
            if (ratio === 'square') {
                container.classList.add('aspect-square');
                btnSquare.classList.remove('text-[#A1A1AA]');
                btnSquare.classList.add('bg-[#27272A]', 'text-white');
            } else if (ratio === 'landscape') {
                container.classList.add('aspect-video'); // Format 16:9
                btnLandscape.classList.remove('text-[#A1A1AA]');
                btnLandscape.classList.add('bg-[#27272A]', 'text-white');
            }
        }
    </script>
</body>
</html>
