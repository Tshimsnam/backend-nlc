<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"></head>
<body style="margin:0;background:#f3f5fa;font-family:Arial,sans-serif;color:#18233e">
<div style="max-width:600px;margin:32px auto;background:white;border-radius:16px;padding:32px">
    <p style="font-size:12px;color:#586687;letter-spacing:2px">NEVER LIMIT CHILDREN · CONSORTIUM</p>
    <h1 style="font-size:24px">{{ $notification->title }}</h1>
    <p style="line-height:1.7;white-space:pre-line">{{ $notification->body }}</p>
    <p style="margin:28px 0"><a href="{{ $notification->action_url }}" style="display:inline-block;background:#6d28d9;color:white;padding:14px 22px;border-radius:8px;text-decoration:none">Ouvrir mon espace</a></p>
    <p style="font-size:12px;color:#64748b">L’adhésion au consortium et l’attribution d’un financement font l’objet de décisions distinctes. Les documents confidentiels sont accessibles dans votre espace sécurisé.</p>
    <p style="font-size:12px;color:#64748b">Équipe NLC · {{ config('consortium.secretariat_email') }}</p>
</div></body></html>
