<?php
require_once __DIR__ . '/db.php';

function entete(string $titre): void
{
    $socials = [
        'TikTok' => ['fab fa-tiktok', 'https://www.tiktok.com/@auriol.migan'],
        'Facebook' => ['fab fa-facebook-f', 'https://www.facebook.com/share/1BZKFY1YBX/'],
        'Instagram' => ['fab fa-instagram', 'https://www.instagram.com/auriolmigan'],
        'YouTube' => ['fab fa-youtube', 'https://www.youtube.com/@auriolmigan-os6fc'],
    ];
    ?><!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title><?php echo htmlspecialchars($titre); ?> — Auriol MIGAN</title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,800" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <link href="css/styles.css" rel="stylesheet" />
        <link href="css/custom.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-shrink" id="mainNav">
            <div class="container">
                <a class="navbar-brand brand-text" href="index.html">Auriol&nbsp;Migan</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Basculer la navigation">
                    Menu <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0 align-items-lg-center gap-lg-4">
                        <li class="nav-item"><a class="nav-link" href="index.html#tour-dates-sec">Soirées</a></li>
                        <li class="nav-item"><a class="nav-link" href="videos.php">En ligne</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.html#listen-sec">La Chapelle</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.html#apropos-sec">À propos</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.html#contact-sec">Contact</a></li>
                    </ul>
                    <div class="ms-lg-4 d-none d-lg-flex align-items-center gap-3">
                        <?php foreach ($socials as $slug => $s) { ?>
                            <a class="social-link" href="<?php echo htmlspecialchars($s[1]); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo $slug; ?>"><i class="<?php echo $s[0]; ?>"></i></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </nav>
        <main style="padding-top:5.5rem;">
    <?php
}

function pied(): void
    {
    ?></main>
        <footer class="site-footer">
            <div class="container text-center">
                <div class="brand-text footer-brand mb-2">Auriol&nbsp;Migan</div>
                <div class="footer-tagline mb-4">Humoriste. Producteur. Sans filtre.</div>
                <div class="footer-social justify-content-center mb-4">
                    <a href="https://www.tiktok.com/@auriol.migan" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    <a href="https://www.facebook.com/share/1BZKFY1YBX/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/auriolmigan" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@auriolmigan-os6fc" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
                <div class="footer-bottom">&copy; 2026 Auriol MIGAN. Tous droits réservés.</div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
    <?php
}

function alerte(string $type, string $message): void
{
    echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
}
