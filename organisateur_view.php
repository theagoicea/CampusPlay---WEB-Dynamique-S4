<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Dashboard Organisateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#0B0B0F] text-[#F5F5F7] min-h-screen font-sans flex">
    <aside class="w-64 shrink-0 bg-[#18181B] border-r border-[#27272A] flex flex-col h-screen sticky top-0">
        <div class="px-6 py-8 border-b border-[#27272A]"><h1 class="text-sm font-bold">Campus Melody</h1></div>
        <nav class="flex-1 px-3 py-4"><div id="sidebar-nav"></div></nav>
    </aside>

    <main class="flex-1 px-8 py-10">
        <div class="bg-[#18181B] rounded-3xl p-8 border border-[#27272A] max-w-5xl mx-auto shadow-xl">
            <div class="flex justify-between items-center mb-10 border-b border-[#27272A] pb-6">
                <div><h2 class="text-2xl font-bold">Gestion de mes événements</h2><p class="text-sm text-[#A1A1AA]">Validez les participants à vos concerts et ateliers</p></div>
                <div class="w-10 h-10 bg-[#A78BFA]/20 border border-[#A78BFA]/40 rounded-xl flex items-center justify-center text-[11px] font-bold text-[#A78BFA]"><?= $initiales ?></div>
            </div>

            <div class="space-y-4">
                <?php if (empty($requests)): ?>
                    <p class="text-center py-10 text-[#52525B]">Aucune demande d'inscription en attente.</p>
                <?php endif; ?>

                <?php foreach ($requests as $req): ?>
                <div class="bg-[#0B0B0F] border border-[#27272A] rounded-2xl p-5 flex justify-between items-center">
                    <div>
                        <span class="text-[10px] font-bold bg-violet-500/10 text-violet-400 px-2 py-1 rounded mb-2 inline-block">INSCRIPTION</span>
                        <h4 class="font-bold text-sm"><?= $req['title'] ?></h4>
                        <p class="text-xs text-[#A1A1AA]">Par <?= $req['user'] ?></p>
                    </div>
                    <form method="POST" class="flex gap-2">
                        <input type="hidden" name="item_id" value="<?= $req['id'] ?>">
                        <input type="hidden" name="item_type" value="inscription">
                        <button name="action" value="refuser" class="px-4 py-2 rounded-lg bg-red-500/10 text-red-500 text-xs font-bold hover:bg-red-500/20">Refuser</button>
                        <button name="action" value="approuver" class="px-4 py-2 rounded-lg bg-green-500/10 text-green-500 text-xs font-bold hover:bg-green-500/20">Accepter</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <script src="navigation.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>