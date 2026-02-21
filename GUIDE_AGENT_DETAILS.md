# 📊 Guide Rapide - Page de Détails des Agents

## 🚀 Installation (2 minutes)

### Étape 1: Exécuter la Migration
```bash
php artisan migrate
```

Cette commande ajoute le champ `validated_by` à la table `tickets`.

### Étape 2: Vérifier l'Installation
```bash
php artisan tinker
```
Puis dans Tinker:
```php
Schema::hasColumn('tickets', 'validated_by');
// Doit retourner: true

exit
```

### Étape 3: Accéder au Dashboard
```
http://localhost:8000/admin
```

---

## 🎯 Utilisation

### Voir les Détails d'un Agent

1. **Accéder au Dashboard Admin**
   - URL: `http://localhost:8000/admin`
   - Connectez-vous si nécessaire

2. **Aller dans l'Onglet "Agents Mobile"**
   - Cliquez sur l'onglet dans la sidebar

3. **Cliquer sur "Voir Détails"**
   - Bouton bleu avec icône de graphique
   - À droite de chaque agent dans le tableau

4. **Explorer les Statistiques**
   - Statistiques globales (3 cartes en haut)
   - Statistiques par type (2 grandes cartes)
   - Graphique d'évolution (30 jours)
   - Validations par événement (tableau)
   - Dernières validations (tableau)

---

## 📊 Ce Que Vous Verrez

### 1. Informations de l'Agent
```
┌─────────────────────────────────────┐
│  [JD]  Jean Dupont                  │
│        jean@example.com             │
│        [Agent] [✓ Vérifié]          │
│        Inscrit le 15/01/2026        │
└─────────────────────────────────────┘
```

### 2. Statistiques Globales (3 cartes)
```
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│ Total        │  │ Revenus      │  │ Performance  │
│ 150          │  │ 7,500 $      │  │ 50 $         │
│ Validations  │  │ Générés      │  │ Par valid.   │
└──────────────┘  └──────────────┘  └──────────────┘
```

### 3. Statistiques par Type (2 grandes cartes)
```
╔═══════════════════════╗  ╔═══════════════════════╗
║ 🔲 BILLETS PHYSIQUES  ║  ║ 💻 BILLETS EN LIGNE   ║
║                       ║  ║                       ║
║ Total: 90             ║  ║ Total: 60             ║
║ Revenus: 4,500 $      ║  ║ Revenus: 3,000 $      ║
║ 60% du total          ║  ║ 40% du total          ║
╚═══════════════════════╝  ╚═══════════════════════╝
```

### 4. Graphique d'Évolution
- Courbe purple: Billets physiques
- Courbe blue: Billets en ligne
- Courbe green (pointillés): Total
- Période: 30 derniers jours

### 5. Tableaux
- **Validations par Événement**: Répartition par événement
- **Dernières Validations**: 20 dernières validations effectuées

---

## 🎨 Codes Couleur

| Type | Couleur | Badge |
|------|---------|-------|
| Billets Physiques | Purple | 🔲 Physique |
| Billets En Ligne | Blue | 💻 En ligne |
| Revenus | Green | 💰 |
| Total | Gray | - |

---

## 🔍 Comprendre les Statistiques

### Total Validations
- Nombre total de billets validés par cet agent
- Inclut physiques + en ligne

### Revenus Générés
- Somme des montants des billets validés
- Uniquement les billets avec statut "completed"

### Performance (Revenu Moyen)
- Calcul: Total revenus ÷ Total validations
- Indique le revenu moyen par validation

### Pourcentage par Type
- Physique: (Validations physiques ÷ Total) × 100
- En ligne: (Validations en ligne ÷ Total) × 100

---

## 📈 Graphique d'Évolution

### Comment le Lire

1. **Axe Horizontal (X)**: Dates (format JJ/MM)
2. **Axe Vertical (Y)**: Nombre de validations
3. **Courbes**:
   - **Purple**: Billets physiques validés
   - **Blue**: Billets en ligne validés
   - **Green** (pointillés): Total des validations

### Interactions
- **Survol**: Affiche les détails pour une date
- **Légende**: Cliquer pour masquer/afficher une courbe

---

## 📋 Tableaux de Données

### Validations par Événement

| Colonne | Description |
|---------|-------------|
| Événement | Nom de l'événement |
| Total | Nombre total de validations |
| Physiques | Badge purple avec nombre |
| En Ligne | Badge blue avec nombre |
| Revenus | Montant total en $ |

**Tri**: Par nombre total (décroissant)

### Dernières Validations

| Colonne | Description |
|---------|-------------|
| Référence | Code unique du billet |
| Type | Badge purple ou blue |
| Participant | Nom du participant |
| Événement | Nom de l'événement |
| Montant | Montant + devise |
| Date | Date et heure de validation |

**Limite**: 20 dernières validations

---

## 🔄 Navigation

### Retour à la Liste
- Cliquer sur la flèche ← en haut à gauche
- Retourne à l'onglet "Agents Mobile"

### Déconnexion
- Bouton "Déconnexion" en haut à droite

---

## 💡 Cas d'Usage

