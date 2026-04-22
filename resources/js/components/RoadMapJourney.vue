<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const props = defineProps({
    memories: {
        type: Array,
        default: () => [
            {
                image: '/image/hero.webp',
                date: 'Aug 29, 2020',
                title: 'The First Hello',
                description:
                    'The day our story began, with shy smiles and a feeling that this moment would stay forever.',
            },
            {
                image: '/image/one.webp',
                date: 'Feb 14, 2021',
                title: 'Our Sweet Promise',
                description:
                    'A warm evening, pink skies, and a promise to keep choosing each other every single day.',
            },
            {
                image: '/image/hero.webp',
                date: 'Dec 25, 2022',
                title: 'Holiday Memory',
                description:
                    'Wrapped in winter lights, we made simple memories that still make our hearts glow.',
            },
            {
                image: '/image/two.webp',
                date: 'Today',
                title: 'Still Growing Together',
                description:
                    'Every day adds another little chapter to our forever, and this roadmap keeps them close.',
            },
        ],
    },
});

const ROW_HEIGHT = 320;
const TOP_OFFSET = 120;
const NODE_MID_OFFSET = 78;
const LEFT_X = 385;
const RIGHT_X = 615;

const ROADMAP_MASK_ID = 'roadmap-path-reveal-mask';
const sectionRef = ref(null);
const pathRef = ref(null);
const viewportWidth = ref(1280);
const memoryNodes = ref([]);
const outerDots = ref([]);
const imageNodes = ref([]);
const cardNodes = ref([]);
const imageOrientations = ref([]);
let gsapContext = null;

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

const lastNodeTop = computed(() => {
    if (!props.memories.length) return TOP_OFFSET;
    return TOP_OFFSET + (props.memories.length - 1) * rowHeight.value;
});

const lineEndY = computed(() => lastNodeTop.value + imageHeight.value);

const totalHeight = computed(() => {
    if (!props.memories.length) return 420;
    return lineEndY.value;
});

const points = computed(() =>
    props.memories.map((_, index) => ({
        x: index % 2 === 0 ? LEFT_X : RIGHT_X,
        y: TOP_OFFSET + index * rowHeight.value + nodeMidOffset.value,
    })),
);

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
    top: `${TOP_OFFSET + index * rowHeight.value}px`,
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
};

const detectAllImageOrientations = () => {
    imageOrientations.value = props.memories.map(() => 'landscape');

    props.memories.forEach((memory, index) => {
        if (!memory?.image || typeof window === 'undefined') return;
        const img = new Image();
        img.onload = () => {
            imageOrientations.value[index] =
                img.naturalHeight > img.naturalWidth ? 'portrait' : 'landscape';
        };
        img.src = memory.image;
    });
};

const resetRefCollections = () => {
    memoryNodes.value = [];
    outerDots.value = [];
    imageNodes.value = [];
    cardNodes.value = [];
    imageOrientations.value = [];
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
            end: 'bottom 42%',
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

watch(
    () => props.memories.length,
    () => {
        resetRefCollections();
        detectAllImageOrientations();
        setupGsapAnimations();
    },
    { immediate: true },
);

onMounted(() => {
    syncViewportWidth();
    window.addEventListener('resize', syncViewportWidth);
    detectAllImageOrientations();
    setupGsapAnimations();
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncViewportWidth);
    gsapContext?.revert();
});
</script>

<template>
    <section
        ref="sectionRef"
        class="relative min-h-screen bg-rose-50 px-4 py-16 sm:px-6 md:py-24"
    >
        <div class="no-scrollbar mx-auto max-w-6xl">
            <div class="mb-10 text-center md:mb-14">
                <p class="text-sm tracking-[0.24em] text-rose-500 uppercase">
                    Our Milestones
                </p>
                <h2
                    class="mt-3 font-['Playfair_Display'] text-4xl text-rose-800 sm:text-5xl"
                >
                    Roadmap Journey
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
                        <div class="w-[88%] sm:w-[84%] md:w-[72%]">
                            <div
                                class="flex items-start gap-4 sm:gap-8 md:gap-20"
                            >
                                <div
                                    :ref="(el) => setImageNodeRef(el, index)"
                                    class="relative w-1/2 shrink-0"
                                    :class="
                                        index % 2 === 0 ? 'order-1' : 'order-2'
                                    "
                                >
                                    <div
                                        class="relative isolate transition-all duration-300"
                                        :class="memoryImageBoxClass(index)"
                                    >
                                        <img
                                            :src="memory.image"
                                            :alt="memory.title"
                                            class="h-full w-full rounded-2xl object-cover shadow-[0_8px_18px_rgba(219,39,119,0.25)]"
                                            @load="
                                                onMemoryImageLoad($event, index)
                                            "
                                        />
                                    </div>
                                </div>

                                <div
                                    :ref="(el) => setCardNodeRef(el, index)"
                                    class="w-1/2 rounded-2xl border border-rose-100 bg-white p-3 shadow-[0_18px_38px_rgba(251,113,133,0.18)] backdrop-blur-sm sm:p-5 md:rounded-3xl md:p-7"
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
                                        class="mt-1 font-['Playfair_Display'] text-base text-rose-800 sm:mt-2 sm:text-xl md:text-2xl"
                                    >
                                        {{ memory.title }}
                                    </h3>
                                    <p
                                        class="mt-1 text-xs leading-relaxed text-rose-700/90 sm:mt-2 sm:text-sm md:mt-3 md:text-base"
                                    >
                                        {{ memory.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
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
</style>
