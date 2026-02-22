# API Statistiques Agent Mobile

## 📱 Vue d'Ensemble

Cette API permet à un agent mobile de consulter ses propres statistiques de validation de billets, exactement comme sur le dashboard admin web, mais accessible depuis l'application mobile.

## 🎯 Fonctionnalités

L'agent peut voir:
- **Statistiques globales** : Total de validations, séparation physique/en ligne, revenus
- **Évolution sur 30 jours** : Graphique des validations par jour
- **Validations par événement** : Performance par événement
- **Dernières validations** : Liste des 20 dernières validations

## 🔐 Authentification Requise

L'agent doit être authentifié avec un token Bearer pour accéder à ses statistiques.

## 📡 Endpoint Principal

### GET /api/my-stats

Récupère toutes les statistiques de l'agent connecté.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Réponse (200 OK):**
```json
{
  "success": true,
  "agent": {
    "id": 5,
    "name": "Agent Dupont",
    "email": "agent@example.com"
  },
  "stats": {
    "total_validations": 150,
    "physical_validations": 80,
    "online_validations": 70,
    "total_revenue": 7500.00,
    "physical_revenue": 4000.00,
    "online_revenue": 3500.00
  },
  "validations_evolution": [
    {
      "date": "2026-02-21",
      "total": 15,
      "physical": 8,
      "online": 7
    },
    {
      "date": "2026-02-20",
      "total": 12,
      "physical": 6,
      "online": 6
    }
    // ... 30 derniers jours
  ],
  "validations_by_event": [
    {
      "id": 1,
      "title": "Le Grand Salon de l'Autisme",
      "total": 50,
      "physical": 25,
      "online": 25,
      "revenue": 2500.00
    },
    {
      "id": 2,
      "title": "Conférence Éducation Inclusive",
      "total": 30,
      "physical": 15,
      "online": 15,
      "revenue": 1500.00
    }
  ],
  "recent_validations": [
    {
      "reference": "TKT-1771703593-H4WITL",
      "ticket_type": "online",
      "full_name": "John Doe",
      "event_title": "Le Grand Salon de l'Autisme",
      "amount": 50.00,
      "currency": "USD",
      "validated_at": "2026-02-21T14:30:00.000000Z"
    },
    {
      "reference": "TKT-1771703594-ABC123",
      "ticket_type": "physical",
      "full_name": "Jane Smith",
      "event_title": "Le Grand Salon de l'Autisme",
      "amount": 30.00,
      "currency": "USD",
      "validated_at": "2026-02-21T13:15:00.000000Z"
    }
    // ... 20 dernières validations
  ]
}
```

**Réponse Erreur (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Utilisateur non authentifié"
}
```

## 📊 Structure des Données

### Stats Object
```typescript
interface Stats {
  total_validations: number;      // Total de billets validés
  physical_validations: number;   // Billets physiques validés
  online_validations: number;     // Billets en ligne validés
  total_revenue: number;          // Revenus totaux générés
  physical_revenue: number;       // Revenus des billets physiques
  online_revenue: number;         // Revenus des billets en ligne
}
```

### Validations Evolution
```typescript
interface ValidationEvolution {
  date: string;        // Format: YYYY-MM-DD
  total: number;       // Total de validations ce jour
  physical: number;    // Billets physiques ce jour
  online: number;      // Billets en ligne ce jour
}
```

### Validations By Event
```typescript
interface ValidationByEvent {
  id: number;          // ID de l'événement
  title: string;       // Titre de l'événement
  total: number;       // Total de validations
  physical: number;    // Billets physiques
  online: number;      // Billets en ligne
  revenue: number;     // Revenus générés
}
```

### Recent Validation
```typescript
interface RecentValidation {
  reference: string;       // Référence du billet
  ticket_type: 'physical' | 'online';  // Type de billet
  full_name: string;       // Nom du participant
  event_title: string;     // Titre de l'événement
  amount: number;          // Montant
  currency: string;        // Devise (USD, CDF, etc.)
  validated_at: string;    // Date ISO 8601
}
```

## 🔧 Utilisation dans l'App Mobile

### 1. Connexion de l'Agent

```javascript
// Login
const loginResponse = await fetch('http://api.example.com/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'agent@example.com',
    password: 'password123'
  })
});

