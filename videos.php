<?php
require_once __DIR__ . '/layout.php';

$videos = db()->query('SELECT * FROM videos ORDER BY id')->fetchAll();

entete('Spectacles en ligne');
?>
<section class="section">
    <div class="container">
        <div class="title-bar">
            <div>
                <div class="tag">// En ligne</div>
                <!-- Spectacles à la demande : catalogue de vidéos en location -->
<h1 class="section-title text-uppercase">Spectacles à la demande</h1>
            </div>
        </div>
        <p class="text-muted mb-5" style="max-width:44rem;">
            <!-- Location pendant <?php echo DUREE_LOCATION_JOURS; ?> jours, lien expirant ensuite -->
            Spectacles filmés, films et podcasts premium. Payez une fois, regardez pendant <?php echo DUREE_LOCATION_JOURS; ?> jours, le lien expire ensuite.
        </p>

        <div class="row g-4">
            <?php foreach ($videos as $v) { ?>
                <div class="col-lg-4 col-md-6">
                    <div class="product-tile">
                        <a class="poster-card" href="louer.php?video=<?php echo (int)$v['id']; ?>" style="aspect-ratio:16/10;">
                            <img src="<?php echo htmlspecialchars($v['miniature'] ?: 'assets/img/portfolio/1.jpg'); ?>" alt="<?php echo htmlspecialchars($v['titre']); ?>" />
                            <span class="stock-tag in"><i class="fas fa-play me-1"></i> Aperçu</span>
                            <div class="poster-title"><?php echo htmlspecialchars($v['titre']); ?></div>
                        </a>
                        <div class="product-body">
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($v['description']); ?></p>
                            <div class="product-price"><?php echo format_montant((int)$v['prix']); ?> <small class="text-muted fw-normal">/ <?php echo DUREE_LOCATION_JOURS; ?> jours</small></div>
                        </div>
                        <a class="btn btn-primary text-uppercase fw-bold product-btn" href="louer.php?video=<?php echo (int)$v['id']; ?>">
                            <i class="fas fa-play me-2"></i>Louer maintenant
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php
pied();
