# Application Mobile - Gestion des Billets et Enregistrements

Application mobile pour la gestion des événements, permettant l'enregistrement des participants, la vérification des billets et le contrôle d'accès.

---

## 📱 Fonctionnalités

### 1. Authentification
- Connexion sécurisée pour les utilisateurs (agents, organisateurs, contrôleurs)
- Gestion des sessions
- Déconnexion

### 2. Enregistrement d'un Participant
- Enregistrement via numéro de référence du billet
- Validation automatique du billet
- Confirmation d'enregistrement

### 3. Vérification de Billet
Trois méthodes de vérification :
- **Scan QR Code** : Scanner directement le QR code sur le billet
- **Numéro de téléphone** : Rechercher par numéro de téléphone
- **Numéro de référence** : Rechercher par référence du billet

Affichage des informations :
- Nom complet du participant
- Événement
- Catégorie (Adulte, Enfant, VIP, etc.)
- Montant payé
- Statut du paiement
- Date d'achat
- **Nombre de scans** : Combien de fois le billet a été scanné
- **Premier scan** : Date et heure du premier scan
- **Dernier scan** : Date et heure du dernier scan

**Enregistrement automatique du scan :**
- Chaque scan est enregistré dans la base de données
- Le compteur de scans est incrémenté automatiquement
- L'agent qui a scanné est enregistré
- Le lieu du scan est enregistré (Entrée, VIP, etc.)

### 4. Enregistrement d'un Client
- Création de nouveaux clients/participants
- Saisie des informations personnelles
- Génération automatique de référence

---

## 🎯 Cas d'Utilisation

### Scénario 1 : Contrôle d'Accès à l'Événement
1. L'agent se connecte à l'application
2. Le participant présente son billet (QR code ou référence)
3. L'agent scanne le QR code ou saisit la référence
4. L'application affiche les informations du billet
5. L'agent valide l'accès si le paiement est confirmé

### Scénario 2 : Enregistrement sur Place
1. Un participant arrive sans billet
2. L'agent crée un nouveau client dans l'application
3. Le système génère une référence
4. Le participant effectue le paiement en caisse
5. L'agent enregistre le participant avec la référence

### Scénario 3 : Vérification Rapide
1. Un participant a perdu son billet physique
2. L'agent recherche par numéro de téléphone
3. Le système retrouve le billet
4. L'agent valide l'accès

---

## 🔧 Architecture Technique

### Stack Technologique Recommandée

#### Option 1 : React Native (Cross-platform)
```
- React Native
- React Navigation
- Axios (API calls)
- React Native Camera (QR Scanner)
- AsyncStorage (Local storage)
```

#### Option 2 : Flutter (Cross-platform)
```
- Flutter
- Provider/Riverpod (State management)
- Dio (API calls)
- qr_code_scanner (QR Scanner)
- shared_preferences (Local storage)
```

#### Option 3 : Native
- **Android** : Kotlin + Jetpack Compose
- **iOS** : Swift + SwiftUI

---

## 📡 API Backend

### Base URL
```
https://votre-api.com/api
```

### Endpoints Requis

#### 1. Authentification

**Login**
```http
POST /login
Content-Type: application/json

{
  "email": "agent@example.com",
  "password": "password123"
}

Response:
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Agent Name",
    "email": "agent@example.com",
    "role": "agent"
  }
}
```

**Logout**
```http
POST /logout
Authorization: Bearer {token}

Response:
{
  "message": "Déconnexion réussie"
}
```

#### 2. Vérification de Billet

**Par Référence**
```http
GET /tickets/{reference}
Authorization: Bearer {token}

Response:
{
  "reference": "ABC123XYZ",
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+243 812 345 678",
  "event": {
    "id": 1,
    "title": "Concert de Musique",
    "date": "2026-03-15",
    "location": "Stade des Martyrs"
  },
  "category": "Adulte",
  "amount": 50.00,
  "currency": "USD",
  "payment_status": "completed",
  "created_at": "2026-02-16T10:30:00Z",
  "qr_data": "{\"reference\":\"ABC123XYZ\",\"event_id\":1}"
}
```

