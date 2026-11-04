<div class="grid sm:grid-cols-2 gap-4 text-sm">
<label>Nom complet<input name="name" value="{{ $p?->name }}" required maxlength="150" class="block border rounded-lg p-3 w-full mt-1"></label>
<label>E-mail professionnel<input name="email" type="email" value="{{ $p?->email }}" required class="block border rounded-lg p-3 w-full mt-1"></label>
<label>Profession / qualification<input name="profession" value="{{ $p?->profession }}" required class="block border rounded-lg p-3 w-full mt-1"></label>
<label>Spécialité et public accompagné<input name="specialty" value="{{ $p?->specialty }}" class="block border rounded-lg p-3 w-full mt-1"></label>
<label>Cadre d’intervention<select name="engagement" class="block border rounded-lg p-3 w-full mt-1"><option value="internal">Interne NLC</option><option value="consultant" @selected($p?->engagement==='consultant')>Consultant externe</option></select></label>
<label>Langues, séparées par une virgule<input name="languages" value="{{ implode(', ',$p?->languages ?? ['Français']) }}" class="block border rounded-lg p-3 w-full mt-1"></label>
<label>Fuseau horaire<input name="timezone" value="{{ $p?->timezone ?? 'Africa/Kinshasa' }}" required class="block border rounded-lg p-3 w-full mt-1"></label>
<label>Tarif indicatif USD (la proposition fera foi)<input type="number" name="default_fee_usd" min="0" max="100000" step=".01" value="{{ ($p?->default_fee_cents ?? 0)/100 }}" required class="block border rounded-lg p-3 w-full mt-1"></label>
</div>
<label class="block text-sm mt-4">Vérification des compétences et cadre de collaboration (privé)<textarea name="credential_notes" class="block border rounded-lg p-3 w-full mt-1" maxlength="3000">{{ $p?->credential_notes }}</textarea></label>
<label class="flex gap-2 mt-4 text-sm"><input type="checkbox" name="validated" value="1" @checked($p?->validated_at)> NLC a vérifié les qualifications, l’intervention auprès des mineurs et le cadre de confidentialité de ce professionnel.</label>
<label class="flex gap-2 mt-3 text-sm"><input type="checkbox" name="active" value="1" @checked($p?->active)> Activer le compte</label>
<label class="flex gap-2 mt-3 text-sm"><input type="checkbox" name="invite" value="1"> Envoyer un lien personnel de connexion</label>
<button class="bg-purple-700 text-white rounded-lg px-5 py-3 mt-5">Enregistrer le professionnel</button>
