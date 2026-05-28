//Version thea 
async function loadSidebar() {
    const navDiv = document.getElementById('sidebar-nav');
    
    // APPEL DU FICHIER BACKEND AVEC LE PREFIXE b_
    const response = await fetch('auth_status.php');
    const user = await response.json();
    
    const currentPage = window.location.pathname.split("/").pop();

    const baseClasses = "w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all cursor-pointer text-left";
    const inactiveClasses = "text-[#A1A1AA] hover:text-[#F5F5F7] hover:bg-[#27272A]";
    const activeClasses = "bg-[#A78BFA] text-[#0B0B0F] font-bold";

    let menuItems = [
        { name: 'Accueil', icon: 'home', url: 'accueil.html' },
        { name: 'Catalogue', icon: 'calendar', url: 'catalogue.html' },
        { name: 'Réservations', icon: 'settings', url: 'reservations.php' },
        { name: 'Evénements', icon: 'music', url: 'evenements.html' }
    ];

    if (user.is_logged) {
        menuItems.push({ name: `Profil (${user.prenom})`, icon: 'user', url: 'profil.html' });
    } else {
        menuItems.push({ name: 'Authentification', icon: 'user', url: 'authentification.html' });
    }

    menuItems.push({ name: 'Notifications', icon: 'bell', url: 'notifications.html' });

    if (user.role === 'Admin') {
        menuItems.push({ name: 'Tableau de bord Admin', icon: 'layout-grid', url: 'admin.html' });
    }

    menuItems.push({ name: 'Forums', icon: 'message-square', url: 'forums.html' });

    navDiv.innerHTML = menuItems.map(item => {
        const stateClasses = (currentPage.toLowerCase() === item.url.toLowerCase()) ? activeClasses : inactiveClasses;
        
        return `
            <a href="${item.url}" class="${baseClasses} ${stateClasses}">
                <i data-lucide="${item.icon}" class="w-4 h-4 shrink-0"></i>
                <span class="text-sm font-medium">${item.name}</span>
            </a>
        `;
    }).join('');

    lucide.createIcons();
}

document.addEventListener('DOMContentLoaded', loadSidebar);
