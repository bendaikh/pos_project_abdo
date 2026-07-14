<template>
    <div class="gp-login">
        <div class="gp-login__bg" :style="{ backgroundImage: `url(${loginBg})` }" aria-hidden="true" />
        <div class="gp-login__veil" aria-hidden="true" />

        <div class="gp-login__inner">
            <!-- Left branding -->
            <aside class="gp-brand">
                <div class="gp-brand__logo">
                    <div class="gp-brand__mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none" class="gp-brand__svg">
                            <path d="M10 14h4l4.5 18h17l4-12H18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="22" cy="38" r="2.4" fill="currentColor"/>
                            <circle cx="33" cy="38" r="2.4" fill="currentColor"/>
                            <path d="M28 6c0 4 2.5 6 2.5 9.5 0 2-1.2 3.5-2.5 3.5s-2.5-1.5-2.5-3.5C25.5 12 28 10 28 6z" fill="currentColor"/>
                            <path d="M28 16c2.5-1 5-1 7 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="gp-brand__text">
                        <h1 class="gp-brand__title">
                            <span class="gp-brand__gr">GR</span><span class="gp-brand__een">een</span><span class="gp-brand__pos">POS</span>
                        </h1>
                        <p class="gp-brand__tagline">
                            <span class="gp-slogan-line">Solution qui</span>
                            <span class="gp-slogan-gere">GERE</span>
                        </p>
                        <div class="gp-slogan-underline" aria-hidden="true"></div>
                    </div>
                </div>

                <ul class="gp-features">
                    <li v-for="feature in features" :key="feature.title" class="gp-feature">
                        <div class="gp-feature__icon">
                            <component :is="feature.icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="gp-feature__title">{{ feature.title }}</p>
                            <p class="gp-feature__desc">{{ feature.desc }}</p>
                        </div>
                    </li>
                </ul>
            </aside>

            <!-- Login panel -->
            <section class="gp-panel-wrap">
                <div class="gp-panel">
                    <div class="gp-panel__lock">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>

                    <h2 class="gp-panel__hello">Bienvenue !</h2>
                    <p class="gp-panel__sub">
                        Connectez-vous à votre espace<br>
                        <span class="gp-text-green font-semibold">GReenPOS</span>
                    </p>

                    <form class="gp-form" @submit.prevent="handleLogin">
                        <div v-if="!isOnline" class="gp-alert gp-alert--warn">
                            Mode hors ligne — utilisez vos identifiants habituels
                        </div>
                        <div v-if="offlineLoginSuccess" class="gp-alert gp-alert--ok">
                            Connexion hors ligne réussie !
                        </div>
                        <div v-if="error" class="gp-alert gp-alert--err">{{ error }}</div>

                        <div class="gp-field">
                            <span class="gp-field__icon" aria-hidden="true">
                                <EnvelopeIcon class="w-5 h-5" />
                            </span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="gp-input"
                                placeholder="Adresse email"
                                autocomplete="username"
                            >
                        </div>

                        <div class="gp-field">
                            <span class="gp-field__icon" aria-hidden="true">
                                <LockClosedIcon class="w-5 h-5" />
                            </span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="gp-input gp-input--password"
                                placeholder="Mot de passe"
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                class="gp-field__eye"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                            >
                                <EyeSlashIcon v-if="showPassword" class="w-5 h-5" />
                                <EyeIcon v-else class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="gp-row">
                            <label class="gp-remember">
                                <input v-model="rememberMe" type="checkbox" class="gp-checkbox">
                                <span>Se souvenir de moi</span>
                            </label>
                            <button type="button" class="gp-forgot">Mot de passe oublié ?</button>
                        </div>

                        <button type="submit" class="gp-submit" :disabled="loading">
                            <svg v-if="loading" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ loading ? 'Connexion...' : 'Se connecter' }}
                        </button>
                    </form>

                    <div class="gp-divider">
                        <span>ou continuer avec</span>
                    </div>

                    <div class="gp-social">
                        <button type="button" class="gp-social__btn gp-social__btn--fb" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07c0 5.02 3.66 9.18 8.44 9.93v-7.02H7.9v-2.91h2.54V9.84c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.91h-2.34v7.02C18.34 21.25 22 17.09 22 12.07z"/>
                            </svg>
                        </button>
                        <button type="button" class="gp-social__btn gp-social__btn--ig" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm0 2a3 3 0 00-3 3v10a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H7zm5 3.5A4.5 4.5 0 1112 16.5 4.5 4.5 0 0112 7.5zm0 2a2.5 2.5 0 100 5 2.5 2.5 0 000-5zm5.25-.9a1.05 1.05 0 11-2.1 0 1.05 1.05 0 012.1 0z"/>
                            </svg>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="gp-quick"
                        @click="continueWithoutLogin"
                    >
                        Accès rapide au POS{{ isOnline ? '' : ' (Hors ligne)' }}
                    </button>
                </div>
            </section>
        </div>

        <!-- Footer trust bar -->
        <footer class="gp-footer">
            <div class="gp-footer__items">
                <div v-for="item in trustItems" :key="item.title" class="gp-footer__item">
                    <component :is="item.icon" class="w-5 h-5 gp-text-green" />
                    <div>
                        <p class="gp-footer__title">{{ item.title }}</p>
                        <p class="gp-footer__desc">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
            <div class="gp-footer__brand">
                <span class="gp-text-green font-bold">GReenPOS</span>
                <span class="text-white/70"> Solution qui GERE votre activité</span>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOfflineStore } from '../stores/offline'
