<?php
require_once __DIR__ . '/db.php';

function envoyer_ticket(array $reservation): bool
{
    $sujet = 'Votre billet - ' . SITE_NOM . ' - ' . $reservation['ville'];
    $lien = SITE_URL . '/billet.php?code=' . urlencode($reservation['code_ticket']);
    $corps = "Bonjour " . $reservation['nom'] . ",\n\n"
        . "Votre paiement a été confirmé. Voici votre billet numérique :\n\n"
        . "Spectacle : " . $reservation['ville'] . " - " . $reservation['salle'] . "\n"
        . "Date : " . date_fr($reservation['date_spectacle']) . "\n"
        . "Type : " . strtoupper($reservation['type_billet']) . " x" . $reservation['quantite'] . "\n"
        . "Total payé : " . format_montant((int)$reservation['montant_total']) . "\n"
        . "Code du billet : " . $reservation['code_ticket'] . "\n\n"
        . "Présentez ce code (QR) à l'entrée : " . $lien . "\n\n"
        . "À très bientôt,\n" . SITE_NOM;

    if (envoyer_via_gmail($reservation['email'], $sujet, $corps)) {
        return true;
    }

    $dir = __DIR__ . '/mails';
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    file_put_contents(
        $dir . '/' . preg_replace('/[^A-Z0-9-]/', '', $reservation['code_ticket']) . '.eml',
        "To: {$reservation['email']}\r\nSubject: $sujet\r\n\r\n$corps"
    );
    return false;
}

function envoyer_lien_video(array $location): bool
{
    $sujet = 'Votre acces - ' . SITE_NOM . ' - ' . $location['titre'];
    $lien = SITE_URL . '/acces_video.php?code=' . urlencode($location['code_acces']);
    $corps = "Bonjour " . $location['nom'] . ",\n\n"
        . "Votre paiement a ete confirme. Vous pouvez regarder :\n\n"
        . $location['titre'] . "\n"
        . "Accessible jusqu'au : " . date('d/m/Y H:i', strtotime($location['expire_le'])) . "\n\n"
        . "Votre lien personnel : " . $lien . "\n"
        . "Code d'acces : " . $location['code_acces'] . "\n\n"
        . "Bonne seance,\n" . SITE_NOM;

    if (envoyer_via_gmail($location['email'], $sujet, $corps)) {
        return true;
    }

    $dir = __DIR__ . '/mails';
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    file_put_contents(
        $dir . '/' . preg_replace('/[^A-Z0-9-]/', '', $location['code_acces']) . '.eml',
        "To: {$location['email']}\r\nSubject: $sujet\r\n\r\n$corps"
    );
    return false;
}

function envoyer_via_gmail(string $to, string $subject, string $body): bool
{
    if (!defined('SMTP_PASS') || strpos(SMTP_PASS, 'MOT_DE_PASSE') === 0) {
        return false;
    }
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        foreach ([__DIR__ . '/vendor/autoload.php', __DIR__ . '/PHPMailer/src/PHPMailer.php'] as $p) {
            if (file_exists($p)) {
                if (substr($p, -12) === 'autoload.php') { require_once $p; }
                else { require_once __DIR__ . '/PHPMailer/src/PHPMailer.php'; require_once __DIR__ . '/PHPMailer/src/SMTP.php'; require_once __DIR__ . '/PHPMailer/src/Exception.php'; }
                break;
            }
        }
    }
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) { return false; }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(SMTP_USER, SITE_NOM);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        return $mail->send();
    } catch (Throwable $e) {
        return false;
    }
}