const { token, user } = await loginResponse.json();
// Sauvegarder le token pour les requêtes suivantes
```

### 2. Récupérer les Statistiques

```javascript
const getMyStats = async (token) => {
  try {
    const response = await fetch('http://api.example.com/api/my-stats', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      }
    });

    if (!response.ok) {
      throw new Error('Erreur lors de la récupération des statistiques');
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Erreur:', error);
    throw error;
  }
};

// Utilisation
const stats = await getMyStats(token);
console.log('Total validations:', stats.stats.total_validations);
console.log('Revenus:', stats.stats.total_revenue);
```

### 3. Exemple Complet avec React Native

```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, ActivityIndicator } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

const AgentStatsScreen = () => {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    loadStats();
  }, []);

  const loadStats = async () => {
    try {
      setLoading(true);
      
      // Récupérer le token stocké
      const token = await AsyncStorage.getItem('auth_token');
      
      if (!token) {
        throw new Error('Non authentifié');
      }

      // Appeler l'API
      const response = await fetch('http://api.example.com/api/my-stats', {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        }
      });

      if (!response.ok) {
        throw new Error('Erreur lors de la récupération des statistiques');
      }

      const data = await response.json();
      setStats(data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
        <ActivityIndicator size="large" color="#0000ff" />
      </View>
    );
  }

  if (error) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
        <Text style={{ color: 'red' }}>Erreur: {error}</Text>
      </View>
    );
  }

  return (
    <ScrollView style={{ flex: 1, padding: 20 }}>
      {/* Header Agent */}
      <View style={{ marginBottom: 20 }}>
        <Text style={{ fontSize: 24, fontWeight: 'bold' }}>
          {stats.agent.name}
        </Text>
        <Text style={{ color: '#666' }}>{stats.agent.email}</Text>
      </View>

      {/* Statistiques Globales */}
      <View style={{ marginBottom: 20 }}>
        <Text style={{ fontSize: 18, fontWeight: 'bold', marginBottom: 10 }}>
          Statistiques Globales
        </Text>
        
        <View style={{ backgroundColor: '#f0f0f0', padding: 15, borderRadius: 10, marginBottom: 10 }}>
          <Text style={{ fontSize: 16 }}>Total Validations</Text>
          <Text style={{ fontSize: 32, fontWeight: 'bold' }}>
            {stats.stats.total_validations}
          </Text>
        </View>

        <View style={{ flexDirection: 'row', gap: 10 }}>
          <View style={{ flex: 1, backgroundColor: '#e8d5f5', padding: 15, borderRadius: 10 }}>
            <Text style={{ fontSize: 14, color: '#6b21a8' }}>🔲 Physiques</Text>
            <Text style={{ fontSize: 24, fontWeight: 'bold', color: '#6b21a8' }}>
              {stats.stats.physical_validations}
            </Text>
            <Text style={{ fontSize: 12, color: '#6b21a8' }}>
              {stats.stats.physical_revenue.toFixed(2)} USD
            </Text>
          </View>

          <View style={{ flex: 1, backgroundColor: '#dbeafe', padding: 15, borderRadius: 10 }}>
            <Text style={{ fontSize: 14, color: '#1e40af' }}>💻 En ligne</Text>
            <Text style={{ fontSize: 24, fontWeight: 'bold', color: '#1e40af' }}>
              {stats.stats.online_validations}
            </Text>
            <Text style={{ fontSize: 12, color: '#1e40af' }}>
              {stats.stats.online_revenue.toFixed(2)} USD
            </Text>
          </View>
        </View>

        <View style={{ backgroundColor: '#d1fae5', padding: 15, borderRadius: 10, marginTop: 10 }}>
          <Text style={{ fontSize: 14, color: '#065f46' }}>💰 Revenus Totaux</Text>
          <Text style={{ fontSize: 32, fontWeight: 'bold', color: '#065f46' }}>
            {stats.stats.total_revenue.toFixed(2)} USD
          </Text>
        </View>
      </View>

      {/* Validations par Événement */}
      <View style={{ marginBottom: 20 }}>
        <Text style={{ fontSize: 18, fontWeight: 'bold', marginBottom: 10 }}>
          Par Événement
        </Text>
        {stats.validations_by_event.map((event) => (
          <View key={event.id} style={{ backgroundColor: '#f9fafb', padding: 15, borderRadius: 10, marginBottom: 10 }}>
            <Text style={{ fontSize: 16, fontWeight: 'bold' }}>{event.title}</Text>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginTop: 5 }}>
              <Text>Total: {event.total}</Text>
              <Text>🔲 {event.physical} | 💻 {event.online}</Text>
              <Text style={{ fontWeight: 'bold' }}>{event.revenue.toFixed(2)} USD</Text>
            </View>
          </View>
        ))}
      </View>

      {/* Dernières Validations */}
      <View style={{ marginBottom: 20 }}>
        <Text style={{ fontSize: 18, fontWeight: 'bold', marginBottom: 10 }}>
          Dernières Validations
        </Text>
        {stats.recent_validations.map((validation, index) => (
          <View key={index} style={{ backgroundColor: '#f9fafb', padding: 15, borderRadius: 10, marginBottom: 10 }}>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 5 }}>
              <Text style={{ fontWeight: 'bold' }}>{validation.reference}</Text>
              <Text>{validation.ticket_type === 'physical' ? '🔲' : '💻'}</Text>
            </View>
            <Text>{validation.full_name}</Text>
            <Text style={{ color: '#666', fontSize: 12 }}>{validation.event_title}</Text>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginTop: 5 }}>
              <Text style={{ fontWeight: 'bold' }}>
                {validation.amount.toFixed(2)} {validation.currency}
              </Text>
              <Text style={{ color: '#666', fontSize: 12 }}>
                {new Date(validation.validated_at).toLocaleDateString('fr-FR')}
              </Text>
            </View>
          </View>
        ))}
      </View>
    </ScrollView>
  );
};