**Scanner un Billet (Enregistre le scan)**
```http
POST /tickets/scan
Authorization: Bearer {token}
Content-Type: application/json

{
  "qr_data": "{\"reference\":\"ABC123XYZ\",\"event\":\"Concert de Musique\",\"participant\":\"John Doe\",\"email\":\"john@example.com\",\"phone\":\"+243812345678\",\"amount\":\"50.00\",\"currency\":\"USD\",\"category\":\"medecin\",\"date\":\"2026-03-15\",\"location\":\"Kinshasa\"}",
  "scan_location": "Entrée principale"
}

OU avec référence uniquement:

{
  "reference": "ABC123XYZ",
  "scan_location": "Entrée VIP"
}

OU avec téléphone:

{
  "phone": "+243812345678",
  "scan_location": "Entrée"
}

Response:
{
  "success": true,
  "message": "Billet scanné avec succès",
  "ticket": {
    "id": 1,
    "reference": "ABC123XYZ",
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+243 812 345 678",
    "category": "medecin",
    "amount": "50.00",
    "currency": "USD",
    "payment_status": "completed",
    "scan_count": 3,
    "first_scanned_at": "2026-02-18T10:00:00.000000Z",
    "last_scanned_at": "2026-02-18T14:30:00.000000Z",
    "event": {
      "id": 1,
      "title": "Concert de Musique",
      "date": "2026-03-15",
      "time": "09:00:00",
      "location": "Stade des Martyrs"
    },
    "price": {
      "label": "Médecin - Événement complet",
      "category": "medecin",
      "duration_type": "full_event"
    }
  },
  "scan_info": {
    "scan_count": 3,
    "is_first_scan": false,
    "last_scanned_at": "2026-02-18T14:30:00.000000Z"
  }
}
```

**Historique des Scans d'un Billet**
```http
GET /tickets/{reference}/scans
Authorization: Bearer {token}

Response:
{
  "success": true,
  "ticket_reference": "ABC123XYZ",
  "total_scans": 3,
  "scans": [
    {
      "id": 3,
      "scanned_at": "2026-02-18T14:30:00.000000Z",
      "scan_location": "Entrée principale",
      "scanned_by_user": {
        "id": 5,
        "name": "Agent Dupont",
        "email": "agent@example.com"
      }
    }
  ]
}
```

**Par Téléphone**
```http
GET /tickets/search?phone=+243812345678
Authorization: Bearer {token}

Response:
{
  "tickets": [
    {
      "reference": "ABC123XYZ",
      "full_name": "John Doe",
      "event": "Concert de Musique",
      "payment_status": "completed"
    }
  ]
}
```

#### 3. Enregistrement d'un Participant

**Enregistrer avec Référence**
```http
POST /tickets/{reference}/register
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Participant enregistré avec succès",
  "ticket": {
    "reference": "ABC123XYZ",
    "full_name": "John Doe",
    "registered_at": "2026-02-16T14:30:00Z"
  }
}
```

#### 4. Création d'un Client

**Créer un Nouveau Client**
```http
POST /events/{event_id}/register
Authorization: Bearer {token}
Content-Type: application/json

{
  "event_price_id": 1,
  "full_name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+243 812 345 679",
  "pay_type": "cash"
}

Response:
{
  "success": true,
  "payment_mode": "cash",
  "ticket": {
    "reference": "XYZ789ABC",
    "full_name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "+243 812 345 679",
    "event": "Concert de Musique",
    "category": "Adulte",
    "amount": 50.00,
    "currency": "USD",
    "status": "pending_cash",
    "qr_data": "{\"reference\":\"XYZ789ABC\",\"event_id\":1}"
  },
  "message": "Ticket créé. Paiement en caisse requis."
}
```

#### 5. Validation Paiement en Caisse

**Valider un Paiement**
```http
POST /tickets/{reference}/validate-cash
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Paiement validé avec succès",
  "ticket": {
    "reference": "XYZ789ABC",
    "status": "completed"
  }
}
```

