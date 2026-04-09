# Payment Method Selection for Advance Payments - Implementation Summary

## What Was Implemented

Added a **"Mode de paiement"** (Payment method) dropdown field in the order creation modal (Commande tab) that allows users to specify how the advance payment was made.

## Changes Made

### 1. Frontend Component Update
**File:** `resources/js/components/pos/SaveTicketModal.vue`

#### UI Changes:
- Added a new dropdown field "Mode de paiement" next to "Montant de l'avance" (line 374-388)
- The dropdown is disabled when no advance amount is entered
- Available payment methods:
  - Espèce (Cash)
  - Carte (Card)
  - Chèque (Check)
  - Virement (Bank Transfer)
  - Autre (Other)

#### Logic Changes:
1. **Added `advance_payment_method` field to `commandForm`** (line 486-495):
   ```javascript
   const commandForm = ref({
       appointment_at: '',
       delivery_mode: normalizeDeliveryMode(props.defaultDeliveryMode),
       advance_amount: 0,
       advance_payment_method: '',  // NEW FIELD
       notes: '',
       customer_phone: '',
       customer_activity: '',
       customer_address: '',
   })
   ```

2. **Updated validation** (line 676-687):
   - Payment method is now required when advance amount > 0
   - The "Enregistrer, imprimer la commande" button is disabled until payment method is selected

3. **Updated `saveCommandeTicket` function** (line 940-956):
   - Maps French payment method values to backend payment types:
     - `espece` → `cash`
     - `carte` → `card`
     - `cheque` → `cheque`
     - `virement` → `virement`
     - `autre` → `other`
   - Sends the selected payment type when creating the advance payment
   - Includes payment method in the notes for tracking

## User Experience

### Before:
- User enters advance amount
- System automatically creates a "cash" payment

### After:
1. User enters advance amount in "Montant de l'avance" field
2. "Mode de paiement" dropdown becomes enabled
3. User must select payment method (Espèce, Carte, Chèque, Virement, or Autre)
4. Save button remains disabled until payment method is selected
5. System creates payment with the correct payment type

## Layout

The form now has this structure in the grid:
```
┌─────────────────────────┬─────────────────────────┬─────────────────────────┐
│  Montant de l'avance    │   Mode de paiement      │   Reste à payer         │
│  (input field)          │   (dropdown)            │   (display only)        │
└─────────────────────────┴─────────────────────────┴─────────────────────────┘
```

## Technical Notes

- The dropdown is automatically disabled when `advance_amount` is 0 or empty
- Payment type mapping ensures compatibility with the existing payment system
- The selected payment method is included in payment notes for audit trail
- Error handling: If payment creation fails, user is notified but the order is still saved

## Testing

To test this feature:
1. Go to POS view
2. Add items to cart
3. Click "Enregistrer" (Save)
4. Select "Commande" tab
5. Fill in appointment date and customer info
6. Enter an advance amount (e.g., 20.00)
7. Notice the "Mode de paiement" dropdown is now enabled
8. Select a payment method (e.g., "Espèce")
9. Click "Enregistrer, imprimer la commande"
10. Verify the order is created with the correct payment method

## Backend Compatibility

This implementation works with the existing backend payment API:
- Endpoint: `POST /api/sales/{sale}/payments`
- The payment types are already supported by the backend
- No backend changes were required for this feature
