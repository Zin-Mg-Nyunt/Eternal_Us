<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { X } from 'lucide-vue-next';

const props = defineProps({
    memories: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: () => ({
            currentPage: 1,
            lastPage: 1,
            prevPage: null,
            nextPage: null,
        }),
    },
});

const ROW_HEIGHT = 320;
const TOP_OFFSET = 120;
const NODE_MID_OFFSET = 78;
const LEFT_X = 385;
const RIGHT_X = 615;
const VIEWBOX_WIDTH = 1000;

const ROADMAP_MASK_ID = 'roadmap-path-reveal-mask';
const sectionRef = ref(null);
const pathRef = ref(null);
const viewportWidth = ref(1280);
const sectionHeight = ref(1000);
const memoryNodes = ref([]);
const nodeHeights = ref([]);
const outerDots = ref([]);
const imageNodes = ref([]);
const cardNodes = ref([]);
const imageOrientations = ref([]);
const imageLoadedStates = ref([]);
const lightboxOpen = ref(false);
const lightboxSrc = ref('');
const lightboxAlt = ref('');
const lightboxTitle = ref('');
let gsapContext = null;
let relayoutTimer = null;

const onLightboxEscape = (event) => {
    if (event.key === 'Escape') {
        closeMemoryLightbox();
    }
};

const openMemoryLightbox = (memory) => {
    if (!memory?.image) {
        return;
    }

    lightboxSrc.value = memory.image;
    lightboxAlt.value = memory.title || 'Journey memory';
    lightboxTitle.value = memory.title || '';
    lightboxOpen.value = true;

    if (typeof window !== 'undefined') {
        window.addEventListener('keydown', onLightboxEscape);
    }
};

const closeMemoryLightbox = () => {
    lightboxOpen.value = false;

    if (typeof window !== 'undefined') {
        window.removeEventListener('keydown', onLightboxEscape);
    }
};

const rowHeight = computed(() => {
    if (viewportWidth.value < 640) return 230;
    if (viewportWidth.value < 768) return 270;
    return ROW_HEIGHT;
});

const nodeMidOffset = computed(() => {
    if (viewportWidth.value < 640) return 58;
    if (viewportWidth.value < 768) return 68;
    return NODE_MID_OFFSET;
});

const imageHeight = computed(() => {
    if (viewportWidth.value < 640) return 112;
    if (viewportWidth.value < 768) return 160;
    return 208;
});

const roseNodeSize = computed(() => {
    if (viewportWidth.value < 640) return 54;
    if (viewportWidth.value < 768) return 48;
    return 42;
});

const roseNodeX = (point) => point.x - roseNodeSize.value / 2;
const roseNodeY = (point) => point.y - roseNodeSize.value * 0.48;

const roadmapStrokeWidth = computed(() => {
    if (viewportWidth.value < 640) return 5;
    if (viewportWidth.value < 768) return 4.5;
    return 4;
});

const roadmapDashArray = computed(() => {
    if (viewportWidth.value < 640) return '3 12';
    if (viewportWidth.value < 768) return '2.5 13';
    return '2 14';
});

const nodeXPair = computed(() => {
    if (viewportWidth.value < 640) {
        return {
            left: VIEWBOX_WIDTH * 0.44,
            right: VIEWBOX_WIDTH * 0.56,
        };
    }
    if (viewportWidth.value < 768) {
        return {
            left: VIEWBOX_WIDTH * 0.42,
            right: VIEWBOX_WIDTH * 0.58,
        };
    }

    return {
        left: LEFT_X,
        right: RIGHT_X,
    };
});

const verticalGap = computed(() => {
    if (viewportWidth.value < 640) return 34;
    if (viewportWidth.value < 768) return 52;
    return 56;
});

const estimatedNodeHeight = computed(() => {
    if (viewportWidth.value < 640) return 236;
    if (viewportWidth.value < 768) return 246;
    return 248;
});

const nodeTopPositions = computed(() => {
    if (!props.memories.length) return [];

    const positions = [];
    let nextTop = TOP_OFFSET;

    props.memories.forEach((_, index) => {
        positions.push(nextTop);
        const currentHeight =
            nodeHeights.value[index] ?? estimatedNodeHeight.value;
        nextTop += currentHeight + verticalGap.value;
    });

    return positions;
});

