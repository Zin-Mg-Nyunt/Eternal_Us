<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { store as loginStore } from '@/routes/login';
import { store as registerStore } from '@/routes/register';

const emit = defineEmits(['select']);

const actions = [
    { id: 'journey', label: 'Love Journey' },
    { id: 'gallery', label: 'Gallery' },
    { id: 'cover', label: 'Change Cover Image' },
    { id: 'manage', label: 'Manage Photos' },
];
const guestActions = [
    { id: 'login', label: 'Log in' },
    { id: 'register', label: 'Register' },
];

const rootRef = ref(null);
const menuOpen = ref(false);
const isMobile = ref(false);
const authModalOpen = ref(false);
const authMode = ref('login');

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});
const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
const activeAuthForm = computed(() =>
    authMode.value === 'login' ? loginForm : registerForm,
);
const authProcessing = computed(() => activeAuthForm.value.processing);
const authErrors = computed(() => activeAuthForm.value.errors);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const visibleActions = computed(() => (user.value ? actions : guestActions));

const updateViewport = () => {
    isMobile.value = window.innerWidth < 768;
};

const actionOffset = (index) => {
    const radius = isMobile.value ? 92 : 110;
    const guestAngles = isMobile.value ? [-156, -112] : [-160, -118];
    const authAngles = isMobile.value
        ? [-166, -136, -106, -78]
        : [-170, -143, -116, -89];
    const angles = user.value ? authAngles : guestAngles;
    const angle = ((angles[index] ?? -120) * Math.PI) / 180;
    return {
        x: Math.cos(angle) * radius,
        y: Math.sin(angle) * radius,
    };
};

const actionStyle = (index) => {
    const { x, y } = actionOffset(index);
    return {
        '--fab-x': `${x}px`,
        '--fab-y': `${y}px`,
        '--fab-delay': `${index * 55}ms`,
    };
};

const closeMenu = () => {
    if (!menuOpen.value) return;
    menuOpen.value = false;
};

const toggleMenu = () => {
    menuOpen.value = !menuOpen.value;
};

const onActionClick = (id) => {
    if (id === 'login' || id === 'register') {
        authMode.value = id;
        loginForm.clearErrors();
        registerForm.clearErrors();
        authModalOpen.value = true;
        closeMenu();
        return;
    }
    emit('select', id);
    closeMenu();
};

const closeAuthModal = () => {
    authModalOpen.value = false;
    loginForm.clearErrors();
    registerForm.clearErrors();
};

const switchAuthMode = (mode) => {
    authMode.value = mode;
    loginForm.clearErrors();
    registerForm.clearErrors();
};

const submitLogin = () => {
    loginForm.post(loginStore.url(), {
        preserveScroll: true,
        onSuccess: () => {
            closeAuthModal();
            loginForm.reset('password');
        },
    });
};

const submitRegister = () => {
    registerForm.post(registerStore.url(), {
        preserveScroll: true,
        onSuccess: () => {
            closeAuthModal();
            registerForm.reset('password', 'password_confirmation');
        },
    });
};

const onClickOutside = (event) => {
    if (!menuOpen.value) return;
    if (rootRef.value?.contains(event.target)) return;
    closeMenu();
};

const onKeydown = (event) => {
    if (event.key !== 'Escape') return;
    if (authModalOpen.value) {
        closeAuthModal();
        return;
    }
    closeMenu();
};

onMounted(() => {
    updateViewport();
    document.addEventListener('mousedown', onClickOutside);
    window.addEventListener('keydown', onKeydown);
    window.addEventListener('resize', updateViewport);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside);
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('resize', updateViewport);
});
</script>

