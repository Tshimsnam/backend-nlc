# Solution Rapide - Erreur 422 MaxiCash ✅

## Problème résolu!

L'erreur 422 était causée par une **URL API MaxiCash incorrecte** dans votre fichier `.env`.

## Ce qui a été corrigé

### 1. URL API MaxiCash ❌ → ✅

**Avant (incorrect)**:
```env
MAXICASH_API_URL=https://api-testbed.maxicashme.com/Merchant/api.asmx
MAXICASH_REDIRECT_BASE=https://api-testbed.maxicashme.com
```

**Après (correct)**:
```env
MAXICASH_API_URL=https://webapi-test.maxicashapp.com
MAXICASH_REDIRECT_BASE=https://api-testbed.maxicashapp.com
```

### 2. Adresses IP cohérentes ✅

Toutes les URLs utilisent maintenant `192.168.58.9`:
```env
MAXICASH_SUCCESS_URL=http://192.168.58.9:8080/paiement/success
MAXICASH_FAILURE_URL=http://192.168.58.9:8080/paiement/failure
MAXICASH_CANCEL_URL=http://192.168.58.9:8080/paiement/cancel
MAXICASH_NOTIFY_URL=http://192.168.58.9:8000/api/webhooks/maxicash
```

## Test de validation ✅

```bash
php test-inscription-complete.php
```

**Résultat**:
```
✓ Ticket créé avec succès
✓ Paiement initié avec succès
- Log ID: 97759
- Redirect URL: https://api-testbed.maxicashapp.com/payentryweb?logid=97759
```

## Votre payload frontend est correct ✅

```json
{
  "event_price_id": 2,
  "full_name": "Franck Kapuya",
  "email": "franckkapuya13@gmail.com",
  "phone": "+243822902681",
  "days": 1,
  "pay_type": "online"
}
```

## Prochaines étapes

1. **Redémarrer le serveur Laravel** (si déjà en cours):
   ```bash
   # Arrêter avec Ctrl+C, puis:
   php artisan serve --host=192.168.58.9 --port=8000
   ```

2. **Tester depuis votre frontend**:
   - Accéder à la page d'inscription
   - Remplir le formulaire
   - Cliquer sur "S'inscrire"
   - Vous devriez être redirigé vers MaxiCash

3. **Vérifier les logs** en cas de problème:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Commandes utiles

```bash
# Effacer le cache de configuration
php artisan config:clear

# Tester l'inscription
php test-inscription-complete.php

# Vérifier la configuration
php test-inscription-debug.php
```

## Documentation complète

Pour plus de détails, consultez:
- `CORRECTION_INSCRIPTION_MAXICASH.md` - Guide complet
- Documentation MaxiCash: https://developer.maxicashme.com/

## Support

Si vous rencontrez encore des problèmes:
1. Vérifiez que le serveur Laravel tourne sur `192.168.58.9:8000`
2. Vérifiez que `event_price_id=2` existe dans votre base de données
3. Consultez les logs Laravel: `storage/logs/laravel.log`

---

🎉 **Votre système d'inscription MaxiCash est maintenant opérationnel!**
