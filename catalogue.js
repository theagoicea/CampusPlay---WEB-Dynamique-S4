let allEvents = []; 

async function loadCatalog() {
    const grid = document.getElementById('event-grid');
    
    try {
        const response = await fetch('get_events.php');
        allEvents = await response.json();

        if(allEvents.error) { 
            grid.innerHTML = `<p>${allEvents.error}</p>`; 
            return; 
        }
        displayEvents(allEvents);
        setupFilters();

    } catch (error) {
        console.error("Erreur de chargement :", error);
    }
}

function displayEvents(eventsToDisplay) {
    const grid = document.getElementById('event-grid');
    
    if (eventsToDisplay.length === 0) {
        grid.innerHTML = `<p class="text-[#A1A1AA] col-span-full text-center py-10">Aucun événement ne correspond à cette catégorie.</p>`;
        return;
    }

    grid.innerHTML = eventsToDisplay.map(event => {
        const emoji = event.type_evenement === 'CONCERT' ? '🎸' : (event.type_evenement === 'WORKSHOP' ? '🎧' : '🎷');
        
        return `
        <a href="detail_evenement.html?id=${event.id_evenement}" class="card bg-[#18181B] border border-[#27272A] p-6 rounded-3xl space-y-4 hover:border-[#A78BFA]/50 transition-all group block">
            <div class="flex justify-center items-center h-28 bg-[#0B0B0F] rounded-2xl text-4xl group-hover:scale-105 transition-transform">${emoji}</div>
            <div class="space-y-2">
                <div class="text-[10px] font-black text-[#A78BFA] uppercase tracking-widest">${event.type_evenement}</div>
                <h3 class="text-xl font-bold italic text-white">${event.titre}</h3>
                <p class="text-xs text-[#A1A1AA] line-clamp-2">${event.description}</p>
                <div class="flex justify-between items-center pt-4 border-t border-[#27272A] mt-4">
                    <span class="text-[11px] text-[#52525B]">📍 ${event.lieu}</span>
                    <span class="text-xs font-bold text-[#A78BFA]">Détails →</span>
                </div>
            </div>
        </a>`;
    }).join('');
}

// filtre
function setupFilters() {
    const buttons = document.querySelectorAll('.filter-btn');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => {
                b.classList.remove('bg-[#A78BFA]', 'text-[#0B0B0F]', 'font-bold');
                b.classList.add('bg-[#18181B]', 'text-[#A1A1AA]', 'border-[#27272A]');
            });
            btn.classList.add('bg-[#A78BFA]', 'text-[#0B0B0F]', 'font-bold');
            btn.classList.remove('bg-[#18181B]', 'text-[#A1A1AA]');
            const typeRequested = btn.getAttribute('data-type');
            if (typeRequested === 'ALL') {
                displayEvents(allEvents);
            } else {
                const filtered = allEvents.filter(e => e.type_evenement === typeRequested);
                displayEvents(filtered);
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', loadCatalog);
