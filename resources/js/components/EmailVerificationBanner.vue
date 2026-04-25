<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const sending = ref(false);
const sent = ref(false);

const shouldShow = computed(() => user.value && !user.value.email_verified_at);

const resendVerification = () => {
    if (!shouldShow.value || sending.value) return;

    router.post(
        route('verification.send'),
        {},
        {
            preserveScroll: true,
            onStart: () => {
                sending.value = true;
            },
            onSuccess: () => {
                sent.value = true;
            },
            onFinish: () => {
                sending.value = false;
            },
        },
    );
};
</script>

<template>
    <div
        v-if="shouldShow"
        class="fixed right-0 bottom-0 left-0 z-80 w-full rounded-none border border-emerald-300/80 bg-white/96 px-3 py-2.5 backdrop-blur-sm sm:right-5 sm:bottom-24 sm:left-auto sm:w-[min(78vw,360px)] sm:rounded-xl sm:px-3.5 sm:py-3"
    >
        <div class="flex items-center justify-between gap-3 sm:block">
            <div class="min-w-0">
                <p
                    class="truncate text-[12px] font-semibold tracking-[0.01em] text-emerald-800 sm:text-[13px]"
                >
                    Verify your email to stay in the loop.
                </p>
                <p
                    class="truncate text-[10px] text-emerald-700/85 sm:text-[11px]"
                >
                    For anniversary announcement email delivery.
                </p>
            </div>
            <button
                type="button"
                class="shrink-0 rounded-full border border-emerald-500 bg-emerald-600 px-3 py-1.5 text-[10px] font-semibold whitespace-nowrap text-white transition hover:bg-emerald-700 focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-70 sm:mt-2 sm:text-[11px]"
                :disabled="sending"
                @click="resendVerification"
            >
                {{
                    sending
                        ? 'Sending...'
                        : sent
                          ? 'Resend Link'
                          : 'Verify Email'
                }}
            </button>
        </div>
    </div>
</template>
