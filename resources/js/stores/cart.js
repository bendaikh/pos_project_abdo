import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useSettingsStore } from './settings'
import {
    useCustomListsStore,
    calculateLineDiscountAmount,
    calculateTaxAmount,
} from './customLists'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])
    const customerId = ref(null)
    const customerName = ref('Client Anonyme')
    const discountAmount = ref(0)
    const discountPercent = ref(0)
    const appliedCartDiscountIds = ref([])
    const appliedCartTaxIds = ref([])
    const customListsStore = useCustomListsStore()
    const deliveryMode = ref(customListsStore.defaultServiceModeValue())
    const deliveryAgentId = ref(null)
    const deliveryAgentLabel = ref('')
    const notes = ref('')
    const currentSaleId = ref(null)

    const settingsStore = useSettingsStore()

    // Computed
    const itemCount = computed(() => items.value.length)

    function resolveBaseUnitPrice(item) {
        if (item?.selected_variant) {
            return Number(item.selected_variant.price_impact ?? item.variant_price ?? item.unit_price) || 0
        }

        return Number(item?.unit_price) || 0
    }
    
    function calculateItemTotal(item) {
        const unitPrice = resolveBaseUnitPrice(item)
        const optionsPrice = Number(item.options_price) || 0
        const quantity = Number(item.quantity) || 0
        const discount = Number(item.discount_amount) || 0
        return (unitPrice + optionsPrice) * quantity - discount
    }

    const subtotal = computed(() => {
        return items.value.reduce((sum, item) => sum + calculateItemTotal(item), 0)
    })

    const itemDiscountsTotal = computed(() => {
        return items.value.reduce((sum, item) => sum + (Number(item.discount_amount) || 0), 0)
    })

    const appliedCartDiscounts = computed(() => {
        if (!customListsStore.discountEnabled || appliedCartDiscountIds.value.length === 0) {
            return []
        }

        return appliedCartDiscountIds.value
            .map((id) => customListsStore.activeDiscounts.find(
                (discount) => String(discount.id) === String(id)
            ))
            .filter(Boolean)
    })

    const customListDiscountTotal = computed(() => {
        let runningSubtotal = subtotal.value
        let totalDiscount = 0

        for (const discount of appliedCartDiscounts.value) {
            const amount = calculateLineDiscountAmount(discount, runningSubtotal)
            totalDiscount += amount
            runningSubtotal = Math.max(0, runningSubtotal - amount)
        }

        return Math.round(totalDiscount * 100) / 100
    })

    const cartLevelDiscountTotal = computed(() => {
        let discount = customListDiscountTotal.value + discountAmount.value
        if (discountPercent.value > 0) {
            discount += subtotal.value * (discountPercent.value / 100)
        }
        return Math.round(discount * 100) / 100
    })

    const discountTotal = computed(() => {
        return itemDiscountsTotal.value + cartLevelDiscountTotal.value
    })

    const afterDiscount = computed(() => {
        return Math.max(0, subtotal.value - cartLevelDiscountTotal.value)
    })

    const appliedCartTaxes = computed(() => {
        if (!customListsStore.taxEnabled || appliedCartTaxIds.value.length === 0) {
            return []
        }

        return appliedCartTaxIds.value
            .map((id) => customListsStore.activeTaxes.find(
                (tax) => String(tax.id) === String(id)
            ))
            .filter(Boolean)
    })

    const customListTaxTotal = computed(() => {
        const base = afterDiscount.value
        if (base <= 0 || appliedCartTaxes.value.length === 0) {
            return 0
        }

        return appliedCartTaxes.value.reduce(
            (sum, tax) => sum + calculateTaxAmount(tax, base),
            0
        )
    })

    const effectiveTaxRate = computed(() => {
        if (afterDiscount.value <= 0) return 0
        if (appliedCartTaxes.value.length > 0) {
            return Math.round((customListTaxTotal.value / afterDiscount.value) * 10000) / 100
        }
        if (!settingsStore.taxEnabled) return 0
        return Number(settingsStore.taxRate) || 0
    })

    const taxAmount = computed(() => {
        if (appliedCartTaxes.value.length > 0) {
            return Math.round(customListTaxTotal.value * 100) / 100
        }
        if (!settingsStore.taxEnabled) return 0
        return Math.round(afterDiscount.value * (settingsStore.taxRate / 100) * 100) / 100
    })

    const total = computed(() => {
        return afterDiscount.value + taxAmount.value
    })

    // Actions
    function addItem(article, quantity = 1, selectedOptions = null, optionsPrice = 0, selectedVariant = null) {
        const variantPrice = selectedVariant ? Number(selectedVariant.price_impact) || 0 : 0
        const resolvedUnitPrice = selectedVariant ? variantPrice : (Number(article.sell_price) || 0)
        const finalOptionsPrice = Number(optionsPrice) || 0
        const itemTotal = (resolvedUnitPrice + finalOptionsPrice) * quantity
        const selectedVariantId = selectedVariant?.id || null
        
        console.log('🛒 addItem details:', {
            article: article.name,
            sell_price: article.sell_price,
            variantPrice,
            resolvedUnitPrice,
            optionsPrice: finalOptionsPrice,
            quantity,
            itemTotal,
            calculated: `${resolvedUnitPrice} + ${finalOptionsPrice} = ${resolvedUnitPrice + finalOptionsPrice}`
        })
        
        const existingIndex = items.value.findIndex(
            item => item.article_id === article.id && 
                    JSON.stringify(item.selected_options || []) === JSON.stringify(selectedOptions || []) &&
                    ((item.selected_variant?.id || null) === selectedVariantId)
        )

        if (existingIndex > -1) {
            items.value[existingIndex].quantity += quantity
            recalculateItemTotal(existingIndex)
        } else {
            items.value.push({
                article_id: article.id,
                article_name: article.name,
                unit_price: resolvedUnitPrice,
                quantity: quantity,
                selected_options: selectedOptions,
                options_price: finalOptionsPrice,
                variant_price: variantPrice,
                selected_variant: selectedVariant,
                comment: '',
                discount_amount: 0,
                applied_discount: null,
                total: itemTotal,
                article: article
            })
        }
    }

    function updateItemQuantity(index, quantity) {
        if (quantity <= 0) {
            removeItem(index)
        } else {
            items.value[index].quantity = quantity
            recalculateItemTotal(index)
        }
    }

    function removeItem(index) {
        items.value.splice(index, 1)
    }

    function recalculateItemDiscount(item) {
        if (!item?.applied_discount) {
            return
        }

        const unitPrice = resolveBaseUnitPrice(item)
        const optionsPrice = Number(item.options_price) || 0
        const quantity = Number(item.quantity) || 0
        const lineSubtotal = (unitPrice + optionsPrice) * quantity
        item.discount_amount = calculateLineDiscountAmount(item.applied_discount, lineSubtotal)
    }

    function recalculateItemTotal(index) {
        const item = items.value[index]
        recalculateItemDiscount(item)
        item.total = calculateItemTotal(item)
    }

    function setItemDiscount(index, appliedDiscount = null) {
        const item = items.value[index]
        if (!item) return

        if (!appliedDiscount) {
            item.applied_discount = null
            item.discount_amount = 0
        } else {
            item.applied_discount = {
                id: appliedDiscount.id,
                label: appliedDiscount.label,
                discount_type: appliedDiscount.discount_type === 'fixed' ? 'fixed' : 'percentage',
                discount_value: Number(appliedDiscount.discount_value) || 0,
                discount_limit: Number(appliedDiscount.discount_limit) || 0,
            }
            recalculateItemDiscount(item)
        }

        item.total = calculateItemTotal(item)
    }

    function updateItemOptions(index, selectedOptions = null, optionsPrice = 0) {
        if (!items.value[index]) return
        items.value[index].selected_options = selectedOptions
        items.value[index].options_price = optionsPrice
        recalculateItemTotal(index)
    }

    function setCustomer(id, name) {
        customerId.value = id
        customerName.value = name || 'Client Anonyme'
    }

    function setDiscount(amount = 0, percent = 0) {
        discountAmount.value = amount
        discountPercent.value = percent
    }

    function setAppliedCartAdjustments({ discountIds = [], taxIds = [] } = {}) {
        appliedCartDiscountIds.value = [...discountIds]
        appliedCartTaxIds.value = [...taxIds]
        discountAmount.value = 0
        discountPercent.value = 0
    }

    function setDeliveryMode(mode) {
        deliveryMode.value = mode || customListsStore.defaultServiceModeValue()
    }

    function setDeliveryAgent(agent) {
        deliveryAgentId.value = agent?.id || null
        deliveryAgentLabel.value = agent?.label || agent?.name || ''
    }

    function setNotes(text) {
        notes.value = text
    }

    function setSaleId(id) {
        currentSaleId.value = id
    }

    function hydrateFromSale(sale) {
        const customer = sale?.customer || null
        const deliveryAgent = sale?.delivery_agent || sale?.deliveryAgent || null
        const resolvedServiceMode = customListsStore.findServiceMode(
            sale?.service_mode || sale?.delivery_mode,
            { includeInactive: true }
        )?.value || customListsStore.defaultServiceModeValue()

        items.value = (sale?.items || []).map((item) => ({
            article_id: item.article_id,
            article_name: item.article_name,
            unit_price: Number(item.unit_price) || 0,
            quantity: Number(item.quantity) || 0,
            selected_options: Array.isArray(item.selected_options)
                ? JSON.parse(JSON.stringify(item.selected_options))
                : (item.selected_options || null),
            options_price: Number(item.options_price) || 0,
            variant_price: 0,
            selected_variant: null,
            comment: item.comment || '',
            discount_amount: Number(item.discount_amount) || 0,
            applied_discount: item.applied_discount
                ? JSON.parse(JSON.stringify(item.applied_discount))
                : null,
            total: Number(item.total) || 0,
            article: item.article || null,
        }))

        customerId.value = customer?.id || sale?.customer_id || null
        customerName.value = customer?.name || 'Client Anonyme'
        discountAmount.value = Number(sale?.discount_amount) || 0
        discountPercent.value = Number(sale?.discount_percent) || 0
        deliveryMode.value = resolvedServiceMode
        deliveryAgentId.value = deliveryAgent?.id || sale?.delivery_agent_id || null
        deliveryAgentLabel.value = deliveryAgent?.name
            || sale?.delivery_agent_name_snapshot
            || ''
        notes.value = sale?.notes || ''
        currentSaleId.value = sale?.id || null
    }

    function clearCart() {
        items.value = []
        customerId.value = null
        customerName.value = 'Client Anonyme'
        discountAmount.value = 0
        discountPercent.value = 0
        appliedCartDiscountIds.value = []
        appliedCartTaxIds.value = []
        deliveryMode.value = customListsStore.defaultServiceModeValue()
        deliveryAgentId.value = null
        deliveryAgentLabel.value = ''
        notes.value = ''
        currentSaleId.value = null
    }

    function getCartData() {
        const data = {
            customer_id: customerId.value,
            items: items.value.map(item => ({
                article_id: item.article_id,
                quantity: item.quantity,
                unit_price: resolveBaseUnitPrice(item),
                variant_price: item.variant_price || 0,
                selected_options: item.selected_options,
                options_price: item.options_price,
                discount_amount: item.discount_amount,
                comment: item.comment || '',
            })),
            discount_amount: cartLevelDiscountTotal.value,
            discount_percent: discountPercent.value,
            tax_rate: effectiveTaxRate.value,
            service_mode: deliveryMode.value || customListsStore.defaultServiceModeValue(),
            delivery_mode: deliveryAgentId.value
                ? 'delivery'
                : customListsStore.getServiceModeMeta(deliveryMode.value).operational_mode,
            delivery_agent_id: deliveryAgentId.value,
            notes: notes.value,
            subtotal: subtotal.value,
            tax: taxAmount.value,
            total: total.value,
            items_count: items.value.length
        }
        console.log('DEBUG cart.getCartData:', data)
        return data
    }

    return {
        items,
        customerId,
        customerName,
        discountAmount,
        discountPercent,
        appliedCartDiscountIds,
        appliedCartTaxIds,
        appliedCartDiscounts,
        appliedCartTaxes,
        customListDiscountTotal,
        customListTaxTotal,
        effectiveTaxRate,
        deliveryMode,
        deliveryAgentId,
        deliveryAgentLabel,
        notes,
        currentSaleId,
        itemCount,
        subtotal,
        itemDiscountsTotal,
        cartLevelDiscountTotal,
        discountTotal,
        afterDiscount,
        taxAmount,
        total,
        addItem,
        updateItemQuantity,
        removeItem,
        updateItemOptions,
        setItemDiscount,
        setCustomer,
        setDiscount,
        setAppliedCartAdjustments,
        setDeliveryMode,
        setDeliveryAgent,
        setNotes,
        setSaleId,
        hydrateFromSale,
        clearCart,
        getCartData
    }
})
