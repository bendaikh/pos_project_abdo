<template>
    <div class="login-page relative min-h-screen overflow-hidden">
        <!-- Background image -->
        <div
            class="login-bg absolute inset-0"
            :style="{ backgroundImage: `url(${loginBg})` }"
            aria-hidden="true"
        />

        <!-- Ambient light layers -->
        <div class="login-overlay absolute inset-0" aria-hidden="true" />
        <div class="login-glow absolute inset-0 pointer-events-none" aria-hidden="true" />
        <div class="light-beam light-beam-1" aria-hidden="true" />
        <div class="light-beam light-beam-2" aria-hidden="true" />
        <div class="aurora absolute inset-0 pointer-events-none" aria-hidden="true" />

        <!-- Star field -->
        <div class="stars-layer absolute inset-0 pointer-events-none" aria-hidden="true">
            <span
                v-for="star in stars"
                :key="star.id"
                class="star"
                :class="star.type"
                :style="{
                    left: star.x + '%',
                    top: star.y + '%',
                    width: star.size + 'px',
                    height: star.size + 'px',
                    animationDelay: star.delay + 's',
                    animationDuration: star.duration + 's',
                    opacity: star.opacity
                }"
            />
        </div>

        <!-- Floating sparkles -->
        <div class="sparkles-layer absolute inset-0 pointer-events-none" aria-hidden="true">
            <span
                v-for="sparkle in sparkles"
                :key="'s-' + sparkle.id"
                class="sparkle"
                :style="{
                    left: sparkle.x + '%',
                    top: sparkle.y + '%',
                    animationDelay: sparkle.delay + 's',
                    animationDuration: sparkle.duration + 's'
                }"
            />
        </div>

        <div class="relative z-10 min-h-screen flex items-center justify-center lg:justify-end px-4 sm:px-8 lg:px-16 xl:px-24 py-12">
            <div class="w-full max-w-md flex flex-col items-center gap-6">
                <!-- Mobile branding -->
                <div class="brand-logo-wrap relative inline-block lg:hidden">
                    <h1 class="brand-logo select-none">
                        <span class="brand-green">Green</span><span class="brand-pos">POS</span>
                    </h1>
                </div>

                <!-- Slogan au-dessus du panneau -->
                <div class="slogan-banner w-full flex justify-center">
                    <div class="slogan-wrap relative">
                        <h2 class="slogan select-none text-center">
                            <span class="slogan-text">La Solution qui </span>
                            <span class="slogan-gere">GERE</span>
                        </h2>
                        <div class="slogan-shimmer" aria-hidden="true" />
                        <div class="slogan-underline" aria-hidden="true" />
                    </div>
                </div>

                <!-- Login card -->
                <div class="w-full login-card-wrap">
                    <div class="card-glow" aria-hidden="true" />
                    <div class="login-card rounded-2xl p-8 sm:p-10 relative">
                        <div class="card-shine" aria-hidden="true" />

                        <div class="text-center mb-8">
                            <div class="card-icon mx-auto mb-4">
                                <svg class="w-6 h-6 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-white tracking-tight">Connexion</h2>
                            <p class="mt-2 text-sm text-slate-400">Accédez à votre espace de gestion</p>
                        </div>

                        <form class="login-form space-y-6" @submit.prevent="handleLogin">
                        <div v-if="!isOnline" class="alert-box alert-warning">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-orange-300">Mode hors ligne</p>
                                    <p class="text-xs text-orange-200/70 mt-1">Utilisez vos identifiants habituels pour vous connecter</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="offlineLoginSuccess" class="alert-box alert-success">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-cyan-200">Connexion hors ligne réussie !</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="error" class="alert-box alert-error">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-300">{{ error }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="login-fields space-y-4">
                            <div class="input-group">
                                <label for="email" class="input-label">Email</label>
                                <div class="input-wrap">
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="login-input w-full"
                                        placeholder="votre@email.com"
                                    >
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="password" class="input-label">Mot de passe</label>
                                <div class="input-wrap">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        required
                                        class="login-input w-full"
                                        placeholder="••••••••"
                                    >
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading"
                            class="login-btn w-full flex justify-center items-center py-3.5 px-4 rounded-xl text-sm font-bold tracking-wide disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span class="btn-glow" aria-hidden="true" />
                            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 relative z-10" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="relative z-10">{{ loading ? 'Connexion...' : 'Se connecter' }}</span>
                        </button>
                    </form>

                    <div class="mt-6 text-center space-y-2">
                        <p class="text-xs text-slate-500">
                            Demo : superadmin@example.com / Admin@12345
                        </p>
                        <p v-if="!isOnline" class="text-xs text-orange-400/80 font-medium">
                            Mode hors ligne : connectez-vous une fois en ligne pour activer l'accès hors ligne
                        </p>
                    </div>

                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full divider-line"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-3 text-slate-500 divider-text">ou</span>
                            </div>
                        </div>

                        <button
                            @click="continueWithoutLogin"
                            type="button"
                            class="quick-btn mt-4 w-full py-3 px-4 border rounded-xl font-medium flex items-center justify-center space-x-2"
                            :class="isOnline ? 'quick-btn-online' : 'quick-btn-offline'"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>{{ isOnline ? 'Accès rapide au POS' : 'Accès rapide au POS (Hors ligne)' }}</span>
                        </button>
                        <p class="mt-2 text-xs text-center text-slate-500">
                            Continuer sans connexion pour traiter les ventes
                        </p>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOfflineStore } from '../stores/offline'
