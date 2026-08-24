<?php
require_once __DIR__ . '/layout.php';

$cle = $_GET['cle'] ?? '';
if ($cle !== SCAN_KEY) {
    http_response_code(403);
    entete('Accès refusé');
    echo '<div class="container py-5 text-center"><div class="tag">Contrôle d’accès</div>';
    alerte('danger', 'Clé invalide. Ajoutez ?cle=' . 'VOTRE_CLE à l’URL.');
    pied();
    exit;
}

entete('Scan des billets');
?>
<section class="section">
    <div class="container" style="max-width:640px;">
        <div class="tag">// Contrôle d’accès</div>
        <h1 class="section-title text-uppercase mb-4">Scanner un billet</h1>

        <div id="resultat" class="pod-tile p-4 mb-4 text-center d-none"></div>

        <div class="pod-tile p-4 mb-4">
            <div class="listen-label mb-3"><i class="fas fa-camera me-2"></i>Scanner avec la caméra</div>
            <div id="lecteur" style="min-height:240px;"></div>
        </div>

        <form id="form-manuel" class="d-flex gap-2">
            <input class="form-control" id="code-manuel" placeholder="Ou saisir le code (AUR-XXXX…)" autocomplete="off" />
            <button class="btn btn-primary text-uppercase fw-bold px-4" type="submit">Vérifier</button>
        </form>
    </div>
</section>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const resultat = document.getElementById('resultat');

function afficher(d) {
    resultat.classList.remove('d-none', 'alert-success', 'alert-danger');
    resultat.classList.add(d.ok ? 'border-success' : 'border-danger');
    resultat.innerHTML =
        '<i class="fas ' + (d.ok ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger') + ' fa-3x mb-3"></i>' +
        '<h3 class="pod-title">' + d.message + '</h3>' +
        (d.titulaire ? '<p class="mb-1"><strong>' + d.titulaire + '</strong></p>' : '') +
        (d.spectacle ? '<p class="text-muted mb-1">' + d.spectacle + '</p>' : '') +
        (d.categorie ? '<span class="badge-weekly">' + d.categorie + '</span>' : '');
    resultat.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function verifier(code) {
    fetch('api_scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'code=' + encodeURIComponent(code)
    })
        .then(r => r.json())
        .then(afficher)
        .catch(() => afficher({ ok: false, message: 'Erreur réseau.' }));
}

document.getElementById('form-manuel').addEventListener('submit', e => {
    e.preventDefault();
    const c = document.getElementById('code-manuel').value.trim();
    if (c) { verifier(c); document.getElementById('code-manuel').value = ''; }
});

const scanner = new Html5Qrcode('lecteur');
scanner.start(
    { facingMode: 'environment' },
    { fps: 10, qrbox: 220 },
    texte => { scanner.pause(); verifier(texte); setTimeout(() => scanner.resume(), 2500); },
    () => {}
).catch(() => {
    document.getElementById('lecteur').innerHTML = '<p class="text-muted mb-0">Caméra indisponible — utilisez la saisie manuelle.</p>';
});
</script>
<?php
pied();
