const DENOMINATION_PRESETS = {
    MAD: {
        country: 'MA',
        bills: [20, 50, 100, 200],
        coins: [0.5, 1, 2, 5, 10],
    },
    EUR: {
        country: 'FR',
        bills: [5, 10, 20, 50, 100, 200, 500],
        coins: [0.01, 0.02, 0.05, 0.1, 0.2, 0.5, 1, 2],
    },
    USD: {
        country: 'US',
        bills: [1, 2, 5, 10, 20, 50, 100],
        coins: [0.01, 0.05, 0.1, 0.25, 0.5, 1],
    },
    GBP: {
        country: 'GB',
        bills: [5, 10, 20, 50],
        coins: [0.01, 0.02, 0.05, 0.1, 0.2, 0.5, 1, 2],
    },
    AED: {
        country: 'AE',
        bills: [5, 10, 20, 50, 100, 200, 500, 1000],
        coins: [0.25, 0.5, 1],
    },
    SAR: {
        country: 'SA',
        bills: [1, 5, 10, 50, 100, 500],
        coins: [0.05, 0.1, 0.25, 0.5, 1, 2],
    },
    DZD: {
        country: 'DZ',
        bills: [100, 200, 500, 1000, 2000],
        coins: [5, 10, 20, 50, 100, 200],
    },
    TND: {
        country: 'TN',
        bills: [5, 10, 20, 50],
        coins: [0.01, 0.02, 0.05, 0.1, 0.2, 0.5, 1, 2, 5],
    },
    EGP: {
        country: 'EG',
        bills: [1, 5, 10, 20, 50, 100, 200],
        coins: [0.25, 0.5, 1],
    },
    CAD: {
        country: 'CA',
        bills: [5, 10, 20, 50, 100],
        coins: [0.05, 0.1, 0.25, 1, 2],
    },
    CHF: {
        country: 'CH',
        bills: [10, 20, 50, 100, 200, 1000],
        coins: [0.05, 0.1, 0.2, 0.5, 1, 2, 5],
    },
    CNY: {
        country: 'CN',
        bills: [1, 5, 10, 20, 50, 100],
        coins: [0.1, 0.5, 1],
    },
    JPY: {
        country: 'JP',
        bills: [1000, 2000, 5000, 10000],
        coins: [1, 5, 10, 50, 100, 500],
    },
    INR: {
        country: 'IN',
        bills: [10, 20, 50, 100, 200, 500, 2000],
        coins: [1, 2, 5, 10],
    },
    AUD: {
        country: 'AU',
        bills: [5, 10, 20, 50, 100],
        coins: [0.05, 0.1, 0.2, 0.5, 1, 2],
    },
    BRL: {
        country: 'BR',
        bills: [2, 5, 10, 20, 50, 100, 200],
        coins: [0.05, 0.1, 0.25, 0.5, 1],
    },
    MXN: {
        country: 'MX',
        bills: [20, 50, 100, 200, 500, 1000],
        coins: [0.5, 1, 2, 5, 10, 20],
    },
    ZAR: {
        country: 'ZA',
        bills: [10, 20, 50, 100, 200],
        coins: [0.1, 0.2, 0.5, 1, 2, 5],
    },
    TRY: {
        country: 'TR',
        bills: [5, 10, 20, 50, 100, 200],
        coins: [0.05, 0.1, 0.25, 0.5, 1],
    },
}

export const COUNTRY_CURRENCY_MAP = {
    MA: { name: 'Maroc', currency: 'MAD', symbol: 'DH' },
    FR: { name: 'France', currency: 'EUR', symbol: '€' },
    DE: { name: 'Allemagne', currency: 'EUR', symbol: '€' },
    ES: { name: 'Espagne', currency: 'EUR', symbol: '€' },
    IT: { name: 'Italie', currency: 'EUR', symbol: '€' },
    BE: { name: 'Belgique', currency: 'EUR', symbol: '€' },
    NL: { name: 'Pays-Bas', currency: 'EUR', symbol: '€' },
    PT: { name: 'Portugal', currency: 'EUR', symbol: '€' },
    US: { name: 'États-Unis', currency: 'USD', symbol: '$' },
    GB: { name: 'Royaume-Uni', currency: 'GBP', symbol: '£' },
    AE: { name: 'Émirats Arabes Unis', currency: 'AED', symbol: 'د.إ' },
    SA: { name: 'Arabie Saoudite', currency: 'SAR', symbol: 'ر.س' },
    DZ: { name: 'Algérie', currency: 'DZD', symbol: 'د.ج' },
    TN: { name: 'Tunisie', currency: 'TND', symbol: 'د.ت' },
    EG: { name: 'Égypte', currency: 'EGP', symbol: '£' },
    CA: { name: 'Canada', currency: 'CAD', symbol: '$' },
    CH: { name: 'Suisse', currency: 'CHF', symbol: 'CHF' },
    CN: { name: 'Chine', currency: 'CNY', symbol: '¥' },
    JP: { name: 'Japon', currency: 'JPY', symbol: '¥' },
    IN: { name: 'Inde', currency: 'INR', symbol: '₹' },
    AU: { name: 'Australie', currency: 'AUD', symbol: '$' },
    BR: { name: 'Brésil', currency: 'BRL', symbol: 'R$' },
    MX: { name: 'Mexique', currency: 'MXN', symbol: '$' },
    ZA: { name: 'Afrique du Sud', currency: 'ZAR', symbol: 'R' },
    TR: { name: 'Turquie', currency: 'TRY', symbol: '₺' },
}

function normalizeAmount(value) {
    return Math.round(Number(value || 0) * 100) / 100
}

function uniqueSorted(values) {
    return [...new Set(values.map((value) => normalizeAmount(value)).filter((value) => value > 0))]
        .sort((a, b) => a - b)
}

function resolveCurrencyKey(currencyCountry, currencyCode) {
    const normalizedCode = String(currencyCode || '').trim().toUpperCase()
    if (DENOMINATION_PRESETS[normalizedCode]) {
        return normalizedCode
    }

    const normalizedCountry = String(currencyCountry || '').trim().toUpperCase()
    return Object.entries(DENOMINATION_PRESETS).find(([, preset]) => preset.country === normalizedCountry)?.[0] || 'MAD'
}

export function getCurrencyDenominationCatalog(currencyCountry, currencyCode) {
    const key = resolveCurrencyKey(currencyCountry, currencyCode)
    const preset = DENOMINATION_PRESETS[key] || DENOMINATION_PRESETS.MAD

    return {
        key,
        bills: uniqueSorted(preset.bills || []),
        coins: uniqueSorted(preset.coins || []),
    }
}

export function normalizeDenominationSelection(selection, availableValues) {
    const availableSet = new Set(uniqueSorted(availableValues))

    return uniqueSorted(Array.isArray(selection) ? selection : [])
        .filter((value) => availableSet.has(value))
}

export function getVisibleCurrencyDenominations({
    currencyCountry,
    currencyCode,
    visibleBills,
    visibleCoins,
}) {
    const catalog = getCurrencyDenominationCatalog(currencyCountry, currencyCode)
    const bills = Array.isArray(visibleBills)
        ? normalizeDenominationSelection(visibleBills, catalog.bills)
        : catalog.bills
    const coins = Array.isArray(visibleCoins)
        ? normalizeDenominationSelection(visibleCoins, catalog.coins)
        : catalog.coins

    return {
        ...catalog,
        visibleBills: bills,
        visibleCoins: coins,
        visible: uniqueSorted([...bills, ...coins]),
    }
}