#### 6. Liste des Événements

**Obtenir les Événements Actifs**
```http
GET /events
Authorization: Bearer {token}

Response:
{
  "events": [
    {
      "id": 1,
      "title": "Concert de Musique",
      "slug": "concert-de-musique",
      "date": "2026-03-15",
      "location": "Stade des Martyrs",
      "prices": [
        {
          "id": 1,
          "category": "Adulte",
          "amount": 50.00,
          "currency": "USD"
        },
        {
          "id": 2,
          "category": "Enfant",
          "amount": 25.00,
          "currency": "USD"
        }
      ]
    }
  ]
}
```

---

## 📱 Écrans de l'Application

### 1. Écran de Connexion
```
┌─────────────────────────┐
│                         │
│    [Logo Application]   │
│                         │
│  ┌───────────────────┐  │
│  │ Email            │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Mot de passe     │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │   SE CONNECTER    │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

### 2. Écran d'Accueil (Dashboard)
```
┌─────────────────────────┐
│  Bonjour, Agent Name    │
│                         │
│  ┌─────────────────┐    │
│  │  📷 Scanner QR  │    │
│  └─────────────────┘    │
│                         │
│  ┌─────────────────┐    │
│  │  🔍 Rechercher  │    │
│  └─────────────────┘    │
│                         │
│  ┌─────────────────┐    │
│  │  ➕ Nouveau     │    │
│  │     Client      │    │
│  └─────────────────┘    │
│                         │
│  ┌─────────────────┐    │
│  │  📋 Historique  │    │
│  └─────────────────┘    │
│                         │
└─────────────────────────┘
```

### 3. Écran Scanner QR
```
┌─────────────────────────┐
│  ← Scanner le Billet    │
│                         │
│  ┌─────────────────┐    │
│  │                 │    │
│  │   [Caméra QR]   │    │
│  │                 │    │
│  │   ┌─────────┐   │    │
│  │   │         │   │    │
│  │   └─────────┘   │    │
│  │                 │    │
│  └─────────────────┘    │
│                         │
│  Positionnez le QR code │
│  dans le cadre          │
│                         │
└─────────────────────────┘
```

### 4. Écran Recherche
```
┌─────────────────────────┐
│  ← Rechercher un Billet │
│                         │
│  ┌───────────────────┐  │
│  │ Référence        │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Téléphone        │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │    RECHERCHER     │  │
│  └───────────────────┘  │
│                         │
│  OU                     │
│                         │
│  ┌───────────────────┐  │
│  │   📷 Scanner QR   │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

### 5. Écran Détails du Billet
```
┌─────────────────────────┐
│  ← Détails du Billet    │
│                         │
│  ✅ Paiement Confirmé   │
│                         │
│  Référence: ABC123XYZ   │
│  ─────────────────────  │
│                         │
│  👤 John Doe            │
│  📧 john@example.com    │
│  📱 +243 812 345 678    │
│                         │
│  🎫 Concert de Musique  │
│  📅 15 Mars 2026        │
│  📍 Stade des Martyrs   │
│                         │
│  💰 50.00 USD           │
│  🏷️  Catégorie: Adulte  │
│                         │
│  ┌───────────────────┐  │
│  │   ENREGISTRER     │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

### 6. Écran Nouveau Client
```
┌─────────────────────────┐
│  ← Nouveau Client       │
│                         │
│  Événement              │
│  ┌───────────────────┐  │
│  │ Concert de Musique│  │
│  └───────────────────┘  │
│                         │
│  Catégorie              │
│  ┌───────────────────┐  │
│  │ Adulte - 50 USD   │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Nom complet      │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Email            │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Téléphone        │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │     CRÉER         │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

---

## 🔐 Sécurité

### Authentification
- Utiliser JWT (JSON Web Token) pour l'authentification
- Stocker le token de manière sécurisée (Keychain iOS, Keystore Android)
- Expiration automatique du token après 24h
- Refresh token pour renouveler la session

