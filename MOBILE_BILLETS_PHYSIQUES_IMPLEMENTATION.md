# Implémentation Mobile - Activation de Billets Physiques

## Vue d'ensemble

Ce guide détaille l'implémentation complète de la fonctionnalité d'activation de billets physiques dans l'application mobile React Native.

**Note importante :** La devise est fixée en USD uniquement. Tous les montants sont en dollars américains.

## Architecture

```
Scanner QR Code
    ↓
Détection type: "physical_ticket"
    ↓
Afficher PhysicalTicketActivationScreen
    ↓
Formulaire d'activation
    ↓
API: POST /api/tickets/physical
    ↓
Ticket créé et validé
    ↓
Afficher le ticket généré
```

## 1. Types TypeScript

Créer le fichier `types/PhysicalTicket.ts` :

```typescript
export interface PhysicalQRData {
  id: string;                    // Ex: PHY-1234567890-ABC123XYZ
  event_id: string;
  type: 'physical_ticket';
  created_at: string;
}

export interface PhysicalTicketFormData {
  physical_qr_id: string;
  event_id: string;
  full_name: string;
  email: string;
  phone: string;
  event_price_id: string;
}

export interface EventPrice {
  id: number;
  category: string;
  duration_type: string;
  amount: number;
  currency: string;
  label: string | null;
  description: string | null;
  display_label: string; // Label complet pour l'affichage
}

export interface PhysicalTicketResponse {
  success: boolean;
  ticket: {
    id: number;
    reference: string;
    physical_qr_id: string;
    event_id: number;
    full_name: string;
    email: string;
    phone: string;
    amount: number;
    currency: string;
    pay_type: 'cash';
    payment_status: 'completed';
    qr_data: string;
    created_at: string;
  };
  message: string;
}
```

## 2. Service API

Ajouter dans `services/api.ts` ou `services/ticketService.ts` :

```typescript
import axios from 'axios';
import { PhysicalTicketFormData, PhysicalTicketResponse } from '../types/PhysicalTicket';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

export const activatePhysicalTicket = async (
  data: PhysicalTicketFormData,
  token: string
): Promise<PhysicalTicketResponse> => {
  try {
    const response = await axios.post<PhysicalTicketResponse>(
      `${API_BASE_URL}/tickets/physical`,
      data,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      }
    );
    return response.data;
  } catch (error: any) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message);
    }
    throw new Error('Erreur lors de l\'activation du billet physique');
  }
};

export const getEventPrices = async (eventId: string, token: string) => {
  try {
    const response = await axios.get(`${API_BASE_URL}/events/${eventId}/prices`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    return response.data;
  } catch (error) {
    throw new Error('Erreur lors de la récupération des prix de l\'événement');
  }
};
```

## 3. Écran d'activation - PhysicalTicketActivationScreen.tsx

