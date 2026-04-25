<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    feedbacks: {
        type: Array,
        default: () => [],
    },
});

const scrollerRef = ref(null);
const trackRef = ref(null);
let resizeHandler = null;
const shouldAnimate = ref(false);
const animationDuration = ref(28);

const displayFeedbacks = computed(() =>
    shouldAnimate.value ? [...props.feedbacks, ...props.feedbacks] : props.feedbacks,
);

const remeasure = async () => {
    await nextTick();
    if (!scrollerRef.value || !trackRef.value) return;

    shouldAnimate.value = false;
    await nextTick();

    const contentWidth = trackRef.value.scrollWidth;
    const viewportWidth = scrollerRef.value.clientWidth;
    const overflow = contentWidth > viewportWidth + 1;

    shouldAnimate.value = overflow;
    if (overflow) {
        const speed = 70; // px/sec
        animationDuration.value = Math.max(14, contentWidth / speed);
    }
};

onMounted(async () => {
    await remeasure();
    resizeHandler = async () => {
        await remeasure();
    };
    window.addEventListener('resize', resizeHandler);
});

watch(
    () => props.feedbacks,
    async () => {
        await remeasure();
    },
    { deep: true },
);

onBeforeUnmount(() => {
    if (resizeHandler && typeof window !== 'undefined') {
        window.removeEventListener('resize', resizeHandler);
    }
});
</script>

<template>
    <section class="relative -mb-1 w-full overflow-hidden bg-rose-50 px-0 py-16 md:py-24">
        <div class="w-full">
            <div class="mb-8 text-center md:mb-12">
                <p class="text-sm tracking-[0.22em] text-rose-500 uppercase">
                    Notes of Love
                </p>
                <h2 class="font-title mt-2 text-4xl text-rose-800 sm:text-5xl">
                    Whispering Wishes
                </h2>
            </div>

            <div
                ref="scrollerRef"
                class="w-full overflow-hidden border-y border-rose-100/70 bg-white/45 px-4 py-8 shadow-[0_18px_40px_rgba(251,113,133,0.16)] backdrop-blur-sm md:px-6 md:py-10"
            >
                <div
                    ref="trackRef"
                    class="feedback-track flex w-max gap-10 py-10 md:gap-20"
                    :class="{ 'is-animated': shouldAnimate }"
                    :style="{
                        '--marquee-duration': `${animationDuration}s`,
                    }"
                >
                    <article
                        v-for="(feedback, index) in displayFeedbacks"
                        :key="`${feedback.name}-${index}`"
                        class="wavy-card group relative min-h-[240px] w-[82vw] max-w-[420px] min-w-[240px] cursor-grab border-2 border-rose-200/80 bg-linear-to-br from-white via-rose-50 to-pink-100 p-6 shadow-[0_18px_38px_rgba(244,114,182,0.24)] ring-2 ring-white/60 active:cursor-grabbing sm:w-[68vw] sm:p-7 md:min-h-[280px] md:w-[48vw] md:min-w-[360px] md:p-9 lg:w-[420px]"
                        :class="[
                            feedback.tone || 'bg-rose-100',
                            { 'wavy-card-alt': index % 2 === 1 },
                        ]"
                    >
                        <span
                            class="pointer-events-none absolute -top-2 -left-2 rounded-full px-2 py-0.5 text-sm"
                        >
                            🌸
                        </span>
                        <span
                            class="pointer-events-none absolute -right-2 -bottom-2 rounded-full px-2 py-0.5 text-sm"
                        >
                            💗
                        </span>
                        <span
                            class="pointer-events-none absolute top-3 right-4 text-xs text-rose-300/80"
                        >
                            ✿
                        </span>
                        <p
                            class="text-lg leading-relaxed text-rose-800/90 md:text-xl"
                        >
                            {{ feedback.message }}
                        </p>
                        <p
                            class="mt-6 text-sm tracking-[0.2em] text-rose-600 uppercase"
                        >
                            {{ feedback.name }}
                        </p>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.feedback-wall {
    contain: layout;
}

.feedback-track.is-animated {
    animation: marquee-scroll var(--marquee-duration) linear infinite;
    will-change: transform;
}

.feedback-track.is-animated:hover {
    animation-play-state: paused;
}

.wavy-card {
    border-radius: 1.75rem 2rem 1.6rem 1.9rem / 1.8rem 1.6rem 2rem 1.7rem;
}

.wavy-card-alt {
    border-radius: 1.95rem 1.65rem 1.9rem 1.7rem / 1.65rem 1.95rem 1.7rem 1.9rem;
}

@keyframes marquee-scroll {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}
</style>
