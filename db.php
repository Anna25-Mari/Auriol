<?php
require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            $tmp = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        }
        installer($pdo);
        sync_tike($pdo);
    }
    return $pdo;
}

define('TIKE_API', 'https://api.tike229.ghinel.com/api/events');

function sync_tike(PDO $pdo): void
{
    $cache = sys_get_temp_dir() . '/auriol_tike_sync.cache';
    if (is_file($cache) && (time() - (int)filemtime($cache)) < 900) {
        return;
    }

    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true, 'header' => "User-Agent: AuriolMiganSite\r\n"]]);
    $raw = @file_get_contents(TIKE_API, false, $ctx);
    if ($raw === false) {
        return;
    }
    @touch($cache);

    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['events']) || !is_array($data['events'])) {
        return;
    }

    foreach ($data['events'] as $ev) {
        $org = mb_strtolower((string)($ev['organizer']['publicName'] ?? ''));
        if (strpos($org, 'auriol') === false) {
            continue;
        }

        $cats = $ev['ticketCategories'] ?? [];
        usort($cats, fn($a, $b) => ($a['price'] ?? 0) <=> ($b['price'] ?? 0));
        $pStd = (int)($cats[0]['price'] ?? 5000);
        $pVip = (int)($cats[1]['price'] ?? $pStd * 2);
        $catStd = $cats[0]['name'] ?? null;
        $catVip = $cats[1]['name'] ?? null;
        $persVip = max(1, (int)($cats[1]['personsPerTicket'] ?? 1));

        $complet = !empty($cats) ? 1 : 0;
        foreach ($cats as $c) {
            if ((int)$c['capacity'] > (int)$c['soldCount']) {
                $complet = 0;
                break;
            }
        }

        $titre = trim(preg_replace('/\s+\d{1,2}\s+[A-Za-zéûàè]+$/u', '', (string)$ev['title']));
        $ville = 'Cotonou';
        $salle = trim((string)$ev['venue']);
        $date = substr((string)$ev['dateTime'], 0, 10);
        $tikeId = (string)$ev['id'];

        $st = $pdo->prepare('SELECT id FROM spectacles WHERE tike_id = ?');
        $st->execute([$tikeId]);
        if ($row = $st->fetch()) {
            $u = $pdo->prepare('UPDATE spectacles SET titre = ?, ville = ?, salle = ?, date_spectacle = ?, prix_standard = ?, prix_vip = ?, cat_standard = ?, cat_vip = ?, pers_vip = ?, complet = ? WHERE id = ?');
            $u->execute([$titre, $ville, $salle, $date, $pStd, $pVip, $catStd, $catVip, $persVip, $complet, $row['id']]);
        } else {
            $i = $pdo->prepare('INSERT INTO spectacles (tike_id, titre, ville, salle, date_spectacle, prix_standard, prix_vip, cat_standard, cat_vip, pers_vip, complet) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $i->execute([$tikeId, $titre, $ville, $salle, $date, $pStd, $pVip, $catStd, $catVip, $persVip, $complet]);
        }
    }

    try {
        $pdo->exec('DELETE s FROM spectacles s LEFT JOIN reservations r ON r.spectacle_id = s.id WHERE s.tike_id IS NULL AND r.id IS NULL');
    } catch (PDOException $e) {
    }
}

function spectacles_tournees(): array
{
    return db()->query('SELECT * FROM spectacles ORDER BY date_spectacle ASC')->fetchAll();
}

