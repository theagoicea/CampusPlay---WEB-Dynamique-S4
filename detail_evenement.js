async function loadEvent() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (!id) { window.location.href = 'catalogue.html'; return; }

    try {
        const response = await fetch(`get_event_details.php?id=${id}`);
        const event = await response.json();

        if (event.error) { alert(event.error); return; }

        document.getElementById('title').innerText = event.titre;
        document.getElementById('description').innerText = event.description || "";
        document.getElementById('organizer').innerText = `${event.prenom_orga} ${event.nom_orga}`;
        document.getElementById('location').innerText = event.lieu;
        document.getElementById('type-badge').innerText = event.type_evenement;

        const dD = new Date(event.date_debut.replace(' ', 'T'));
        const dF = new Date(event.date_fin.replace(' ', 'T'));
        document.getElementById('date').innerText = dD.toLocaleDateString('fr-FR', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric'
        });        
        document.getElementById('time').innerText = `${dD.getHours()}h${dD.getMinutes().toString().padStart(2, '0')} - ${dF.getHours()}h${dF.getMinutes().toString().padStart(2, '0')}`;

        const bAcc = document.getElementById('badge-access');
        bAcc.innerText = event.est_reserve_membres == 1 ? "MEMBRES UNIQUEMENT" : "OUVERT À TOUS";
        bAcc.className = event.est_reserve_membres == 1 ? "px-3 py-1 rounded-md bg-blue-500/10 text-blue-400 text-[10px] font-bold" : "px-3 py-1 rounded-md bg-green-500/10 text-green-400 text-[10px] font-bold";

        const bVal = document.getElementById('badge-validation');
        bVal.innerText = event.besoin_validation_inscription == 1 ? "VALIDATION MANUELLE" : "AUTO-VALIDATION";
        bVal.className = "px-3 py-1 rounded-md bg-[#27272A] text-[#A1A1AA] text-[10px] font-bold";

        document.getElementById('restantes').innerText = event.places_restantes;
        document.getElementById('total').innerText = event.capacite_max;
        const ratio = (event.places_restantes / event.capacite_max) * 100;
        
        document.getElementById('loader').classList.add('hidden');
        document.getElementById('content').classList.remove('hidden');
        setTimeout(() => { document.getElementById('progress').style.width = ratio + "%"; }, 100);

        // --- GESTION DU BOUTON (DEJA INSCRIT OU COMPLET) ---
        const btnReserve = document.getElementById('btn-action');
        if (event.est_inscrit) {
            btnReserve.innerText = "DÉJÀ INSCRIT";
            btnReserve.disabled = true;
            btnReserve.className = "px-10 py-4 bg-green-500/10 border border-green-500/20 text-green-500 font-black rounded-2xl cursor-not-allowed";
        } else if (event.places_restantes <= 0) {
            btnReserve.innerText = "COMPLET";
            btnReserve.disabled = true;
            btnReserve.className = "px-10 py-4 bg-[#27272A] text-[#52525B] font-black rounded-2xl cursor-not-allowed";
        }

        if (window.lucide) lucide.createIcons();

        // --- GESTION DU CLIC SUR RÉSERVER (Sans alert) ---
        btnReserve.onclick = async () => {
            btnReserve.disabled = true;
            btnReserve.innerText = "Traitement...";

            const formData = new FormData();
            formData.append('id_evenement', id);

            try {
                const response = await fetch('inscription_event.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    btnReserve.innerText = "Inscription validée !";
                    btnReserve.className = "px-10 py-4 bg-green-500/10 border border-green-500/20 text-green-500 font-black rounded-2xl transition-all";
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    btnReserve.innerText = result.error;
                    btnReserve.className = "px-10 py-4 bg-rose-500/10 border border-rose-500/20 text-rose-500 font-black rounded-2xl transition-all text-xs";
                    setTimeout(() => {
                        btnReserve.disabled = false;
                        btnReserve.innerText = "Réserver ma place";
                        btnReserve.className = "px-10 py-4 bg-[#A78BFA] text-[#0B0B0F] font-black rounded-2xl hover:scale-105 transition-all shadow-xl shadow-[#A78BFA]/10";
                    }, 3000);
                }
            } catch (error) {
                btnReserve.innerText = "Erreur serveur";
                setTimeout(() => { btnReserve.disabled = false; btnReserve.innerText = "Réserver ma place"; }, 2000);
            }
        };

        // --- GESTION DE L'IMAGE DE BANNIÈRE ---
        if (event.image_url && event.image_url !== "") {
            const banner = document.getElementById('event-banner'); 
            if (banner) {
                banner.style.backgroundImage = `linear-gradient(to bottom, rgba(11,11,15,0.8), rgba(11,11,15,0.95)), url('uploads/${event.image_url}')`;
                banner.style.backgroundSize = 'cover';
                banner.style.backgroundPosition = 'center';
            }
        }

    } catch (e) { 
        console.error(e); 
    }
}

document.addEventListener('DOMContentLoaded', loadEvent);