import {
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    ChartBarIcon,
    CubeIcon,
    UsersIcon,
    ChartPieIcon,
    ShieldCheckIcon,
    BoltIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline'
import loginBg from '../../images/login-bg.png'

const router = useRouter()
const authStore = useAuthStore()
const offlineStore = useOfflineStore()

const form = reactive({
    email: 'superadmin@example.com',
    password: 'password',
})
const error = ref('')
const loading = ref(false)
const offlineLoginSuccess = ref(false)
const isOnline = ref(navigator.onLine)
const showPassword = ref(false)
const rememberMe = ref(true)

const features = [
    {
        title: 'Gestion des ventes',
        desc: 'Encaissement rapide, tickets, remises et suivi quotidien.',
        icon: ChartBarIcon,
    },
    {
        title: 'Gestion de stock',
        desc: 'Inventaire en temps réel, alertes et mouvements détaillés.',
        icon: CubeIcon,
    },
    {
        title: 'Gestion des clients',
        desc: 'Fiches clients, historique d’achats et fidélisation.',
        icon: UsersIcon,
    },
    {
        title: 'Rapports & Statistiques',
        desc: 'Tableaux de bord clairs pour piloter votre activité.',
        icon: ChartPieIcon,
    },
]

const trustItems = [
    {
        title: 'Sécurisé',
        desc: 'Vos données sont protégées',
        icon: ShieldCheckIcon,
    },
    {
        title: 'Rapide',
        desc: 'Une solution efficace et performante',
        icon: BoltIcon,
    },
    {
        title: 'Support',
        desc: 'Une équipe à votre écoute à tout moment',
        icon: PhoneIcon,
    },
]

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
            setTimeout(() => router.push('/dashboard'), 1000)
        } else if (result.needs_store_setup) {
            router.push('/store-setup')
        } else {
            router.push('/dashboard')
        }
    } else {
        error.value = result.message
    }
}

function continueWithoutLogin() {
    authStore.setOfflineGuestMode()
    router.push('/pos')
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
})

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus)
    window.removeEventListener('offline', updateOnlineStatus)
})
</script>

<style scoped>
.gp-login {
    --gp-green: #00d7d7;
    --gp-green-dark: #5ea336;
    --gp-panel: rgba(18, 18, 22, 0.82);
    --gp-field: rgba(10, 10, 14, 0.9);
    --gp-muted: #b8b8b8;
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    color: #fff;
    font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
}

