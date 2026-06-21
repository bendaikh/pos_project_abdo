export function normalizeKey(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
}

export function createMethodId(item, index) {
    if (item.payment_type === 'cash') return 'cash'
    if (item.payment_type === 'card') return 'card'
    if (item.payment_type === 'mobile') return 'mobile'
    if (item.payment_type === 'credit') return 'credit'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return 'instant_transfer'
    if (item.payment_type === 'virement') return 'simple_transfer'

    return `other:${normalizeKey(item.label || item.value || index)}`
}

export function iconForPaymentMethod(item) {
    if (item.payment_type === 'cash') return '💵'
    if (item.payment_type === 'card') return '💳'
    if (item.payment_type === 'mobile') return '📱'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return '⚡'
    if (item.payment_type === 'virement') return '🏦'
    if (item.payment_type === 'credit') return '📋'
    if (item.payment_type === 'cheque') return '📝'
    return '🧾'
}

export function describePaymentMethod(item) {
    if (item.payment_type === 'cash') return 'Paiement en liquide'
    if (item.payment_type === 'card') return 'Carte bancaire'
    if (item.payment_type === 'mobile') return 'Paiement mobile'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return 'Transfert bancaire rapide'
    if (item.payment_type === 'virement') return 'Transfert bancaire standard'
    if (item.payment_type === 'credit') return 'Paiement différé'
    if (item.payment_type === 'cheque') return 'Paiement par chèque'
    return 'Mode personnalisé'
}

export function paymentTimingLabel(method) {
    return method?.paymentTiming === 'deferred' ? 'Différé' : 'Immédiat'
}

export function mapPaymentModeItem(item, index) {
    return {
        id: createMethodId(item, index),
        sourceId: item.id,
        paymentType: item.payment_type || 'other',
        transferMode: item.transfer_mode || null,
        label: item.label,
        description: describePaymentMethod(item),
        icon: iconForPaymentMethod(item),
        isDefault: item.is_default === true,
        paymentTiming: item.payment_timing === 'deferred' ? 'deferred' : 'immediate',
        show_transaction_number: item.show_transaction_number === true,
        show_piece_number: item.show_piece_number === true,
        show_issue_date: item.show_issue_date === true,
        show_due_date: item.show_due_date === true,
        show_bank_name: item.show_bank_name === true,
        show_notes: item.show_notes !== false,
    }
}

export function matchesPaymentMethod(payment, method) {
    const paymentType = payment.payment_type || payment.type
    const normalizedType = paymentType === 'check' ? 'cheque' : paymentType

    if (method.paymentType === 'virement' || ['simple_transfer', 'instant_transfer'].includes(method.id)) {
        const methodTransferMode = method.transferMode || (method.id === 'instant_transfer' ? 'instant' : 'simple')
        const paymentTransferMode = normalizedType === 'instant_transfer'
            ? 'instant'
            : (normalizedType === 'simple_transfer' ? 'simple' : (payment.transfer_mode || 'simple'))

        return (normalizedType === 'virement' || normalizedType === 'simple_transfer' || normalizedType === 'instant_transfer')
            && methodTransferMode === paymentTransferMode
    }

    return method.paymentType === normalizedType
}

export function resolveApiPaymentMethod(method) {
    if (!method) return null

    if (['simple_transfer', 'instant_transfer'].includes(method.id)) {
        return method.id
    }

    return method.paymentType
}

export function resolveApiTransferMode(method) {
    if (!method) return null

    if (method.id === 'simple_transfer') return 'simple'
    if (method.id === 'instant_transfer') return 'instant'
    if (method.paymentType === 'virement') return method.transferMode || 'simple'

    return null
}

export function findMatchingPaymentMethod(payment, methods) {
    if (!payment || !methods?.length) return null

    return methods.find((method) => matchesPaymentMethod(payment, method)) || null
}

export function findPaymentMethodLabel(paymentType, transferMode, methods) {
    const payment = { payment_type: paymentType, transfer_mode: transferMode }
    const match = findMatchingPaymentMethod(payment, methods)
    return match?.label || null
}

export function getEmptyPaymentForm() {
    return {
        transaction_number: '',
        piece_number: '',
        issue_date: '',
        due_date: '',
        bank_name: '',
        notes: '',
    }
}

export function hasVisibleExtraFields(method) {
    if (!method) return false

    return [
        method.show_transaction_number,
        method.show_piece_number,
        method.show_issue_date,
        method.show_due_date,
        method.show_bank_name,
    ].some(Boolean)
}

export function getPaymentFieldLabels(method) {
    const paymentType = method?.paymentType

    return {
        transactionLabel: paymentType === 'virement' ? 'N° de transaction' : 'N° transaction',
        transactionPlaceholder: paymentType === 'virement' ? 'N° opération bancaire' : 'Ex: 12345678, ABC123XYZ',
        pieceLabel: 'N° pièce',
        piecePlaceholder: paymentType === 'credit' ? 'Référence dossier / effet / LCN' : 'CIN / justificatif',
        bankLabel: 'Banque',
        bankPlaceholder: 'Nom de la banque',
    }
}

export function validatePaymentMethodFields(method, form) {
    if (!method) return false

    const checks = [
        !method.show_transaction_number || !!String(form.transaction_number || '').trim(),
        !method.show_piece_number || !!String(form.piece_number || '').trim(),
        !method.show_issue_date || !!form.issue_date,
        !method.show_due_date || !!form.due_date,
        !method.show_bank_name || !!String(form.bank_name || '').trim(),
    ]

    return checks.every(Boolean)
}

export function encodePaymentModeLabel(label, paymentTiming, notes) {
    const cleanNotes = String(notes || '')
        .replace(/\[PAYMENT_TIMING:[^\]]+\]\s*/g, '')
        .replace(/\[PAYMENT_MODE_LABEL:[^\]]+\]\s*/g, '')
        .trim()
    const cleanLabel = String(label || '').trim()
    const timing = paymentTiming === 'deferred' ? 'deferred' : 'immediate'
    const timingMarker = `[PAYMENT_TIMING:${timing}]`

    if (!cleanLabel) {
        return cleanNotes ? `${timingMarker} ${cleanNotes}` : timingMarker
    }

    const marker = `[PAYMENT_MODE_LABEL:${cleanLabel}]`
    return cleanNotes ? `${marker} ${timingMarker} ${cleanNotes}` : `${marker} ${timingMarker}`
}

export function prefillPaymentFormFromPayment(payment, method) {
    const form = getEmptyPaymentForm()
    if (!payment || !method) return form

    if (method.show_transaction_number && payment.transaction_number) {
        form.transaction_number = payment.transaction_number
    }
    if (method.show_piece_number && payment.piece_number) {
        form.piece_number = payment.piece_number
    }
    if (method.show_issue_date && payment.issue_date) {
        form.issue_date = String(payment.issue_date).slice(0, 10)
    }
    if (method.show_due_date && payment.due_date) {
        form.due_date = String(payment.due_date).slice(0, 10)
    }
    if (method.show_bank_name && payment.bank_name) {
        form.bank_name = payment.bank_name
    }

    return form
}