function installer(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS spectacles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(150) NULL,
        tike_id VARCHAR(40) NULL UNIQUE,
        ville VARCHAR(100) NOT NULL,
        salle VARCHAR(150) NOT NULL,
        date_spectacle DATE NOT NULL,
        prix_standard INT NOT NULL DEFAULT 5000,
        prix_vip INT NOT NULL DEFAULT 10000,
        cat_standard VARCHAR(30) NULL,
        cat_vip VARCHAR(30) NULL,
        pers_vip INT NOT NULL DEFAULT 1,
        complet TINYINT(1) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    foreach ([
        'ALTER TABLE spectacles ADD COLUMN titre VARCHAR(150) NULL AFTER id',
        'ALTER TABLE spectacles ADD COLUMN tike_id VARCHAR(40) NULL UNIQUE',
        'ALTER TABLE spectacles ADD COLUMN cat_standard VARCHAR(30) NULL AFTER prix_vip',
        'ALTER TABLE spectacles ADD COLUMN cat_vip VARCHAR(30) NULL AFTER cat_standard',
        'ALTER TABLE spectacles ADD COLUMN pers_vip INT NOT NULL DEFAULT 1 AFTER cat_vip',
    ] as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference VARCHAR(20) NOT NULL UNIQUE,
        spectacle_id INT NOT NULL,
        nom VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL,
        type_billet ENUM('standard','vip') NOT NULL,
        quantite INT NOT NULL,
        montant_total INT NOT NULL,
        statut ENUM('en_attente','paye','annule') NOT NULL DEFAULT 'en_attente',
        code_ticket VARCHAR(30) NOT NULL UNIQUE,
        utilise TINYINT(1) NOT NULL DEFAULT 0,
        scan_le DATETIME NULL,
        cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (spectacle_id) REFERENCES spectacles(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    try {
        $pdo->exec('ALTER TABLE reservations ADD COLUMN scan_le DATETIME NULL AFTER utilise');
    } catch (PDOException $e) {
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference VARCHAR(20) NOT NULL UNIQUE,
        video_id INT NOT NULL,
        nom VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL,
        montant INT NOT NULL,
        statut ENUM('en_attente','paye') NOT NULL DEFAULT 'en_attente',
        code_acces VARCHAR(30) NOT NULL UNIQUE,
        expire_le DATETIME NULL,
        cree_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(190) NOT NULL,
        description TEXT NULL,
        prix INT NOT NULL DEFAULT 5000,
        miniature VARCHAR(255) NULL,
        url_video VARCHAR(255) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $n = (int)$pdo->query('SELECT COUNT(*) FROM spectacles')->fetchColumn();
    if ($n === 0) {
        $ins = $pdo->prepare('INSERT INTO spectacles (tike_id, titre, ville, salle, date_spectacle, prix_standard, prix_vip, cat_standard, cat_vip, pers_vip, complet) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $ins->execute(['cmt1wadru0009pt1vadca0ui2', 'Blague Live Matter', 'Cotonou', 'La Kalebasse', '2026-09-19', 3000, 5000, 'Solo', 'Duo', 2, 0]);
        $ins->execute(['cmt1wd2s4000gpt1v8rcscxvk', 'Blague Live Matter', 'Cotonou', 'La Kalebasse', '2026-10-03', 3000, 5000, 'Solo', 'Duo', 2, 0]);
    }

    $nv = (int)$pdo->query('SELECT COUNT(*) FROM videos')->fetchColumn();
    if ($nv === 0) {
        $inv = $pdo->prepare('INSERT INTO videos (titre, description, prix, miniature, url_video) VALUES (?, ?, ?, ?, ?)');
        $inv->execute(['Sans Filtre - Le Film', 'Le spectacle culte, enfin disponible en ligne.', 10000, 'assets/img/portfolio/3.jpg', '']);
        $inv->execute(['La Chapelle - Le Live', 'L\'intégrale d\'une soirée, au cœur de Fidjrossè.', 15000, 'assets/img/portfolio/5.jpg', '']);
        $inv->execute(['Best-of Comédie CHAPELLE', 'Les meilleurs moments des dernières sessions.', 5000, 'assets/img/about/1.jpg', '']);
    }
}

function spectacle_par_id(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM spectacles WHERE id = ?');
    $st->execute([$id]);
    $s = $st->fetch();
    return $s ?: null;
}

function reservation_par_reference(string $ref): ?array
{
    $st = db()->prepare('SELECT r.*, s.ville, s.salle, s.date_spectacle FROM reservations r JOIN spectacles s ON s.id = r.spectacle_id WHERE r.reference = ?');
    $st->execute([$ref]);
    $r = $st->fetch();
    return $r ?: null;
}

function reservation_par_code(string $code): ?array
{
    $st = db()->prepare('SELECT r.*, s.ville, s.salle, s.date_spectacle FROM reservations r JOIN spectacles s ON s.id = r.spectacle_id WHERE r.code_ticket = ?');
    $st->execute([$code]);
    $r = $st->fetch();
    return $r ?: null;
}

function video_par_id(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM videos WHERE id = ?');
    $st->execute([$id]);
    $v = $st->fetch();
    return $v ?: null;
}

function location_par_reference(string $ref): ?array
{
    $st = db()->prepare('SELECT l.*, v.titre FROM locations l JOIN videos v ON v.id = l.video_id WHERE l.reference = ?');
    $st->execute([$ref]);
    $l = $st->fetch();
    return $l ?: null;
}

function location_par_code(string $code): ?array
{
    $st = db()->prepare('SELECT l.*, v.titre, v.url_video FROM locations l JOIN videos v ON v.id = l.video_id WHERE l.code_acces = ?');
    $st->execute([$code]);
    $l = $st->fetch();
    return $l ?: null;
}

function generer_reference(): string
{
    return 'RES-' . strtoupper(bin2hex(random_bytes(4)));
}

function generer_code_ticket(): string
{
    return 'AUR-' . strtoupper(bin2hex(random_bytes(5)));
}

function format_montant(int $montant): string
{
    return number_format($montant, 0, ',', ' ') . ' ' . DEVISE;
}

function date_fr(string $date): string
{
    $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $t = strtotime($date);
    return date('j', $t) . ' ' . $mois[(int)date('n', $t)] . ' ' . date('Y', $t);
}
