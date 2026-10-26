<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de consultation reçue</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;color:#333;line-height:1.6;">
<div style="max-width:600px;margin:20px auto;background:#fff;border-radius:10px;overflow:hidden;">
    <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:28px;text-align:center;">
        <h1 style="margin:0;font-size:24px;">Demande bien reçue</h1>
        <p style="margin:8px 0 0;opacity:.9;">Never Limit Children - Kinshasa</p>
    </div>

    <div style="padding:28px;">
        <p>Bonjour {{ $consultation->parent_prenom }} {{ $consultation->parent_nom }},</p>

        <p>Nous avons bien reçu l'inscription de <strong>{{ $consultation->enfant_prenom }}</strong>
            et votre demande de consultation à distance.</p>

        <div style="background:#f3f0ff;border-left:4px solid #764ba2;padding:14px 18px;border-radius:6px;margin:20px 0;">
            Numéro de dossier : <strong style="font-size:18px;letter-spacing:1px;">{{ $consultation->reference }}</strong><br>
            <span style="font-size:13px;color:#666;">Merci de le rappeler dans tous vos échanges avec le centre.</span>
        </div>

        <h3 style="margin-bottom:8px;">Vos créneaux proposés</h3>
        <ul style="padding-left:18px;margin-top:0;">
            @foreach ($creneaux as $creneau)
                <li style="margin-bottom:6px;">
                    {{ $creneau['parent'] }} <span style="color:#666;">(votre heure)</span><br>
                    <span style="font-size:13px;color:#666;">soit {{ $creneau['kinshasa'] }}, heure de Kinshasa</span>
                </li>
            @endforeach
        </ul>

        <h3 style="margin-bottom:8px;">Et maintenant ?</h3>
        <ol style="padding-left:18px;margin-top:0;">
            <li>Le secrétariat du centre étudie votre demande.</li>
            <li>Il vous confirme la date et l'heure de la séance par e-mail et WhatsApp, avec le lien
                {{ $consultation->plateforme === 'meet' ? 'Google Meet' : ($consultation->plateforme === 'zoom' ? 'Zoom' : 'de visioconférence') }}.</li>
            <li>Pensez à nous transmettre l'avis du psychiatre dès que vous l'aurez : il est nécessaire pour la suite de la prise en charge.</li>
        </ol>

        <p>Pour toute question, répondez simplement à cet e-mail.</p>

        <p>Bien cordialement,<br><strong>Le secrétariat de Never Limit Children</strong></p>
    </div>

    <div style="background:#f8f8f8;padding:16px;text-align:center;font-size:12px;color:#888;">
        Les informations transmises sont confidentielles et réservées à l'équipe de Never Limit Children.
    </div>
</div>
</body>
</html>