export default AgentStatsScreen;
```

## 📈 Affichage du Graphique

Pour afficher le graphique d'évolution, vous pouvez utiliser une bibliothèque comme `react-native-chart-kit`:

```javascript
import { LineChart } from 'react-native-chart-kit';

const EvolutionChart = ({ data }) => {
  // Préparer les données pour le graphique
  const chartData = {
    labels: data.validations_evolution.map(item => {
      const date = new Date(item.date);
      return `${date.getDate()}/${date.getMonth() + 1}`;
    }),
    datasets: [
      {
        data: data.validations_evolution.map(item => item.physical),
        color: (opacity = 1) => `rgba(139, 92, 246, ${opacity})`, // Purple
        strokeWidth: 2,
      },
      {
        data: data.validations_evolution.map(item => item.online),
        color: (opacity = 1) => `rgba(59, 130, 246, ${opacity})`, // Blue
        strokeWidth: 2,
      },
    ],
    legend: ['Physiques', 'En ligne'],
  };

  return (
    <View>
      <Text style={{ fontSize: 18, fontWeight: 'bold', marginBottom: 10 }}>
        Évolution (30 jours)
      </Text>
      <LineChart
        data={chartData}
        width={350}
        height={220}
        chartConfig={{
          backgroundColor: '#ffffff',
          backgroundGradientFrom: '#ffffff',
          backgroundGradientTo: '#ffffff',
          decimalPlaces: 0,
          color: (opacity = 1) => `rgba(0, 0, 0, ${opacity})`,
          style: {
            borderRadius: 16,
          },
        }}
        bezier
        style={{
          marginVertical: 8,
          borderRadius: 16,
        }}
      />
    </View>
  );
};
```

## 🔄 Rafraîchissement des Données

Pour rafraîchir les statistiques:

```javascript
import { RefreshControl } from 'react-native';

