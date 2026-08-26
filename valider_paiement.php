<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

$ref = trim($_POST['ref'] ?? '');
$res = reservation_par_reference($ref);
$loc = $res ? null : location_par_reference($ref);

if (!$res && !$loc) {
    header('Location: index.php');
    exit;
}

$numero = preg_replace('/\D/', '', $_POST['numero'] ?? '');
$valide = strlen($numero) >= 16
    && preg_match('/^(0[1-9]|1[0-2])\/?[0-9]{2}$/', trim($_POST['expiration'] ?? ''))
    && preg_match('/^[0-9]{3,4}$/', trim($_POST['cvv'] ?? ''))
    && mb_strlen(trim($_POST['titulaire'] ?? '')) >= 3;

if (!$valide) {
    header('Location: paiement.php?ref=' . urlencode($ref) . '&erreur=1');
    exit;
}

if ($res) {
    if ($res['statut'] !== 'paye') {
        db()->prepare("UPDATE reservations SET statut = 'paye' WHERE id = ?")->execute([$res['id']]);
        $res = reservation_par_reference($ref);
        envoyer_ticket($res);
    }
    header('Location: billet.php?code=' . urlencode($res['code_ticket']));
} else {
    if ($loc['statut'] !== 'paye') {
        db()->prepare("UPDATE locations SET statut = 'paye', expire_le = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?")
            ->execute([DUREE_LOCATION_JOURS, $loc['id']]);
        $loc = location_par_reference($ref);
        envoyer_lien_video($loc);
    }
    header('Location: acces_video.php?code=' . urlencode($loc['code_acces']));
}
exit;
