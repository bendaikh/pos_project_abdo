import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useSettingsStore } from './settings'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])
    const customerId = ref(null)
    const customerName = ref('Client Anonyme')
    const discountAmount = ref(0)
    const discountPercent = ref(0)
    const deliveryMode = ref('pickup')
    const notes = ref('')
    const currentSaleId = ref(null)

    const settingsStore = useSettingsStore()

    // Computed
    const itemCount = computed(() => items.value.length)
    
    const subtotal = computed(() => {
        return items.value.reduce((sum, item) => sum + item.total, 0)
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
    function addItem(article, quantity = 1, selectedOptions = null, optionsPrice = 0) {
        const existingIndex = items.value.findIndex(
            item => item.article_id === article.id && 
                    JSON.stringify(item.selected_options) === JSON.stringify(selectedOptions)
        )

        if (existingIndex > -1) {
            items.value[existingIndex].quantity += quantity
            recalculateItemTotal(existingIndex)
        } else {
            const itemTotal = (article.sell_price + optionsPrice) * quantity
            items.value.push({
                article_id: article.id,
                article_name: article.name,
                unit_price: article.sell_price,
                quantity: quantity,
                selected_options: selectedOptions,
                options_price: optionsPrice,
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
        const baseTotal = (item.unit_price + item.options_price) * item.quantity
        item.total = baseTotal - item.discount_amount
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
        deliveryMode.value = mode
    }

    function setNotes(text) {
        notes.value = text
    }

    function setSaleId(id) {
        currentSaleId.value = id
    }

    function clearCart() {
        items.value = []
        customerId.value = null
        customerName.value = 'Client Anonyme'
        discountAmount.value = 0
        discountPercent.value = 0
        deliveryMode.value = 'pickup'
        notes.value = ''
        currentSaleId.value = null
    }

    function getCartData() {
        return {
            customer_id: customerId.value,
            items: items.value.map(item => ({
                article_id: item.article_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                selected_options: item.selected_options,
                options_price: item.options_price,
                discount_amount: item.discount_amount
            })),
            discount_amount: discountAmount.value,
            discount_percent: discountPercent.value,
            delivery_mode: deliveryMode.value,
            notes: notes.value
        }
    }

    return {
        items,
        customerId,
        customerName,
        discountAmount,
        discountPercent,
        deliveryMode,
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
        setCustomer,
        setDiscount,
        setDeliveryMode,
        setNotes,
        setSaleId,
        clearCart,
        getCartData
    }
})