import loginBg from '../../images/login-bg.png'

const router = useRouter()
const authStore = useAuthStore()
const offlineStore = useOfflineStore()

const form = reactive({
    email: '',
    password: ''
})
const error = ref('')
const loading = ref(false)
const offlineLoginSuccess = ref(false)
const hasCachedData = ref(false)
const isOnline = ref(navigator.onLine)

function generateStars(count) {
    return Array.from({ length: count }, (_, i) => ({
        id: i,
        x: Math.random() * 100,
        y: Math.random() * 100,
        size: Math.random() * 2.5 + 1,
        delay: Math.random() * 5,
        duration: Math.random() * 3 + 2,
        opacity: Math.random() * 0.6 + 0.2,
        type: Math.random() > 0.7 ? 'star-bright' : Math.random() > 0.5 ? 'star-cross' : 'star-dot'
    }))
}

function generateSparkles(count) {
    return Array.from({ length: count }, (_, i) => ({
        id: i,
        x: Math.random() * 100,
        y: Math.random() * 100,
        delay: Math.random() * 8,
        duration: Math.random() * 6 + 4
    }))
}

const stars = generateStars(80)
const sparkles = generateSparkles(25)

function updateOnlineStatus() {
    isOnline.value = navigator.onLine
}

async function handleLogin() {
    error.value = ''
    offlineLoginSuccess.value = false
    loading.value = true

    const result = await authStore.login(form)

    loading.value = false

    if (result.success) {
        if (result.offline) {
            offlineLoginSuccess.value = true
            setTimeout(() => {
                router.push('/dashboard')
            }, 1000)
        } else {
            router.push('/dashboard')
        }
    } else {
        error.value = result.message
    }
}

async function continueWithoutLogin() {
    authStore.setOfflineGuestMode()
    router.push('/pos')
}

async function checkCachedData() {
    try {
        const articles = await offlineStore.getCachedArticles()
        hasCachedData.value = articles && articles.length > 0
    } catch {
        hasCachedData.value = false
    }
}

onMounted(async () => {
    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)
    isOnline.value = navigator.onLine

    try {
        await offlineStore.init()
    } catch (e) {
        console.error('Error initializing offline store:', e)
    }

    await checkCachedData()
})

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus)
    window.removeEventListener('offline', updateOnlineStatus)
})
</script>

<style scoped>
/* ── Background image ── */
.login-bg {
    background-size: cover;
    background-position: center left;
    background-repeat: no-repeat;
    animation: bg-drift 25s ease-in-out infinite alternate;
    z-index: 0;
}

.login-overlay {
    z-index: 1;
    background:
        linear-gradient(to left, rgba(3, 7, 18, 0.8) 0%, rgba(3, 7, 18, 0.5) 35%, rgba(3, 7, 18, 0.15) 60%, transparent 100%);
}

.login-glow {
    z-index: 1;
    background: radial-gradient(ellipse at 25% 45%, rgba(34, 211, 238, 0.12) 0%, transparent 55%);
    animation: glow-pulse 6s ease-in-out infinite;
}

