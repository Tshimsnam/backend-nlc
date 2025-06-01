<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Définir votre mot de passe - Never Limit Children</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f4f6fb; font-family: Arial, sans-serif; margin: 0; padding: 0;}
        .email-container { max-width: 520px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px #e5e7ef; }
        .logo { display: flex; justify-content: center; align-items: center; padding: 32px 0 8px; }
        .content { padding: 0 32px 24px; color: #232323; }
        .content h1 { font-size: 1.4rem; margin-bottom: 16px;}
        .btn-link {
            display: inline-block;
            background: #267cff;
            color: #fff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 7px;
            font-size: 1rem;
            font-weight: bold;
            margin: 20px 0;
            transition: background 0.2s;
        }
        .btn-link:hover { background: #195fad; }
        .footer {
            border-top: 1px solid #e5e7ef;
            background: #f8fafc;
            padding: 24px 32px;
            text-align: center;
            color: #888;
            font-size: 12px;
        }
        .footer-social img {
            margin: 0 4px;
            vertical-align: middle;
        }
        a { color: #267cff; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="logo">
            <img src="https://yourdomain.com/images/logo.png" alt="Never Limit Children" style="height:54px;">
        </div>
        <div class="content">
            <h1>Bienvenue sur Never Limit Children !</h1>
            <p>
                Une demande de création de mot de passe a été faite pour votre compte.<br>
                Cliquez sur le bouton ci-dessous pour définir votre mot de passe.<br>
                <b>Ce lien est valable 7 jours.</b>
            </p>
            <div style="text-align:center;">
                <a href="{{ $url }}" class="btn-link">
                    Définir mon mot de passe
                </a>
            </div>
            <p>
                Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :<br>
                <a href="{{ $url }}">{{ $url }}</a>
            </p>
            <p style="color:#888;">Si vous n’êtes pas à l’origine de cette demande, ignorez simplement cet e-mail.</p>
        </div>
        <div class="footer">
            <div class="footer-social" style="margin-bottom:10px;">
                <a href="https://www.linkedin.com/company/neverlimitchildren" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/24/1384/1384014.png" alt="LinkedIn">
                </a>
                <a href="https://twitter.com/neverlimitchild" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/24/733/733579.png" alt="Twitter">
                </a>
                <a href="https://facebook.com/neverlimitchildren" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/24/733/733547.png" alt="Facebook">
                </a>
            </div>
            Copyright ©{{ date('Y') }} Never Limit Children. Tous droits réservés.
        </div>
    </div>
</body>
</html>
