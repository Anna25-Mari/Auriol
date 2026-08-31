<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erreur' => 'Méthode non autorisée.']);
    exit;
}

$nom    = trim($_POST['nom']    ?? '');
$email  = trim($_POST['email']  ?? '');
$sujet  = trim($_POST['sujet']  ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if ($nom === '')   { $errors[] = 'Le nom est requis.'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Adresse email invalide.'; }
if ($sujet === '') { $errors[] = 'Le sujet est requis.'; }
if ($message === '') { $errors[] = 'Le message est requis.'; }

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erreurs' => $errors]);
    exit;
}

$html = "Nom : " . htmlspecialchars($nom) . "\n"
      . "Email : " . htmlspecialchars($email) . "\n"
      . "Sujet : " . htmlspecialchars($sujet) . "\n\n"
      . "Message :\n" . htmlspecialchars($message);

$envoye = false;

if (defined('SMTP_PASS') && strpos(SMTP_PASS, 'MOT_DE_PASSE') === false && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(SMTP_USER, SITE_NOM);
        $mail->addAddress(SMTP_USER);
        $mail->addReplyTo($email, $nom);
        $mail->Subject = '[Contact] ' . $sujet . ' — ' . $nom;
        $mail->Body    = $html;
        $mail->send();
        $envoye = true;
    } catch (Throwable $e) {
        $envoye = false;
    }
}

if (!$envoye) {
    $pdo = db();
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS messages_contact (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(150) NOT NULL,
            email VARCHAR(190) NOT NULL,
            sujet VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            lu TINYINT(1) NOT NULL DEFAULT 0,
            cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
    }

    $stmt = $pdo->prepare('INSERT INTO messages_contact (nom, email, sujet, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nom, $email, $sujet, $message]);
}

echo json_encode(['ok' => true]);
