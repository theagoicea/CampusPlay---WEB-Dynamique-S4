async function loadCatalog() {
    const grid = document.getElementById('event-grid');
    
    try {
        // Appelle ton fichier PHP (API)
        const response = await fetch('get_events.php');
        const events = await response.json();

        if (events.error) {
            grid.innerHTML = `<div class="col-span-full text-center text-red-400">Erreur : ${events.error}</div>`;
            return;
        }

        if (events.length === 0) {
            grid.innerHTML = `<div class="col-span-full text-center text-[#A1A1AA]">Aucun événement disponible pour le moment.</div>`;
            return;
        }

        grid.innerHTML = events.map(event => {
            // Gestion dynamique de l'icône selon le type
            let emoji = '📅';
            const type = event.type_evenement.toLowerCase();
            if (type.includes('concert')) emoji = '🎸';
            else if (type.includes('workshop')) emoji = '🎧';
            else if (type.includes('jam')) emoji = '🎷';
            else if (type.includes('open-mic')) emoji = '🎤';

            return `
            <div class="bg-[#0B0B0F] border border-[#27272A] rounded-2xl overflow-hidden hover:border-[#A78BFA]/50 transition group flex flex-col h-full">
                <!-- Header Icon -->
                <div class="h-32 flex items-center justify-center bg-[#18181B] text-4xl group-hover:scale-110 transition duration-500">
                    ${emoji}
                </div>
                
                <!-- Content -->
                <div class="p-5 flex flex-col flex-1">
                    <div class="text-[10px] font-bold text-[#A78BFA] uppercase tracking-widest mb-1">${event.type_evenement}</div>
                    <h3 class="text-xl font-bold mb-2 text-[#F5F5F7] line-clamp-1">${event.titre}</h3>
                    <p class="text-xs text-[#A1A1AA] line-clamp-3 mb-4 flex-1">${event.description}</p>
                    
                    <div class="flex flex-col gap-3 pt-4 border-t border-[#27272A]">
                        <div class="flex items-center justify-between text-[11px] text-[#71717A]">
                            <span>📍 ${event.lieu}</span>
                            <span>👤 ${event.prenom_orga}</span>
                        </div>
                        <a href="Detail.html?id=${event.id_evenement}" class="w-full py-2 bg-[#A78BFA]/10 border border-[#A78BFA]/20 text-[#A78BFA] text-center rounded-xl text-xs font-bold hover:bg-[#A78BFA] hover:text-[#0B0B0F] transition duration-300">
                            Voir les détails
                        </a>
                    </div>
                </div>
            </div>`;
        }).join('');

    } catch (err) {
        grid.innerHTML = `<div class="col-span-full text-center text-red-400">Erreur de connexion au serveur.</div>`;
    }
}

document.addEventListener('DOMContentLoaded', loadCatalog);
