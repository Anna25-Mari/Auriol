<?php
require_once __DIR__ . '/layout.php';

$code = trim($_GET['code'] ?? '');
$loc = location_par_code($code);

if (!$loc || $loc['statut'] !== 'paye') {
    entete('Accès vidéo');
    echo '<div class="container py-5 text-center">';
    alerte('warning', 'Lien d’accès invalide ou paiement non confirmé.');
    echo '<a class="btn btn-outline-gold mt-3" href="videos.php">Voir le catalogue</a></div>';
    pied();
    exit;
}

$expire = strtotime($loc['expire_le']);
$actif = $expire > time();
$restant = $expire - time();
$jours = floor($restant / 86400);
$heures = floor(($restant % 86400) / 3600);
$minutes = floor(($restant % 3600) / 60);

entete('Regarder — ' . $loc['titre']);
?>
<section class="section">
    <div class="container" style="max-width:960px;">
        <div class="tag">// Votre séance</div>
        <h1 class="section-title text-uppercase mb-4"><?php echo htmlspecialchars($loc['titre']); ?></h1>

        <?php if ($actif) { ?>
            <div class="ratio ratio-16x9 mb-4" style="background:#000;">
                <?php if (!empty($loc['url_video'])) { ?>
                    <iframe src="<?php echo htmlspecialchars($loc['url_video']); ?>" title="<?php echo htmlspecialchars($loc['titre']); ?>" allowfullscreen style="border:0;"></iframe>
                <?php } else { ?>
                    <div class="d-flex align-items-center justify-content-center text-center p-4">
                        <div>
                            <i class="fas fa-film fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Emplacement du lecteur vidéo.<br />Ajoutez l’URL de la vidéo dans la table « videos » (champ url_video).</p>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="pod-tile p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div>
                    <span class="listen-label d-block mb-1"><i class="fas fa-clock me-2"></i>Accès actif</span>
                    <strong>Expire dans <?php echo $jours; ?> j <?php echo $heures; ?> h <?php echo $minutes; ?> min</strong>
                    <span class="text-muted">(le <?php echo date('d/m/Y à H:i', $expire); ?>)</span>
                </div>
                <a class="btn btn-outline-gold text-uppercase fw-bold" href="videos.php">Autres spectacles</a>
            </div>
        <?php } else { ?>
            <div class="pod-tile p-5 text-center">
                <i class="fas fa-hourglass-end fa-3x text-muted mb-3"></i>
                <h3 class="pod-title">Votre accès a expiré</h3>
                <p class="text-muted">Cet accès était valable jusqu’au <?php echo date('d/m/Y à H:i', $expire); ?>.<br />Louez à nouveau pour revoir le spectacle.</p>
                <a class="btn btn-primary text-uppercase fw-bold mt-2" href="videos.php">Revoir les offres</a>
            </div>
        <?php } ?>
    </div>
</section>
<?php
pied();