/* ── Light beams ── */
.light-beam {
    position: absolute;
    pointer-events: none;
    filter: blur(60px);
    opacity: 0.35;
}

.light-beam-1 {
    top: -10%;
    left: 15%;
    width: 300px;
    height: 500px;
    background: linear-gradient(180deg, rgba(34, 211, 238, 0.4) 0%, transparent 100%);
    transform: rotate(-15deg);
    animation: beam-sway 8s ease-in-out infinite;
}

.light-beam-2 {
    bottom: -5%;
    right: 20%;
    width: 250px;
    height: 400px;
    background: linear-gradient(0deg, rgba(6, 182, 212, 0.3) 0%, transparent 100%);
    transform: rotate(10deg);
    animation: beam-sway 10s ease-in-out infinite reverse;
}

.aurora {
    background:
        radial-gradient(ellipse 80% 50% at 50% 0%, rgba(34, 211, 238, 0.08) 0%, transparent 70%),
        radial-gradient(ellipse 60% 40% at 80% 60%, rgba(6, 182, 212, 0.06) 0%, transparent 60%);
    animation: aurora-shift 12s ease-in-out infinite alternate;
}

/* ── Stars ── */
.stars-layer {
    z-index: 1;
}

.star {
    position: absolute;
    border-radius: 50%;
    background: #e0f7fa;
    box-shadow: 0 0 4px #22d3ee, 0 0 8px rgba(34, 211, 238, 0.5);
    animation: twinkle ease-in-out infinite;
}

.star-bright {
    background: #fff;
    box-shadow: 0 0 6px #fff, 0 0 12px #22d3ee, 0 0 20px rgba(34, 211, 238, 0.4);
}

