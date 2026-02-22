# Guide d'Implémentation - Activation de Billets Physiques (Application Mobile)

## Vue d'ensemble

Ce guide explique comment implémenter la fonctionnalité d'activation de billets physiques dans l'application mobile React Native. Les agents peuvent scanner des QR codes pré-générés et créer des tickets validés en remplissant les informations des participants.

---

## Architecture du Flux

```
1. Agent scanne QR physique
   ↓
2. App détecte type "physical_ticket"
   ↓
3. App vérifie si QR déjà activé (API check)
   ↓
4a. SI DÉJÀ ACTIVÉ:
    → Afficher écran avec infos du propriétaire
    → Afficher détails du ticket
   ↓
4b. SI NON ACTIVÉ:
    → App récupère les prix de l'événement
    → Agent remplit formulaire
    → App envoie requête création ticket
    → Backend crée participant + ticket
    → Ticket validé et prêt pour l'entrée
```

---

## 1. Détection du QR Code Physique

### Structure du QR Code

```json
{
  "id": "PHY-1708345678-ABC123XYZ",
  "event_id": "1",
  "type": "physical_ticket",
  "created_at": "2025-02-19T10:30:00.000Z"
}
```

### Code de Détection

```typescript
// Dans votre composant de scan QR
const handleQRCodeScanned = async (data: string) => {
  try {
    const qrData = JSON.parse(data);
    
    // Vérifier si c'est un billet physique
    if (qrData.type === 'physical_ticket') {
      // Vérifier d'abord si le QR est déjà activé
      await checkAndHandlePhysicalTicket(qrData);
    } else {
      // Gérer les autres types de QR codes (tickets normaux, etc.)
      handleRegularTicket(qrData);
    }
  } catch (error) {
    Alert.alert('Erreur', 'QR code invalide');
  }
};

const checkAndHandlePhysicalTicket = async (qrData: any) => {
  try {
    const token = await getAuthToken();
    
    // Vérifier si le QR est déjà activé
    const response = await axios.post(
      `${API_BASE_URL}/api/tickets/physical/check`,
      { physical_qr_id: qrData.id },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
      }
    );

    if (response.data.is_activated) {
      // QR déjà activé - Afficher les infos du propriétaire
      navigation.navigate('PhysicalTicketAlreadyActivated', {
        ticket: response.data.ticket,
        participant: response.data.participant,
      });
    } else {
      // QR disponible - Ouvrir le formulaire d'activation
      navigation.navigate('ActivatePhysicalTicket', {
        physicalQrId: qrData.id,
        eventId: qrData.event_id,
      });
    }
  } catch (error) {
    console.error('Erreur lors de la vérification du QR:', error);
    Alert.alert('Erreur', 'Impossible de vérifier le QR code');
  }
};
```

---

## 2. Vérification du Statut du QR Code

### API Endpoint

```
POST /api/tickets/physical/check
```

### Code React Native

```typescript
interface CheckPhysicalQRResponse {
  success: boolean;
  is_activated: boolean;
  message: string;
  ticket?: Ticket;
  participant?: Participant;
}

const checkPhysicalQR = async (
  physicalQrId: string,
  token: string
): Promise<CheckPhysicalQRResponse> => {
  try {
    const response = await axios.post(
      `${API_BASE_URL}/api/tickets/physical/check`,
      { physical_qr_id: physicalQrId },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
      }
    );
    return response.data;
  } catch (error) {
    console.error('Erreur lors de la vérification du QR:', error);
    throw error;
  }
};
```

---

## 3. Écran "QR Déjà Activé"

### Composant d'Affichage