<template v-if="!user || user.role === 'admin'">
    <div ref="rootRef" class="fixed right-6 bottom-6 z-50">
        <div
            v-for="(action, index) in visibleActions"
            :key="action.id"
            class="action-item absolute right-0 bottom-0"
            :class="{ 'is-open': menuOpen }"
            :style="actionStyle(index)"
        >
            <button
                type="button"
                class="peer/icon grid h-10 w-10 place-items-center rounded-full border border-rose-200 bg-rose-100 text-[#b76e79] shadow-[0_8px_16px_rgba(244,114,182,0.22)] transition hover:scale-105"
                @click="onActionClick(action.id)"
            >
                <svg
                    v-if="action.id === 'journey'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M12 3v18M3 12h18" />
                </svg>
                <svg
                    v-else-if="action.id === 'gallery'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <circle cx="9" cy="10" r="1.5" />
                    <path d="M21 16l-5-5-6 6" />
                </svg>
                <svg
                    v-else-if="action.id === 'cover'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M3 5h18v6H3zM3 13h8v6H3zM13 13h8v6h-8z" />
                </svg>
                <svg
                    v-else-if="action.id === 'login'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                    <path d="M10 17l5-5-5-5" />
                    <path d="M15 12H3" />
                </svg>
                <svg
                    v-else-if="action.id === 'register'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="9" cy="7" r="4" />
                    <path d="M17 11v6M14 14h6" />
                    <path d="M3 21a6 6 0 0 1 12 0" />
                </svg>
                <svg
                    v-else
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        d="M4 20h4l10.5-10.5a1.4 1.4 0 000-2L16.5 5.5a1.4 1.4 0 00-2 0L4 16v4zM13.5 7.5l3 3"
                    />
                </svg>
            </button>
            <span
                class="pointer-events-none absolute top-1/2 right-full mr-2 -translate-x-1 -translate-y-1/2 scale-95 rounded-full border border-rose-200 bg-white/95 px-2.5 py-0.5 text-[11px] font-medium whitespace-nowrap text-rose-700 opacity-0 shadow-[0_6px_14px_rgba(244,114,182,0.2)] backdrop-blur-sm transition-all duration-200 peer-hover/icon:translate-x-0 peer-hover/icon:scale-100 peer-hover/icon:opacity-100 peer-focus-visible/icon:translate-x-0 peer-focus-visible/icon:scale-100 peer-focus-visible/icon:opacity-100"
            >
                {{ action.label }}
            </span>
        </div>

        <button
            type="button"
            class="fab-main group relative grid h-12 w-12 cursor-pointer place-items-center rounded-full border border-rose-200 bg-rose-100/85 shadow-[0_0_0_6px_rgba(244,114,182,0.24),0_14px_24px_rgba(244,114,182,0.34)] backdrop-blur-sm transition hover:scale-110 hover:rotate-6 md:h-15 md:w-15"
            :class="{ 'is-open': menuOpen }"
            aria-label="Open love quick actions"
            @click="toggleMenu"
        >
            <img
                src="/image/pink-rose.png"
                alt="Pink rose action button"
                class="h-8 w-8 object-contain md:h-12 md:w-12"
            />
        </button>

        <Teleport to="body">
            <Transition name="auth-modal-overlay">
                <div
                    v-if="authModalOpen"
                    class="fixed inset-0 z-60 grid place-items-center bg-rose-100/30 px-4 backdrop-blur-sm"
                    @click.self="closeAuthModal"
                >
                    <Transition name="auth-modal-card" appear>
                        <div
                            class="w-full max-w-sm rounded-3xl border border-rose-200/90 bg-linear-to-br from-white via-rose-50 to-pink-100 p-5 shadow-[0_24px_70px_rgba(190,24,93,0.25)]"
                        >
                            <div class="mb-4 text-center">
                                <p
                                    class="text-xs font-semibold tracking-[0.2em] text-rose-500"
                                >
                                    SWEET ACCESS
                                </p>
                                <h3
                                    class="mt-1 text-xl font-semibold text-rose-700"
                                >
                                    {{
                                        authMode === 'login'
                                            ? 'Welcome back'
                                            : 'Join our love space'
                                    }}
                                </h3>
                            </div>

                            <div
                                class="mb-4 grid grid-cols-2 rounded-full border border-rose-200 bg-white/70 p-1 text-xs font-semibold"
                            >
                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 transition"
                                    :class="
                                        authMode === 'login'
                                            ? 'bg-rose-500 text-white shadow'
                                            : 'text-rose-600 hover:bg-rose-100'
                                    "
                                    @click="switchAuthMode('login')"
                                >
                                    Log in
                                </button>
                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 transition"
                                    :class="
                                        authMode === 'register'
                                            ? 'bg-rose-500 text-white shadow'
                                            : 'text-rose-600 hover:bg-rose-100'
                                    "
                                    @click="switchAuthMode('register')"
                                >
                                    Register
                                </button>
                            </div>

                            <Transition name="auth-form-fade" mode="out-in">
                                <form
                                    v-if="authMode === 'login'"
                                    key="login"
                                    class="space-y-3"
                                    @submit.prevent="submitLogin"
                                >
                                    <div>
                                        <input
                                            v-model="loginForm.email"
                                            type="email"
                                            required
                                            autocomplete="email"
                                            placeholder="Email address"
                                            class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 ring-rose-300 transition outline-none focus:ring-2"
                                        />
                                        <p
                                            v-if="authErrors.email"
                                            class="mt-1 text-xs text-rose-600"
                                        >
                                            {{ authErrors.email }}
                                        </p>
                                    </div>
                                    <div>
                                        <input
                                            v-model="loginForm.password"
                                            type="password"
                                            required
                                            autocomplete="current-password"
                                            placeholder="Password"
                                            class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 ring-rose-300 transition outline-none focus:ring-2"
                                        />
                                        <p
                                            v-if="authErrors.password"
                                            class="mt-1 text-xs text-rose-600"
                                        >
                                            {{ authErrors.password }}
                                        </p>
                                    </div>
                                    <label
                                        class="flex items-center gap-2 text-xs text-rose-700"
                                    >
                                        <input
                                            v-model="loginForm.remember"
                                            type="checkbox"
                                            class="h-3.5 w-3.5 rounded border-rose-300 text-rose-500 focus:ring-rose-300"
                                        />
                                        Remember me
                                    </label>
                                    <button
                                        type="submit"
                                        :disabled="authProcessing"
                                        class="w-full rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-600 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {{
                                            authProcessing
                                                ? 'Signing in...'
                                                : 'Log in'
                                        }}
                                    </button>
                                </form>

                                <form
                                    v-else
                                    key="register"
                                    class="space-y-3"
                                    @submit.prevent="submitRegister"
                                >
                                    <div>
                                        <input
                                            v-model="registerForm.name"
                                            type="text"
                                            required
                                            autocomplete="name"
                                            placeholder="Full name"
                                            class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 ring-rose-300 transition outline-none focus:ring-2"
                                        />
                                        <p
                                            v-if="authErrors.name"
                                            class="mt-1 text-xs text-rose-600"
                                        >
                                            {{ authErrors.name }}
                                        </p>
                                    </div>
                                    <div>
                                        <input
                                            v-model="registerForm.email"
                                            type="email"
                                            required
                                            autocomplete="email"
                                            placeholder="Email address"
                                            class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 ring-rose-300 transition outline-none focus:ring-2"
                                        />
                                        <p
                                            v-if="authErrors.email"
                                            class="mt-1 text-xs text-rose-600"
                                        >
                                            {{ authErrors.email }}
                                        </p>
                                    </div>
                                    <div>
                                        <input
                                            v-model="registerForm.password"
                                            type="password"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Password"
                                            class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 ring-rose-300 transition outline-none focus:ring-2"
                                        />
                                        <p
                                            v-if="authErrors.password"
                                            class="mt-1 text-xs text-rose-600"
                                        >
                                            {{ authErrors.password }}
                                        </p>
                                    </div>
                                    <div>
                                        <input
                                            v-model="
                                                registerForm.password_confirmation
                                            "
                                            type="password"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Confirm password"
                                            class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 ring-rose-300 transition outline-none focus:ring-2"
                                        />
                                        <p
                                            v-if="
                                                authErrors.password_confirmation
                                            "
                                            class="mt-1 text-xs text-rose-600"
                                        >
                                            {{
                                                authErrors.password_confirmation
                                            }}
                                        </p>
                                    </div>
                                    <button
                                        type="submit"
                                        :disabled="authProcessing"
                                        class="w-full rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-600 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {{
                                            authProcessing
                                                ? 'Creating...'
                                                : 'Create account'
                                        }}
                                    </button>
                                </form>
                            </Transition>

                            <button
                                type="button"
                                class="mt-3 w-full rounded-xl border border-rose-200 bg-white/90 px-4 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-100"
                                @click="closeAuthModal"
                            >
                                Close
                            </button>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.action-item {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translate3d(0, 0, 0) scale(0.6);
    transition:
        transform 0.48s cubic-bezier(0.2, 0.85, 0.25, 1.15),
        opacity 0.22s ease,
        visibility 0.22s step-end;
    transition-delay: 0ms;
}