.star-cross::before,
.star-cross::after {
    content: '';
    position: absolute;
    background: linear-gradient(90deg, transparent, #67e8f9, transparent);
    border-radius: 2px;
}

.star-cross::before {
    width: 200%;
    height: 1px;
    top: 50%;
    left: -50%;
    transform: translateY(-50%);
}

.star-cross::after {
    width: 1px;
    height: 200%;
    left: 50%;
    top: -50%;
    transform: translateX(-50%);
    background: linear-gradient(180deg, transparent, #67e8f9, transparent);
}

/* ── Sparkles ── */
.sparkle {
    position: absolute;
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: #22d3ee;
    box-shadow: 0 0 6px #22d3ee, 0 0 12px rgba(34, 211, 238, 0.6);
    animation: float-sparkle ease-in-out infinite;
}

/* ── Branding ── */
.slogan-banner {
    width: 100%;
    text-align: center;
    animation: fade-up 1s ease-out 0.2s both;
}

.slogan-wrap {
    display: inline-block;
}

.slogan {
    font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(1.35rem, 3.5vw, 2rem);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -0.02em;
    position: relative;
}

.slogan-text {
    color: #e2e8f0;
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
}

.slogan-gere {
    display: inline-block;
    background: linear-gradient(135deg, #ffffff 0%, #67e8f9 25%, #22d3ee 50%, #06b6d4 75%, #0891b2 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 800;
    letter-spacing: 0.08em;
    filter: drop-shadow(0 0 20px rgba(34, 211, 238, 0.8)) drop-shadow(0 0 40px rgba(6, 182, 212, 0.4));
    animation: gere-glow 3s ease-in-out infinite, gere-shine 4s linear infinite;
}

.slogan-shimmer {
    position: absolute;
    inset: -8px -16px;
    background: linear-gradient(105deg, transparent 35%, rgba(34, 211, 238, 0.12) 50%, transparent 65%);
    animation: shimmer 5s ease-in-out infinite;
    pointer-events: none;
}

.slogan-underline {
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #22d3ee, #06b6d4, transparent);
    border-radius: 2px;
    box-shadow: 0 0 12px rgba(34, 211, 238, 0.6);
    animation: underline-pulse 2.5s ease-in-out infinite;
}

@keyframes gere-glow {
    0%, 100% {
        filter: drop-shadow(0 0 20px rgba(34, 211, 238, 0.8)) drop-shadow(0 0 40px rgba(6, 182, 212, 0.4));
        transform: scale(1);
    }
    50% {
        filter: drop-shadow(0 0 30px rgba(34, 211, 238, 1)) drop-shadow(0 0 60px rgba(6, 182, 212, 0.6));
        transform: scale(1.02);
    }
}

@keyframes gere-shine {
    0% { background-position: 200% center; }
    100% { background-position: -200% center; }
}

@keyframes underline-pulse {
    0%, 100% { opacity: 0.6; transform: translateX(-50%) scaleX(0.9); }
    50% { opacity: 1; transform: translateX(-50%) scaleX(1); }
}

.brand-logo-wrap {
    animation: fade-up 1s ease-out 0.15s both;
}

.brand-logo {
    font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(3rem, 8vw, 5rem);
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.03em;
    position: relative;
}

.brand-green {
    background: linear-gradient(180deg, #ffffff 0%, #e2e8f0 50%, #94a3b8 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 2px 16px rgba(255, 255, 255, 0.2));
}

.brand-pos {
    background: linear-gradient(135deg, #a5f3fc 0%, #22d3ee 30%, #06b6d4 60%, #0891b2 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 24px rgba(34, 211, 238, 0.7)) drop-shadow(0 0 48px rgba(6, 182, 212, 0.35));
    animation: text-glow 3s ease-in-out infinite;
}

.logo-shimmer {
    position: absolute;
    inset: -10px -20px;
    background: linear-gradient(105deg, transparent 40%, rgba(34, 211, 238, 0.08) 50%, transparent 60%);
    animation: shimmer 4s ease-in-out infinite;
    pointer-events: none;
}

.feature-pill {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    color: #94a3b8;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(34, 211, 238, 0.15);
    border-radius: 9999px;
    backdrop-filter: blur(8px);
    transition: border-color 0.3s, box-shadow 0.3s;
}

.feature-pill:hover {
    border-color: rgba(34, 211, 238, 0.35);
    box-shadow: 0 0 16px rgba(34, 211, 238, 0.1);
}

.feature-dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #22d3ee;
    box-shadow: 0 0 6px rgba(34, 211, 238, 0.8);
}

/* ── Login card ── */
.login-card-wrap {
    position: relative;
    animation: fade-up 1s ease-out 0.4s both;
}

.card-glow {
    position: absolute;
    inset: -2px;
    border-radius: 1.25rem;
    background: linear-gradient(135deg, rgba(34, 211, 238, 0.4), rgba(6, 182, 212, 0.1), rgba(34, 211, 238, 0.3));
    filter: blur(1px);
    animation: border-glow 4s ease-in-out infinite;
    z-index: 0;
}

.login-card {
    position: relative;
    z-index: 1;
    background: rgba(10, 15, 30, 0.65);
    border: 1px solid rgba(34, 211, 238, 0.15);
    backdrop-filter: blur(24px);
    box-shadow:
        0 25px 60px -12px rgba(0, 0, 0, 0.6),
        0 0 40px rgba(6, 182, 212, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
    overflow: hidden;
}

.card-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 60%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.03), transparent);
    animation: card-shine 6s ease-in-out infinite;
    pointer-events: none;
}

.card-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: rgba(34, 211, 238, 0.1);
    border: 1px solid rgba(34, 211, 238, 0.25);
    box-shadow: 0 0 20px rgba(34, 211, 238, 0.15);
}

/* ── Alerts ── */
.alert-box {
    border-radius: 0.75rem;
    padding: 1rem;
    backdrop-filter: blur(8px);
}

.alert-warning {
    background: rgba(249, 115, 22, 0.1);
    border: 1px solid rgba(251, 146, 60, 0.3);
}

.alert-success {
    background: rgba(6, 182, 212, 0.1);
    border: 1px solid rgba(34, 211, 238, 0.3);
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(248, 113, 113, 0.3);
}

/* ── Inputs ── */
.login-form {
    width: 100%;
    text-align: left;
}

.login-fields {
    width: 100%;
}

.input-group {
    width: 100%;
}

.input-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #cbd5e1;
    margin-bottom: 0.375rem;
}

.input-wrap {
    position: relative;
    width: 100%;
}

