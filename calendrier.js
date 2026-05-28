let currentViewDate = new Date(); 

async function loadCalendar() {
    const grid = document.getElementById('calendarGrid');
    const monthDisplay = document.getElementById('monthDisplay');
    
    // 1. Récupération des données
    const response = await fetch('get_events.php');
    const events = await response.json();

    if(events.error) {
        console.error("Erreur PHP:", events.error);
        return;
    }

    const year = currentViewDate.getFullYear();
    const month = currentViewDate.getMonth(); 
    
    const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
    monthDisplay.innerText = `${monthNames[month]} ${year}`;

    let firstDayOfMonth = new Date(year, month, 1).getDay();
    let startingPadding = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1;
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    grid.innerHTML = ""; 

    // Cases vides
    for (let i = 0; i < startingPadding; i++) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = "min-h-[120px] bg-transparent";
        grid.appendChild(emptyDiv);
    }

    // Jours du mois
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.className = "min-h-[120px] p-4 rounded-2xl bg-[#18181B] border border-[#27272A] flex flex-col gap-2 hover:border-[#A78BFA]/50 transition-all";

        // FILTRAGE CORRIGÉ (Une seule fois)
        const dayEvents = events.filter(e => {
            // On s'assure que la date est bien lue
            const dateString = e.date_debut.replace(' ', 'T');
            const eventDate = new Date(dateString);
            
            return eventDate.getDate() === day && 
                   eventDate.getMonth() === month && 
                   eventDate.getFullYear() === year;
        });

        const dayColor = dayEvents.length > 0 ? 'text-[#A78BFA]' : 'text-[#52525B]';

        let eventsHtml = dayEvents.map(e => {
            let icon = '🎸';
            if(e.type_evenement === 'WORKSHOP') icon = '🎧';
            if(e.type_evenement === 'JAM SESSION') icon = '🎷';
            return `
                <div title="${e.titre}" class="text-[10px] bg-[#A78BFA]/10 text-[#A78BFA] p-1.5 rounded-lg border border-[#A78BFA]/20 truncate font-medium">
                    ${icon} ${e.titre}
                </div>`;
        }).join('');

        dayCell.innerHTML = `
            <span class="text-sm font-bold ${dayColor}">${day}</span>
            <div class="flex flex-col gap-1 overflow-y-auto max-h-[80px] custom-scrollbar">
                ${eventsHtml}
            </div>
        `;
        grid.appendChild(dayCell);
    }

    if (window.lucide) lucide.createIcons();
}

// Boutons de navigation
document.getElementById('prevMonth').onclick = () => {
    currentViewDate.setMonth(currentViewDate.getMonth() - 1);
    loadCalendar();
};

document.getElementById('nextMonth').onclick = () => {
    currentViewDate.setMonth(currentViewDate.getMonth() + 1);
    loadCalendar();
};

document.addEventListener('DOMContentLoaded', loadCalendar);