const totalHeight = computed(() => {
    if (!props.memories.length) {
        return Math.max(760, sectionHeight.value);
    }

    const lastIndex = props.memories.length - 1;
    const lastTop = nodeTopPositions.value[lastIndex] ?? TOP_OFFSET;
    const lastHeight =
        nodeHeights.value[lastIndex] ?? estimatedNodeHeight.value;

    return Math.max(760, lastTop + lastHeight + TOP_OFFSET);
});

const points = computed(() => {
    return props.memories.map((_, index) => {
        const top = nodeTopPositions.value[index] ?? TOP_OFFSET;
        const height = nodeHeights.value[index] ?? estimatedNodeHeight.value;
        return {
            x: index % 2 === 0 ? nodeXPair.value.left : nodeXPair.value.right,
            y: top + height / 2,
        };
    });
});

const lineEndY = computed(() => {
    if (!points.value.length) return TOP_OFFSET;
    const lastPoint = points.value[points.value.length - 1];
    return lastPoint.y + roseNodeSize.value * 0.66;
});

const curvePath = computed(() => {
    if (!points.value.length) return '';
    if (points.value.length === 1)
        return `M ${points.value[0].x} ${points.value[0].y} L ${points.value[0].x} ${lineEndY.value}`;

    let path = `M ${points.value[0].x} ${points.value[0].y}`;
    for (let i = 1; i < points.value.length; i += 1) {
        const prev = points.value[i - 1];
        const curr = points.value[i];
        const bend = rowHeight.value * 0.42;
        const cp1x = prev.x;
        const cp1y = prev.y + bend;
        const cp2x = curr.x;
        const cp2y = curr.y - bend;
        path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${curr.x} ${curr.y}`;
    }
    const lastPoint = points.value[points.value.length - 1];
    path += ` L ${lastPoint.x} ${lineEndY.value}`;
    return path;
});

const svgViewBox = computed(
    () => `0 0 1000 ${Math.max(1000, totalHeight.value)}`,
);

const nodeStyle = (index) => ({
    top: `${nodeTopPositions.value[index] ?? TOP_OFFSET}px`,
});

const setMemoryNodeRef = (el, index) => {
    if (!el) return;
    memoryNodes.value[index] = el;
};

const setOuterDotRef = (el, index) => {
    if (!el) return;
    outerDots.value[index] = el;
};

const setImageNodeRef = (el, index) => {
    if (!el) return;
    imageNodes.value[index] = el;
};

const setCardNodeRef = (el, index) => {
    if (!el) return;
    cardNodes.value[index] = el;
};

const memoryImageBoxClass = (index) => {
    const orientation = imageOrientations.value[index];
    if (orientation === 'portrait') {
        return 'mx-auto h-44 w-[62%] sm:h-56 sm:w-[58%] md:h-72 md:w-[54%]';
    }
    return 'h-28 w-full sm:h-40 md:h-52';
};

const onMemoryImageLoad = (event, index) => {
    const target = event?.target;
    if (!(target instanceof HTMLImageElement)) return;
    imageOrientations.value[index] =
        target.naturalHeight > target.naturalWidth ? 'portrait' : 'landscape';
    imageLoadedStates.value[index] = true;
    scheduleRelayout();
};

const scheduleRelayout = () => {
    if (typeof window === 'undefined') return;
    window.clearTimeout(relayoutTimer);
    relayoutTimer = window.setTimeout(async () => {
        await syncNodeHeights();
        setupGsapAnimations();
    }, 80);
};

const resetRefCollections = () => {
    memoryNodes.value = [];
    nodeHeights.value = [];
    outerDots.value = [];
    imageNodes.value = [];
    cardNodes.value = [];
    imageOrientations.value = [];
    imageLoadedStates.value = props.memories.map(() => false);
};

const memorySignature = computed(() =>
    props.memories
        .map((memory) => `${memory?.id ?? ''}:${memory?.image ?? ''}`)
        .join('|'),
);

const syncNodeHeights = async () => {
    await nextTick();
    nodeHeights.value = props.memories.map(
        (_, index) =>
            memoryNodes.value[index]?.offsetHeight ?? estimatedNodeHeight.value,
    );
};

const calculateNodeLengths = (pathElement, allPoints) => {
    const pathLength = pathElement.getTotalLength();
    const sampleStep = 4;

    return allPoints.map((point) => {
        let bestLength = 0;
        let smallestDistance = Number.POSITIVE_INFINITY;

        for (let length = 0; length <= pathLength; length += sampleStep) {
            const sampled = pathElement.getPointAtLength(length);
            const dx = sampled.x - point.x;
            const dy = sampled.y - point.y;
            const distance = dx * dx + dy * dy;

            if (distance < smallestDistance) {
                smallestDistance = distance;
                bestLength = length;
            }
        }

        return bestLength;
    });
};

const setupGsapAnimations = async () => {
    const clientSafe =
        (typeof process !== 'undefined' && process.client) ||
        typeof window !== 'undefined';
    if (!clientSafe || !sectionRef.value || !pathRef.value) return;

    await nextTick();
    gsapContext?.revert();

    gsap.registerPlugin(ScrollTrigger);

    gsapContext = gsap.context(() => {
        const path = pathRef.value;
        if (!path) return;

        const pathLength = path.getTotalLength();
        const nodeLengths = calculateNodeLengths(path, points.value);
        const contentShown = new Set();
        let maxProgress = 0;

        gsap.set(path, {
            strokeDasharray: pathLength,
            strokeDashoffset: pathLength,
        });

        gsap.set(outerDots.value, {
            autoAlpha: 0,
            scale: 0.35,
            transformOrigin: 'center center',
        });

        gsap.set(imageNodes.value, {
            autoAlpha: 0,
            x: (index) => (index % 2 === 0 ? -52 : 52),
        });
        gsap.set(cardNodes.value, {
            autoAlpha: 0,
            x: (index) => (index % 2 === 0 ? 52 : -52),
        });

        ScrollTrigger.create({
            trigger: sectionRef.value,
            start: 'top 78%',
            end: 'bottom 85%',
            scrub: true,
            onUpdate: (self) => {
                maxProgress = Math.max(maxProgress, self.progress);
                const visibleLength = pathLength * maxProgress;
                const nextDashOffset = Math.max(0, pathLength - visibleLength);

                gsap.set(path, { strokeDashoffset: nextDashOffset });

                nodeLengths.forEach((thresholdLength, index) => {
                    if (visibleLength < thresholdLength) return;

                    gsap.to(outerDots.value[index], {
                        autoAlpha: 1,
                        scale: 1,
                        duration: 0.22,
                        ease: 'power2.out',
                        overwrite: 'auto',
                    });

                    if (contentShown.has(index)) return;
                    contentShown.add(index);

                    gsap.timeline()
                        .to(imageNodes.value[index], {
                            autoAlpha: 1,
                            x: 0,
                            duration: 0.7,
                            ease: 'power3.out',
                        })
                        .to(
                            cardNodes.value[index],
                            {
                                autoAlpha: 1,
                                x: 0,
                                duration: 0.7,
                                ease: 'power3.out',
                            },
                            '<+0.08',
                        );
                });
            },
        });
    }, sectionRef.value);
};

const syncViewportWidth = () => {
    if (typeof window === 'undefined') return;
    viewportWidth.value = sectionRef.value?.clientWidth || window.innerWidth;
};

const syncSectionHeight = () => {
    if (typeof window === 'undefined') return;
    sectionHeight.value = sectionRef.value?.clientHeight || window.innerHeight;
};

const onResize = async () => {
    syncViewportWidth();
    syncSectionHeight();
    await syncNodeHeights();
    setupGsapAnimations();
};

const goToJourneyPage = (page) => {
    if (!page || page < 1 || page === props.pagination?.currentPage) return;
    router.get(
        window.location.pathname,
        { journey_page: page },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            only: ['journeyItems', 'journeyPagination'],
        },
    );
};

watch(
    memorySignature,
    async () => {
        resetRefCollections();
        await syncNodeHeights();
        setupGsapAnimations();
    },
    { immediate: true },
);

onMounted(() => {
    syncViewportWidth();
    syncSectionHeight();
    window.addEventListener('resize', onResize);
    syncNodeHeights().then(setupGsapAnimations);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', onResize);
    if (typeof window !== 'undefined') {
        window.clearTimeout(relayoutTimer);
        window.removeEventListener('keydown', onLightboxEscape);
    }
    gsapContext?.revert();
});
</script>

<template>
    <section
        ref="sectionRef"
        class="relative -mb-1 min-h-screen bg-rose-50 px-4 py-12 sm:px-6 md:py-16"
    >
        <div class="no-scrollbar mx-auto max-w-6xl">
            <div class="text-center">
                <p class="text-sm tracking-[0.24em] text-rose-500 uppercase">
                    Our Milestones
                </p>
                <h2 class="font-title mt-3 text-4xl text-rose-800 sm:text-5xl">
                    Love Journey
                </h2>
            </div>
            <div
                class="relative mx-auto"
                :style="{ minHeight: `${totalHeight}px` }"
            >
                <svg
                    class="pointer-events-none absolute inset-0 z-0 h-full w-full"
                    :viewBox="svgViewBox"
                    preserveAspectRatio="none"
                    aria-hidden="true"
                >
                    <defs>
                        <mask :id="ROADMAP_MASK_ID">
                            <rect width="100%" height="100%" fill="black" />
                            <path
                                ref="pathRef"
                                :d="curvePath"
                                fill="none"
                                stroke="white"
                                stroke-linecap="round"
                                stroke-width="10"
                            />
                        </mask>
                    </defs>
                    <path
                        :d="curvePath"
                        fill="none"
                        stroke="#ec4899"
                        stroke-linecap="round"
                        :stroke-width="roadmapStrokeWidth"
                        :stroke-dasharray="roadmapDashArray"
                        :mask="`url(#${ROADMAP_MASK_ID})`"
                        class="romantic-dotted"
                    />
                    <image
                        v-for="(point, index) in points"
                        :key="`rose-node-${index}`"
                        :ref="(el) => setOuterDotRef(el, index)"
                        href="/image/pink-rose.png"
                        :x="roseNodeX(point)"
                        :y="roseNodeY(point)"
                        :width="roseNodeSize"
                        :height="roseNodeSize"
                        preserveAspectRatio="xMidYMid meet"
                        class="rose-node-icon"
                    />
                </svg>

                <article
                    v-for="(memory, index) in memories"
                    :key="`${memory.title}-${memory.date}-${index}`"
                    :ref="(el) => setMemoryNodeRef(el, index)"
                    class="memory-node absolute right-0 left-0 z-10 px-2 sm:px-4 md:px-8"
                    :style="nodeStyle(index)"
                >
                    <div
                        class="flex"
                        :class="
                            index % 2 === 0 ? 'justify-start' : 'justify-end'
                        "
                    >
                        <div class="w-[88%] sm:w-[84%] md:w-[88%] lg:w-[72%]">
                            <div
                                class="flex items-start gap-4 sm:gap-8 md:gap-8 lg:gap-20"
                            >
                                <div
                                    :ref="(el) => setImageNodeRef(el, index)"
                                    class="relative w-[42%] shrink-0 lg:w-1/2"
                                    :class="
                                        index % 2 === 0 ? 'order-1' : 'order-2'
                                    "
                                >
                                    <div
                                        class="relative isolate cursor-pointer rounded-2xl transition-all duration-300 hover:ring-2 hover:ring-rose-300/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400"
                                        :class="memoryImageBoxClass(index)"
                                        role="button"
                                        tabindex="0"
                                        :aria-label="`View larger photo: ${memory.title}`"
                                        @click="openMemoryLightbox(memory)"
                                        @keydown.enter.prevent="
                                            openMemoryLightbox(memory)
                                        "
                                        @keydown.space.prevent="
                                            openMemoryLightbox(memory)
                                        "
                                    >
                                        <div
                                            v-if="!imageLoadedStates[index]"
                                            class="absolute inset-0 animate-pulse rounded-2xl bg-rose-100/90"
                                        />
                                        <img
                                            :src="memory.image"
                                            :alt="memory.title"
                                            class="pointer-events-none h-full w-full rounded-2xl object-cover shadow-[0_8px_18px_rgba(219,39,119,0.25)]"
                                            :class="
                                                imageLoadedStates[index]
                                                    ? 'opacity-100'
                                                    : 'opacity-0'
                                            "
                                            :loading="
                                                index < 2 ? 'eager' : 'lazy'
                                            "
                                            :fetchpriority="
                                                index === 0 ? 'high' : 'auto'
                                            "
                                            decoding="async"
                                            @load="
                                                onMemoryImageLoad($event, index)
                                            "
                                        />
                                    </div>
                                </div>

                                <div
                                    :ref="(el) => setCardNodeRef(el, index)"
                                    class="w-[58%] rounded-2xl border border-rose-100 bg-white p-3 shadow-[0_18px_38px_rgba(251,113,133,0.18)] backdrop-blur-sm sm:p-5 md:rounded-3xl md:p-6 lg:w-1/2 lg:p-7"
                                    :class="
                                        index % 2 === 0 ? 'order-2' : 'order-1'
                                    "
                                >
                                    <span class="rose-corner rose-corner-card">
                                        <img
                                            src="/image/pink-rose.png"
                                            alt=""
                                            class="rose-corner-icon"
                                        />
                                    </span>
                                    <p
                                        class="text-[10px] font-semibold tracking-[0.14em] text-rose-500 uppercase sm:text-xs sm:tracking-[0.2em]"
                                    >
                                        {{ memory.date }}
                                    </p>
                                    <h3
                                        class="font-title mt-1 text-base text-rose-800 sm:mt-2 sm:text-xl md:text-2xl"
                                    >
                                        {{ memory.title }}
                                    </h3>
                                    <p
                                        class="memory-description mt-1 text-xs leading-relaxed text-rose-700/90 sm:mt-2 sm:text-sm md:mt-3 md:text-base"
                                    >
                                        {{ memory.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div
                class="relative z-20 mt-12 flex items-center justify-center gap-4 pb-6"
            >
                <button
                    type="button"
                    class="rounded-full border border-rose-300 bg-white px-4 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!pagination?.prevPage"
                    @click="goToJourneyPage(pagination?.prevPage)"
                >
                    ←
                </button>
                <p class="text-xs tracking-[0.2em] text-rose-500 uppercase">
                    Page {{ pagination?.currentPage ?? 1 }} /
                    {{ pagination?.lastPage ?? 1 }}
                </p>
                <button
                    type="button"
                    class="rounded-full border border-rose-300 bg-white px-4 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!pagination?.nextPage"
                    @click="goToJourneyPage(pagination?.nextPage)"
                >
                    →
                </button>
            </div>
        </div>
    </section>

    <Teleport to="body">
        <div
            v-if="lightboxOpen"
            class="fixed inset-0 z-10000 flex flex-col items-center justify-center bg-rose-100/30 px-4 py-8 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            :aria-label="lightboxTitle || 'Memory photo'"
            @click.self="closeMemoryLightbox"
        >
            <div
                class="relative flex max-h-[90vh] w-full max-w-5xl flex-col items-center"
            >
                <button
                    type="button"
                    class="absolute top-1 right-1 z-10 cursor-pointer rounded-full border border-rose-200/80 bg-white/95 p-3 text-sm font-medium text-rose-800 shadow-sm transition hover:bg-rose-50"
                    @click="closeMemoryLightbox"
                >
                    <X class="size-4" />
                </button>
                <img
                    :src="lightboxSrc"
                    :alt="lightboxAlt"
                    class="max-h-[min(82vh,900px)] w-full max-w-full rounded-2xl border border-rose-100/60 object-contain shadow-[0_24px_60px_rgba(190,24,93,0.35)]"
                />
                <p
                    v-if="lightboxTitle"
                    class="mt-4 max-w-full truncate text-center text-sm font-medium text-rose-800/80 drop-shadow-md sm:text-base"
                >
                    {{ lightboxTitle }}
                </p>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.romantic-dotted-base {
    filter: drop-shadow(0 0 6px rgba(244, 114, 182, 0.18));
}

.romantic-dotted {
    filter: drop-shadow(0 0 10px rgba(244, 114, 182, 0.25));
}

.rose-node-icon {
    filter: drop-shadow(0 3px 6px rgba(236, 72, 153, 0.35)) saturate(1.1);
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.rose-corner {
    position: absolute;
    left: -1.5rem;
    top: -1rem;
    z-index: 20;
    display: inline-flex;
    padding: 0.22rem;
}

.rose-corner-icon {
    width: 3rem;
    height: 3rem;
    object-fit: contain;
    display: block;
}

.rose-corner-image .rose-corner-icon {
    filter: hue-rotate(-8deg) saturate(1.12);
}

.rose-corner-card .rose-corner-icon {
    filter: hue-rotate(8deg) saturate(1.22);
}

@media (max-width: 1024px) {
    .memory-description {
        max-height: 7rem;
        overflow-y: auto;
        padding-right: 0.2rem;
    }
}
</style>