.gp-login__bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    filter: brightness(1.15) contrast(1.05) saturate(1.08);
    transform: scale(1.02);
    z-index: 0;
}

.gp-login__veil {
    position: absolute;
    inset: 0;
    z-index: 1;
    background:
        linear-gradient(90deg, rgba(8, 10, 12, 0.45) 0%, rgba(8, 10, 12, 0.12) 40%, rgba(8, 10, 12, 0.28) 100%),
        linear-gradient(180deg, rgba(8, 10, 12, 0.08) 0%, rgba(8, 10, 12, 0.35) 100%);
}

.gp-login__inner {
    position: relative;
    z-index: 2;
    flex: 1;
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 2rem;
    align-items: center;
    padding: 2.5rem 3rem 7rem;
    max-width: 1280px;
    margin: 0 auto;
    width: 100%;
}

.gp-text-green { color: var(--gp-green); }

.gp-brand__logo {
    display: flex;
    align-items: center;
    gap: 1.15rem;
    margin-bottom: 2.75rem;
}

.gp-brand__mark {
    width: 4.75rem;
    height: 4.75rem;
    border-radius: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gp-green);
    background: rgba(0, 215, 215, 0.12);
    border: 1.5px solid rgba(0, 215, 215, 0.4);
    box-shadow:
        0 0 24px rgba(0, 215, 215, 0.35),
        inset 0 0 18px rgba(0, 215, 215, 0.08);
    animation: gp-mark-pulse 3.2s ease-in-out infinite;
    flex-shrink: 0;
}

.gp-brand__svg {
    width: 3rem;
    height: 3rem;
    filter: drop-shadow(0 0 8px rgba(0, 215, 215, 0.65));
}

.gp-brand__text {
    position: relative;
    min-width: 0;
}

