async function loadSidebar() {
    const navDiv = document.getElementById('sidebar-nav');
    const avatarLink = document.getElementById('user-avatar-link');
    
    if (!navDiv) return;

    let user = { is_logged: false, role: 'Visiteur', prenom: '', nom: '' };

    try {
        const response = await fetch('auth_status.php');
        user = await response.json();
    } catch (error) {
        console.error("Erreur auth_status", error);
    }
    
    // --- MISE À JOUR DES INITIALES ---
    if (avatarLink) {
        if (user.is_logged && user.prenom && user.nom) {
            const initiales = (user.prenom.charAt(0) + user.nom.charAt(0)).toUpperCase();
            avatarLink.textContent = initiales;
        } else {
            avatarLink.textContent = "??"; 
        }
    }

    const currentPage = window.location.pathname.split("/").pop().toLowerCase();
    const activeClasses = "bg-[#A78BFA] text-[#0B0B0F] font-bold shadow-lg shadow-[#A78BFA]/10";
    const inactiveClasses = "text-[#A1A1AA] hover:text-[#F5F5F7] hover:bg-[#27272A]";

    let menuItems = [
        { name: 'Accueil', icon: 'home', url: 'accueil.html' },
        { name: 'Catalogue', icon: 'calendar', url: 'catalogue.html' },
        { name: 'Réservations', icon: 'settings', url: 'reservations.php' },
    ];

    if (user.is_logged) {
        menuItems.push({ name: 'Profil', icon: 'user', url: 'profil.html' });
        menuItems.push({ name: 'Notifications', icon: 'bell', url: 'notifications.html' });
    } else {
        menuItems.push({ name: 'Connexion', icon: 'user', url: 'authentification.html' });
    }

    if (user.role === 'Admin') {
        menuItems.push({ name: 'Tableau de Bord Admin', icon: 'layout-grid', url: 'admin.php' });
    }
    menuItems.push({ name: 'Forum', icon: 'message-square', url: 'forums.html' });

    navDiv.innerHTML = menuItems.map(item => {
        const isCurrent = (currentPage === item.url.toLowerCase() || (currentPage === "" && item.url === "accueil.html"));
        return `
            <a href="${item.url}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ${isCurrent ? activeClasses : inactiveClasses}">
                <i data-lucide="${item.icon}" class="w-4 h-4 shrink-0"></i>
                <span class="text-sm font-medium">${item.name}</span>
            </a>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}
document.addEventListener('DOMContentLoaded', loadSidebar);
