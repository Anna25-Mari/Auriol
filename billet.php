<?php
require_once __DIR__ . '/layout.php';

$code = trim($_GET['code'] ?? '');
$res = reservation_par_code($code);

if (!$res) {
    entete('Billet');
    echo '<div class="container py-5 text-center" style="background:#050505;min-height:100vh;">';
    echo '<div class="d-flex align-items-center justify-center h-100">';
    echo '<div class="text-center">';
    alerte('danger', 'Code de billet introuvable.');
    echo '<br/><br/>';
    echo '<a href="index.php" class="btn btn-outline-gold mt-3">Retour à l\'accueil</a>';
    echo '</div></div></div>';
    pied();
    exit;
}

if ($res['statut'] !== 'paye') {
    entete('Billet');
    echo '<div class="container py-5 text-center" style="background:#050505;min-height:100vh;">';
    echo '<div class="d-flex align-items-center justify-center h-100">';
    echo '<div class="text-center">';
    alerte('warning', 'Paiement en attente. Votre billet vous sera envoyé par email une fois le paiement confirmé.');
    echo '<br/><br/>';
    echo '<a href="index.php" class="btn btn-outline-gold mt-3">Retour à l\'accueil</a>';
    echo '</div></div></div>';
    pied();
    exit;
}

entete('Votre billet — ' . $res['ville']);
?>
<section class="section" style="background:#050505;color:#fff;">
    <div class="container" style="max-width:640px;">
        <div class="tag mb-3" style="background:rgba(220,224,103,.08);">// Billetterie évènementielle</div>
        <h1 class="section-title text-uppercase mb-4" style="font-size:1.9rem;">Billet numérique</h1>

        <div class="pod-tile p-4 p-md-5 text-center" style="background:#111;border-bottom:2px dashed var(--bb-line);">
            <div>
                <h2 class="pod-title mb-0" style="font-size:2rem;">Billet</h2>
            </div>
        </div>

        <div class="pod-tile p-4 p-md-5" style="background:#111;min-height:420px;">
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="small-title">Spectacle</div>
                    <strong><?php echo htmlspecialchars($res['ville']); ?></strong>
                </div>
                <div class="col-6">
                    <div class="small-title">Date</div>
                    <strong><?php echo date_fr($res['date_spectacle']); ?></strong>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="small-title">Salle</div>
                    <strong><?php echo htmlspecialchars($res['salle']); ?></strong>
                </div>
                <div class="col-6">
                    <div class="small-title">Type</div>
                    <strong><?php echo strtoupper(htmlspecialchars($res['type_billet'])); ?> × <?php echo (int)$res['quantite']; ?></strong>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="small-title">Titulaire</div>
                    <strong><?php echo htmlspecialchars($res['nom']); ?></strong>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="small-title">Référence</div>
                    <strong><?php echo htmlspecialchars($res['reference']); ?></strong>
                </div>
                <div class="col-6">
                    <div class="small-title">Montant</div>
                    <strong><?php echo format_montant((int)$res['montant_total']); ?></strong>
                </div>
            </div>

            <div class="text-center bg-white p-3 rounded mt-4">
                <div id="qrcode" class="d-inline-block"></div>
            </div>
            <p class="text-center mt-3 mb-0 code-ticket" style="font-family:monospace;font-size:1.25rem;letter-spacing:.2em;color:#DCB86B;">Code : <?php echo htmlspecialchars($res['code_ticket']); ?></p>
            <p class="text-center text-muted small mt-2">Présentez ce QR code à l'entrée. Un seul scan possible.</p>
        </div>

        <div class="d-flex gap-3 justify-content-center mt-4 no-print">
            <button class="btn btn-primary text-uppercase fw-bold" onclick="window.print()"><i class="fas fa-print me-2"></i>Imprimer</button>
            <a href="index.php" class="btn btn-outline-gold text-uppercase fw-bold">Retour au site</a>
        </div>
        <?php if (!empty($GLOBALS['mail_echec'])) { ?>
            <p class="text-muted small text-center mt-3">Note : l'envoi email est en mode démonstration (voir config.php pour activer Gmail).</p>
        <?php } ?>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qrcode'), {
    text: <?php echo json_encode($res['code_ticket']); ?>,
    width: 200,
    height: 200,
    colorDark: '#111111',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
});
</script>
<style>
@media print {
    #mainNav, footer, .no-print { display: none !important; }
    body { background: #fff !important; }
}
.code-ticket { font-family: monospace; font-size: 1.25rem; letter-spacing: .2em; color: var(--bb-accent); }
</style>
<?php
pied();