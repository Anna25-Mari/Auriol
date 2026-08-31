<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Billets — Auriol MIGAN</title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,800" rel="stylesheet" type="text/css" />
        <link href="css/styles.css" rel="stylesheet" />
        <link href="css/custom.css" rel="stylesheet" />
        <style>
            body { margin: 0; padding: 0; overflow: hidden; }
            .billets-frame { width: 100%; height: 100vh; border: none; }
            .retour-btn {
                position: fixed;
                bottom: 1.5rem;
                left: 1.5rem;
                z-index: 9999;
                background: var(--bb-accent, #ff9e2c);
                color: #111;
                padding: 0.55rem 0.9rem;
                border-radius: 2rem;
                text-decoration: none;
                font-family: "Montserrat", sans-serif;
                font-weight: 700;
                font-size: 0.85rem;
                text-transform: uppercase;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.4);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .retour-btn:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(0,0,0,0.5); }
        </style>
    </head>
    <body>
        <a class="retour-btn" href="index.php">
            <i class="fas fa-arrow-left"></i> Retour au site
        </a>
        <iframe class="billets-frame" src="https://tike229.ghinel.com/" allowfullscreen></iframe>
    </body>
</html>