.gp-brand__title {
    font-size: clamp(2.6rem, 5.5vw, 3.75rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 0.95;
    display: flex;
    align-items: baseline;
}

.gp-brand__gr {
    background: linear-gradient(135deg, #4dfff6 0%, #00d7d7 45%, #4f9a28 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 18px rgba(0, 215, 215, 0.7));
    animation: gp-green-shine 4s linear infinite;
}

.gp-brand__een {
    color: #ffffff;
    text-shadow: 0 0 18px rgba(255, 255, 255, 0.25);
}

.gp-brand__pos {
    color: #ffffff;
    text-shadow:
        0 0 12px rgba(0, 215, 215, 0.45),
        0 0 28px rgba(0, 215, 215, 0.25);
    animation: gp-pos-glow 2.8s ease-in-out infinite;
}

.gp-brand__tagline {
    margin-top: 0.55rem;
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.gp-slogan-line {
    font-size: clamp(1.05rem, 2.2vw, 1.35rem);
    font-weight: 500;
    color: rgba(255, 255, 255, 0.92);
    letter-spacing: 0.02em;
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
    animation: gp-slogan-fade 3.5s ease-in-out infinite;
}

.gp-slogan-gere {
    font-size: clamp(1.35rem, 2.8vw, 1.85rem);
    font-weight: 800;
    letter-spacing: 0.12em;
    background: linear-gradient(105deg, #e8ffc8 0%, #00d7d7 30%, #4dfff6 55%, #4f9a28 80%, #00d7d7 100%);
    background-size: 220% auto;
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    filter:
        drop-shadow(0 0 14px rgba(0, 215, 215, 0.85))
        drop-shadow(0 0 28px rgba(0, 215, 215, 0.4));
    animation: gp-gere-glow 2.6s ease-in-out infinite, gp-green-shine 3.5s linear infinite;
}

.gp-slogan-underline {
    margin-top: 0.55rem;
    width: 7.5rem;
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, transparent, #00d7d7, #4dfff6, #00d7d7, transparent);
    box-shadow: 0 0 14px rgba(0, 215, 215, 0.7);
    animation: gp-underline-pulse 2.4s ease-in-out infinite;
}

@keyframes gp-mark-pulse {
    0%, 100% {
        box-shadow: 0 0 18px rgba(0, 215, 215, 0.28), inset 0 0 14px rgba(0, 215, 215, 0.06);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 32px rgba(0, 215, 215, 0.55), inset 0 0 20px rgba(0, 215, 215, 0.12);
        transform: scale(1.03);
    }
}

@keyframes gp-green-shine {
    0% { background-position: 0% center; }
    100% { background-position: 200% center; }
}

@keyframes gp-pos-glow {
    0%, 100% {
        text-shadow: 0 0 10px rgba(0, 215, 215, 0.35), 0 0 22px rgba(0, 215, 215, 0.18);
    }
    50% {
        text-shadow: 0 0 18px rgba(0, 215, 215, 0.7), 0 0 36px rgba(0, 215, 215, 0.35);
    }
}

@keyframes gp-gere-glow {
    0%, 100% {
        filter: drop-shadow(0 0 12px rgba(0, 215, 215, 0.75)) drop-shadow(0 0 24px rgba(0, 215, 215, 0.35));
        transform: scale(1);
    }
    50% {
        filter: drop-shadow(0 0 22px rgba(0, 215, 215, 1)) drop-shadow(0 0 40px rgba(0, 215, 215, 0.55));
        transform: scale(1.04);
    }
}

@keyframes gp-slogan-fade {
    0%, 100% { opacity: 0.88; }
    50% { opacity: 1; }
}

@keyframes gp-underline-pulse {
    0%, 100% { width: 6.5rem; opacity: 0.7; }
    50% { width: 8.5rem; opacity: 1; }
}

.gp-features {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 1.35rem;
}

.gp-feature {
    display: flex;
    gap: 0.9rem;
    align-items: flex-start;
}

.gp-feature__icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gp-green);
    background: rgba(0, 215, 215, 0.12);
    border: 1px solid rgba(0, 215, 215, 0.25);
    flex-shrink: 0;
}

.gp-feature__title {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
}

.gp-feature__desc {
    margin-top: 0.15rem;
    font-size: 0.82rem;
    line-height: 1.4;
    color: var(--gp-muted);
    max-width: 22rem;
}

.gp-panel-wrap {
    display: flex;
    justify-content: flex-end;
}

.gp-panel {
    width: 100%;
    max-width: 380px;
    background: var(--gp-panel);
    border: 1px solid rgba(0, 215, 215, 0.35);
    border-radius: 1.25rem;
    padding: 2rem 1.75rem 1.5rem;
    backdrop-filter: blur(18px);
    text-align: center;
    box-shadow:
        0 0 0 1px rgba(0, 215, 215, 0.15),
        0 0 28px rgba(0, 215, 215, 0.35),
        0 0 56px rgba(0, 215, 215, 0.2),
        0 24px 60px rgba(0, 0, 0, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
    animation: gp-panel-glow 3s ease-in-out infinite;
}

@keyframes gp-panel-glow {
    0%, 100% {
        box-shadow:
            0 0 0 1px rgba(0, 215, 215, 0.15),
            0 0 24px rgba(0, 215, 215, 0.3),
            0 0 48px rgba(0, 215, 215, 0.15),
            0 24px 60px rgba(0, 0, 0, 0.45),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    50% {
        box-shadow:
            0 0 0 1px rgba(0, 215, 215, 0.28),
            0 0 36px rgba(0, 215, 215, 0.5),
            0 0 72px rgba(0, 215, 215, 0.28),
            0 24px 60px rgba(0, 0, 0, 0.45),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }
}

.gp-panel__lock {
    width: 3.25rem;
    height: 3.25rem;
    margin: 0 auto 1rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gp-green);
    box-shadow: 0 8px 24px rgba(0, 215, 215, 0.4);
}

.gp-panel__hello {
    font-size: 1.65rem;
    font-weight: 800;
    color: #fff;
}

.gp-panel__sub {
    margin-top: 0.35rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.88);
    line-height: 1.45;
}

.gp-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    text-align: left;
}

.gp-alert {
    border-radius: 0.65rem;
    padding: 0.7rem 0.85rem;
    font-size: 0.8rem;
}

.gp-alert--warn {
    background: rgba(0, 215, 215, 0.12);
    border: 1px solid rgba(0, 215, 215, 0.35);
    color: #c8ef9f;
}

.gp-alert--ok {
    background: rgba(0, 215, 215, 0.18);
    border: 1px solid rgba(0, 215, 215, 0.4);
    color: #d7f5b5;
}

.gp-alert--err {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(248, 113, 113, 0.35);
    color: #fecaca;
}

.gp-field {
    position: relative;
}

.gp-field__icon {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
}

.gp-field__eye {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    background: transparent;
    border: none;
    padding: 0.25rem;
    cursor: pointer;
}

.gp-field__eye:hover { color: #fff; }

.gp-input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.85rem 1rem 0.85rem 2.75rem;
    border-radius: 0.7rem;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: var(--gp-field);
    color: #fff;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.gp-input--password {
    padding-right: 2.75rem;
}

.gp-input::placeholder { color: #8b8f98; }

.gp-input:focus {
    border-color: var(--gp-green);
    box-shadow: 0 0 0 3px rgba(0, 215, 215, 0.18);
}

.gp-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 0.15rem;
}

.gp-remember {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.8rem;
    color: #fff;
    cursor: pointer;
}

.gp-checkbox {
    width: 1rem;
    height: 1rem;
    accent-color: var(--gp-green);
    cursor: pointer;
}

.gp-forgot {
    background: none;
    border: none;
    color: var(--gp-green);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
}

.gp-forgot:hover { text-decoration: underline; }

.gp-submit {
    margin-top: 0.35rem;
    width: 100%;
    border: none;
    border-radius: 0.7rem;
    padding: 0.9rem 1rem;
    background: var(--gp-green);
    color: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 22px rgba(0, 215, 215, 0.35);
    transition: background 0.2s, transform 0.2s;
}

.gp-submit:hover:not(:disabled) {
    background: var(--gp-green-dark);
    transform: translateY(-1px);
}

.gp-submit:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.gp-divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1.25rem 0 1rem;
    color: #8b8f98;
    font-size: 0.78rem;
}

