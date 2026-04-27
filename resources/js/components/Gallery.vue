<script setup>
import { InfiniteScroll } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        default: () => [],
    },
    galleryPages: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const imageCardRefs = ref([]);
const visibleCards = ref([]);
let cardObserver = null;
const showAllModal = ref(false);

const setImageCardRef = (el, index) => {
    const node = el && typeof el === 'object' && '$el' in el ? el.$el : el;

    if (!node || !(node instanceof HTMLElement)) {
        return;
    }

    imageCardRefs.value[index] = node;
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
                if (!entry.isIntersecting) {
                    return;
                }

                const target = entry.target;

                if (!(target instanceof HTMLElement)) {
                    return;
                }

                const index = Number(target.dataset.cardIndex);

                if (!Number.isNaN(index)) {
                    visibleCards.value[index] = true;
                }

                cardObserver?.unobserve(target);
            });
        },
        { threshold: 0.2, rootMargin: '0px 0px -8% 0px' },
    );

    imageCardRefs.value.forEach((el) => {
        if (el) {
            cardObserver?.observe(el);
        }
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

const openAllPhotosModal = () => {
    showAllModal.value = true;
};

const closeAllPhotosModal = () => {
    showAllModal.value = false;
};
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
                        loading="lazy"
                        decoding="async"
                    />
                </article>
            </div>

            <div class="mt-8 flex justify-center">
                <button
                    type="button"
                    class="rounded-full border border-rose-300 bg-white px-6 py-2 text-sm font-semibold text-rose-700 shadow-[0_8px_18px_rgba(251,113,133,0.18)] transition hover:-translate-y-0.5 hover:bg-rose-50"
                    @click="openAllPhotosModal"
                >
                    View All Photos
                </button>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="showAllModal"
                class="fixed inset-0 z-10000 bg-rose-100/30 px-4 py-6 backdrop-blur-sm md:px-6"
                @click.self="closeAllPhotosModal"
            >
                <div
                    class="mx-auto flex h-full w-full max-w-6xl flex-col rounded-3xl border border-rose-200 bg-white shadow-[0_24px_60px_rgba(190,24,93,0.24)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-rose-100 px-5 py-4 md:px-7"
                    >
                        <h3 class="font-title text-3xl text-rose-800">
                            Gallery
                        </h3>
                        <button
                            type="button"
                            class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
                            @click="closeAllPhotosModal"
                        >
                            Close
                        </button>
                    </div>

                    <div
                        class="min-h-0 flex-1 overflow-y-auto px-5 py-5 md:px-7"
                    >
                        <InfiniteScroll
                            data="galleryPages"
                            preserve-url
                            only-next
                            :buffer="240"
                        >
                            <template #default="{ loading, loadingNext }">
                                <div
                                    v-if="!galleryPages.data?.length && loading"
                                    class="py-16 text-center text-sm text-rose-500"
                                >
                                    Loading photos...
                                </div>

                                <div v-else>
                                    <div
                                        class="columns-2 gap-4 md:columns-3 md:gap-5 lg:columns-4"
                                    >
                                        <article
                                            v-for="(
                                                item, index
                                            ) in galleryPages.data"
                                            :key="item.id"
                                            class="mb-4 break-inside-avoid md:mb-5"
                                        >
                                            <img
                                                :src="item.image"
                                                :alt="`Gallery photo ${Number(index) + 1}`"
                                                class="w-full rounded-3xl border border-rose-100 object-cover shadow-[0_12px_34px_rgba(251,113,133,0.22)]"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        </article>
                                    </div>
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