```typescript
import React from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
} from 'react-native';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

interface PhysicalTicketAlreadyActivatedScreenProps {
  route: {
    params: {
      ticket: Ticket;
      participant: Participant;
    };
  };
  navigation: any;
}

const PhysicalTicketAlreadyActivatedScreen: React.FC<
  PhysicalTicketAlreadyActivatedScreenProps
> = ({ route, navigation }) => {
  const { ticket, participant } = route.params;

  return (
    <ScrollView style={styles.container}>
      {/* En-tête d'alerte */}
      <View style={styles.alertHeader}>
        <View style={styles.alertIconContainer}>
          <Text style={styles.alertIcon}>⚠️</Text>
        </View>
        <Text style={styles.alertTitle}>Billet Déjà Activé</Text>
        <Text style={styles.alertMessage}>
          Ce QR code a déjà été utilisé pour créer un billet
        </Text>
      </View>

      {/* Informations du propriétaire */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Informations du Propriétaire</Text>
        
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Nom complet</Text>
            <Text style={styles.infoValue}>{participant.name}</Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Email</Text>
            <Text style={styles.infoValue}>{participant.email}</Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Téléphone</Text>
            <Text style={styles.infoValue}>{participant.phone}</Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Catégorie</Text>
            <Text style={styles.infoValue}>
              {getCategoryLabel(participant.category)}
            </Text>
          </View>
        </View>
      </View>

      {/* Informations du billet */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Informations du Billet</Text>
        
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Référence</Text>
            <Text style={[styles.infoValue, styles.monospace]}>
              {ticket.reference}
            </Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Événement</Text>
            <Text style={styles.infoValue}>{ticket.event.title}</Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Montant payé</Text>
            <Text style={[styles.infoValue, styles.amount]}>
              {ticket.amount} {ticket.currency}
            </Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Statut</Text>
            <View style={styles.statusBadge}>
              <Text style={styles.statusText}>✓ Validé</Text>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Date d'activation</Text>
            <Text style={styles.infoValue}>
              {format(new Date(ticket.created_at), 'dd MMMM yyyy à HH:mm', {
                locale: fr,
              })}
            </Text>
          </View>
        </View>
      </View>

      {/* QR Code physique */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>QR Code Physique</Text>
        
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Code</Text>
            <Text style={[styles.infoValue, styles.monospace]}>
              {ticket.physical_qr_id}
            </Text>
          </View>
        </View>
      </View>

      {/* Boutons d'action */}
      <View style={styles.actions}>
        <TouchableOpacity
          style={styles.primaryButton}
          onPress={() => {
            navigation.navigate('TicketDetails', { ticket });
          }}
        >
          <Text style={styles.primaryButtonText}>
            Voir les Détails Complets
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.secondaryButton}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.secondaryButtonText}>
            Scanner un Autre QR
          </Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
};

// Fonction helper pour les labels de catégorie
const getCategoryLabel = (category: string): string => {
  const labels: Record<string, string> = {
    enseignant: 'Enseignant',
    etudiant: 'Étudiant',
    medecin: 'Médecin',
    parent: 'Parent',
  };
  return labels[category] || category;
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  alertHeader: {
    backgroundColor: '#fff3cd',
    padding: 20,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#ffc107',
  },
  alertIconContainer: {
    marginBottom: 10,
  },
  alertIcon: {
    fontSize: 48,
  },
  alertTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#856404',
    marginBottom: 8,
  },
  alertMessage: {
    fontSize: 16,
    color: '#856404',
    textAlign: 'center',
  },
  section: {
    marginTop: 20,
    paddingHorizontal: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  infoCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
    flex: 1,
  },
  infoValue: {
    fontSize: 16,
    color: '#333',
    fontWeight: '600',
    flex: 2,
    textAlign: 'right',
  },
  monospace: {
    fontFamily: 'monospace',
  },
  amount: {
    color: '#007AFF',
    fontSize: 18,
  },
  divider: {
    height: 1,
    backgroundColor: '#e0e0e0',
  },
  statusBadge: {
    backgroundColor: '#d4edda',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  statusText: {
    color: '#155724',
    fontSize: 14,
    fontWeight: '600',
  },
  actions: {
    padding: 20,
    paddingBottom: 40,
  },
  primaryButton: {
    backgroundColor: '#007AFF',
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    marginBottom: 12,
  },
  primaryButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  secondaryButton: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#007AFF',
  },
  secondaryButtonText: {
    color: '#007AFF',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default PhysicalTicketAlreadyActivatedScreen;
```

---

## 4. Récupération des Prix de l'Événement

### API Endpoint

```
GET /api/events/{eventId}/prices-for-physical
```

### Code React Native

