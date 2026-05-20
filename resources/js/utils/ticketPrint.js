function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
}

function formatDateTime(value) {
    if (!value) return '-'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return String(value)
    return date.toLocaleString('fr-FR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

export function openPrintWindowSafely() {
    const printTargetWindow = window.open('', '_blank', 'width=420,height=760')
    if (!printTargetWindow) {
        return null
    }

    printTargetWindow.document.write(`
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Préparation impression</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #334155;
                }
            </style>
        </head>
        <body>Préparation du ticket...</body>
        </html>
    `)
    printTargetWindow.document.close()
    return printTargetWindow
}

export function buildSaleFromCart(cartStore, extras = {}) {
    const items = (cartStore.items || []).map((item) => ({
        article_name: item.article_name || item.article?.name || 'Article',
        quantity: item.quantity,
        total: Number(item.total) || 0,
        comment: item.comment || '',
        selected_options: item.selected_options || [],
    }))

    return {
        ticket_name: extras.ticket_name || 'Ticket',
        ticket_group: extras.ticket_group || null,
        order_number: extras.order_number || null,
        reference: extras.reference || null,
        customer: {
            name: cartStore.customerName || 'Client anonyme',
            phone: extras.customer_phone || '',
        },
        service_mode: cartStore.deliveryMode,
        delivery_mode: cartStore.deliveryMode,
        items,
        subtotal: cartStore.subtotal,
        tax_amount: cartStore.taxAmount,
        total: cartStore.total,
        notes: cartStore.notes || '',
        paid_confirmed_amount: 0,
        remaining_amount: cartStore.total,
    }
}

export function printTicketDocument(sale, {
    mode = 'addition',
    settingsStore,
    customListsStore,
    printTargetWindow = null,
} = {}) {
    const activePrintWindow = printTargetWindow || openPrintWindowSafely()
    if (!activePrintWindow) {
        alert('La fenêtre d’impression a été bloquée par le navigateur.')
        return false
    }

    const formatCurrency = (amount) => settingsStore.formatCurrency(amount)
    const formatDeliveryMode = (value) => customListsStore.getServiceModeLabel(value)
    const title = sale.ticket_name || sale.order_number || sale.reference || 'Ticket'
    const groupedLabel = sale.ticket_group ? `${escapeHtml(sale.ticket_group)} · ` : ''
    const customerName = sale.customer?.name || 'Client anonyme'
    const isKitchen = mode === 'kitchen'

    const itemsHtml = (sale.items || []).map((item) => {
        const options = Array.isArray(item.selected_options) && item.selected_options.length
            ? `<div style="font-size:11px;color:#64748b;margin-top:2px;">${escapeHtml(
                item.selected_options.map((opt) => opt?.label || opt?.name || '').filter(Boolean).join(', ')
            )}</div>`
            : ''
        const comment = item.comment
            ? `<div style="font-size:11px;color:#b45309;margin-top:2px;">${escapeHtml(item.comment)}</div>`
            : ''

        if (isKitchen) {
            return `
                <tr>
                    <td>
                        <strong>${escapeHtml(item.article_name || '')}</strong>
                        ${options}
                        ${comment}
                    </td>
                    <td style="text-align:center;font-size:16px;font-weight:bold;">${escapeHtml(String(item.quantity || 0))}</td>
                </tr>
            `
        }

        return `
            <tr>
                <td>${escapeHtml(item.article_name || '')}${options}${comment}</td>
                <td style="text-align:center;">${escapeHtml(String(item.quantity || 0))}</td>
                <td style="text-align:right;">${escapeHtml(formatCurrency(item.total || 0))}</td>
            </tr>
        `
    }).join('')

    const noteLine = sale.notes
        ? `<div class="note-box"><strong>Note:</strong><br>${escapeHtml(sale.notes).replace(/\n/g, '<br>')}</div>`
        : ''

    const kitchenMeta = isKitchen
        ? `<p><strong>Service:</strong> ${escapeHtml(formatDeliveryMode(sale.service_mode || sale.delivery_mode))}</p>`
        : `
            <p><strong>Reference:</strong> ${escapeHtml(sale.order_number || sale.reference || '-')}</p>
            <p><strong>Client:</strong> ${escapeHtml(customerName)}</p>
            <p><strong>Mode:</strong> ${escapeHtml(formatDeliveryMode(sale.service_mode || sale.delivery_mode))}</p>
        `

    const tableHead = isKitchen
        ? `
            <thead>
                <tr>
                    <th>Article</th>
                    <th style="text-align:center;">Qte</th>
                </tr>
            </thead>
        `
        : `
            <thead>
                <tr>
                    <th>Article</th>
                    <th style="text-align:center;">Qte</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
        `

    const totalsBlock = isKitchen
        ? ''
        : `
            <div class="section totals">
                <p><span>Sous-total</span><span>${escapeHtml(formatCurrency(sale.subtotal || 0))}</span></p>
                <p><span>TVA</span><span>${escapeHtml(formatCurrency(sale.tax_amount || 0))}</span></p>
                <p class="total"><span>Total</span><span>${escapeHtml(formatCurrency(sale.total || 0))}</span></p>
            </div>
        `

    const footerLabel = isKitchen
        ? 'Bon de commande cuisine'
        : 'Addition en attente de paiement'

    activePrintWindow.document.write(`
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>${escapeHtml(isKitchen ? 'Cuisine' : title)}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 18px; color: #0f172a; }
                .header, .footer { text-align: center; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p, .meta p, .footer p { margin: 4px 0; font-size: 12px; }
                .pill {
                    display: inline-block;
                    margin-top: 8px;
                    padding: 6px 10px;
                    border-radius: 999px;
                    background: ${isKitchen ? '#ffedd5' : '#e2e8f0'};
                    color: ${isKitchen ? '#9a3412' : '#0f172a'};
                    font-size: 11px;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 0.12em;
                }
                .section { margin-top: 14px; border-top: 1px dashed #94a3b8; padding-top: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
                th, td { padding: 6px 0; border-bottom: 1px dashed #cbd5e1; }
                th { text-align: left; font-size: 11px; text-transform: uppercase; color: #475569; }
                .totals p { display: flex; justify-content: space-between; margin: 6px 0; font-size: 12px; }
                .total { font-size: 16px; font-weight: bold; }
                .note-box { margin-top: 12px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px; font-size: 12px; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>${escapeHtml(settingsStore.storeName || 'POS')}</h1>
                <p>${escapeHtml(settingsStore.settings?.receipt?.receipt_header || 'Ticket')}</p>
                <span class="pill">${groupedLabel}${escapeHtml(isKitchen ? 'Cuisine' : title)}</span>
            </div>
            <div class="section meta">${kitchenMeta}</div>
            <div class="section">
                <table>${tableHead}<tbody>${itemsHtml}</tbody></table>
            </div>
            ${totalsBlock}
            ${noteLine}
            <div class="section footer">
                <p>${escapeHtml(settingsStore.settings?.receipt?.receipt_footer || 'Merci')}</p>
                <p>${escapeHtml(formatDateTime(new Date().toISOString()))}</p>
                <p>${escapeHtml(footerLabel)}</p>
            </div>
        </body>
        </html>
    `)
    activePrintWindow.document.close()
    activePrintWindow.focus()
    activePrintWindow.onload = () => {
        activePrintWindow.print()
        setTimeout(() => activePrintWindow.close(), 300)
    }

    return true
}
