@php
    $plateforme = $c->plateforme === 'zoom' ? 'Zoom' : (str_contains((string) $c->lien_visio, 'zoom') ? 'Zoom' : 'Google Meet');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendez-vous confirmé</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;color:#333;line-height:1.6;">
<div style="max-width:600px;margin:20px auto;background:#fff;border-radius:10px;overflow:hidden;">
    <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:28px;text-align:center;">
        <h1 style="margin:0;font-size:24px;">Votre rendez-vous est confirmé</h1>
        <p style="margin:8px 0 0;opacity:.9;">Dossier {{ $c->reference }}</p>
    </div>

    <div style="padding:28px;">
        <p>Bonjour {{ $c->parent_prenom }} {{ $c->parent_nom }},</p>

        <p>Nous avons le plaisir de vous confirmer la séance à distance pour <strong>{{ $c->enfant_prenom }}</strong>.</p>

        <div style="background:#f3f0ff;border-left:4px solid #764ba2;padding:16px 18px;border-radius:6px;margin:20px 0;">
            <p style="margin:0 0 6px;"><strong>{{ ucfirst($seance['parent']) }}</strong> (votre heure)</p>
            <p style="margin:0;font-size:13px;color:#666;">soit {{ $seance['kinshasa'] }}, heure de Kinshasa</p>
            @if ($c->duree_minutes)
                <p style="margin:8px 0 0;font-size:13px;color:#666;">Durée prévue : environ {{ $c->duree_minutes }} minutes</p>
            @endif
            @if ($c->professionnel_assigne)
                <p style="margin:8px 0 0;font-size:13px;color:#666;">Avec : {{ $c->professionnel_assigne }}</p>
            @endif
        </div>

        <p style="text-align:center;margin:28px 0;">
            <a href="{{ $c->lien_visio }}" style="background:#764ba2;color:#fff;text-decoration:none;padding:14px 28px;border-radius:8px;font-weight:bold;display:inline-block;">
                Rejoindre la séance sur {{ $plateforme }}
            </a>
        </p>
        <p style="font-size:12px;color:#666;text-align:center;word-break:break-all;">Lien : {{ $c->lien_visio }}</p>

        @if (!$c->estGratuite())
            <p><strong>Tarif de la séance :</strong> {{ number_format((float) $c->prix, 2, ',', ' ') }} {{ $c->devise }}.
                @if ($c->statut_paiement === 'paye')
                    Votre paiement a bien été reçu, merci.
                @else
                    Le secrétariat vous indiquera les modalités de paiement.
                @endif
            </p>
        @endif

        <h3 style="margin-bottom:8px;">Pour bien préparer la séance</h3>
        <ul style="padding-left:18px;margin-top:0;">
            <li>Connectez-vous 5 minutes avant, depuis un endroit calme, avec une bonne connexion internet.</li>
            <li>Gardez à portée de main les bilans et comptes rendus de votre enfant.</li>
            <li>Si vous avez déjà l'avis du psychiatre, envoyez-le au centre avant la séance en répondant à cet e-mail.</li>
            <li>En cas d'empêchement, prévenez-nous au moins 24 h à l'avance en répondant à cet e-mail.</li>
        </ul>

        <p>Bien cordialement,<br><strong>Le secrétariat de Never Limit Children</strong></p>
    </div>

    <div style="background:#f8f8f8;padding:16px;text-align:center;font-size:12px;color:#888;">
        Never Limit Children - Kinshasa, RD Congo - info@nlcrdc.org
    </div>
</div>
</body>
</html>
