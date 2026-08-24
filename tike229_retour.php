<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

$ref = trim($_GET['ref'] ?? '');
$code = trim($_GET['code'] ?? '');
$montant = (int)($_GET['montant'] ?? 0);
$salle = trim($_GET['salle'] ?? '');
$ville = trim($_GET['ville'] ?? '');
$date = trim($_GET['date'] ?? '');

if (!$ref || !$code) {
    entete('Erreur');
    echo '<div class="container py-5 text-center">';
    alerte('danger', 'Réponse de paiement invalide.');
    echo '<a class="btn btn-outline-gold mt-3" href="index.html">Retour à l\'accueil</a></div>';
    pied();
    exit;
}

$res = reservation_par_reference($ref);

if (!$res) {
    entete('Erreur');
    echo '<div class="container py-5 text-center">';
    alerte('danger', 'Réservation introuvable.');
    echo '<a class="btn btn-outline-gold mt-3" href="index.html">Retour à l\'accueil</a></div>';
    pied();
    exit;
}

if ($res['statut'] === 'paye') {
    header('Location: billet.php?code=' . urlencode($res['code_ticket']));
    exit;
}

db()->prepare("UPDATE reservations SET statut = 'paye' WHERE id = ?")->execute([$res['id']]);
$res = reservation_par_reference($ref);

envoyer_ticket($res);

entete('Paiement confirmé');
?>
<section class="section">
    <div class="container text-center">
        <div class="tag">// Paiement confirmé</div>
        <h1 class="section-title text-uppercase mb-4">Paiement réussi</h1>
        <div class="pod-tile p-4 mb-4" style="background: #e8f5e9;">
            <div class="small-title">Spectacle</div>
            <h3><?php echo htmlspecialchars($ville . ' — ' . $salle . ' (' . $date . ')'); ?></h3>
            <p class="text-muted mb-2">Référence : <strong><?php echo htmlspecialchars($ref); ?></strong></p>
            <p class="mb-0">Montant payé : <strong><?php echo format_montant($montant); ?></strong></p>
        </div>

        <div class="pod-tile p-4">
            <h3 class="pod-title mb-1">Votre billet</h3>
            <p class="text-muted mb-4">Votre billet est prêt. <a href="billet.php?code=<?php echo urlencode($res['code_ticket']); ?>" class="btn btn-primary">Voir mon billet</a></p>
        </div>
    </div>
</section>
<?php pied();