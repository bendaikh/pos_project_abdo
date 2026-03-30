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
