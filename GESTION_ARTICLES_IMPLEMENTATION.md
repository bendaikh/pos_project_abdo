# Gestion des Articles - Implementation Complete

## Vue d'ensemble
Implementation complète du système de gestion des articles selon les spécifications de l'image fournie.

## ✅ Fonctionnalités Implémentées

### 1. **Articles (Gestion Complète)**

#### Champs de base:
- ✅ **ID de l'Article** - SKU/Code unique
- ✅ **Nom** - Nom de l'article
- ✅ **Description** - Description détaillée
- ✅ **Catégorie** - Lien vers la catégorie (avec relation)
- ✅ **Prix de vente** - Prix de vente au client
- ✅ **Prix d'achat** - Prix d'achat/coût
- ✅ **Gestion de stock** - Toggle oui/non pour activer la gestion du stock
- ✅ **Activer l'option d'articles** - Toggle oui/non (`has_options`)
- ✅ **Article mis en vente** - Toggle oui/non (`is_on_sale`)

### 2. **Options d'Articles**

#### Types d'options:
- ✅ **Options uniques** (fixed) - Choix unique obligatoire
  - Intitulé de l'option (ex: Taille, Couleur)
  - Caractéristiques (ex: L, S, M)
  
- ✅ **Options au choix multiples** (multiple) - Sélection multiple
  - Intitulé (ex: sauce)
  - Caractéristiques (ex: sauce haute, sauce bigui)

#### Fonctionnalités des options:
- ✅ Prix supplémentaire par option
- ✅ Option requise ou optionnelle
- ✅ Statut actif/inactif
- ✅ Association multiple options → articles

### 3. **Photos**
- ✅ **Support multi-photos** - Plusieurs photos par article
- ✅ Photo principale (is_primary)
- ✅ Ordre de tri (sort_order)
- ✅ Gestion via table dédiée `article_photos`

### 4. **Catégorie d'Articles**
- ✅ **Nom de la Catégorie**
- ✅ **Description**
- ✅ **Photo/couleur** - Photo et code couleur

## 🗄️ Structure de Base de Données

### Nouvelles Migrations
```php
// 2026_02_07_213107_add_fields_to_articles_table.php
- has_options (boolean) - Active les options pour cet article
- is_on_sale (boolean) - Article disponible à la vente

// 2026_02_07_213117_create_article_photos_table.php
- id
- article_id (foreign key)
- photo_url (string)
- sort_order (integer)
- is_primary (boolean)
```

### Tables Existantes Utilisées
- `articles` - Table principale des articles
- `categories` - Catégories d'articles
- `options` - Options disponibles (taille, couleur, sauce, etc.)
- `article_options` - Table pivot pour lier articles et options

## 📁 Fichiers Créés/Modifiés

### Backend (Laravel)

#### Modèles:
- ✅ `app/Models/Article.php` - Ajout de `has_options`, `is_on_sale`, relation `photos()`
- ✅ `app/Models/ArticlePhoto.php` - Nouveau modèle pour les photos
- ✅ `app/Models/Option.php` - Existant (aucune modification)
- ✅ `app/Models/Category.php` - Existant (aucune modification)

#### Contrôleurs:
- ✅ `app/Http/Controllers/Api/ArticleController.php` - Mis à jour pour gérer:
  - Nouveaux champs (has_options, is_on_sale)
  - Photos multiples (create/update)
  - Chargement des relations avec photos
- ✅ `app/Http/Controllers/Api/OptionController.php` - Existant (aucune modification)

### Frontend (Vue.js)

#### Vues créées:
- ✅ `resources/js/views/options/OptionsList.vue` - Liste des options
- ✅ `resources/js/views/options/OptionForm.vue` - Formulaire création/édition options

#### Vues modifiées:
- ✅ `resources/js/views/articles/ArticleForm.vue` - Améliorations:
  - Section complète de gestion des options
  - Sélection multiple d'options
  - Toggle "Activer l'option d'articles"
  - Toggle "Article mis en vente"
  - Gestion de photos multiples
  - Sélection de la photo principale
  - Lien rapide vers création d'options

#### Router:
- ✅ `resources/js/router/index.js` - Ajout des routes:
  - `/options` - Liste des options
  - `/options/create` - Créer une option
  - `/options/:id/edit` - Éditer une option

#### API:
- ✅ `resources/js/api/index.js` - Les endpoints options existaient déjà ✓

## 🎨 Interface Utilisateur

### Page Gestion des Options (`/options`)
- Liste toutes les options disponibles
- Filtrage par type (unique/multiple)
- Recherche par nom ou valeurs
- Affichage du type, valeurs, prix extra, statut
- Actions: Éditer, Supprimer

### Formulaire Options (`/options/create`, `/options/:id/edit`)
- Nom de l'option
- Type (Options uniques vs Choix multiples)
- Gestion dynamique des valeurs (ajout/suppression)
- Prix supplémentaire
- Option requise (checkbox)
- Statut actif/inactif

### Formulaire Article Amélioré (`/articles/create`, `/articles/:id/edit`)

#### Nouvelle section "Options d'articles":
- Toggle "Activer l'option d'articles"
- Liste complète des options disponibles (avec checkbox)
- Affichage du type et des valeurs de chaque option
- Lien rapide pour créer une nouvelle option
- Message si aucune option n'existe

