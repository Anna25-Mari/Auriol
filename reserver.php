<?php
require_once __DIR__ . '/layout.php';

$id = isset($_GET['spectacle']) ? (int)$_GET['spectacle'] : 0;
$spec = spectacle_par_id($id);

if (!$spec || (int)$spec['complet'] === 1) {
    entete('Réservation');
    echo '<div class="container py-5 text-center">';
    alerte('warning', $spec ? 'Ce spectacle est complet.' : 'Spectacle introuvable.');
    echo '<a class="btn btn-outline-gold mt-3" href="index.php#tour-dates-sec">Retour aux dates</a></div>';
    pied();
    exit;
}

$erreurs = [];
$old = ['nom' => '', 'email' => '', 'type_billet' => 'standard', 'quantite' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nom'] = trim($_POST['nom'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['type_billet'] = ($_POST['type_billet'] ?? 'standard') === 'vip' ? 'vip' : 'standard';
    $old['quantite'] = max(1, min(10, (int)($_POST['quantite'] ?? 1)));

    if (mb_strlen($old['nom']) < 3) { $erreurs[] = 'Merci d’indiquer votre nom complet.'; }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) { $erreurs[] = 'Adresse email invalide : le billet y sera envoyé.'; }

    if (!$erreurs) {
        $prix = (int)($old['type_billet'] === 'vip' ? $spec['prix_vip'] : $spec['prix_standard']);
        $ref = generer_reference();
        $code = generer_code_ticket();
        $montant = $prix * $old['quantite'];
        $st = db()->prepare('INSERT INTO reservations (reference, spectacle_id, nom, email, type_billet, quantite, montant_total, code_ticket, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $st->execute([$ref, $spec['id'], $old['nom'], $old['email'], $old['type_billet'], $old['quantite'], $montant, $code, 'en_attente']);
        $url = 'paiement.php?ref=' . urlencode($ref) . '&code=' . urlencode($code) . '&montant=' . $montant . '&salle=' . urlencode($spec['salle']) . '&ville=' . urlencode($spec['ville']) . '&date=' . urlencode(date('d/m/Y', strtotime($spec['date_spectacle'])));
        header('Location: ' . $url);
        exit;
    }
}

entete('Réserver — ' . $spec['ville']);
?>
<section class="section">
    <div class="container" style="max-width:760px;">
        <div class="tag">// Réservation</div>
        <h1 class="section-title text-uppercase mb-1"><?php echo htmlspecialchars($spec['ville']); ?></h1>
        <p class="text-muted mb-4"><?php echo htmlspecialchars($spec['salle']); ?> — <?php echo date_fr($spec['date_spectacle']); ?></p>

        <?php foreach ($erreurs as $e) { alerte('danger', $e); } ?>

        <form method="post" class="pod-tile p-4 p-md-5">
            <div class="mb-4">
                <label class="listen-label d-block mb-2">Type de billet</label>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="d-block h-100 m-0 p-3" style="border:1px solid var(--bb-line);cursor:pointer;background:<?php echo $old['type_billet'] === 'standard' ? 'rgba(220,224,103,.08)' : 'transparent'; ?>;">
                            <input type="radio" name="type_billet" value="standard" <?php echo $old['type_billet'] === 'standard' ? 'checked' : ''; ?> onchange="this.closest('.row').querySelectorAll('label').forEach(l=>l.style.background='transparent');this.closest('label').style.background='rgba(220,224,103,.08)';" />
                            <strong>Standard</strong>
                            <div class="product-price"><?php echo format_montant((int)$spec['prix_standard']); ?></div>
                        </label>
                    </div>
                    <div class="col-sm-6">
                        <label class="d-block h-100 m-0 p-3" style="border:1px solid var(--bb-line);cursor:pointer;background:<?php echo $old['type_billet'] === 'vip' ? 'rgba(220,224,103,.08)' : 'transparent'; ?>;">
                            <input type="radio" name="type_billet" value="vip" <?php echo $old['type_billet'] === 'vip' ? 'checked' : ''; ?> onchange="this.closest('.row').querySelectorAll('label').forEach(l=>l.style.background='transparent');this.closest('label').style.background='rgba(220,224,103,.08)';" />
                            <strong>VIP</strong>
                            <div class="product-price"><?php echo format_montant((int)$spec['prix_vip']); ?></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="listen-label d-block mb-2" for="nom">Nom complet</label>
                    <input class="form-control" id="nom" name="nom" type="text" required value="<?php echo htmlspecialchars($old['nom']); ?>" placeholder="Votre nom" />
                </div>
                <div class="col-md-6">
                    <label class="listen-label d-block mb-2" for="email">Email (réception du billet)</label>
                    <input class="form-control" id="email" name="email" type="email" required value="<?php echo htmlspecialchars($old['email']); ?>" placeholder="vous@exemple.com" />
                </div>
                <div class="col-md-6">
                    <label class="listen-label d-block mb-2" for="quantite">Nombre de places</label>
                    <select class="form-control" id="quantite" name="quantite">
                        <?php for ($i = 1; $i <= 10; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo $old['quantite'] === $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <button class="btn btn-primary btn-lg w-100 text-uppercase fw-bold mt-4" type="submit">Continuer vers le paiement</button>
            <p class="text-muted small text-center mt-3 mb-0"><i class="fas fa-lock me-1"></i> Billet numérique envoyé par email après paiement.</p>
        </form>
    </div>
</section>
<?php
pied();