### 1. Évaluer la Performance d'un Agent
```
1. Voir le total de validations
2. Comparer avec les autres agents
3. Vérifier le revenu moyen par validation
4. Analyser la répartition physique/en ligne
```

### 2. Suivre l'Évolution
```
1. Regarder le graphique sur 30 jours
2. Identifier les pics d'activité
3. Comparer physique vs en ligne
4. Détecter les tendances
```

### 3. Analyser par Événement
```
1. Voir le tableau "Validations par Événement"
2. Identifier les événements les plus actifs
3. Comparer la répartition par type
4. Calculer les revenus par événement
```

### 4. Vérifier les Dernières Actions
```
1. Consulter le tableau "Dernières Validations"
2. Vérifier les billets récemment validés
3. Contrôler la qualité des validations
4. Détecter les anomalies
```

---

## 🚨 Résolution de Problèmes

### Problème: Aucune Donnée Affichée

**Cause**: L'agent n'a pas encore validé de billets

**Solution**:
1. Valider quelques billets depuis le dashboard
2. S'assurer que l'agent est connecté lors de la validation
3. Rafraîchir la page de détails

### Problème: Le Graphique Ne S'Affiche Pas

**Cause**: Chart.js n'est pas chargé

**Solution**:
1. Vérifier la connexion internet
2. Ouvrir la console du navigateur (F12)
3. Vérifier les erreurs JavaScript
4. Rafraîchir la page

### Problème: Erreur 404

**Cause**: Route non trouvée

**Solution**:
```bash
php artisan route:clear
php artisan route:cache
```

### Problème: Statistiques Incorrectes

**Cause**: Cache ou données obsolètes

**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
```

---

## 🎯 Bonnes Pratiques

### Pour les Administrateurs

1. **Vérifier Régulièrement**:
   - Consulter les détails des agents chaque semaine
   - Identifier les agents les plus performants
   - Détecter les agents inactifs

2. **Analyser les Tendances**:
   - Observer l'évolution sur 30 jours
   - Comparer physique vs en ligne
   - Ajuster les stratégies si nécessaire

3. **Suivre les Événements**:
   - Vérifier la répartition par événement
   - Identifier les événements populaires
   - Optimiser l'allocation des agents

### Pour les Agents

1. **Suivre sa Performance**:
   - Consulter ses propres statistiques
   - Comparer avec les objectifs
   - Améliorer son efficacité

2. **Équilibrer les Types**:
   - Valider autant de physiques que d'en ligne
   - Diversifier les événements
   - Maintenir un bon revenu moyen

---

## 📊 Métriques Clés à Surveiller

### Performance Individuelle
- ✅ Total validations > 100 par mois
- ✅ Revenu moyen > 40 $ par validation
- ✅ Répartition équilibrée (40-60% chaque type)

### Évolution
- ✅ Tendance croissante sur 30 jours
- ✅ Pas de périodes d'inactivité prolongées
- ✅ Pics d'activité lors des événements

### Qualité
- ✅ Validations réparties sur plusieurs événements
- ✅ Pas de concentration excessive sur un type
- ✅ Revenus cohérents avec les tarifs

---

## 🎓 Comprendre les Données

### Pourquoi Séparer Physique et En Ligne?

1. **Canaux Différents**:
   - Physique: Billets pré-imprimés avec QR code
   - En ligne: Billets générés sur le site web

2. **Processus Différents**:
   - Physique: Scan + remplissage des infos
   - En ligne: Validation après paiement

3. **Analyse Différente**:
   - Identifier les canaux les plus efficaces
   - Optimiser les ressources
   - Adapter les stratégies

### Comment Sont Calculées les Statistiques?

```sql
-- Total validations
SELECT COUNT(*) FROM tickets WHERE validated_by = agent_id

-- Billets physiques
SELECT COUNT(*) FROM tickets 
WHERE validated_by = agent_id AND physical_qr_id IS NOT NULL

-- Billets en ligne
SELECT COUNT(*) FROM tickets 
WHERE validated_by = agent_id AND physical_qr_id IS NULL

-- Revenus
SELECT SUM(amount) FROM tickets 
WHERE validated_by = agent_id AND payment_status = 'completed'
```

---

## 📞 Besoin d'Aide?

### Documentation
- **AGENT_DETAILS_FEATURE.md** - Documentation technique complète
- **README_SYSTEME_BILLETS.md** - Documentation du système

### Support
- Vérifier les logs: `storage/logs/laravel.log`
- Consulter la console du navigateur (F12)
- Exécuter les commandes de diagnostic

---

## ✅ Checklist d'Utilisation

- [ ] Migration exécutée
- [ ] Colonne `validated_by` existe
- [ ] Dashboard accessible
- [ ] Onglet "Agents Mobile" visible
- [ ] Bouton "Voir Détails" présent
- [ ] Page de détails s'affiche
- [ ] Statistiques visibles
- [ ] Graphique fonctionnel
- [ ] Tableaux remplis

---

**Astuce**: Validez quelques billets pour voir les statistiques en action!

---

**Date**: 21 Février 2026  
**Version**: 1.0.0  
**Status**: ✅ Prêt à l'Emploi
