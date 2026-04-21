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
                image: '/image/hero.jpg',
                date: 'Aug 29, 2020',
                title: 'The First Hello',
                description:
                    'The day our story began, with shy smiles and a feeling that this moment would stay forever.',
            },
            {
                image: '/image/hero.jpg',
                date: 'Feb 14, 2021',
                title: 'Our Sweet Promise',
                description:
                    'A warm evening, pink skies, and a promise to keep choosing each other every single day.',
            },
            {
                image: '/image/hero.jpg',
                date: 'Dec 25, 2022',
                title: 'Holiday Memory',
                description:
                    'Wrapped in winter lights, we made simple memories that still make our hearts glow.',
            },
            {
                image: '/image/hero.jpg',
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
const LEFT_X = 420;
const RIGHT_X = 580;

const ROADMAP_MASK_ID = 'roadmap-path-reveal-mask';
const sectionRef = ref(null);
const pathRef = ref(null);
const memoryNodes = ref([]);
const outerDots = ref([]);
const innerDots = ref([]);
const imageNodes = ref([]);
const cardNodes = ref([]);
let gsapContext = null;

const totalHeight = computed(() => {
    if (!props.memories.length) return 420;
    return (
        TOP_OFFSET * 1.6 + ROW_HEIGHT * Math.max(props.memories.length - 1, 0)
    );
});

const points = computed(() =>
    props.memories.map((_, index) => ({
        x: index % 2 === 0 ? LEFT_X : RIGHT_X,
        y: TOP_OFFSET + index * ROW_HEIGHT,
    })),
);

const curvePath = computed(() => {
    if (!points.value.length) return '';
    if (points.value.length === 1)
        return `M ${points.value[0].x} ${points.value[0].y}`;

    let path = `M ${points.value[0].x} ${points.value[0].y}`;
    for (let i = 1; i < points.value.length; i += 1) {
        const prev = points.value[i - 1];
        const curr = points.value[i];
        const bend = ROW_HEIGHT * 0.42;
        const cp1x = prev.x;
        const cp1y = prev.y + bend;
        const cp2x = curr.x;
        const cp2y = curr.y - bend;
        path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${curr.x} ${curr.y}`;
    }
    return path;
});

const svgViewBox = computed(
    () => `0 0 1000 ${Math.max(1000, totalHeight.value)}`,
);

const nodeStyle = (index) => ({
    top: `${TOP_OFFSET + index * ROW_HEIGHT}px`,
});

const setMemoryNodeRef = (el, index) => {
    if (!el) return;
    memoryNodes.value[index] = el;
};

const setOuterDotRef = (el, index) => {
    if (!el) return;
    outerDots.value[index] = el;
};

const setInnerDotRef = (el, index) => {
    if (!el) return;
    innerDots.value[index] = el;
};

const setImageNodeRef = (el, index) => {
    if (!el) return;
    imageNodes.value[index] = el;
};

const setCardNodeRef = (el, index) => {
    if (!el) return;
    cardNodes.value[index] = el;
};

const resetRefCollections = () => {
    memoryNodes.value = [];
    outerDots.value = [];
    innerDots.value = [];
    imageNodes.value = [];
    cardNodes.value = [];
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
        gsap.set(innerDots.value, {
            autoAlpha: 0,
            scale: 0.2,
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
            end: 'bottom 20%',
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
                    gsap.to(innerDots.value[index], {
                        autoAlpha: 1,
                        scale: 1,
                        duration: 0.2,
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

watch(
    () => props.memories.length,
    () => {
        resetRefCollections();
        setupGsapAnimations();
    },
    { immediate: true },
);

onMounted(() => {
    setupGsapAnimations();
});

onBeforeUnmount(() => {
    gsapContext?.revert();
});
</script>

<template>
    <section
        ref="sectionRef"
        class="relative overflow-hidden bg-rose-50 px-4 py-16 sm:px-6 md:py-24"
    >
        <div class="mx-auto max-w-6xl">
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
                    class="pointer-events-none absolute inset-0 hidden h-full w-full md:block"
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
                        stroke-width="5"
                        stroke-dasharray="1.5 14"
                        :mask="`url(#${ROADMAP_MASK_ID})`"
                        class="romantic-dotted"
                    />
                    <rect
                        v-for="(point, index) in points"
                        :key="`dot-${index}`"
                        :ref="(el) => setOuterDotRef(el, index)"
                        :x="point.x - 8"
                        :y="point.y - 8"
                        width="16"
                        height="16"
                        rx="3"
                        fill="#f472b6"
                        fill-opacity="0.2"
                    />
                    <rect
                        v-for="(point, index) in points"
                        :key="`dot-inner-${index}`"
                        :ref="(el) => setInnerDotRef(el, index)"
                        :x="point.x - 3"
                        :y="point.y - 3"
                        width="6"
                        height="6"
                        rx="1"
                        fill="#ec4899"
                    />
                </svg>

                <div
                    class="pointer-events-none absolute top-0 bottom-0 left-6 w-px border-l-2 border-dashed border-rose-300 md:hidden"
                />

                <article
                    v-for="(memory, index) in memories"
                    :key="`${memory.title}-${memory.date}-${index}`"
                    :ref="(el) => setMemoryNodeRef(el, index)"
                    class="memory-node absolute right-0 left-0"
                    :style="nodeStyle(index)"
                >
                    <div
                        class="grid items-center gap-6 md:grid-cols-2 md:gap-20"
                    >
                        <div
                            :ref="(el) => setImageNodeRef(el, index)"
                            class="relative pl-8 md:pl-0"
                            :class="
                                index % 2 === 0 ? 'md:order-1' : 'md:order-2'
                            "
                        >
                            <div
                                class="absolute top-8 left-2 size-3 rounded-full bg-rose-400 shadow-[0_0_0_7px_rgba(251,207,232,0.65)] md:hidden"
                            />
                            <img
                                :src="memory.image"
                                :alt="memory.title"
                                class="h-44 w-full rounded-3xl border-2 border-rose-100 object-cover shadow-[0_14px_34px_rgba(244,114,182,0.28)] sm:h-52"
                            />
                        </div>

                        <div
                            :ref="(el) => setCardNodeRef(el, index)"
                            class="rounded-3xl border border-rose-100 bg-white/80 p-6 shadow-[0_18px_38px_rgba(251,113,133,0.18)] backdrop-blur-sm md:p-7"
                            :class="
                                index % 2 === 0 ? 'md:order-2' : 'md:order-1'
                            "
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.2em] text-rose-500 uppercase"
                            >
                                {{ memory.date }}
                            </p>
                            <h3
                                class="mt-2 font-['Playfair_Display'] text-2xl text-rose-800"
                            >
                                {{ memory.title }}
                            </h3>
                            <p class="mt-3 leading-relaxed text-rose-700/90">
                                {{ memory.description }}
                            </p>
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
</style>
