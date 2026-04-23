<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        default: () => [],
    },
});

const imageCardRefs = ref([]);
const visibleCards = ref([]);
let cardObserver = null;

const setImageCardRef = (el, index) => {
    if (!el) return;
    imageCardRefs.value[index] = el;
};

const resetVisibleCards = () => {
    visibleCards.value = props.images.map(() => false);
};

const observeCards = async () => {
    await nextTick();
    cardObserver?.disconnect();

    const canObserve =
        typeof window !== 'undefined' &&
        typeof IntersectionObserver !== 'undefined';
    if (!canObserve) {
        visibleCards.value = props.images.map(() => true);
        return;
    }

    cardObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const index = Number(entry.target.dataset.cardIndex);
                if (!Number.isNaN(index)) visibleCards.value[index] = true;
                cardObserver?.unobserve(entry.target);
            });
        },
        { threshold: 0.2, rootMargin: '0px 0px -8% 0px' },
    );

    imageCardRefs.value.forEach((el) => {
        if (el) cardObserver?.observe(el);
    });
};

watch(
    () => props.images.length,
    async () => {
        imageCardRefs.value = [];
        resetVisibleCards();
        await observeCards();
    },
    { immediate: true },
);

onMounted(async () => {
    resetVisibleCards();
    await observeCards();
});

onBeforeUnmount(() => {
    cardObserver?.disconnect();
});
</script>

<template>
    <section class="-mb-1 min-h-screen bg-rose-50 px-4 py-16 sm:px-6 md:py-24">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 text-center md:mb-12">
                <p class="text-sm tracking-[0.22em] text-rose-500 uppercase">
                    Our Cute Moments
                </p>
                <h2 class="font-title mt-2 text-4xl text-rose-800 sm:text-5xl">
                    Gallery
                </h2>
            </div>

            <div class="columns-2 gap-4 md:columns-3 md:gap-5 lg:columns-4">
                <article
                    v-for="(image, index) in images"
                    :key="image.src ?? `gallery-${index}`"
                    :ref="(el) => setImageCardRef(el, index)"
                    :data-card-index="index"
                    class="group relative mb-4 break-inside-avoid transition-all duration-700 ease-out md:mb-5"
                    :class="
                        visibleCards[index]
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0'
                    "
                    :style="{ transitionDelay: `${(index % 4) * 90}ms` }"
                >
                    <img
                        :src="image.src"
                        :alt="image.alt || `Memory photo ${index + 1}`"
                        class="w-full rounded-3xl border border-rose-100 object-cover shadow-[0_12px_34px_rgba(251,113,133,0.22)] transition duration-300 ease-out group-hover:scale-105 group-hover:shadow-[0_20px_48px_rgba(244,114,182,0.34)]"
                    />
                </article>
            </div>
        </div>
    </section>
</template>
