<?php
require_once __DIR__ . '/layout.php';

$id = isset($_GET['video']) ? (int)$_GET['video'] : 0;
$video = video_par_id($id);

if (!$video) {
    entete('Location');
    echo '<div class="container py-5 text-center">';
    alerte('warning', 'Vidéo introuvable.');
    echo '<a class="btn btn-outline-gold mt-3" href="videos.php">Retour au catalogue</a></div>';
    pied();
    exit;
}

$erreurs = [];
$old = ['nom' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nom'] = trim($_POST['nom'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');

    if (mb_strlen($old['nom']) < 3) { $erreurs[] = 'Merci d’indiquer votre nom complet.'; }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) { $erreurs[] = 'Adresse email invalide : le lien d’accès y sera envoyé.'; }

    if (!$erreurs) {
        $ref = generer_reference();
        $ref = 'LOC' . substr($ref, 3);
        $code = generer_code_ticket();
        db()->prepare('INSERT INTO locations (reference, video_id, nom, email, montant, code_acces) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$ref, $video['id'], $old['nom'], $old['email'], (int)$video['prix'], $code]);
        header('Location: paiement.php?ref=' . urlencode($ref));
        exit;
    }
}

entete('Louer — ' . $video['titre']);
?>
<section class="section">
    <div class="container" style="max-width:760px;">
        <div class="tag">// Location numérique</div>
        <h1 class="section-title text-uppercase mb-1"><?php echo htmlspecialchars($video['titre']); ?></h1>
        <p class="text-muted mb-4">Accès personnel pendant <?php echo DUREE_LOCATION_JOURS; ?> jours après paiement.</p>

        <?php foreach ($erreurs as $e) { alerte('danger', $e); } ?>

        <form method="post" class="pod-tile p-4 p-md-5">
            <input type="hidden" name="video_id" value="<?php echo (int)$video['id']; ?>" />
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="listen-label d-block mb-2" for="nom">Nom complet</label>
                    <input class="form-control" id="nom" name="nom" type="text" required value="<?php echo htmlspecialchars($old['nom']); ?>" placeholder="Votre nom" />
                </div>
                <div class="col-md-6">
                    <label class="listen-label d-block mb-2" for="email">Email (réception du lien)</label>
                    <input class="form-control" id="email" name="email" type="email" required value="<?php echo htmlspecialchars($old['email']); ?>" placeholder="vous@exemple.com" />
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mt-4">
                <div>
                    <span class="listen-label d-block mb-0">Total</span>
                    <div class="product-price fs-3"><?php echo format_montant((int)$video['prix']); ?></div>
                </div>
                <button class="btn btn-primary btn-lg text-uppercase fw-bold px-5" type="submit">Continuer vers le paiement</button>
            </div>
        </form>
    </div>
</section>
<?php
pied();
