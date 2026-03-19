import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useSettingsStore } from './settings'
import { useCustomListsStore } from './customLists'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])
    const customerId = ref(null)
    const customerName = ref('Client Anonyme')
    const discountAmount = ref(0)
    const discountPercent = ref(0)
    const customListsStore = useCustomListsStore()
    const deliveryMode = ref(customListsStore.defaultServiceModeValue())
    const deliveryAgentId = ref(null)
    const deliveryAgentLabel = ref('')
    const notes = ref('')
    const currentSaleId = ref(null)

    const settingsStore = useSettingsStore()

    // Computed
    const itemCount = computed(() => items.value.length)
    
    function calculateItemTotal(item) {
        const unitPrice = Number(item.unit_price) || 0
        const variantPrice = Number(item.variant_price) || 0
        const optionsPrice = Number(item.options_price) || 0
        const quantity = Number(item.quantity) || 0
        const discount = Number(item.discount_amount) || 0
        return (unitPrice + variantPrice + optionsPrice) * quantity - discount
    }

    const subtotal = computed(() => {
        return items.value.reduce((sum, item) => sum + calculateItemTotal(item), 0)
    })

    const discountTotal = computed(() => {
        let discount = discountAmount.value
        if (discountPercent.value > 0) {
            discount += subtotal.value * (discountPercent.value / 100)
        }
        return discount
    })

    const afterDiscount = computed(() => {
        return Math.max(0, subtotal.value - discountTotal.value)
    })

    const taxAmount = computed(() => {
        if (!settingsStore.taxEnabled) return 0
        return afterDiscount.value * (settingsStore.taxRate / 100)
    })

    const total = computed(() => {
        return afterDiscount.value + taxAmount.value
    })

    // Actions
    function addItem(article, quantity = 1, selectedOptions = null, optionsPrice = 0, selectedVariant = null) {
        const variantPrice = selectedVariant ? Number(selectedVariant.price_impact) || 0 : 0
        const finalOptionsPrice = Number(optionsPrice) || 0
        const itemTotal = (article.sell_price + variantPrice + finalOptionsPrice) * quantity
        
        console.log('🛒 addItem details:', {
            article: article.name,
            sell_price: article.sell_price,
            variantPrice,
            optionsPrice: finalOptionsPrice,
            quantity,
            itemTotal,
            calculated: `${article.sell_price} + ${variantPrice} + ${finalOptionsPrice} = ${article.sell_price + variantPrice + finalOptionsPrice}`
        })
        
        const existingIndex = items.value.findIndex(
            item => item.article_id === article.id && 
                    JSON.stringify(item.selected_options || []) === JSON.stringify(selectedOptions || []) &&
                    (item.variant_price || 0) === variantPrice
        )

        if (existingIndex > -1) {
            items.value[existingIndex].quantity += quantity
            recalculateItemTotal(existingIndex)
        } else {
            items.value.push({
                article_id: article.id,
                article_name: article.name,
                unit_price: article.sell_price,
                quantity: quantity,
                selected_options: selectedOptions,
                options_price: finalOptionsPrice,
                variant_price: variantPrice,
                selected_variant: selectedVariant,
                discount_amount: 0,
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

    function recalculateItemTotal(index) {
        const item = items.value[index]
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
            discount_amount: Number(item.discount_amount) || 0,
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
                unit_price: item.unit_price,
                variant_price: item.variant_price || 0,
                selected_options: item.selected_options,
                options_price: item.options_price,
                discount_amount: item.discount_amount
            })),
            discount_amount: discountAmount.value,
            discount_percent: discountPercent.value,
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
        deliveryMode,
        deliveryAgentId,
        deliveryAgentLabel,
        notes,
        currentSaleId,
        itemCount,
        subtotal,
        discountTotal,
        afterDiscount,
        taxAmount,
        total,
        addItem,
        updateItemQuantity,
        removeItem,
        updateItemOptions,
        setCustomer,
        setDiscount,
        setDeliveryMode,
        setDeliveryAgent,
        setNotes,
        setSaleId,
        hydrateFromSale,
        clearCart,
        getCartData
    }
})