```tsx
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  Alert,
  ActivityIndicator,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { PhysicalQRData, PhysicalTicketFormData } from '../types/PhysicalTicket';
import { activatePhysicalTicket, getEventDetails } from '../services/api';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface RouteParams {
  qrData: PhysicalQRData;
}

const PhysicalTicketActivationScreen: React.FC = () => {
  const navigation = useNavigation();
  const route = useRoute();
  const { qrData } = route.params as RouteParams;

  const [loading, setLoading] = useState(false);
  const [eventLoading, setEventLoading] = useState(true);
  const [eventDetails, setEventDetails] = useState<any>(null);
  const [eventPrices, setEventPrices] = useState<EventPrice[]>([]);
  const [selectedPrice, setSelectedPrice] = useState<EventPrice | null>(null);
  
  const [formData, setFormData] = useState<Omit<PhysicalTicketFormData, 'physical_qr_id' | 'event_id' | 'event_price_id'>>({
    full_name: '',
    email: '',
    phone: '',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    loadEventDetails();
  }, []);

  const loadEventDetails = async () => {
    try {
      const token = await AsyncStorage.getItem('auth_token');
      if (!token) {
        Alert.alert('Erreur', 'Vous devez être connecté');
        navigation.goBack();
        return;
      }

      const response = await getEventPrices(qrData.event_id, token);
      setEventDetails(response.event);
      setEventPrices(response.prices);
      
      // Sélectionner le premier prix par défaut
      if (response.prices && response.prices.length > 0) {
        setSelectedPrice(response.prices[0]);
      }
    } catch (error: any) {
      Alert.alert('Erreur', error.message);
    } finally {
      setEventLoading(false);
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.full_name.trim()) {
      newErrors.full_name = 'Le nom complet est requis';
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!formData.email.trim()) {
      newErrors.email = 'L\'email est requis';
    } else if (!emailRegex.test(formData.email)) {
      newErrors.email = 'Email invalide';
    }

    if (!formData.phone.trim()) {
      newErrors.phone = 'Le numéro de téléphone est requis';
    } else if (formData.phone.length < 9) {
      newErrors.phone = 'Numéro de téléphone invalide';
    }

    if (!selectedPrice) {
      newErrors.price = 'Veuillez sélectionner un prix';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) {
      return;
    }

    setLoading(true);
    try {
      const token = await AsyncStorage.getItem('auth_token');
      if (!token) {
        Alert.alert('Erreur', 'Vous devez être connecté');
        return;
      }

      const payload: PhysicalTicketFormData = {
        physical_qr_id: qrData.id,
        event_id: qrData.event_id,
        event_price_id: selectedPrice!.id.toString(),
        ...formData,
      };

      const response = await activatePhysicalTicket(payload, token);

      Alert.alert(
        'Succès',
        'Billet physique activé avec succès !',
        [
          {
            text: 'Voir le billet',
            onPress: () => {
              navigation.navigate('TicketDetails', { ticket: response.ticket });
            },
          },
        ]
      );
    } catch (error: any) {
      Alert.alert('Erreur', error.message || 'Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  if (eventLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#3B82F6" />
        <Text style={styles.loadingText}>Chargement des détails...</Text>
      </View>
    );
  }

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
            <Ionicons name="arrow-back" size={24} color="#1F2937" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Activer Billet Physique</Text>
        </View>

        {/* Event Info */}
        {eventDetails && (
          <View style={styles.eventCard}>
            <View style={styles.eventIconContainer}>
              <Ionicons name="calendar" size={32} color="#3B82F6" />
            </View>
            <View style={styles.eventInfo}>
              <Text style={styles.eventTitle}>{eventDetails.title}</Text>
              <Text style={styles.eventDate}>
                {new Date(eventDetails.date).toLocaleDateString('fr-FR', {
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric',
                })}
              </Text>
              <Text style={styles.eventLocation}>{eventDetails.location}</Text>
            </View>
          </View>
        )}

        {/* QR Info */}
        <View style={styles.qrInfoCard}>
          <Ionicons name="qr-code" size={24} color="#6B7280" />
          <View style={styles.qrInfoText}>
            <Text style={styles.qrInfoLabel}>ID Billet Physique</Text>
            <Text style={styles.qrInfoValue}>{qrData.id}</Text>
          </View>
        </View>

        {/* Form */}
        <View style={styles.formContainer}>
          <Text style={styles.formTitle}>Informations du Participant</Text>

          {/* Nom complet */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>
              Nom complet <Text style={styles.required}>*</Text>
            </Text>
            <TextInput
              style={[styles.input, errors.full_name && styles.inputError]}
              placeholder="Ex: Jean Dupont"
              value={formData.full_name}
              onChangeText={(text) => {
                setFormData({ ...formData, full_name: text });
                setErrors({ ...errors, full_name: '' });
              }}
              autoCapitalize="words"
            />
            {errors.full_name && (
              <Text style={styles.errorText}>{errors.full_name}</Text>
            )}
          </View>

          {/* Email */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>
              Email <Text style={styles.required}>*</Text>
            </Text>
            <TextInput
              style={[styles.input, errors.email && styles.inputError]}
              placeholder="exemple@email.com"
              value={formData.email}
              onChangeText={(text) => {
                setFormData({ ...formData, email: text });
                setErrors({ ...errors, email: '' });
              }}
              keyboardType="email-address"
              autoCapitalize="none"
            />
            {errors.email && (
              <Text style={styles.errorText}>{errors.email}</Text>
            )}
          </View>

          {/* Téléphone */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>
              Numéro de téléphone <Text style={styles.required}>*</Text>
            </Text>
            <TextInput
              style={[styles.input, errors.phone && styles.inputError]}
              placeholder="+243 XXX XXX XXX"
              value={formData.phone}
              onChangeText={(text) => {
                setFormData({ ...formData, phone: text });
                setErrors({ ...errors, phone: '' });
              }}
              keyboardType="phone-pad"
            />
            {errors.phone && (
              <Text style={styles.errorText}>{errors.phone}</Text>
            )}
          </View>

          {/* Sélection du prix */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>
              Catégorie et Prix <Text style={styles.required}>*</Text>
            </Text>
            <Text style={styles.helperText}>
              Sélectionnez la catégorie du participant
            </Text>
            <View style={styles.pricesListContainer}>
              {eventPrices.map((price) => (
                <TouchableOpacity
                  key={price.id}
                  style={[
                    styles.priceOption,
                    selectedPrice?.id === price.id && styles.priceOptionSelected,
                  ]}
                  onPress={() => {
                    setSelectedPrice(price);
                    setErrors({ ...errors, price: '' });
                  }}
                >
                  <View style={styles.priceOptionContent}>
                    <View style={styles.priceOptionHeader}>
                      <Text
                        style={[
                          styles.priceOptionLabel,
                          selectedPrice?.id === price.id && styles.priceOptionLabelSelected,
                        ]}
                      >
                        {price.display_label}
                      </Text>
                      {selectedPrice?.id === price.id && (
                        <Ionicons name="checkmark-circle" size={24} color="#10B981" />
                      )}
                    </View>
                    <Text
                      style={[
                        styles.priceOptionAmount,
                        selectedPrice?.id === price.id && styles.priceOptionAmountSelected,
                      ]}
                    >
                      ${price.amount} {price.currency}
                    </Text>
                  </View>
                </TouchableOpacity>
              ))}
            </View>
            {errors.price && (
              <Text style={styles.errorText}>{errors.price}</Text>
            )}
          </View>

          {/* Mode de paiement (fixe) */}
          <View style={styles.paymentModeCard}>
            <Ionicons name="cash" size={24} color="#10B981" />
            <View style={styles.paymentModeInfo}>
              <Text style={styles.paymentModeLabel}>Mode de paiement</Text>
              <Text style={styles.paymentModeValue}>Paiement en Caisse</Text>
            </View>
            <View style={styles.paymentModeBadge}>
              <Text style={styles.paymentModeBadgeText}>CASH</Text>
            </View>
          </View>

          {/* Submit Button */}
          <TouchableOpacity
            style={[styles.submitButton, loading && styles.submitButtonDisabled]}
            onPress={handleSubmit}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <>
                <Ionicons name="checkmark-circle" size={24} color="#FFFFFF" />
                <Text style={styles.submitButtonText}>Activer le Billet</Text>
              </>
            )}
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#6B7280',
  },
  scrollView: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 16,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  backButton: {
    padding: 8,
    marginRight: 8,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1F2937',
  },
  eventCard: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    margin: 16,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  eventIconContainer: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#EFF6FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  eventInfo: {
    flex: 1,
  },
  eventTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 4,
  },
  eventDate: {
    fontSize: 14,
    color: '#6B7280',
    marginBottom: 2,
  },
  eventLocation: {
    fontSize: 14,
    color: '#6B7280',
  },
  qrInfoCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F3F4F6',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 12,
    borderRadius: 8,
  },
  qrInfoText: {
    marginLeft: 12,
    flex: 1,
  },
  qrInfoLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginBottom: 2,
  },
  qrInfoValue: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1F2937',
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  formContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 24,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  formTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 20,
  },
  inputGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#374151',
    marginBottom: 8,
  },
  required: {
    color: '#EF4444',
  },
  input: {
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    paddingHorizontal: 16,
    paddingVertical: 12,
    fontSize: 16,
    color: '#1F2937',
  },
  inputError: {
    borderColor: '#EF4444',
  },
  errorText: {
    fontSize: 12,
    color: '#EF4444',
    marginTop: 4,
  },
  row: {
    flexDirection: 'row',
    gap: 12,
  },
  flex1: {
    flex: 1,
  },
  flex2: {
    flex: 2,
  },
  helperText: {
    fontSize: 13,
    color: '#6B7280',
    marginBottom: 12,
  },
  pricesListContainer: {
    gap: 12,
  },
  priceOption: {
    backgroundColor: '#F9FAFB',
    borderWidth: 2,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    padding: 16,
  },
  priceOptionSelected: {
    backgroundColor: '#ECFDF5',
    borderColor: '#10B981',
  },
  priceOptionContent: {
    gap: 8,
  },
  priceOptionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  priceOptionLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#374151',
    flex: 1,
  },
  priceOptionLabelSelected: {
    color: '#065F46',
  },
  priceOptionAmount: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1F2937',
  },
  priceOptionAmountSelected: {
    color: '#047857',
  },
  paymentModeCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ECFDF5',
    padding: 16,
    borderRadius: 8,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: '#A7F3D0',
  },
  paymentModeInfo: {
    flex: 1,
    marginLeft: 12,
  },
  paymentModeLabel: {
    fontSize: 12,
    color: '#065F46',
    marginBottom: 2,
  },
  paymentModeValue: {
    fontSize: 16,
    fontWeight: '600',
    color: '#047857',
  },
  paymentModeBadge: {
    backgroundColor: '#10B981',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
  },
  paymentModeBadgeText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  submitButton: {
    backgroundColor: '#3B82F6',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    borderRadius: 8,
    gap: 8,
  },
  submitButtonDisabled: {
    backgroundColor: '#9CA3AF',
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#FFFFFF',
  },
});

export default PhysicalTicketActivationScreen;
```

