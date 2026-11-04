<!doctype html><html lang="fr"><head><meta charset="UTF-8"><title>{{ $notice->title }}</title></head>
<body style="margin:0;background:#f2f6fa;font:16px/1.7 Arial,sans-serif;color:#25344b;padding:24px">
<div style="max-width:600px;margin:auto;background:white;border-radius:16px;padding:32px">
<p style="color:#7147ac;font-weight:bold">NEVER LIMIT CHILDREN · ORIENTATION</p>
<h1 style="font-size:24px">{{ $notice->title }}</h1><p style="white-space:pre-line">{{ $notice->body }}</p>
@if($notice->action_url)<p><a href="{{ $notice->action_url }}" style="display:inline-block;background:#7445b4;color:white;padding:12px 20px;border-radius:8px;text-decoration:none">Ouvrir mon espace</a></p>@endif
@if($notice->expires_at)<p>Ce lien personnel expire le {{ $notice->expires_at->timezone('Africa/Kinshasa')->format('d/m/Y à H:i') }} (Kinshasa). Si vous n’êtes pas à l’origine de cette démarche, vous pouvez ignorer ce message.</p>@endif
<p style="font-size:13px;color:#68758a">Les documents et informations de santé restent dans l’espace sécurisé. Ce message ne contient pas de bilan médical.<br>Le secrétariat NLC</p></div></body></html>
