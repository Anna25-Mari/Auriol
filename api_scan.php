<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$code = strtoupper(trim($_POST['code'] ?? $_GET['code'] ?? ''));

if ($code === '') {
    echo json_encode(['ok' => false, 'message' => 'Code manquant.']);
    exit;
}

$res = reservation_par_code($code);

if (!$res) {
    echo json_encode(['ok' => false, 'message' => 'Billet inconnu.', 'code' => $code]);
    exit;
}
if ($res['statut'] !== 'paye') {
    echo json_encode(['ok' => false, 'message' => 'Paiement non confirmé.', 'code' => $code]);
    exit;
}
if ((int)$res['utilise'] === 1) {
    echo json_encode([
        'ok' => false,
        'message' => 'Billet déjà utilisé le ' . date('d/m/Y à H:i', strtotime($res['scan_le'])) . '.',
        'code' => $code,
        'titulaire' => $res['nom'],
    ]);
    exit;
}

db()->prepare('UPDATE reservations SET utilise = 1, scan_le = NOW() WHERE id = ?')->execute([$res['id']]);

echo json_encode([
    'ok' => true,
    'message' => 'Accès autorisé',
    'code' => $code,
    'titulaire' => $res['nom'],
    'spectacle' => $res['ville'] . ' — ' . $res['salle'],
    'categorie' => strtoupper($res['type_billet']) . ' × ' . (int)$res['quantite'],
]);