.gp-divider::before,
.gp-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.12);
}

.gp-social {
    display: flex;
    justify-content: center;
    gap: 0.85rem;
}

.gp-social__btn {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 0.65rem;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #fff;
}

.gp-social__btn--fb { background: #1877f2; }
.gp-social__btn--ig {
    background: linear-gradient(135deg, #f58529, #dd2a7b, #8134af);
}

.gp-quick {
    margin-top: 1rem;
    width: 100%;
    background: transparent;
    border: 1px solid rgba(0, 215, 215, 0.35);
    color: var(--gp-green);
    border-radius: 0.7rem;
    padding: 0.7rem;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
}

.gp-quick:hover {
    background: rgba(0, 215, 215, 0.1);
}

.gp-footer {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 2.5rem;
    background: rgba(8, 10, 12, 0.88);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.gp-footer__items {
    display: flex;
    gap: 1.75rem;
    flex-wrap: wrap;
}

.gp-footer__item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.gp-footer__title {
    font-size: 0.85rem;
    font-weight: 700;
}

.gp-footer__desc {
    font-size: 0.72rem;
    color: var(--gp-muted);
}

.gp-footer__brand {
    font-size: 0.8rem;
    white-space: nowrap;
}

@media (max-width: 1023px) {
    .gp-login__inner {
        grid-template-columns: 1fr;
        padding: 1.5rem 1.25rem 8.5rem;
    }

    .gp-panel-wrap { justify-content: center; }

    .gp-brand { order: 1; }
    .gp-panel-wrap { order: 0; }

    .gp-features { display: none; }

    .gp-footer {
        flex-direction: column;
        align-items: flex-start;
        padding: 0.9rem 1.25rem 1.1rem;
    }

    .gp-footer__brand { white-space: normal; }
}
</style>
