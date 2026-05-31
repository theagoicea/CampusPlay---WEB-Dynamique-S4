/**
 * NAVIGATION.JS
 * Gère l'affichage dynamique de la Sidebar et de la TopBar selon le rôle de l'utilisateur.
 */

async function loadSidebar() {
    const navDiv = document.getElementById('sidebar-nav');
    const avatarLink = document.getElementById('user-avatar-link');
    
    if (!navDiv) return;

    // 1. Récupération du statut de l'utilisateur (Session PHP)
    let user = { is_logged: false, role: 'Visiteur', prenom: '', nom: '' };

    try {
        const response = await fetch('auth_status.php');
        if (response.ok) {
            user = await response.json();
        }
    } catch (error) {
        console.error("Erreur auth_status:", error);
    }
    
    // 2. Mise à jour de l'avatar (Initiales)
    if (avatarLink) {
        if (user.is_logged && user.prenom && user.nom) {
            const initiales = (user.prenom.charAt(0) + user.nom.charAt(0)).toUpperCase();
            avatarLink.textContent = initiales;
        } else {
            avatarLink.textContent = "??"; 
        }
    }

    // 3. Configuration du Menu
    const currentPage = window.location.pathname.split("/").pop().toLowerCase();
    const activeClasses = "bg-[#A78BFA] text-[#0B0B0F] font-bold shadow-lg shadow-[#A78BFA]/20";
    const inactiveClasses = "text-[#A1A1AA] hover:text-[#F5F5F7] hover:bg-[#27272A]";

    // Liens de base (Toujours visibles)
    let menuItems = [
        { name: 'Accueil', icon: 'home', url: 'accueil.html' },
        { name: 'Catalogue', icon: 'layers', url: 'catalogue.html' },
    ];

    // Liens spécifiques aux utilisateurs connectés
    if (user.is_logged) {
        menuItems.push({ name: 'Réservations', icon: 'clock', url: 'reservations.php' });
        menuItems.push({ name: 'Profil', icon: 'user', url: 'profil.html' });
        menuItems.push({ name: 'Notifications', icon: 'bell', url: 'notifications.php' });

        // --- GESTION DES RÔLES SPÉCIAUX ---
        
        // Si c'est un ADMIN : Accès au Dashboard complet
        if (user.role === 'Admin') {
            menuItems.push({ name: 'Tableau Bord Admin', icon: 'layout-grid', url: 'admin.php' });
        } 
        // Si c'est un ORGANISATEUR : Accès uniquement à la gestion de ses inscriptions
        else if (user.role === 'Organisateur') {
            menuItems.push({ name: 'Gestion Inscriptions', icon: 'users', url: 'organisateur_dashboard.php' });
        }

    } else {
        // Lien si NON CONNECTÉ
        menuItems.push({ name: 'Connexion', icon: 'log-in', url: 'authentification.html' });
    }

    // Forum (Visible par tous)
    menuItems.push({ name: 'Forum', icon: 'message-square', url: 'forums.html' });

    // 4. Génération du HTML
    navDiv.innerHTML = menuItems.map(item => {
        // Détection de la page active
        const isCurrent = (currentPage === item.url.toLowerCase());
        
        return `
            <a href="${item.url}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 ${isCurrent ? activeClasses : inactiveClasses}">
                <i data-lucide="${item.icon}" class="w-4 h-4 shrink-0"></i>
                <span class="text-sm font-medium">${item.name}</span>
            </a>
        `;
    }).join('');

    // 5. Initialisation des icônes Lucide
    if (window.lucide) {
        lucide.createIcons();
    }
}

// Lancement au chargement du DOM
document.addEventListener('DOMContentLoaded', loadSidebar);