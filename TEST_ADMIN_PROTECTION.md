# Tests de Protection Admin - Guide Pratique

## 🧪 Comment Tester la Protection

### Étape 1 : Préparer la Base de Données

```bash
# Réinitialiser la base de données
php artisan migrate:fresh --seed
```

### Étape 2 : Créer des Utilisateurs de Test

Ouvrez Tinker :
```bash
php artisan tinker
```

Créez des utilisateurs avec différents rôles :

```php
// 1. Créer un admin
$admin = User::create([
    'name' => 'Admin Test',
    'first_name' => 'Admin',
    'last_name' => 'Test',
    'email' => 'admin@test.com',
    'password' => bcrypt('password123'),
    'role' => 'admin',
    'is_active' => true,
]);

// 2. Créer un parent
$parent = User::create([
    'name' => 'Parent Test',
    'first_name' => 'Parent',
    'last_name' => 'Test',
    'email' => 'parent@test.com',
    'password' => bcrypt('password123'),
    'role' => 'parent',
    'is_active' => true,
]);

// 3. Créer un éducateur
$educator = User::create([
    'name' => 'Educator Test',
    'first_name' => 'Educator',
    'last_name' => 'Test',
    'email' => 'educator@test.com',
    'password' => bcrypt('password123'),
    'role' => 'educator',
    'is_active' => true,
]);

// 4. Créer un enfant de test (pour pouvoir le supprimer)
$child = Child::create([
    'first_name' => 'Enfant',
    'last_name' => 'Test',
    'date_of_birth' => '2020-01-01',
    'parent_id' => $parent->id,
    'status' => 'active',
]);

echo "Admin ID: " . $admin->id . "\n";
echo "Parent ID: " . $parent->id . "\n";
echo "Educator ID: " . $educator->id . "\n";
echo "Child ID: " . $child->id . "\n";

exit;
```

### Étape 3 : Obtenir les Tokens

**Pour l'Admin:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "X-API-SECRET: votre_secret_api" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "password123"
  }'
```

**Pour le Parent:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "X-API-SECRET: votre_secret_api" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "parent@test.com",
    "password": "password123"
  }'
```

**Pour l'Éducateur:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "X-API-SECRET: votre_secret_api" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "educator@test.com",
    "password": "password123"
  }'
```

Notez les tokens retournés pour chaque utilisateur.

### Étape 4 : Tests de Suppression

#### ✅ Test 1 : Admin peut supprimer (DOIT RÉUSSIR)

```bash
curl -X DELETE http://localhost:8000/api/children/{CHILD_ID} \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -v
```

**Résultat attendu:** 
- Code HTTP: `200 OK`
- Message: `"Enfant supprimé avec succès"`

---

#### ❌ Test 2 : Parent ne peut PAS supprimer (DOIT ÉCHOUER)

Créez un nouvel enfant d'abord :
```bash
curl -X POST http://localhost:8000/api/children \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Test2",
    "last_name": "Enfant",
    "date_of_birth": "2020-01-01",
    "parent_id": 1,
    "status": "active"
  }'
```

Puis essayez de le supprimer avec le token parent :
```bash
curl -X DELETE http://localhost:8000/api/children/{NEW_CHILD_ID} \
  -H "Authorization: Bearer {PARENT_TOKEN}" \
  -H "Content-Type: application/json" \
  -v
```

**Résultat attendu:**
- Code HTTP: `403 Forbidden`
- Message: `"Accès refusé. Seuls les administrateurs peuvent effectuer cette action."`

---

#### ❌ Test 3 : Éducateur ne peut PAS supprimer (DOIT ÉCHOUER)

```bash
curl -X DELETE http://localhost:8000/api/children/{NEW_CHILD_ID} \
  -H "Authorization: Bearer {EDUCATOR_TOKEN}" \
  -H "Content-Type: application/json" \
  -v
```

**Résultat attendu:**
- Code HTTP: `403 Forbidden`
- Message: `"Accès refusé. Seuls les administrateurs peuvent effectuer cette action."`

---

#### ❌ Test 4 : Sans token (DOIT ÉCHOUER)

```bash
curl -X DELETE http://localhost:8000/api/children/{NEW_CHILD_ID} \
  -H "Content-Type: application/json" \
  -v
```

**Résultat attendu:**
- Code HTTP: `401 Unauthorized`
- Message: `"Unauthenticated."`

## 📋 Checklist de Tests

Cochez chaque test après l'avoir effectué :

- [ ] Admin peut supprimer un enfant (200)
- [ ] Admin peut supprimer un programme (200)
- [ ] Admin peut supprimer un cours (200)
- [ ] Admin peut supprimer un rendez-vous (200)
- [ ] Admin peut supprimer un message (200)
- [ ] Admin peut supprimer un rapport (200)
- [ ] Admin peut supprimer une notification (200)
- [ ] Admin peut supprimer un dossier (200)
- [ ] Admin peut supprimer un paramètre (200)
- [ ] Parent ne peut PAS supprimer (403)
- [ ] Éducateur ne peut PAS supprimer (403)
- [ ] Super-teacher ne peut PAS supprimer (403)
- [ ] Spécialiste ne peut PAS supprimer (403)
- [ ] Réceptionniste ne peut PAS supprimer (403)
- [ ] Utilisateur non authentifié ne peut PAS supprimer (401)

## 🔍 Vérifications Supplémentaires

### Vérifier que les autres opérations fonctionnent toujours

#### Tout le monde peut lire (GET)
```bash
curl -X GET http://localhost:8000/api/children \
  -H "Authorization: Bearer {PARENT_TOKEN}" \
  -H "Content-Type: application/json"