## 4. Modification du Scanner QR

Modifier le composant de scan QR pour détecter les billets physiques :

```tsx
// Dans votre QRScannerScreen.tsx ou similaire

const handleQRCodeScanned = async (data: string) => {
  try {
    const qrData = JSON.parse(data);
    
    // Détecter le type de QR code
    if (qrData.type === 'physical_ticket') {
      // C'est un billet physique à activer
      navigation.navigate('PhysicalTicketActivation', { qrData });
    } else if (qrData.reference) {
      // C'est un billet normal à valider
      navigation.navigate('TicketValidation', { qrData });
    } else {
      Alert.alert('Erreur', 'QR code non reconnu');
    }
  } catch (error) {
    Alert.alert('Erreur', 'QR code invalide');
  }
};
```

## 5. Navigation

Ajouter la route dans votre navigation stack :

```tsx
// Dans votre navigation/AppNavigator.tsx ou similaire

import PhysicalTicketActivationScreen from '../screens/PhysicalTicketActivationScreen';

<Stack.Screen
  name="PhysicalTicketActivation"
  component={PhysicalTicketActivationScreen}
  options={{
    headerShown: false,
  }}
/>
```

## 6. Écran de détails du ticket (optionnel)

Créer un écran pour afficher le ticket activé avec son QR code :

