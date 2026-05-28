async function loadCatalog() {
    const grid = document.getElementById('event-grid');
    const response = await fetch('get_events.php');
    const events = await response.json();

    if(events.error) { grid.innerHTML = `<p>${events.error}</p>`; return; }

    grid.innerHTML = events.map(event => {
        const emoji = event.type_evenement === 'CONCERT' ? '🎸' : (event.type_evenement === 'WORKSHOP' ? '🎧' : '🎷');
        return `
        <div class="card">
            <div class="flex justify-center items-center h-24 mb-2 bg-[#0B0B0F] rounded-xl text-3xl">${emoji}</div>
            <div class="space-y-3">
                <div class="text-[10px] font-bold text-[#A78BFA] uppercase">${event.type_evenement}</div>
                <h3 class="text-xl font-bold">${event.titre}</h3>
                <p class="text-xs text-[#A1A1AA]">${event.description}</p>
                <div class="flex justify-between items-center pt-4 border-t border-[#27272A]">
                    <span class="text-[11px]">📍 ${event.lieu}</span>
                    <a href="Detail.html?id=${event.id_evenement}" class="text-xs font-bold text-[#A78BFA]">S'inscrire →</a>
                </div>
            </div>
        </div>`;
    }).join('');
}
document.addEventListener('DOMContentLoaded', loadCatalog);
