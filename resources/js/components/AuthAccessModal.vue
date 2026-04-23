<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { store as loginStore } from '@/routes/login';
import { store as registerStore } from '@/routes/register';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    initialMode: {
        type: String,
        default: 'login',
    },
});

const emit = defineEmits(['update:modelValue']);

const authMode = ref('login');
const authErrors = ref({});
const authProcessing = ref(false);

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

const closeModal = () => {
    emit('update:modelValue', false);
    authErrors.value = {};
};

const switchAuthMode = (mode) => {
    authMode.value = mode;
    authErrors.value = {};
};

const submitLogin = () => {
    authProcessing.value = true;
    authErrors.value = {};
    router.post(loginStore.url(), loginForm.data(), {
        preserveScroll: true,
        onError: (errors) => {
            authErrors.value = errors ?? {};
        },
        onFinish: () => {
            authProcessing.value = false;
        },
        onSuccess: () => {
            closeModal();
            loginForm.reset('password');
        },
    });
};

const submitRegister = () => {
    authProcessing.value = true;
    authErrors.value = {};
    router.post(registerStore.url(), registerForm.data(), {
        preserveScroll: true,
        onError: (errors) => {
            authErrors.value = errors ?? {};
        },
        onFinish: () => {
            authProcessing.value = false;
        },
        onSuccess: () => {
            closeModal();
            registerForm.reset('password', 'password_confirmation');
        },
    });
};

watch(
    () => [props.modelValue, props.initialMode],
    ([open, initialMode]) => {
        if (!open) return;
        authMode.value = initialMode === 'register' ? 'register' : 'login';
        authErrors.value = {};
    },
    { immediate: true },
);
</script>

<template>
    <Teleport to="body">
        <Transition name="auth-modal-overlay">
            <div
                v-if="modelValue"
                class="fixed inset-0 z-10001 grid place-items-center bg-rose-100/30 px-4 backdrop-blur-sm"
                @click.self="closeModal"
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
                            <h3 class="mt-1 text-xl font-semibold text-rose-700">
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
                                <input
                                    v-model="loginForm.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    placeholder="Email address"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 outline-none ring-rose-300 transition focus:ring-2"
                                />
                                <p
                                    v-if="authErrors.email"
                                    class="text-xs text-rose-600"
                                >
                                    {{ authErrors.email }}
                                </p>
                                <input
                                    v-model="loginForm.password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Password"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 outline-none ring-rose-300 transition focus:ring-2"
                                />
                                <p
                                    v-if="authErrors.password"
                                    class="text-xs text-rose-600"
                                >
                                    {{ authErrors.password }}
                                </p>
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
                                <input
                                    v-model="registerForm.name"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    placeholder="Full name"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 outline-none ring-rose-300 transition focus:ring-2"
                                />
                                <p
                                    v-if="authErrors.name"
                                    class="text-xs text-rose-600"
                                >
                                    {{ authErrors.name }}
                                </p>
                                <input
                                    v-model="registerForm.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    placeholder="Email address"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 outline-none ring-rose-300 transition focus:ring-2"
                                />
                                <p
                                    v-if="authErrors.email"
                                    class="text-xs text-rose-600"
                                >
                                    {{ authErrors.email }}
                                </p>
                                <input
                                    v-model="registerForm.password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Password"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 outline-none ring-rose-300 transition focus:ring-2"
                                />
                                <p
                                    v-if="authErrors.password"
                                    class="text-xs text-rose-600"
                                >
                                    {{ authErrors.password }}
                                </p>
                                <input
                                    v-model="registerForm.password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Confirm password"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 outline-none ring-rose-300 transition focus:ring-2"
                                />
                                <p
                                    v-if="authErrors.password_confirmation"
                                    class="text-xs text-rose-600"
                                >
                                    {{ authErrors.password_confirmation }}
                                </p>
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
                            @click="closeModal"
                        >
                            Close
                        </button>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
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

@media (prefers-reduced-motion: reduce) {
    .auth-form-fade-enter-active,
    .auth-form-fade-leave-active,
    .auth-modal-overlay-enter-active,
    .auth-modal-overlay-leave-active,
    .auth-modal-card-enter-active,
    .auth-modal-card-leave-active {
        transition-duration: 0.01ms;
    }
}
</style>