### Permissions
```javascript
// Exemple de gestion des rôles
const permissions = {
  agent: ['scan', 'search', 'register'],
  admin: ['scan', 'search', 'register', 'create_client', 'validate_payment'],
  controller: ['scan', 'search']
};
```

### Données Sensibles
- Ne jamais stocker les mots de passe en local
- Chiffrer les données sensibles en cache
- Utiliser HTTPS pour toutes les communications

---

## 📦 Structure du Projet (React Native)

```
mobile-app/
├── src/
│   ├── screens/
│   │   ├── LoginScreen.js
│   │   ├── HomeScreen.js
│   │   ├── ScanQRScreen.js
│   │   ├── SearchScreen.js
│   │   ├── TicketDetailsScreen.js
│   │   ├── NewClientScreen.js
│   │   └── HistoryScreen.js
│   │
│   ├── components/
│   │   ├── Button.js
│   │   ├── Input.js
│   │   ├── TicketCard.js
│   │   ├── QRScanner.js
│   │   └── LoadingSpinner.js
│   │
│   ├── services/
│   │   ├── api.js
│   │   ├── auth.js
│   │   ├── tickets.js
│   │   └── storage.js
│   │
│   ├── navigation/
│   │   ├── AppNavigator.js
│   │   └── AuthNavigator.js
│   │
│   ├── utils/
│   │   ├── constants.js
│   │   ├── validators.js
│   │   └── formatters.js
│   │
│   └── store/
│       ├── authSlice.js
│       ├── ticketsSlice.js
│       └── store.js
│
├── assets/
│   ├── images/
│   └── fonts/
│
├── App.js
├── package.json
└── README.md
```

---

## 🚀 Installation et Configuration

### Prérequis
- Node.js 18+
- React Native CLI ou Expo CLI
- Android Studio (pour Android)
- Xcode (pour iOS)

### Installation

```bash
# Cloner le projet
git clone https://github.com/votre-repo/mobile-app.git
cd mobile-app

# Installer les dépendances
npm install

# iOS uniquement
cd ios && pod install && cd ..

# Lancer l'application
npm run android  # Pour Android
npm run ios      # Pour iOS
```

### Configuration

Créer un fichier `.env` :

```env
API_BASE_URL=https://votre-api.com/api
API_TIMEOUT=30000
```

---

## 📝 Exemples de Code

### 1. Service API

```javascript
// src/services/api.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://votre-api.com/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Intercepteur pour ajouter le token
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### 2. Authentification

```javascript
// src/services/auth.js
import api from './api';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const login = async (email, password) => {
  try {
    const response = await api.post('/login', { email, password });
    const { token, user } = response.data;
    
    // Sauvegarder le token
    await AsyncStorage.setItem('auth_token', token);
    await AsyncStorage.setItem('user', JSON.stringify(user));
    
    return { success: true, user };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur de connexion' 
    };
  }
};

export const logout = async () => {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('user');
  }
};
```

### 3. Vérification de Billet

```javascript
// src/services/tickets.js
import api from './api';

export const getTicketByReference = async (reference) => {
  try {
    const response = await api.get(`/tickets/${reference}`);
    return { success: true, ticket: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Billet non trouvé' 
    };
  }
};

export const scanTicket = async (qrData, scanLocation = 'Entrée') => {
  try {
    const response = await api.post('/tickets/scan', {
      qr_data: qrData,
      scan_location: scanLocation
    });
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors du scan' 
    };
  }
};

export const scanTicketByReference = async (reference, scanLocation = 'Entrée') => {
  try {
    const response = await api.post('/tickets/scan', {
      reference: reference,
      scan_location: scanLocation
    });
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors du scan' 
    };
  }
};

export const searchTicketByPhone = async (phone) => {
  try {
    const response = await api.get(`/tickets/search?phone=${phone}`);
    return { success: true, tickets: response.data.tickets };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Aucun billet trouvé' 
    };
  }
};

export const getTicketScanHistory = async (reference) => {
  try {
    const response = await api.get(`/tickets/${reference}/scans`);
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors de la récupération de l\'historique' 
    };
  }
};

