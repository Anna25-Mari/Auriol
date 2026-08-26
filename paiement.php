<?php
require_once __DIR__ . '/layout.php';

$ref = trim($_GET['ref'] ?? '');
$res = reservation_par_reference($ref);
$loc = null;

if (!$res) {
    $loc = location_par_reference($ref);
}

if (!$res && !$loc) {
    entete('Paiement');
    echo '<div class="container py-5 text-center">';
    alerte('warning', 'Commande introuvable.');
    echo '<a class="btn btn-outline-gold mt-3" href="index.php">Retour à l’accueil</a></div>';
    pied();
    exit;
}

$estBillet = $res !== null;
$libelle = $estBillet
    ? $res['ville'] . ' — ' . $res['salle'] . ' (' . date_fr($res['date_spectacle']) . ')'
    : $loc['titre'];
$detail = $estBillet
    ? strtoupper($res['type_billet']) . ' × ' . $res['quantite']
    : 'Location 5 jours';
$montant = (int)($estBillet ? $res['montant_total'] : $loc['montant']);

if (($estBillet && $res['statut'] === 'paye') || (!$estBillet && $loc['statut'] === 'paye')) {
    header('Location: ' . ($estBillet ? 'billet.php?code=' . urlencode($res['code_ticket']) : 'acces_video.php?code=' . urlencode($loc['code_acces'])));
    exit;
}

entete('Paiement');
?>
<section class="section">
    <div class="container" style="max-width:640px;">
        <div class="tag">// Paiement sécurisé</div>
        <h1 class="section-title text-uppercase mb-4">Finaliser la commande</h1>

        <div class="pod-tile p-4 mb-4">
            <div class="small-title"><?php echo $estBillet ? 'Spectacle' : 'Spectacle en ligne'; ?></div>
            <h3 class="pod-title mb-1"><?php echo htmlspecialchars($libelle); ?></h3>
            <p class="text-muted mb-2"><?php echo htmlspecialchars($detail); ?></p>
            <p class="mb-0">Référence : <strong><?php echo htmlspecialchars($ref); ?></strong></p>
            <div class="product-price fs-3 mt-2"><?php echo format_montant($montant); ?></div>
        </div>

        <?php if (isset($_GET['erreur'])) { alerte('danger', 'Paiement refusé : vérifiez les informations de la carte.'); } ?>

        <form method="post" action="valider_paiement.php" class="pod-tile p-4 p-md-5">
            <input type="hidden" name="ref" value="<?php echo htmlspecialchars($ref); ?>" />
            <div class="listen-label mb-3"><i class="fas fa-credit-card me-2"></i>Informations de carte</div>
            <div class="mb-3">
                <input class="form-control" name="titulaire" required placeholder="Nom du titulaire" />
            </div>
            <div class="mb-3">
                <input class="form-control" name="numero" required inputmode="numeric" pattern="[0-9 ]{16,19}" placeholder="0000 0000 0000 0000" />
            </div>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <input class="form-control" name="expiration" required pattern="(0[1-9]|1[0-2])/?[0-9]{2}" placeholder="MM/AA" />
                </div>
                <div class="col-6">
                    <input class="form-control" name="cvv" required inputmode="numeric" pattern="[0-9]{3,4}" placeholder="CVV" />
                </div>
            </div>
            <button class="btn btn-primary btn-lg w-100 text-uppercase fw-bold" type="submit">Payer <?php echo format_montant($montant); ?></button>
            <p class="text-muted small text-center mt-3 mb-0">Paiement de démonstration — connectez ici Stripe, PayPal ou Mobile Money en production.</p>
        </form>
    </div>
</section>
<?php
pied();