.input-wrap::after {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: 0.8rem;
    background: linear-gradient(135deg, rgba(34, 211, 238, 0.3), transparent, rgba(34, 211, 238, 0.15));
    opacity: 0;
    transition: opacity 0.3s;
    z-index: 0;
    pointer-events: none;
}

.input-wrap:focus-within::after {
    opacity: 1;
}

.login-input {
    position: relative;
    z-index: 1;
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 0.7rem 1rem;
    background: rgba(8, 12, 24, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.75rem;
    color: #f1f5f9;
    font-size: 0.875rem;
    transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
}

.login-input::placeholder {
    color: #475569;
}

.login-input:focus {
    outline: none;
    border-color: rgba(34, 211, 238, 0.45);
    background: rgba(8, 12, 24, 0.85);
    box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12), 0 0 20px rgba(34, 211, 238, 0.08);
}

/* ── Buttons ── */
.login-btn {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #22d3ee 0%, #06b6d4 50%, #0891b2 100%);
    color: #0f172a;
    border: none;
    box-shadow:
        0 4px 24px rgba(6, 182, 212, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.25);
    transition: transform 0.25s, box-shadow 0.25s;
}

.btn-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
    transform: translateX(-100%);
    animation: btn-shine 3s ease-in-out infinite;
}

.login-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow:
        0 8px 32px rgba(6, 182, 212, 0.55),
        0 0 40px rgba(34, 211, 238, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.login-btn:active:not(:disabled) {
    transform: translateY(0);
}

.quick-btn {
    transition: all 0.3s;
    backdrop-filter: blur(8px);
}

.quick-btn-online {
    border-color: rgba(34, 211, 238, 0.3);
    color: #67e8f9;
    background: rgba(6, 182, 212, 0.05);
}

.quick-btn-online:hover {
    background: rgba(6, 182, 212, 0.12);
    border-color: rgba(34, 211, 238, 0.5);
    box-shadow: 0 0 24px rgba(34, 211, 238, 0.12);
}

.quick-btn-offline {
    border-color: rgba(251, 146, 60, 0.3);
    color: #fdba74;
    background: rgba(249, 115, 22, 0.05);
}

.quick-btn-offline:hover {
    background: rgba(249, 115, 22, 0.12);
    border-color: rgba(251, 146, 60, 0.5);
}

.divider-line {
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    background: linear-gradient(90deg, transparent, rgba(34, 211, 238, 0.2), transparent);
    height: 1px;
    border: none;
}

.divider-text {
    background: rgba(10, 15, 30, 0.8);
}

/* ── Animations ── */
@keyframes twinkle {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.3); }
}

@keyframes float-sparkle {
    0%, 100% { opacity: 0; transform: translateY(0) scale(0.5); }
    25% { opacity: 1; transform: translateY(-20px) scale(1); }
    75% { opacity: 0.6; transform: translateY(-40px) scale(0.8); }
}

@keyframes pulse-glow {
    0%, 100% { opacity: 1; box-shadow: 0 0 8px #22d3ee, 0 0 16px rgba(34, 211, 238, 0.5); }
    50% { opacity: 0.6; box-shadow: 0 0 4px #22d3ee; }
}

@keyframes text-glow {
    0%, 100% { filter: drop-shadow(0 0 24px rgba(34, 211, 238, 0.7)) drop-shadow(0 0 48px rgba(6, 182, 212, 0.35)); }
    50% { filter: drop-shadow(0 0 32px rgba(34, 211, 238, 0.9)) drop-shadow(0 0 60px rgba(6, 182, 212, 0.5)); }
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(200%); }
}

@keyframes glow-pulse {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 1; }
}

@keyframes beam-sway {
    0%, 100% { transform: rotate(-15deg) translateX(0); opacity: 0.3; }
    50% { transform: rotate(-12deg) translateX(20px); opacity: 0.45; }
}

@keyframes aurora-shift {
    0% { opacity: 0.6; transform: translateX(0); }
    100% { opacity: 1; transform: translateX(30px); }
}

@keyframes bg-drift {
    0% { transform: scale(1.03); }
    100% { transform: scale(1.06); }
}

@keyframes border-glow {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

@keyframes card-shine {
    0% { left: -100%; }
    40%, 100% { left: 150%; }
}

@keyframes btn-shine {
    0% { transform: translateX(-100%); }
    50%, 100% { transform: translateX(200%); }
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
