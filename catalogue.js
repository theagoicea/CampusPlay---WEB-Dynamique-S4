let allEvents = [];

const categoryEmojis = {
    'CONCERT': '🎸',
    'OPEN MIC': '🎤',
    'JAM SESSION': '🎷',
    'DJ NIGHT': '💿',
    'WORKSHOP': '🎧',
    'AUDITION': '🎵',
    'REPETITION': '🥁'
};

async function loadCatalog() {
    const grid = document.getElementById('event-grid');
    
    try {
        const response = await fetch('get_events.php');
        const data = await response.json();

        if(data.error) { 
            grid.innerHTML = `<p class="text-center py-10 text-rose-500">${data.error}</p>`; 
            return; 
        }

        allEvents = data;
        displayEvents(allEvents);
        setupFilters();
        setupSearch();

    } catch (error) {
        console.error("Erreur de chargement :", error);
        grid.innerHTML = `<p class="text-center py-10 text-rose-500">Erreur de connexion au serveur PHP.</p>`;
    }
}

function displayEvents(events) {
    const grid = document.getElementById('event-grid');
    
    if (events.length === 0) {
        grid.innerHTML = `<div class="col-span-full text-center py-20 text-[#A1A1AA]">Aucun événement trouvé.</div>`;
        return;
    }

    grid.innerHTML = events.map(event => {
        // Uniformisation des clés (majuscules) pour éviter les bugs
        const typeKey = (event.type_evenement || "").toUpperCase();
        const emoji = categoryEmojis[typeKey] || '📅';
        
        // Logique de l'image (Avec le dossier uploads/)
        const visualContent = (event.image_url && event.image_url !== "") 
            ? `<img src="${event.image_url}" alt="${event.titre}" class="w-full h-full object-cover" onerror="this.outerHTML='<div class=&quot;text-5xl group-hover:scale-110 transition-transform&quot;>${emoji}</div>'">`
            : `<div class="text-5xl group-hover:scale-110 transition-transform">${emoji}</div>`;

        return `
        <a href="detail_evenement.html?id=${event.id_evenement}" class="card bg-[#18181B] border border-[#27272A] p-5 rounded-3xl space-y-4 hover:border-[#A78BFA]/50 hover:bg-[#1c1c21] transition-all group block">
            
            <div class="relative w-full h-44 bg-[#0B0B0F] rounded-2xl flex justify-center items-center overflow-hidden border border-[#27272A]">
                ${visualContent}
                <div class="absolute top-3 left-3 px-2 py-1 rounded-md bg-[#0B0B0F]/80 backdrop-blur-md border border-[#27272A] text-[9px] font-black text-[#A78BFA] uppercase tracking-widest">
                    ${event.type_evenement}
                </div>
            </div>
            
            <div class="space-y-2">
                <h3 class="text-lg font-bold italic text-white leading-tight group-hover:text-[#A78BFA] transition-colors">${event.titre}</h3>
                <p class="text-xs text-[#A1A1AA] line-clamp-2 leading-relaxed">${event.description || ""}</p>
                
                <div class="flex justify-between items-center pt-4 border-t border-[#27272A] mt-4 text-[#52525B]">
                    <span class="text-[10px] flex items-center gap-1.5 font-medium">📍 ${event.lieu}</span>
                    <span class="text-[10px] font-bold text-[#A78BFA] group-hover:translate-x-1 transition-all">Détails →</span>
                </div>
            </div>
        </a>`;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function setupFilters() {
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(btn => {
        btn.onclick = () => {
            buttons.forEach(b => {
                b.style.background = "#0B0B0F";
                b.style.color = "#A1A1AA";
                b.style.border = "1px solid #27272A";
                b.style.fontWeight = "normal";
            });

            btn.style.background = "#A78BFA";
            btn.style.color = "#0B0B0F";
            btn.style.border = "1px solid #A78BFA";
            btn.style.fontWeight = "bold";
            
            const type = btn.getAttribute('data-type');
            const filtered = (type === 'ALL') ? allEvents : allEvents.filter(e => (e.type_evenement || "").toUpperCase() === type.toUpperCase());
            displayEvents(filtered);
        };
    });
}

function setupSearch() {
    const searchInput = document.querySelector('input[type="text"]');
    if(searchInput) {
        searchInput.oninput = (e) => {
            const val = e.target.value.toLowerCase();
            const filtered = allEvents.filter(ev => {
                // Vérification stricte pour éviter les plantages si la description est vide
                const titre = ev.titre ? ev.titre.toLowerCase() : "";
                const desc = ev.description ? ev.description.toLowerCase() : "";
                return titre.includes(val) || desc.includes(val);
            });
            displayEvents(filtered);
        };
    }
}

document.addEventListener('DOMContentLoaded', loadCatalog);