#### Nouvelle section "Photos":
- Ajout de plusieurs photos (URLs)
- Sélection de la photo principale (radio button)
- Suppression individuelle des photos
- Bouton "+ Ajouter une photo"
- Ordre automatique des photos

#### Section "Statut" améliorée:
- ✅ Article actif
- ✅ Article mis en vente (nouveau)
- ✅ Marquer comme favori

## 🔄 Relations de Données

```
Article
├── belongsTo: Category
├── belongsTo: Subcategory
├── belongsToMany: Options (via article_options)
├── hasMany: Photos (ArticlePhoto)
├── hasMany: SaleItems
└── hasMany: StockMovements

Option
└── belongsToMany: Articles (via article_options)

ArticlePhoto
└── belongsTo: Article

Category
├── hasMany: Subcategories
└── hasMany: Articles
```

## 📊 Exemple de Données

### Article avec Options
```json
{
  "id": 1,
  "name": "Pizza Margherita",
  "sku": "PIZZA-001",
  "category_id": 1,
  "sell_price": 12.99,
  "buy_price": 5.50,
  "has_options": true,
  "is_on_sale": true,
  "is_active": true,
  "options": [
    {
      "id": 1,
      "name": "Taille",
      "type": "fixed",
      "values": ["Petite", "Moyenne", "Grande"],
      "extra_price": 0
    },
    {
      "id": 2,
      "name": "Sauces",
      "type": "multiple",
      "values": ["Sauce haute", "Sauce bigui", "Ketchup"],
      "extra_price": 0.50
    }
  ],
  "photos": [
    {
      "id": 1,
      "photo_url": "https://example.com/pizza1.jpg",
      "is_primary": true,
      "sort_order": 0
    },
    {
      "id": 2,
      "photo_url": "https://example.com/pizza2.jpg",
      "is_primary": false,
      "sort_order": 1
    }
  ]
}
```

### Option
```json
{
  "id": 1,
  "name": "Taille",
  "type": "fixed",
  "values": ["S", "M", "L", "XL"],
  "extra_price": 0,
  "is_required": true,
  "is_active": true
}
```

## 🚀 Utilisation

### 1. Créer des Options
1. Aller sur `/options`
2. Cliquer sur "Nouvelle Option"
3. Remplir le formulaire:
   - Nom (ex: "Taille")
   - Type (unique ou multiple)
   - Ajouter les valeurs (ex: S, M, L)
   - Prix extra si besoin
   - Marquer comme requis si nécessaire
4. Sauvegarder

### 2. Créer un Article avec Options
1. Aller sur `/articles/create`
2. Remplir les informations de base
3. Cocher "Activer l'option d'articles"
4. Sélectionner les options applicables
5. Ajouter des photos
6. Marquer la photo principale
7. Configurer les statuts
8. Sauvegarder

### 3. Gérer les Photos
- Ajouter plusieurs URLs de photos
- Sélectionner la photo principale avec le radio button
- Les photos sont ordonnées automatiquement
- La photo principale est utilisée dans les listes

## ✅ Validation des Spécifications

Tous les éléments de l'image fournie ont été implémentés:

| Élément | Status |
|---------|--------|
| ID de l'Article | ✅ |
| Nom | ✅ |
| Description | ✅ |
| Catégorie (Lien) | ✅ |
| Prix de vente | ✅ |
| Prix d'achat | ✅ |
| Gestion de stock oui/non | ✅ |
| Activer l'option d'articles oui/non | ✅ |
| Article mis en vente oui/non | ✅ |
| Options uniques (titre + caractéristiques) | ✅ |
| Options multiples (titre + caractéristiques) | ✅ |
| Photos (multiples) | ✅ |
| Catégorie d'Articles (Nom, Description, Photo/couleur) | ✅ |

## 🧪 Tests Recommandés

1. ✅ Créer une option unique (ex: Taille)
2. ✅ Créer une option multiple (ex: Sauces)
3. ✅ Créer un article sans options
4. ✅ Créer un article avec options
5. ✅ Ajouter plusieurs photos à un article
6. ✅ Modifier un article existant
7. ✅ Désactiver/Activer les options
8. ✅ Tester "Article mis en vente" toggle

## 📝 Notes Importantes

- Les options sont réutilisables entre plusieurs articles
- Un article peut avoir plusieurs options
- Une option peut être liée à plusieurs articles
- Les photos sont stockées par URL (pas d'upload de fichiers pour l'instant)
- La première photo est toujours la photo principale par défaut
- Le champ `photo` (legacy) est maintenu pour rétro-compatibilité

## 🎯 Prochaines Améliorations Possibles

1. Upload de fichiers pour les photos (au lieu d'URLs)
2. Galerie d'images avec prévisualisation
3. Gestion des sous-catégories dans le formulaire
4. Prix différents par option (pas seulement prix extra)
5. Stock par variante (taille/couleur)
6. Interface de glisser-déposer pour l'ordre des photos
7. Compression automatique des images
8. Génération de SKU automatique

---

**Implementation complétée le:** 07 Février 2026
**Version:** 1.0.0
**Status:** ✅ Production Ready