```typescript
import axios from 'axios';

interface EventPrice {
  id: number;
  category: string;
  duration_type: string | null;
  amount: string;
  currency: string;
  label: string;
  description: string;
  display_label: string;
}

interface EventPricesResponse {
  success: boolean;
  event: {
    id: number;
    title: string;
    date: string;
    location: string;
  };
  prices: EventPrice[];
}

const fetchEventPrices = async (eventId: string, token: string): Promise<EventPricesResponse> => {
  try {
    const response = await axios.get(
      `${API_BASE_URL}/api/events/${eventId}/prices-for-physical`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      }
    );
    return response.data;
  } catch (error) {
    console.error('Erreur lors de la récupération des prix:', error);
    throw error;
  }
};
```

---

## 3. Formulaire d'Activation

### Interface TypeScript

```typescript
interface PhysicalTicketFormData {
  physical_qr_id: string;
  event_id: string;
  full_name: string;
  email: string;
  phone: string;
  event_price_id: number;
}

interface ActivatePhysicalTicketScreenProps {
  route: {
    params: {
      physicalQrId: string;
      eventId: string;
    };
  };
}
```

### Composant Formulaire

```typescript
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
} from 'react-native';
import { Picker } from '@react-native-picker/picker';

const ActivatePhysicalTicketScreen: React.FC<ActivatePhysicalTicketScreenProps> = ({
  route,
  navigation,
}) => {
  const { physicalQrId, eventId } = route.params;
  const [loading, setLoading] = useState(false);
  const [loadingPrices, setLoadingPrices] = useState(true);
  const [eventData, setEventData] = useState<any>(null);
  const [prices, setPrices] = useState<EventPrice[]>([]);
  
  const [formData, setFormData] = useState<PhysicalTicketFormData>({
    physical_qr_id: physicalQrId,
    event_id: eventId,
    full_name: '',
    email: '',
    phone: '',
    event_price_id: 0,
  });

  // Charger les prix au montage du composant
  useEffect(() => {
    loadEventPrices();
  }, []);

  const loadEventPrices = async () => {
    try {
      setLoadingPrices(true);
      const token = await getAuthToken(); // Votre fonction pour récupérer le token
      const data = await fetchEventPrices(eventId, token);
      
      setEventData(data.event);
      setPrices(data.prices);
      
      // Pré-sélectionner le premier prix si disponible
      if (data.prices.length > 0) {
        setFormData(prev => ({ ...prev, event_price_id: data.prices[0].id }));
      }
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de charger les prix de l\'événement');
      navigation.goBack();
    } finally {
      setLoadingPrices(false);
    }
  };

  const handleSubmit = async () => {
    // Validation
    if (!formData.full_name.trim()) {
      Alert.alert('Erreur', 'Veuillez entrer le nom complet');
      return;
    }
    if (!formData.email.trim()) {
      Alert.alert('Erreur', 'Veuillez entrer l\'email');
      return;
    }
    if (!formData.phone.trim()) {
      Alert.alert('Erreur', 'Veuillez entrer le numéro de téléphone');
      return;
    }
    if (!formData.event_price_id) {
      Alert.alert('Erreur', 'Veuillez sélectionner un tarif');
      return;
    }

    try {
      setLoading(true);
      const token = await getAuthToken();
      
      const response = await axios.post(
        `${API_BASE_URL}/api/tickets/physical`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
        }
      );

      if (response.data.success) {
        Alert.alert(
          'Succès',
          'Billet physique activé avec succès!',
          [
            {
              text: 'OK',
              onPress: () => {
                // Naviguer vers les détails du ticket ou retour
                navigation.navigate('TicketDetails', {
                  ticket: response.data.ticket,
                });
              },
            },
          ]
        );
      }
    } catch (error: any) {
      handleActivationError(error);
    } finally {
      setLoading(false);
    }
  };

  const handleActivationError = (error: any) => {
    if (error.response?.status === 409 && error.response?.data?.already_used) {
      // QR code déjà utilisé - Afficher les infos du billet existant
      const { ticket, participant } = error.response.data;
      
      Alert.alert(
        'Billet Déjà Activé',
        `Ce billet a déjà été activé par:\n\n` +
        `Nom: ${participant.name}\n` +
        `Email: ${participant.email}\n` +
        `Téléphone: ${participant.phone}\n` +
        `Date: ${new Date(ticket.created_at).toLocaleString('fr-FR')}`,
        [
          {
            text: 'Voir le billet',
            onPress: () => {
              navigation.navigate('TicketDetails', { ticket });
            },
          },
          {
            text: 'Fermer',
            style: 'cancel',
          },
        ]
      );
    } else if (error.response?.data?.error) {
      Alert.alert('Erreur', error.response.data.error);
    } else {
      Alert.alert('Erreur', 'Une erreur est survenue lors de l\'activation');
    }
  };

  if (loadingPrices) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#007AFF" />
        <Text style={styles.loadingText}>Chargement des tarifs...</Text>
      </View>
    );
  }

  const selectedPrice = prices.find(p => p.id === formData.event_price_id);

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Activer un Billet Physique</Text>
        {eventData && (
          <View style={styles.eventInfo}>
            <Text style={styles.eventTitle}>{eventData.title}</Text>
            <Text style={styles.eventDetails}>
              {new Date(eventData.date).toLocaleDateString('fr-FR')} • {eventData.location}
            </Text>
          </View>
        )}
      </View>

      <View style={styles.form}>
        {/* QR Code ID (lecture seule) */}
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Code QR</Text>
          <TextInput
            style={[styles.input, styles.inputDisabled]}
            value={physicalQrId}
            editable={false}
          />
        </View>

        {/* Nom complet */}
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Nom complet *</Text>
          <TextInput
            style={styles.input}
            placeholder="Ex: Jean Dupont"
            value={formData.full_name}
            onChangeText={(text) => setFormData({ ...formData, full_name: text })}
            autoCapitalize="words"
          />
        </View>

        {/* Email */}
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Email *</Text>
          <TextInput
            style={styles.input}
            placeholder="Ex: jean.dupont@example.com"
            value={formData.email}
            onChangeText={(text) => setFormData({ ...formData, email: text })}
            keyboardType="email-address"
            autoCapitalize="none"
          />
        </View>

        {/* Téléphone */}
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Téléphone *</Text>
          <TextInput
            style={styles.input}
            placeholder="Ex: +243123456789"
            value={formData.phone}
            onChangeText={(text) => setFormData({ ...formData, phone: text })}
            keyboardType="phone-pad"
          />
        </View>

        {/* Sélection du tarif */}
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Tarif *</Text>
          <View style={styles.pickerContainer}>
            <Picker
              selectedValue={formData.event_price_id}
              onValueChange={(value) => setFormData({ ...formData, event_price_id: value })}
              style={styles.picker}
            >
              <Picker.Item label="Sélectionner un tarif" value={0} />
              {prices.map((price) => (
                <Picker.Item
                  key={price.id}
                  label={`${price.display_label} - ${price.amount} ${price.currency}`}
                  value={price.id}
                />
              ))}
            </Picker>
          </View>
        </View>

        {/* Affichage du montant sélectionné */}
        {selectedPrice && (
          <View style={styles.priceDisplay}>
            <Text style={styles.priceLabel}>Montant à payer:</Text>
            <Text style={styles.priceAmount}>
              {selectedPrice.amount} {selectedPrice.currency}
            </Text>
          </View>
        )}

        {/* Bouton de soumission */}
        <TouchableOpacity
          style={[styles.submitButton, loading && styles.submitButtonDisabled]}
          onPress={handleSubmit}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.submitButtonText}>Activer le Billet</Text>
          )}
        </TouchableOpacity>

        {/* Bouton annuler */}
        <TouchableOpacity
          style={styles.cancelButton}
          onPress={() => navigation.goBack()}
          disabled={loading}
        >
          <Text style={styles.cancelButtonText}>Annuler</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#666',
  },
  header: {
    backgroundColor: '#fff',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  eventInfo: {
    marginTop: 10,
  },
  eventTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#007AFF',
  },
  eventDetails: {
    fontSize: 14,
    color: '#666',
    marginTop: 5,
  },
  form: {
    padding: 20,
  },
  inputGroup: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
  },
  inputDisabled: {
    backgroundColor: '#f0f0f0',
    color: '#666',
  },
  pickerContainer: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    overflow: 'hidden',
  },
  picker: {
    height: 50,
  },
  priceDisplay: {
    backgroundColor: '#e3f2fd',
    padding: 15,
    borderRadius: 8,
    marginBottom: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  priceLabel: {
    fontSize: 16,
    color: '#1976d2',
    fontWeight: '600',
  },
  priceAmount: {
    fontSize: 20,
    color: '#1976d2',
    fontWeight: 'bold',
  },
  submitButton: {
    backgroundColor: '#007AFF',
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 10,
  },
  submitButtonDisabled: {
    backgroundColor: '#ccc',
  },
  submitButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  cancelButton: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  cancelButtonText: {
    color: '#666',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default ActivatePhysicalTicketScreen;
```