```
**Attendu:** `200 OK` avec liste des enfants

#### Tout le monde peut créer (POST)
```bash
curl -X POST http://localhost:8000/api/children \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Nouveau",
    "last_name": "Enfant",
    "date_of_birth": "2020-05-15",
    "parent_id": 1,
    "status": "active"
  }'
```
**Attendu:** `201 Created`

#### Tout le monde peut modifier (PUT/PATCH)
```bash
curl -X PUT http://localhost:8000/api/children/{CHILD_ID} \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Modifié"
  }'
```
**Attendu:** `200 OK`

## 🎯 Résumé des Résultats Attendus

| Opération | Admin | Parent | Educator | Specialist | Super-Teacher | Receptionist | Non Auth |
|-----------|-------|--------|----------|------------|---------------|--------------|----------|
| **GET** | ✅ 200 | ✅ 200 | ✅ 200 | ✅ 200 | ✅ 200 | ✅ 200 | ❌ 401 |
| **POST** | ✅ 201 | ✅ 201 | ✅ 201 | ✅ 201 | ✅ 201 | ✅ 201 | ❌ 401 |
| **PUT/PATCH** | ✅ 200 | ✅ 200 | ✅ 200 | ✅ 200 | ✅ 200 | ✅ 200 | ❌ 401 |
| **DELETE** | ✅ 200 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 401 |

## 🐛 Dépannage

### Si le test admin échoue (403 au lieu de 200)

```bash
# Vérifier le rôle de l'utilisateur
php artisan tinker
>>> User::where('email', 'admin@test.com')->first()->role;
```

Si ce n'est pas "admin", corrigez :
```php
>>> $user = User::where('email', 'admin@test.com')->first();
>>> $user->role = 'admin';
>>> $user->save();
```

### Si aucune route ne fonctionne

```bash
# Nettoyer les caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Vérifier les routes
php artisan route:list --method=DELETE
```

## 📝 Exemple de Script de Test Complet

Créez un fichier `test-admin-protection.sh` :

```bash
#!/bin/bash

# Configuration
API_URL="http://localhost:8000/api"
API_SECRET="votre_secret_api"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "🔐 Test de Protection Admin - NLC"
echo "================================="

# 1. Login Admin
echo -e "\n${YELLOW}1. Connexion Admin...${NC}"
ADMIN_TOKEN=$(curl -s -X POST "$API_URL/login" \
  -H "X-API-SECRET: $API_SECRET" \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@test.com", "password": "password123"}' \
  | jq -r '.token')

if [ -z "$ADMIN_TOKEN" ]; then
  echo -e "${RED}❌ Échec connexion admin${NC}"
  exit 1
fi
echo -e "${GREEN}✅ Admin connecté${NC}"

# 2. Login Parent
echo -e "\n${YELLOW}2. Connexion Parent...${NC}"
PARENT_TOKEN=$(curl -s -X POST "$API_URL/login" \
  -H "X-API-SECRET: $API_SECRET" \
  -H "Content-Type: application/json" \
  -d '{"email": "parent@test.com", "password": "password123"}' \
  | jq -r '.token')

if [ -z "$PARENT_TOKEN" ]; then
  echo -e "${RED}❌ Échec connexion parent${NC}"
  exit 1
fi
echo -e "${GREEN}✅ Parent connecté${NC}"

# 3. Créer un enfant (avec admin)
echo -e "\n${YELLOW}3. Création d'un enfant de test...${NC}"
CHILD_RESPONSE=$(curl -s -X POST "$API_URL/children" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "TestDelete",
    "last_name": "Enfant",
    "date_of_birth": "2020-01-01",
    "parent_id": 1,
    "status": "active"
  }')

CHILD_ID=$(echo $CHILD_RESPONSE | jq -r '.data.id')
echo -e "${GREEN}✅ Enfant créé (ID: $CHILD_ID)${NC}"

# 4. Test: Parent essaie de supprimer (doit échouer)
echo -e "\n${YELLOW}4. Test: Parent essaie de supprimer...${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$API_URL/children/$CHILD_ID" \
  -H "Authorization: Bearer $PARENT_TOKEN")

if [ "$HTTP_CODE" == "403" ]; then
  echo -e "${GREEN}✅ Test réussi: Parent bloqué (403)${NC}"
else
  echo -e "${RED}❌ Test échoué: Code $HTTP_CODE au lieu de 403${NC}"
fi

# 5. Test: Admin supprime (doit réussir)
echo -e "\n${YELLOW}5. Test: Admin supprime...${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$API_URL/children/$CHILD_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN")

if [ "$HTTP_CODE" == "200" ]; then
  echo -e "${GREEN}✅ Test réussi: Admin peut supprimer (200)${NC}"
else
  echo -e "${RED}❌ Test échoué: Code $HTTP_CODE au lieu de 200${NC}"
fi

echo -e "\n${GREEN}✅ Tous les tests terminés !${NC}"
```

Rendez-le exécutable et lancez-le :
```bash
chmod +x test-admin-protection.sh
./test-admin-protection.sh
```

## ✅ Validation Finale

Une fois tous les tests passés, votre système est correctement protégé !

- ✅ Seuls les admins peuvent supprimer
- ✅ Les autres rôles reçoivent une erreur 403
- ✅ Les utilisateurs non authentifiés reçoivent une erreur 401
- ✅ Toutes les autres opérations (GET, POST, PUT) fonctionnent normalement

---

**Tests développés pour le Neuro Learning Center (NLC)**

