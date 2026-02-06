<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="$emit('close')"></div>

            <!-- Modal -->
            <div class="relative bg-white rounded-2xl shadow-xl w-80 z-10">
                <!-- Display -->
                <div class="p-4 bg-gray-50 rounded-t-2xl">
                    <div class="text-right">
                        <p class="text-sm text-gray-500 h-5">{{ expression || ' ' }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ display }}</p>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="p-4 grid grid-cols-4 gap-2">
                    <button @click="clear" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-red-100 text-red-600 hover:bg-red-200">C</button>
                    <button @click="backspace" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-gray-100 hover:bg-gray-200">⌫</button>
                    <button @click="addOperator('%')" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-gray-100 hover:bg-gray-200">%</button>
                    <button @click="addOperator('/')" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-primary-100 text-gray-900 hover:bg-primary-200">÷</button>

                    <button @click="addDigit('7')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">7</button>
                    <button @click="addDigit('8')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">8</button>
                    <button @click="addDigit('9')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">9</button>
                    <button @click="addOperator('*')" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-primary-100 text-gray-900 hover:bg-primary-200">×</button>

                    <button @click="addDigit('4')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">4</button>
                    <button @click="addDigit('5')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">5</button>
                    <button @click="addDigit('6')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">6</button>
                    <button @click="addOperator('-')" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-primary-100 text-gray-900 hover:bg-primary-200">−</button>

                    <button @click="addDigit('1')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">1</button>
                    <button @click="addDigit('2')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">2</button>
                    <button @click="addDigit('3')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">3</button>
                    <button @click="addOperator('+')" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-primary-100 text-gray-900 hover:bg-primary-200">+</button>

                    <button @click="addDigit('0')" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors col-span-2">0</button>
                    <button @click="addDecimal" class="py-4 text-xl font-semibold rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">.</button>
                    <button @click="calculate" class="py-4 text-xl font-semibold rounded-lg border border-gray-200 transition-colors bg-primary-500 text-gray-900 hover:bg-primary-600">=</button>
                </div>

                <!-- Use Result -->
                <div class="p-4 border-t">
                    <button 
                        @click="useResult"
                        class="w-full py-3 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600"
                    >
                        Utiliser ce montant
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const emit = defineEmits(['close', 'result'])

const currentValue = ref('0')
const previousValue = ref('')
const operator = ref('')
const waitingForOperand = ref(false)

const display = computed(() => currentValue.value)
const expression = computed(() => {
    if (previousValue.value && operator.value) {
        const opSymbol = { '+': '+', '-': '−', '*': '×', '/': '÷', '%': '%' }[operator.value]
        return `${previousValue.value} ${opSymbol}`
    }
    return ''
})

function addDigit(digit) {
    if (waitingForOperand.value) {
        currentValue.value = digit
        waitingForOperand.value = false
    } else {
        currentValue.value = currentValue.value === '0' ? digit : currentValue.value + digit
    }
}

function addDecimal() {
    if (waitingForOperand.value) {
        currentValue.value = '0.'
        waitingForOperand.value = false
    } else if (!currentValue.value.includes('.')) {
        currentValue.value += '.'
    }
}

function addOperator(op) {
    if (operator.value && !waitingForOperand.value) {
        calculate()
    }
    previousValue.value = currentValue.value
    operator.value = op
    waitingForOperand.value = true
}

function calculate() {
    if (!operator.value || waitingForOperand.value) return

    const prev = parseFloat(previousValue.value)
    const current = parseFloat(currentValue.value)
    let result = 0

    switch (operator.value) {
        case '+':
            result = prev + current
            break
        case '-':
            result = prev - current
            break
        case '*':
            result = prev * current
            break
        case '/':
            result = current !== 0 ? prev / current : 0
            break
        case '%':
            result = (prev * current) / 100
            break
    }

    currentValue.value = String(Math.round(result * 100) / 100)
    previousValue.value = ''
    operator.value = ''
    waitingForOperand.value = true
}

function clear() {
    currentValue.value = '0'
    previousValue.value = ''
    operator.value = ''
    waitingForOperand.value = false
}

function backspace() {
    if (currentValue.value.length > 1) {
        currentValue.value = currentValue.value.slice(0, -1)
    } else {
        currentValue.value = '0'
    }
}

function useResult() {
    emit('result', parseFloat(currentValue.value))
}
</script>

