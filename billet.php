<?php
require_once __DIR__ . '/layout.php';

$code = trim($_GET['code'] ?? '');
$res = reservation_par_code($code);

if (!$res || $res['statut'] !== 'paye') {
    entete('Billet');
    echo '<div class="container py-5 text-center">';
    alerte('warning', 'Billet introuvable ou paiement non confirmé.');
    echo '<a class="btn btn-outline-gold mt-3" href="index.html#tour-dates-sec">Retour aux dates</a></div>';
    pied();
    exit;
}

entete('Votre billet — ' . $res['ville']);
?>
<section class="section">
    <div class="container" style="max-width:560px;">
        <div id="ticket" class="pod-tile p-0 overflow-hidden">
            <div class="p-4 p-md-5 text-center" style="background:#111;border-bottom:2px dashed var(--bb-line);">
                <div class="tag mb-1"><?php echo htmlspecialchars(SITE_NOM); ?></div>
                <h1 class="section-title text-uppercase" style="font-size:1.9rem;">Billet numérique</h1>
            </div>
            <div class="p-4 p-md-5">
                <h3 class="pod-title mb-0"><?php echo htmlspecialchars($res['ville']); ?></h3>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($res['salle']); ?> — <?php echo date_fr($res['date_spectacle']); ?></p>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="small-title">Titulaire</div>
                        <strong><?php echo htmlspecialchars($res['nom']); ?></strong>
                    </div>
                    <div class="col-6">
                        <div class="small-title">Catégorie</div>
                        <strong><?php echo strtoupper(htmlspecialchars($res['type_billet'])); ?> × <?php echo (int)$res['quantite']; ?></strong>
                    </div>
                    <div class="col-6">
                        <div class="small-title">Total payé</div>
                        <strong><?php echo format_montant((int)$res['montant_total']); ?></strong>
                    </div>
                    <div class="col-6">
                        <div class="small-title">Référence</div>
                        <strong><?php echo htmlspecialchars($res['reference']); ?></strong>
                    </div>
                </div>

                <div class="text-center bg-white p-3 rounded">
                    <div id="qrcode" class="d-inline-block"></div>
                </div>
                <p class="text-center mt-3 mb-0 code-ticket"><?php echo htmlspecialchars($res['code_ticket']); ?></p>
                <p class="text-center text-muted small mt-2">Présentez ce QR code à l’entrée. Un seul scan possible.</p>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-center mt-4 no-print">
            <button class="btn btn-primary text-uppercase fw-bold" onclick="window.print()"><i class="fas fa-print me-2"></i>Imprimer</button>
            <a class="btn btn-outline-gold text-uppercase fw-bold" href="index.html">Retour au site</a>
        </div>
        <?php if (!empty($GLOBALS['mail_echec'])) { ?>
            <p class="text-muted small text-center mt-3">Note : l’envoi email est en mode démonstration (voir config.php pour activer Gmail).</p>
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
