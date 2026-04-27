<script setup>
import { InfiniteScroll } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    feedbacks: {
        type: Array,
        default: () => [],
    },
    wishPages: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const scrollerRef = ref(null);
const trackRef = ref(null);
let resizeHandler = null;
const shouldAnimate = ref(false);
const animationDuration = ref(28);
const showAllWishesModal = ref(false);

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

const openAllWishesModal = () => {
    showAllWishesModal.value = true;
};

const closeAllWishesModal = () => {
    showAllWishesModal.value = false;
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
            <div class="mt-8 flex justify-center">
                <button
                    type="button"
                    class="rounded-full border border-rose-300 bg-white px-6 py-2 text-sm font-semibold text-rose-700 shadow-[0_8px_18px_rgba(251,113,133,0.18)] transition hover:-translate-y-0.5 hover:bg-rose-50"
                    @click="openAllWishesModal"
                >
                    View All Wishes
                </button>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="showAllWishesModal"
                class="fixed inset-0 z-10000 bg-rose-100/30 px-4 py-6 backdrop-blur-sm md:px-6"
                @click.self="closeAllWishesModal"
            >
                <div
                    class="mx-auto flex h-full w-full max-w-5xl flex-col rounded-3xl border border-rose-200 bg-white shadow-[0_24px_60px_rgba(190,24,93,0.24)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-rose-100 px-5 py-4 md:px-7"
                    >
                        <h3 class="font-title text-3xl text-rose-800">
                            All Wishes
                        </h3>
                        <button
                            type="button"
                            class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
                            @click="closeAllWishesModal"
                        >
                            Close
                        </button>
                    </div>

                    <div
                        class="min-h-0 flex-1 overflow-y-auto px-5 py-5 md:px-7"
                    >
                        <InfiniteScroll
                            data="wishPages"
                            preserve-url
                            only-next
                            :buffer="240"
                        >
                            <template #default="{ loading, loadingNext }">
                                <div
                                    v-if="!wishPages.data?.length && loading"
                                    class="py-16 text-center text-sm text-rose-500"
                                >
                                    Loading wishes...
                                </div>

                                <div v-else class="space-y-4">
                                    <article
                                        v-for="item in wishPages.data"
                                        :key="item.id"
                                        class="rounded-3xl border border-rose-100 bg-linear-to-br from-white via-rose-50 to-pink-100 p-5 shadow-[0_12px_28px_rgba(244,114,182,0.2)]"
                                    >
                                        <p
                                            class="text-base leading-relaxed text-rose-800/90 md:text-lg"
                                        >
                                            {{ item.message }}
                                        </p>
                                        <p
                                            class="mt-3 text-xs tracking-[0.16em] text-rose-600 uppercase"
                                        >
                                            {{ item.name }}
                                        </p>
                                    </article>
                                    <p
                                        v-if="loadingNext"
                                        class="py-3 text-center text-sm text-rose-500"
                                    >
                                        Loading more...
                                    </p>
                                </div>
                            </template>
                        </InfiniteScroll>
                    </div>
                </div>
            </div>
        </Teleport>
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
