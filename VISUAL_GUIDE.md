# Visual Guide - Payment Method for Advance Payments

## Before vs After

### Before Implementation
```
┌─────────────────────────────────────────────────────────────────┐
│  Montant de l'avance          │  Reste à payer                  │
│  [    0.00    ]               │  120.00 DHS                     │
└─────────────────────────────────────────────────────────────────┘
```
Problem: System assumed all advances were cash payments.

### After Implementation
```
┌───────────────────────────────────────────────────────────────────────────┐
│  Montant de l'avance  │  Mode de paiement      │  Reste à payer         │
│  [   20.00    ]       │  [Espèce        ▼]     │  100.00 DHS            │
└───────────────────────────────────────────────────────────────────────────┘
```
Solution: User explicitly selects payment method.

## Dropdown Options

When user clicks on "Mode de paiement" dropdown:
```
┌─────────────────────────┐
│ Sélectionner            │ ← Default (when empty)
├─────────────────────────┤
│ Espèce                  │ ← Cash payment (immediate)
│ Carte                   │ ← Card payment (immediate)
│ Chèque                  │ ← Check payment (deferred)
│ Virement               │ ← Bank transfer (deferred)
│ Autre                   │ ← Other payment method
└─────────────────────────┘
```

## User Flow

1. **Initial State**
   - Advance amount field is empty (0)
   - Payment method dropdown is DISABLED

2. **User Enters Advance Amount**
   - User types amount (e.g., 20.00)
   - Payment method dropdown becomes ENABLED
   - Save button remains DISABLED

3. **User Selects Payment Method**
   - User clicks dropdown and selects (e.g., "Espèce")
   - "Reste à payer" updates automatically
   - Save button becomes ENABLED

4. **User Saves Order**
   - Order is created with selected payment method
   - Payment record is created with correct payment type
   - Receipt shows advance payment with method

## Validation Rules

| Condition                           | Payment Method Required? | Save Button State |
|-------------------------------------|--------------------------|-------------------|
| Advance amount = 0                  | No                       | Enabled (if form valid) |
| Advance amount > 0, method empty    | Yes                      | **DISABLED**      |
| Advance amount > 0, method selected | Yes                      | Enabled (if form valid) |
| Advance amount > order total        | N/A                      | **DISABLED**      |

## Example Scenarios

### Scenario 1: Cash Advance
```
Order Total: 120.00 DHS
Advance: 20.00 DHS
Method: Espèce (Cash)
→ Payment created: type=cash, amount=20.00, status=completed
→ Remaining: 100.00 DHS
```

### Scenario 2: Card Advance
```
Order Total: 120.00 DHS
Advance: 30.00 DHS
Method: Carte (Card)
→ Payment created: type=card, amount=30.00, status=completed
→ Remaining: 90.00 DHS
```

### Scenario 3: Check Advance (Deferred)
```
Order Total: 120.00 DHS
Advance: 50.00 DHS
Method: Chèque (Check)
→ Payment created: type=cheque, amount=50.00, status=pending
→ Remaining: 70.00 DHS (but needs check collection)
```

### Scenario 4: No Advance
```
Order Total: 120.00 DHS
Advance: 0.00 DHS
Method: (not required)
→ No payment created
→ Remaining: 120.00 DHS
```

## Receipt Display

The printed receipt will show:
```
┌─────────────────────────────────────┐
│             GREENPOS                │
│                                     │
│  Reference: CMD-00123               │
│  Client: Ahmed El Mansouri          │
│  Tel: +212 600 000 000              │
│  Mode: Sur place                    │
│  RDV: 09/04/2026 16:00             │
│                                     │
│  ─────────────────────────────────  │
│                                     │
│  Article          Qte      Total    │
│  Product A         1      60.00     │
│  Product B         1      60.00     │
│                                     │
│  ─────────────────────────────────  │
│                                     │
│  Sous-total             120.00 DHS  │
│  TVA                      0.00 DHS  │
│  Avance                  20.00 DHS  │ ← Shows advance
│  Reste                  100.00 DHS  │ ← Shows remaining
│  Total                  120.00 DHS  │
│                                     │
│  Note: Avance enregistree depuis    │
│  le POS (espece)                    │ ← Shows payment method
│                                     │
│  Commande en preparation            │
└─────────────────────────────────────┘
```

## Mobile Responsiveness

On mobile devices, the grid adjusts:
```
Desktop (xl:grid-cols-4):
[Advance] [Method] [Remaining] [empty]

Tablet (md:grid-cols-2):
[Advance] [Method]
[Remaining] [empty]

Mobile:
[Advance]
[Method]
[Remaining]
```

## Accessibility Features

- Clear labels for all fields
- Disabled state is visually indicated
- Required field validation with clear error messaging
- Keyboard navigation support
- Screen reader friendly
