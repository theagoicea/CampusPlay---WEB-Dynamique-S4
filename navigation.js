async function loadSidebar() {
    const navDiv = document.getElementById('sidebar-nav');
    const avatarLink = document.getElementById('user-avatar-link');
    
    if (!navDiv) return;

    // État par défaut (visiteur non connecté)
    let user = { is_logged: false, role: 'Visiteur', prenom: '', nom: '' };

    // 1. Récupération du statut de l'utilisateur via le fichier PHP
    try {
        const response = await fetch('auth_status.php');
        if (response.ok) {
            user = await response.json();
        }
    } catch (error) {
        console.error("Erreur lors de la récupération du statut auth:", error);
    }
    
    // 2. Mise à jour des initiales dans la TopBar
    if (avatarLink) {
        if (user.is_logged && user.prenom && user.nom) {
            const initiales = (user.prenom.charAt(0) + user.nom.charAt(0)).toUpperCase();
            avatarLink.textContent = initiales;
        } else {
            avatarLink.textContent = "??"; 
        }
    }

    // 3. Définition du menu dynamique
    const currentPage = window.location.pathname.split("/").pop().toLowerCase();
    const activeClasses = "bg-[#A78BFA] text-[#0B0B0F] font-bold shadow-lg shadow-[#A78BFA]/20";
    const inactiveClasses = "text-[#A1A1AA] hover:text-[#F5F5F7] hover:bg-[#27272A]";

    // Liens visibles par TOUT LE MONDE
    let menuItems = [
        { name: 'Accueil', icon: 'home', url: 'index.php' },
        { name: 'Événements', icon: 'calendar', url: 'evenements.php' },
    ];

    // Liens pour les utilisateurs CONNECTÉS
    if (user.is_logged) {
        menuItems.push({ name: 'Réservations', icon: 'clock', url: 'reservations.php' });
        menuItems.push({ name: 'Notifications', icon: 'bell', url: 'notifications.php' });
        menuItems.push({ name: 'Profil', icon: 'user', url: 'b_profil.php' });
    } else {
        // Lien si NON CONNECTÉ
        menuItems.push({ name: 'Connexion', icon: 'log-in', url: 'b_authentification.php' });
    }

    // Lien pour les ADMINS uniquement
    if (user.role === 'Admin') {
        menuItems.push({ name: 'Dashboard Admin', icon: 'layout-grid', url: 'admin.php' });
    }

    // Forum (toujours visible)
    menuItems.push({ name: 'Forum', icon: 'message-square', url: 'forum.php' });

    // 4. Génération du HTML du menu
    navDiv.innerHTML = menuItems.map(item => {
        // Vérifie si la page actuelle correspond à l'URL de l'item
        const isCurrent = (currentPage === item.url.toLowerCase() || (currentPage === "" && item.url === "index.php"));
        
        return `
            <a href="${item.url}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 ${isCurrent ? activeClasses : inactiveClasses}">
                <i data-lucide="${item.icon}" class="w-4 h-4 shrink-0"></i>
                <span class="text-sm font-medium">${item.name}</span>
            </a>
        `;
    }).join('');

    // 5. Initialisation des icônes Lucide après injection du HTML
    if (window.lucide) {
        lucide.createIcons();
    }
}

// Lancement au chargement du DOM
document.addEventListener('DOMContentLoaded', loadSidebar);
