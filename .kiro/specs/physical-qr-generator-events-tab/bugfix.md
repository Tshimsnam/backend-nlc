# Bugfix Requirements Document

## Introduction

La section de génération de QR codes physiques a disparu du tab "events" du dashboard admin (`resources/views/admin/partials/events-list.blade.php`). Cette section permettait à l'administrateur de sélectionner un événement, définir une série/préfixe et une quantité, puis générer et imprimer des QR codes physiques au format `PHY-{SERIE}-{NUMERO}` directement dans le navigateur via JavaScript. Sa disparition est une régression qui bloque la création de billets physiques depuis le dashboard.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN l'administrateur navigue vers le tab "events" du dashboard THEN le système affiche uniquement le tableau des événements (titre, lieu, date, billets, tarifs) sans aucune section de génération de QR codes physiques

1.2 WHEN l'administrateur souhaite générer des QR codes physiques pour un événement THEN le système ne propose aucun formulaire ni outil pour le faire depuis le dashboard

1.3 WHEN l'administrateur souhaite imprimer ou télécharger des QR codes physiques THEN le système ne fournit aucune interface pour cette action

### Expected Behavior (Correct)

2.1 WHEN l'administrateur navigue vers le tab "events" du dashboard THEN le système SHALL afficher, en plus du tableau des événements, une section dédiée à la génération de QR codes physiques

2.2 WHEN l'administrateur sélectionne un événement, saisit un préfixe de série (ex: `PHY-GSA2026-`) et une quantité THEN le système SHALL générer les QR codes correspondants au format `PHY-{SERIE}-{NUMERO}` (ex: `PHY-GSA2026-001`) directement dans le navigateur via JavaScript (qrcode.js via CDN), sans appel serveur

2.3 WHEN les QR codes sont générés THEN le système SHALL permettre à l'administrateur d'imprimer ou de télécharger les QR codes générés

### Unchanged Behavior (Regression Prevention)

3.1 WHEN l'administrateur consulte le tab "events" THEN le système SHALL CONTINUE TO afficher le tableau paginé des événements avec filtre de recherche

3.2 WHEN l'administrateur utilise le filtre de recherche des événements THEN le système SHALL CONTINUE TO filtrer et paginer les résultats correctement

3.3 WHEN un QR code physique est scanné via l'app mobile THEN le système SHALL CONTINUE TO activer le billet via la route `POST /api/tickets/physical` avec le format `PHY-{SERIE}-{NUMERO}`

3.4 WHEN les statistiques de billets physiques sont affichées dans le dashboard THEN le système SHALL CONTINUE TO calculer correctement `physical_tickets`, `physical_tickets_completed` et `physical_tickets_revenue`
