async function loadEvent() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (!id) { window.location.href = 'catalogue.html'; return; }

    try {
        const response = await fetch(`get_event_details.php?id=${id}`);
        const event = await response.json();

        if (event.error) { alert(event.error); return; }

        // Remplissage simple
        document.getElementById('title').innerText = event.titre;
        document.getElementById('description').innerText = event.description;
        document.getElementById('organizer').innerText = `${event.prenom_orga} ${event.nom_orga}`;
        document.getElementById('location').innerText = event.lieu;
        document.getElementById('type-badge').innerText = event.type_evenement;

        // Gestion Dates et Heures
        const dD = new Date(event.date_debut.replace(' ', 'T'));
        const dF = new Date(event.date_fin.replace(' ', 'T'));
        document.getElementById('date').innerText = dD.toLocaleDateString('fr-FR', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric'  // <-- On ajoute l'année ici
        });        
        document.getElementById('time').innerText = `${dD.getHours()}h${dD.getMinutes().toString().padStart(2, '0')} - ${dF.getHours()}h${dF.getMinutes().toString().padStart(2, '0')}`;

        // Accès (Membres / Libre)
        const bAcc = document.getElementById('badge-access');
        bAcc.innerText = event.est_reserve_membres == 1 ? "MEMBRES UNIQUEMENT" : "OUVERT À TOUS";
        bAcc.className = event.est_reserve_membres == 1 ? "px-3 py-1 rounded-md bg-blue-500/10 text-blue-400 text-[10px] font-bold" : "px-3 py-1 rounded-md bg-green-500/10 text-green-400 text-[10px] font-bold";

        // Validation (Oui / Non)
        const bVal = document.getElementById('badge-validation');
        bVal.innerText = event.besoin_validation_inscription == 1 ? "VALIDATION MANUELLE" : "AUTO-VALIDATION";
        bVal.className = "px-3 py-1 rounded-md bg-[#27272A] text-[#A1A1AA] text-[10px] font-bold";

        // Places
        document.getElementById('restantes').innerText = event.places_restantes;
        document.getElementById('total').innerText = event.capacite_max;
        const ratio = (event.places_restantes / event.capacite_max) * 100;
        
        // Affichage contenu et animation barre
        document.getElementById('loader').classList.add('hidden');
        document.getElementById('content').classList.remove('hidden');
        setTimeout(() => { document.getElementById('progress').style.width = ratio + "%"; }, 100);

        if(event.places_restantes <= 0) {
            const btn = document.getElementById('btn-action');
            btn.innerText = "COMPLET";
            btn.disabled = true;
            btn.className = "px-10 py-4 bg-[#27272A] text-[#52525B] font-black rounded-2xl cursor-not-allowed";
        }

        lucide.createIcons();
    } catch (e) { console.error(e); }


    // --- GESTION DU CLIC SUR RÉSERVER ---
    const btnReserve = document.getElementById('btn-action');
    
    btnReserve.onclick = async () => {
        // Désactiver le bouton pour éviter les doubles clics
        btnReserve.disabled = true;
        btnReserve.innerText = "Traitement...";

        const formData = new FormData();
        formData.append('id_evenement', id);

        try {
            const response = await fetch('inscription_event.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alert("Succès : " + result.message);
                // On recharge la page pour mettre à jour les compteurs de places
                window.location.reload();
            } else {
                alert("Erreur : " + result.error);
                btnReserve.disabled = false;
                btnReserve.innerText = "Réserver ma place";
            }
        } catch (error) {
            console.error("Erreur réservation:", error);
            btnReserve.disabled = false;
        }
    };


    if (event.image_url) {
        // Si tu as un élément img id="event-image" dans ton HTML
        const imgElement = document.getElementById('event-image');
        if (imgElement) imgElement.src = event.image_url;
        
        // Ou si tu veux mettre l'image en fond de bannière
        const banner = document.getElementById('event-banner'); // la div de bannière
        if (banner) {
            banner.style.backgroundImage = `linear-gradient(to bottom, rgba(11,11,15,0.2), #0B0B0F), url('${event.image_url}')`;
            banner.style.backgroundSize = 'cover';
            banner.style.backgroundPosition = 'center';
        }
    }
}

document.addEventListener('DOMContentLoaded', loadEvent);