---

## 4. Gestion des Réponses API

### Succès (201)

```typescript
interface SuccessResponse {
  success: true;
  ticket: {
    id: number;
    reference: string;
    physical_qr_id: string;
    event_id: number;
    full_name: string;
    email: string;
    phone: string;
    amount: string;
    currency: string;
    payment_status: 'completed';
    created_at: string;
    event: Event;
    participant: Participant;
    price: EventPrice;
  };
  participant: Participant;
  message: string;
}
```

### QR Code Déjà Utilisé (409)

```typescript
interface AlreadyUsedResponse {
  success: false;
  already_used: true;
  message: string;
  ticket: Ticket;
  participant: Participant;
}
```

### Erreur de Validation (400)

```typescript
interface ValidationErrorResponse {
  success: false;
  error: string;
}
```

---

## 5. Navigation

### Configuration des Routes

```typescript
// Dans votre navigation stack
import { createStackNavigator } from '@react-navigation/stack';

type RootStackParamList = {
  QRScanner: undefined;
  ActivatePhysicalTicket: {
    physicalQrId: string;
    eventId: string;
  };
  PhysicalTicketAlreadyActivated: {
    ticket: Ticket;
    participant: Participant;
  };
  TicketDetails: {
    ticket: Ticket;
  };
};

const Stack = createStackNavigator<RootStackParamList>();

function AppNavigator() {
  return (
    <Stack.Navigator>
      <Stack.Screen name="QRScanner" component={QRScannerScreen} />
      <Stack.Screen 
        name="ActivatePhysicalTicket" 
        component={ActivatePhysicalTicketScreen}
        options={{ title: 'Activer un Billet' }}
      />
      <Stack.Screen 
        name="PhysicalTicketAlreadyActivated" 
        component={PhysicalTicketAlreadyActivatedScreen}
        options={{ 
          title: 'Billet Déjà Activé',
          headerStyle: { backgroundColor: '#fff3cd' },
        }}
      />
      <Stack.Screen 
        name="TicketDetails" 
        component={TicketDetailsScreen}
        options={{ title: 'Détails du Billet' }}
      />
    </Stack.Navigator>
  );
}
```

