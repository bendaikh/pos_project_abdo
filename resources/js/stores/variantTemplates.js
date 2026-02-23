import { defineStore } from 'pinia'
import { ref } from 'vue'

const STORAGE_KEY = 'pos_variant_templates'

export const useVariantTemplatesStore = defineStore('variantTemplates', () => {
    const templates = ref([])

    function loadTemplates() {
        const raw = localStorage.getItem(STORAGE_KEY)
        if (raw) {
            try {
                templates.value = JSON.parse(raw)
            } catch (error) {
                console.warn('Failed to parse variant templates from storage', error)
                templates.value = []
            }
        }
    }

    function persistTemplates() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(templates.value))
    }

    function addTemplateValue(name, value) {
        const normalizedName = name?.trim()
        const normalizedValue = value?.trim()
        if (!normalizedName || !normalizedValue) return null

        let template = templates.value.find(t => t.name.toLowerCase() === normalizedName.toLowerCase())
        if (!template) {
            template = {
                id: Date.now().toString(),
                name: normalizedName,
                values: []
            }
            templates.value.push(template)
        }

        const exists = template.values.some(v => v.toLowerCase() === normalizedValue.toLowerCase())
        if (!exists) {
            template.values.push(normalizedValue)
        }
        persistTemplates()
        return template
    }

    function removeTemplate(templateId) {
        templates.value = templates.value.filter(t => t.id !== templateId)
        persistTemplates()
    }

    function removeTemplateValue(templateId, value) {
        const template = templates.value.find(t => t.id === templateId)
        if (!template) return
        template.values = template.values.filter(v => v !== value)
        if (template.values.length === 0) {
            removeTemplate(templateId)
            return
        }
        persistTemplates()
    }

    function clearTemplates() {
        templates.value = []
        persistTemplates()
    }

    loadTemplates()

    return {
        templates,
        loadTemplates,
        addTemplateValue,
        removeTemplate,
        removeTemplateValue,
        clearTemplates,
    }
})