export const registerParticipant = async (reference) => {
  try {
    const response = await api.post(`/tickets/${reference}/register`);
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur d\'enregistrement' 
    };
  }
};
```

### 4. Scanner QR Code

```javascript
// src/components/QRScanner.js
import React from 'react';
import { RNCamera } from 'react-native-camera';
import { View, StyleSheet } from 'react-native';

const QRScanner = ({ onScan }) => {
  const handleBarCodeRead = (event) => {
    try {
      // Le QR code contient un JSON avec toutes les infos
      const data = JSON.parse(event.data);
      // Envoyer les données complètes pour enregistrer le scan
      onScan(event.data, data.reference);
    } catch (error) {
      // Si ce n'est pas du JSON, c'est peut-être juste la référence
      onScan(null, event.data);
    }
  };

  return (
    <View style={styles.container}>
      <RNCamera
        style={styles.camera}
        type={RNCamera.Constants.Type.back}
        onBarCodeRead={handleBarCodeRead}
        barCodeTypes={[RNCamera.Constants.BarCodeType.qr]}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  camera: {
    flex: 1,
  },
});

export default QRScanner;
```

### 5. Écran de Scan avec Enregistrement

```javascript
// src/screens/ScanQRScreen.js
import React, { useState } from 'react';
import { View, Text, StyleSheet, Alert } from 'react-native';
import QRScanner from '../components/QRScanner';
import { scanTicket, scanTicketByReference } from '../services/tickets';

const ScanQRScreen = ({ navigation }) => {
  const [scanning, setScanning] = useState(true);

  const handleScan = async (qrData, reference) => {
    if (!scanning) return;
    
    setScanning(false);

    try {
      let result;
      
      if (qrData) {
        // Scanner avec les données complètes du QR code
        result = await scanTicket(qrData, 'Entrée principale');
      } else {
        // Scanner avec juste la référence
        result = await scanTicketByReference(reference, 'Entrée principale');
      }

      if (result.success) {
        const { ticket, scan_info } = result.data;
        
        // Afficher une alerte si c'est le premier scan
        if (scan_info.is_first_scan) {
          Alert.alert(
            '✅ Premier Scan',
            `Bienvenue ${ticket.full_name}!\nC'est votre premier scan.`,
            [
              {
                text: 'OK',
                onPress: () => navigation.navigate('TicketDetails', { ticket, scan_info })
              }
            ]
          );
        } else {
          Alert.alert(
            '✅ Billet Scanné',
            `${ticket.full_name}\nScan #${scan_info.scan_count}`,
            [
              {
                text: 'Voir Détails',
                onPress: () => navigation.navigate('TicketDetails', { ticket, scan_info })
              },
              {
                text: 'Scanner Suivant',
                onPress: () => setScanning(true)
              }
            ]
          );
        }
      } else {
        Alert.alert('Erreur', result.message, [
          { text: 'Réessayer', onPress: () => setScanning(true) }
        ]);
      }
    } catch (error) {
      Alert.alert('Erreur', 'Une erreur est survenue', [
        { text: 'Réessayer', onPress: () => setScanning(true) }
      ]);
    }
  };

  return (
    <View style={styles.container}>
      {scanning ? (
        <QRScanner onScan={handleScan} />
      ) : (
        <View style={styles.loadingContainer}>
          <Text style={styles.loadingText}>Traitement...</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#000',
  },
  loadingText: {
    color: '#fff',
    fontSize: 18,
  },
});

export default ScanQRScreen;
```

### 5. Écran de Détails du Billet

```javascript
// src/screens/TicketDetailsScreen.js
import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Alert } from 'react-native';
import { registerParticipant } from '../services/tickets';