const [refreshing, setRefreshing] = useState(false);

const onRefresh = async () => {
  setRefreshing(true);
  await loadStats();
  setRefreshing(false);
};

// Dans le ScrollView
<ScrollView
  refreshControl={
    <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
  }
>
  {/* Contenu */}
</ScrollView>
```

## 🧪 Test avec Postman

### 1. Login
```
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "agent@example.com",
  "password": "password123"
}
```

Copier le `token` de la réponse.

### 2. Récupérer les Stats
```
GET http://localhost:8000/api/my-stats
Authorization: Bearer {token}
```

## 🧪 Test avec cURL

```bash
# 1. Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"agent@example.com","password":"password123"}'

# Copier le token

# 2. Récupérer les stats
curl -X GET http://localhost:8000/api/my-stats \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

## 📱 Écrans de l'Application Mobile

### Écran Principal des Stats
```
┌─────────────────────────────────────┐
│  ← Mes Statistiques                 │
│                                     │
│  👤 Agent Dupont                    │
│  agent@example.com                  │
│  ─────────────────────────────────  │
│                                     │
│  📊 Statistiques Globales           │
│  ┌─────────────────────────────┐   │
│  │  Total Validations          │   │
│  │        150                  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌──────────────┬──────────────┐   │
│  │ 🔲 Physiques │ 💻 En ligne  │   │
│  │     80       │     70       │   │
│  │  4,000 USD   │  3,500 USD   │   │
│  └──────────────┴──────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │  💰 Revenus Totaux          │   │
│  │      7,500.00 USD           │   │
│  └─────────────────────────────┘   │
│                                     │
│  📈 Évolution (30 jours)            │
│  ┌─────────────────────────────┐   │
│  │     [Graphique]             │   │
│  └─────────────────────────────┘   │
│                                     │
│  🎫 Par Événement                   │
│  ┌─────────────────────────────┐   │
│  │ Le Grand Salon de l'Autisme │   │
│  │ 50 validations              │   │
│  │ 🔲 25 | 💻 25 | 2,500 USD   │   │
│  └─────────────────────────────┘   │
│                                     │
│  📜 Dernières Validations           │
│  ┌─────────────────────────────┐   │
│  │ TKT-ABC123      💻          │   │
│  │ John Doe                    │   │
│  │ Grand Salon                 │   │
│  │ 50.00 USD    21/02/2026     │   │
│  └─────────────────────────────┘   │
│                                     │
└─────────────────────────────────────┘
```

## 🔐 Sécurité

1. **Token JWT** : Toujours utiliser HTTPS en production
2. **Expiration** : Le token expire après 24h
3. **Refresh** : Implémenter un système de refresh token
4. **Stockage** : Utiliser AsyncStorage (React Native) ou SecureStore (Expo)

## 📝 Notes Importantes

1. **Authentification obligatoire** : L'agent doit être connecté
2. **Données en temps réel** : Les stats sont calculées à chaque requête
3. **Performance** : Utiliser un cache pour éviter trop de requêtes
4. **Pagination** : Les dernières validations sont limitées à 20
5. **Période** : L'évolution couvre les 30 derniers jours

## 🚀 Prochaines Étapes

1. ✅ Endpoint API créé
2. ✅ Documentation complète
3. □ Implémenter dans l'app mobile
4. □ Ajouter le graphique d'évolution
5. □ Tester avec des données réelles
6. □ Ajouter un système de cache
7. □ Implémenter le pull-to-refresh

## 📞 Support

Pour toute question:
- Documentation API: `/api/documentation`
- Email: support@nlcrdc.org
- Dashboard web: `/admin/agents/{id}/details`
