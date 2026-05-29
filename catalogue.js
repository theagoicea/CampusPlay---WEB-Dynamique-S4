let allEvents = [];
let activeFilters = new Set();

async function loadCatalog() {
    const grid = document.getElementById('event-grid');
    
    try {
        const response = await fetch('get_events.php');
        const data = await response.json();

        if (data.error) { 
            grid.innerHTML = `<p class="col-span-full text-center text-red-400">${data.error}</p>`; 
            return; 
        }

        allEvents = data;
        
        // On initialise l'affichage
        displayEvents(allEvents);
        setupFilters();
        setupSearch();

    } catch (error) {
        console.error("Erreur de chargement :", error);
    }
}

function getFilteredEvents() {
    const searchInput = document.getElementById('search-input');
    // On vérifie si l'élément existe pour éviter les erreurs
    const searchQuery = searchInput ? searchInput.value.trim().toLowerCase() : '';

    return allEvents.filter(event => {
        // 1. Filtrage par Type
        // On s'assure que le type de l'événement est bien en majuscules pour comparer
        const eventType = event.type_evenement ? event.type_evenement.toUpperCase() : '';
        const matchesType = activeFilters.size === 0 || activeFilters.has(eventType);

        // 2. Filtrage par Nom (Recherche)
        // SECURITÉ : On vérifie que le titre n'est pas vide avant le toLowerCase()
        const eventTitle = event.titre ? event.titre.toLowerCase() : '';
        const matchesSearch = searchQuery === '' || eventTitle.includes(searchQuery);

        return matchesType && matchesSearch;
    });
}

function displayEvents(eventsToDisplay) {
    const grid = document.getElementById('event-grid');
    
    if (!grid) return;

    if (eventsToDisplay.length === 0) {
        grid.innerHTML = `<p class="text-[#A1A1AA] col-span-full text-center py-20 italic">Aucun événement ne correspond à cette recherche.</p>`;
        return;
    }

    const typeEmojis = {
        'CONCERT':     '🎸',
        'OPEN MIC':    '🎤',
        'JAM SESSION': '🎷',
        'DJ NIGHT':    '🎧',
        'WORKSHOP':    '📚',
        'AUDITION':    '🎼',
        'RÉPÉTITION':  '🥁'
    };

    grid.innerHTML = eventsToDisplay.map(event => {
        // On gère l'emoji même si le type est en minuscule dans la BDD
        const typeKey = event.type_evenement ? event.type_evenement.toUpperCase() : '';
        const emoji = typeEmojis[typeKey] ?? '🎵';
        
        return `
        <a href="detail_evenement.html?id=${event.id_evenement}" class="bg-[#0B0B0F] border border-[#27272A] p-6 rounded-3xl space-y-4 hover:border-[#A78BFA]/50 transition-all group block h-full">
            <div class="flex justify-center items-center h-28 bg-[#18181B] rounded-2xl text-4xl group-hover:scale-105 transition-transform">${emoji}</div>
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

function updateButtonStyles() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const type = btn.getAttribute('data-type').toUpperCase();
        const isActive = type === 'ALL' ? activeFilters.size === 0 : activeFilters.has(type);
        
        if (isActive) {
            btn.classList.add('bg-[#A78BFA]', 'text-[#0B0B0F]', 'font-bold');
            btn.classList.remove('bg-[#0B0B0F]', 'text-[#A1A1AA]');
            btn.style.borderColor = '#A78BFA';
        } else {
            btn.classList.remove('bg-[#A78BFA]', 'text-[#0B0B0F]', 'font-bold');
            btn.classList.add('bg-[#0B0B0F]', 'text-[#A1A1AA]');
            btn.style.borderColor = '#27272A';
        }
    });
}

function setupFilters() {
    updateButtonStyles();

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-type').toUpperCase();

            if (type === 'ALL') {
                activeFilters.clear();
            } else {
                if (activeFilters.has(type)) {
                    activeFilters.delete(type);
                } else {
                    activeFilters.add(type);
                }
            }

            updateButtonStyles();
            displayEvents(getFilteredEvents());
        });
    });
}

function setupSearch() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        // On utilise 'input' pour que la recherche soit instantanée à chaque touche
        searchInput.addEventListener('input', () => {
            const filtered = getFilteredEvents();
            displayEvents(filtered);
        });
    }
}

document.addEventListener('DOMContentLoaded', loadCatalog);
