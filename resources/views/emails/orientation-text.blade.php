{{-- Version texte brut : {!! !!} évite l'échappement HTML (&amp;) dans un message non HTML. --}}
NEVER LIMIT CHILDREN · ORIENTATION

{!! $notice->title !!}

{!! $notice->body !!}
@if($notice->action_url)

Ouvrir mon espace :
{!! $notice->action_url !!}
@endif
@if($notice->expires_at)

Ce lien personnel expire le {{ $notice->expires_at->timezone('Africa/Kinshasa')->format('d/m/Y à H:i') }} (Kinshasa). Si vous n’êtes pas à l’origine de cette démarche, vous pouvez ignorer ce message.
@endif

Les documents et informations de santé restent dans l’espace sécurisé. Ce message ne contient pas de bilan médical.
Le secrétariat NLC