---

## 6. Gestion du Token d'Authentification

```typescript
import AsyncStorage from '@react-native-async-storage/async-storage';

const TOKEN_KEY = '@auth_token';

export const getAuthToken = async (): Promise<string> => {
  try {
    const token = await AsyncStorage.getItem(TOKEN_KEY);
    if (!token) {
      throw new Error('Token non trouvé');
    }
    return token;
  } catch (error) {
    console.error('Erreur lors de la récupération du token:', error);
    throw error;
  }
};

export const setAuthToken = async (token: string): Promise<void> => {
  try {
    await AsyncStorage.setItem(TOKEN_KEY, token);
  } catch (error) {
    console.error('Erreur lors de la sauvegarde du token:', error);
    throw error;
  }
};

export const removeAuthToken = async (): Promise<void> => {
  try {
    await AsyncStorage.removeItem(TOKEN_KEY);
  } catch (error) {
    console.error('Erreur lors de la suppression du token:', error);
    throw error;
  }
};
```

---

## 7. Configuration Axios

```typescript
import axios from 'axios';
import { getAuthToken } from './auth';

export const API_BASE_URL = 'http://votre-api.com'; // À configurer

// Créer une instance axios avec configuration par défaut
export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Intercepteur pour ajouter le token automatiquement
apiClient.interceptors.request.use(
  async (config) => {
    try {
      const token = await getAuthToken();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (error) {
      // Token non disponible, continuer sans
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercepteur pour gérer les erreurs d'authentification
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expiré ou invalide - rediriger vers login
      await removeAuthToken();
      // Naviguer vers l'écran de connexion
      // navigation.navigate('Login');
    }
    return Promise.reject(error);
  }
);
```

