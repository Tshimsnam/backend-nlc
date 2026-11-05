{{-- Version texte brut : {!! !!} évite l'échappement HTML (&amp;) dans un message non HTML. --}}
NEVER LIMIT CHILDREN · CONSORTIUM

{!! $notification->title !!}

{!! $notification->body !!}

Ouvrir mon espace :
{!! $notification->action_url !!}

L’adhésion au consortium et l’attribution d’un financement font l’objet de décisions distinctes. Les documents confidentiels sont accessibles dans votre espace sécurisé.

Équipe NLC · {!! config('consortium.secretariat_email') !!}