```tsx
// TicketDetailsScreen.tsx

import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import QRCode from 'react-native-qrcode-svg';

const TicketDetailsScreen = ({ route }) => {
  const { ticket } = route.params;

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Billet Activé</Text>
      <Text style={styles.reference}>{ticket.reference}</Text>
      
      <View style={styles.qrContainer}>
        <QRCode
          value={ticket.qr_data}
          size={250}
          backgroundColor="white"
        />
      </View>
      
      <View style={styles.infoContainer}>
        <Text style={styles.label}>Participant</Text>
        <Text style={styles.value}>{ticket.full_name}</Text>
        
        <Text style={styles.label}>Email</Text>
        <Text style={styles.value}>{ticket.email}</Text>
        
        <Text style={styles.label}>Montant</Text>
        <Text style={styles.value}>{ticket.amount} {ticket.currency}</Text>
      </View>
    </View>
  );
};

export default TicketDetailsScreen;
```

## 7. Tests

### Test du flux complet :

1. **Générer un QR code** dans le dashboard admin
2. **Scanner le QR code** avec l'app mobile
3. **Vérifier** que le formulaire s'affiche avec les bonnes infos
4. **Remplir** le formulaire avec des données valides
5. **Soumettre** et vérifier que le ticket est créé
6. **Vérifier** dans le dashboard que le ticket apparaît comme validé

### Cas d'erreur à tester :

- QR code déjà utilisé
- Champs manquants
- Email invalide
- Montant négatif ou zéro
- Événement inexistant
- Token expiré

## 8. Dépendances nécessaires

```json
{
  "dependencies": {
    "@react-navigation/native": "^6.x.x",
    "@react-navigation/stack": "^6.x.x",
    "react-native-qrcode-svg": "^6.x.x",
    "axios": "^1.x.x",
    "@react-native-async-storage/async-storage": "^1.x.x",
    "expo-barcode-scanner": "^12.x.x" // ou react-native-camera
  }
}
```

## 9. Variables d'environnement

Ajouter dans `.env` :

```
REACT_APP_API_URL=https://votre-api.com/api
```

## 10. Permissions

Ajouter dans `app.json` (Expo) ou `AndroidManifest.xml` / `Info.plist` :

```json
{
  "expo": {
    "plugins": [
      [
        "expo-barcode-scanner",
        {
          "cameraPermission": "Autoriser l'accès à la caméra pour scanner les QR codes"
        }
      ]
    ]
  }
}
```

## Résumé du flux

1. ✅ Admin génère QR codes physiques
2. ✅ Designer imprime les billets
3. ✅ Billets distribués/vendus
4. ✅ Agent scanne QR code
5. ✅ App détecte type "physical_ticket"
6. ✅ Formulaire d'activation s'affiche
7. ✅ Agent remplit les infos
8. ✅ Ticket créé et validé automatiquement
9. ✅ QR code marqué comme utilisé
10. ✅ Ticket peut être scanné à l'entrée

## Sécurité

- ✅ Authentification requise (Bearer token)
- ✅ Validation côté client et serveur
- ✅ QR code unique (utilisable une seule fois)
- ✅ Traçabilité (qui a activé, quand)
- ✅ Vérification de l'événement

## Prochaines étapes

1. Implémenter le backend (voir `QR_BILLETS_PHYSIQUES_SYSTEME.md`)
2. Créer les composants React Native ci-dessus
3. Tester le flux complet
4. Déployer en production