---

## 8. Tests

### Test du Flux Complet

```typescript
// __tests__/PhysicalTicketActivation.test.ts
import { render, fireEvent, waitFor } from '@testing-library/react-native';
import ActivatePhysicalTicketScreen from '../screens/ActivatePhysicalTicketScreen';

describe('ActivatePhysicalTicketScreen', () => {
  it('devrait charger les prix de l\'événement', async () => {
    const { getByText } = render(
      <ActivatePhysicalTicketScreen
        route={{
          params: {
            physicalQrId: 'PHY-123-ABC',
            eventId: '1',
          },
        }}
      />
    );

    await waitFor(() => {
      expect(getByText('Activer un Billet Physique')).toBeTruthy();
    });
  });

  it('devrait valider les champs requis', async () => {
    const { getByText, getByPlaceholderText } = render(
      <ActivatePhysicalTicketScreen
        route={{
          params: {
            physicalQrId: 'PHY-123-ABC',
            eventId: '1',
          },
        }}
      />
    );

    const submitButton = getByText('Activer le Billet');
    fireEvent.press(submitButton);

    await waitFor(() => {
      expect(getByText('Veuillez entrer le nom complet')).toBeTruthy();
    });
  });
});
```

---

## 9. Checklist d'Implémentation

- [ ] Installer les dépendances nécessaires
  - [ ] `@react-native-picker/picker`
  - [ ] `@react-native-async-storage/async-storage`
  - [ ] `axios`
  - [ ] Scanner QR (react-native-camera ou expo-camera)

- [ ] Configurer l'API
  - [ ] Définir l'URL de base
  - [ ] Configurer axios avec intercepteurs
  - [ ] Implémenter la gestion du token

- [ ] Créer les écrans
  - [ ] Écran de scan QR
  - [ ] Écran d'activation de billet physique
  - [ ] Écran de détails du ticket

- [ ] Implémenter la logique
  - [ ] Détection du type de QR code
  - [ ] Récupération des prix
  - [ ] Validation du formulaire
  - [ ] Gestion des erreurs (QR déjà utilisé, etc.)

- [ ] Tests
  - [ ] Tests unitaires
  - [ ] Tests d'intégration
  - [ ] Tests end-to-end

- [ ] Documentation
  - [ ] Documenter les composants
  - [ ] Créer un guide utilisateur

---

## 10. Dépannage

### Problème: Token non valide

**Solution:** Vérifier que le token est bien stocké et envoyé dans les headers.

```typescript
// Vérifier le token
const token = await getAuthToken();
console.log('Token:', token);
```

### Problème: QR code non reconnu

**Solution:** Vérifier le format du JSON et le type.

```typescript
try {
  const qrData = JSON.parse(data);
  console.log('QR Data:', qrData);
  console.log('Type:', qrData.type);
} catch (error) {
  console.error('Erreur de parsing:', error);
}
```

### Problème: Erreur 409 (QR déjà utilisé)

**Solution:** C'est normal! Afficher les informations du billet existant à l'utilisateur.

---

## Ressources

- [Documentation React Native](https://reactnative.dev/)
- [Documentation Axios](https://axios-http.com/)
- [Documentation React Navigation](https://reactnavigation.org/)
- [API Backend - Documentation Postman](./TEST_BILLET_PHYSIQUE_POSTMAN.md)

---

## Support

Pour toute question ou problème, consultez:
- La documentation du backend: `QR_BILLETS_PHYSIQUES_SYSTEME.md`
- Les tests Postman: `TEST_BILLET_PHYSIQUE_POSTMAN.md`
- Les exemples d'activation: `EXEMPLE_ACTIVATION_BILLET_PHYSIQUE.md`