const TicketDetailsScreen = ({ route, navigation }) => {
  const { ticket } = route.params;

  const handleRegister = async () => {
    const result = await registerParticipant(ticket.reference);
    
    if (result.success) {
      Alert.alert('Succès', 'Participant enregistré avec succès');
      navigation.goBack();
    } else {
      Alert.alert('Erreur', result.message);
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed': return '#4CAF50';
      case 'pending': return '#FF9800';
      case 'failed': return '#F44336';
      default: return '#9E9E9E';
    }
  };

  return (
    <View style={styles.container}>
      <View style={[styles.statusBadge, { backgroundColor: getStatusColor(ticket.payment_status) }]}>
        <Text style={styles.statusText}>
          {ticket.payment_status === 'completed' ? '✅ Paiement Confirmé' : '⏳ En Attente'}
        </Text>
      </View>

      <Text style={styles.reference}>Référence: {ticket.reference}</Text>

      <View style={styles.section}>
        <Text style={styles.label}>👤 Participant</Text>
        <Text style={styles.value}>{ticket.full_name}</Text>
        <Text style={styles.subValue}>{ticket.email}</Text>
        <Text style={styles.subValue}>{ticket.phone}</Text>
      </View>

      <View style={styles.section}>
        <Text style={styles.label}>🎫 Événement</Text>
        <Text style={styles.value}>{ticket.event.title}</Text>
        <Text style={styles.subValue}>📅 {ticket.event.date}</Text>
        <Text style={styles.subValue}>📍 {ticket.event.location}</Text>
      </View>

      <View style={styles.section}>
        <Text style={styles.label}>💰 Paiement</Text>
        <Text style={styles.value}>{ticket.amount} {ticket.currency}</Text>
        <Text style={styles.subValue}>🏷️ Catégorie: {ticket.category}</Text>
      </View>

      {ticket.payment_status === 'completed' && (
        <TouchableOpacity style={styles.button} onPress={handleRegister}>
          <Text style={styles.buttonText}>ENREGISTRER LE PARTICIPANT</Text>
        </TouchableOpacity>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#fff',
  },
  statusBadge: {
    padding: 15,
    borderRadius: 10,
    marginBottom: 20,
    alignItems: 'center',
  },
  statusText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  reference: {
    fontSize: 18,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 20,
    color: '#333',
  },
  section: {
    marginBottom: 20,
    padding: 15,
    backgroundColor: '#f5f5f5',
    borderRadius: 10,
  },
  label: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
    color: '#666',
  },
  value: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  subValue: {
    fontSize: 14,
    color: '#666',
    marginTop: 3,
  },
  button: {
    backgroundColor: '#2196F3',
    padding: 15,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 20,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default TicketDetailsScreen;
```

---

## 🧪 Tests

### Tests Unitaires
```bash
npm test
```

### Tests E2E
```bash
npm run test:e2e
```

---

## 📊 Métriques et Analytics

Intégrer des analytics pour suivre :
- Nombre de scans par jour
- Taux de réussite des vérifications
- Temps moyen de traitement
- Erreurs fréquentes

---

## 🔄 Synchronisation Offline

L'application devrait fonctionner en mode offline :
- Cache des billets récemment consultés
- File d'attente pour les enregistrements
- Synchronisation automatique quand la connexion revient

```javascript
// Exemple de gestion offline
import NetInfo from '@react-native-community/netinfo';

const syncQueue = async () => {
  const isConnected = await NetInfo.fetch().then(state => state.isConnected);
  
  if (isConnected) {
    const pendingActions = await AsyncStorage.getItem('pending_actions');
    // Traiter les actions en attente
  }
};
```

---

## 📱 Déploiement

### Android
```bash
cd android
./gradlew assembleRelease
```

### iOS
```bash
cd ios
xcodebuild -workspace App.xcworkspace -scheme App -configuration Release
```

---

## 🆘 Support et Maintenance

### Logs
- Utiliser un service de logging (Sentry, Crashlytics)
- Capturer les erreurs et exceptions
- Monitorer les performances

### Mises à Jour
- Utiliser CodePush pour les mises à jour OTA
- Versionning sémantique (1.0.0, 1.1.0, etc.)

---

## 📄 Licence

[Votre Licence]

---

## 👥 Contributeurs

[Liste des contributeurs]

---

## 📞 Contact

Pour toute question ou support :
- Email: support@votre-app.com
- Documentation API: https://api.votre-app.com/docs