.action-item.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translate3d(var(--fab-x, 0), var(--fab-y, 0), 0) scale(1);
    transition-delay: var(--fab-delay, 0ms);
}

.fab-main {
    animation: fab-bob 1.9s ease-in-out infinite alternate;
}

.fab-main.is-open {
    animation-play-state: paused;
}

@keyframes fab-bob {
    from {
        transform: translateY(0);
    }
    to {
        transform: translateY(-8px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .action-item,
    .action-item.is-open,
    .fab-main {
        animation: none;
        transition-duration: 0.01ms;
        transition-delay: 0ms;
    }

    .auth-form-fade-enter-active,
    .auth-form-fade-leave-active {
        transition-duration: 0.01ms;
    }

    .auth-modal-overlay-enter-active,
    .auth-modal-overlay-leave-active,
    .auth-modal-card-enter-active,
    .auth-modal-card-leave-active {
        transition-duration: 0.01ms;
    }
}

.auth-modal-overlay-enter-active,
.auth-modal-overlay-leave-active {
    transition: opacity 0.24s ease;
}

.auth-modal-overlay-enter-from,
.auth-modal-overlay-leave-to {
    opacity: 0;
}

.auth-modal-card-enter-active,
.auth-modal-card-leave-active {
    transition:
        opacity 0.26s ease,
        transform 0.26s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.auth-modal-card-enter-from,
.auth-modal-card-leave-to {
    opacity: 0;
    transform: translateY(16px) scale(0.96);
}

.auth-form-fade-enter-active,
.auth-form-fade-leave-active {
    transition:
        opacity 0.24s ease,
        transform 0.24s ease;
}

.auth-form-fade-enter-from,
.auth-form-fade-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}
</style>
