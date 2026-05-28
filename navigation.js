async function loadSidebar() {
    const navDiv = document.getElementById('sidebar-nav');
    const response = await fetch('auth_status.php');
    const user = await response.json();
    const currentPage = window.location.pathname.split("/").pop();

    const menuItems = [
        { name: 'Accueil', icon: 'home', url: 'Accueil.html' },
        { name: 'Catalogue', icon: 'calendar', url: 'Catalogue.html' },
        { name: 'Réservations', icon: 'settings', url: 'Reservations.html' },
        { name: 'Evénements', icon: 'music', url: 'Evenements.html' },
        { name: user.is_logged ? `Profil (${user.prenom})` : 'Profil / Connexion', icon: 'user', url: 'Profil.html' },
        { name: 'Notifications', icon: 'bell', url: 'Notifications.html' },
        { name: 'Forums', icon: 'message-square', url: 'Forums.html' }
    ];

    navDiv.innerHTML = menuItems.map(item => `
        <a href="${item.url}" class="nav-item ${currentPage === item.url ? 'active' : ''}">
            <i data-lucide="${item.icon}" class="w-4 h-4"></i><span>${item.name}</span>
        </a>
    `).join('');

    lucide.createIcons();
}
document.addEventListener('DOMContentLoaded', loadSidebar);
