<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { gsap } from 'gsap';
import { Draggable } from 'gsap/Draggable';

const props = defineProps({
    feedbacks: {
        type: Array,
        default: () => [
            {
                name: 'May',
                message: 'Every moment with you feels soft, warm, and magical.',
                tone: 'bg-rose-100',
            },
            {
                name: 'Ko',
                message:
                    'Our little memories are my favorite place to live in.',
                tone: 'bg-pink-100',
            },
            {
                name: 'Yuki',
                message: 'You make ordinary days look like love letters.',
                tone: 'bg-fuchsia-100',
            },
            {
                name: 'Nora',
                message: 'This space feels like a scrapbook of pure happiness.',
                tone: 'bg-rose-50',
            },
            {
                name: 'Ari',
                message: 'Still falling for you in new ways, every single day.',
                tone: 'bg-pink-50',
            },
            {
                name: 'Su',
                message: 'Our story is gentle, beautiful, and forever mine.',
                tone: 'bg-red-50',
            },
        ],
    },
});

const sectionRef = ref(null);
const trackRef = ref(null);
const cardRefs = ref([]);

let draggableInstance = null;
let resizeHandler = null;
let wallWidth = 0;
let maxLoopDistance = 0;
let currentX = 0;
let autoRunning = true;
let tickerAttached = false;
let currentSpeed = 0;
let targetSpeed = 0;
let lastSampleX = 0;
let lastSampleTime = 0;
let releaseMomentumSpeed = AUTO_SPEED_PX_PER_SEC;

const AUTO_SPEED_PX_PER_SEC = 34;
const SPEED_LERP = 0.08;

const repeatedFeedbacks = computed(() => [
    ...props.feedbacks,
    ...props.feedbacks,
]);

const setCardRef = (el, index) => {
    if (!el) return;
    cardRefs.value[index] = el;
};

const wrapX = (value) => {
    if (!maxLoopDistance) return value;
    return gsap.utils.wrap(-maxLoopDistance, 0, value);
};

const pauseAuto = () => {
    autoRunning = false;
};

const playAuto = () => {
    autoRunning = true;
};

const startVelocitySampling = () => {
    lastSampleX = Number(gsap.getProperty(trackRef.value, 'x')) || 0;
    lastSampleTime =
        typeof performance !== 'undefined' ? performance.now() : Date.now();
};

const sampleDragVelocity = () => {
    const now =
        typeof performance !== 'undefined' ? performance.now() : Date.now();
    const x = Number(gsap.getProperty(trackRef.value, 'x')) || 0;
    const dt = (now - lastSampleTime) / 1000;
    if (dt <= 0) return;

    const velocity = Math.abs((x - lastSampleX) / dt);
    if (Number.isFinite(velocity)) {
        currentSpeed = Math.min(velocity, 1400);
        targetSpeed = AUTO_SPEED_PX_PER_SEC;
    }

    lastSampleX = x;
    lastSampleTime = now;
};

const captureReleaseSpeed = (draggable) => {
    const pluginVelocity =
        typeof draggable?.getVelocity === 'function'
            ? Math.abs(Number(draggable.getVelocity('x')) || 0)
            : 0;
    const sampledVelocity = Math.abs(currentSpeed || 0);
    const picked = Math.max(
        pluginVelocity,
        sampledVelocity,
        AUTO_SPEED_PX_PER_SEC,
    );
    releaseMomentumSpeed = Math.min(picked, 1400);
};

const setupDraggable = () => {
    if (!trackRef.value) return;
    draggableInstance?.kill();

    draggableInstance = Draggable.create(trackRef.value, {
        type: 'x',
        inertia: true,
        onPress() {
            pauseAuto();
            startVelocitySampling();
        },
        onDrag() {
            currentX = wrapX(this.x);
            gsap.set(this.target, { x: currentX });
            sampleDragVelocity();
        },
        onThrowUpdate() {
            currentX = wrapX(this.x);
            gsap.set(this.target, { x: currentX });
            sampleDragVelocity();
        },
        onThrowComplete() {
            currentX = wrapX(Number(gsap.getProperty(this.target, 'x')) || 0);
            gsap.set(this.target, { x: currentX });
            currentSpeed = releaseMomentumSpeed;
            playAuto();
        },
        onRelease() {
            captureReleaseSpeed(this);
            if (!this.tween || !this.tween.isActive()) {
                currentX = wrapX(
                    Number(gsap.getProperty(this.target, 'x')) || 0,
                );
                gsap.set(this.target, { x: currentX });
                currentSpeed = releaseMomentumSpeed;
                playAuto();
            }
        },
    })[0];
};

const tickerUpdate = () => {
    if (!trackRef.value || !maxLoopDistance || !autoRunning) return;

    const deltaRatio = gsap.ticker.deltaRatio(60);
    currentSpeed += (targetSpeed - currentSpeed) * SPEED_LERP;
    const step = (currentSpeed / 60) * deltaRatio;
    currentX = wrapX(currentX - step);
    gsap.set(trackRef.value, { x: currentX });
};

const ensureTicker = () => {
    if (tickerAttached) return;
    gsap.ticker.add(tickerUpdate);
    tickerAttached = true;
};

const removeTicker = () => {
    if (!tickerAttached) return;
    gsap.ticker.remove(tickerUpdate);
    tickerAttached = false;
};

const measure = async () => {
    await nextTick();
    if (!trackRef.value || !cardRefs.value.length) return;

    const half = cardRefs.value.length / 2;
    wallWidth = cardRefs.value
        .slice(0, half)
        .reduce((sum, card) => sum + card.offsetWidth, 0);

    maxLoopDistance = wallWidth;
};

const initializeWall = async () => {
    const canUseDom = typeof window !== 'undefined';
    if (!canUseDom) return;

    await measure();
    currentX = wrapX(Number(gsap.getProperty(trackRef.value, 'x')) || 0);
    gsap.set(trackRef.value, { x: currentX });
    currentSpeed = AUTO_SPEED_PX_PER_SEC;
    targetSpeed = AUTO_SPEED_PX_PER_SEC;
    playAuto();
    ensureTicker();
    setupDraggable();
};

onMounted(async () => {
    gsap.registerPlugin(Draggable);
    await initializeWall();

    resizeHandler = async () => {
        await initializeWall();
    };
    window.addEventListener('resize', resizeHandler);
});

onBeforeUnmount(() => {
    removeTicker();
    draggableInstance?.kill();
    if (resizeHandler && typeof window !== 'undefined') {
        window.removeEventListener('resize', resizeHandler);
    }
});
</script>

<template>
    <section
        ref="sectionRef"
        class="relative w-full overflow-hidden bg-rose-50 px-0 py-16 md:py-24"
    >
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
                class="w-full overflow-hidden border-y border-rose-100/70 bg-white/45 px-4 py-8 shadow-[0_18px_40px_rgba(251,113,133,0.16)] backdrop-blur-sm md:px-6 md:py-10"
                @mouseenter="pauseAuto"
                @mouseleave="playAuto"
            >
                <div
                    ref="trackRef"
                    class="flex w-max gap-10 py-10 will-change-transform md:gap-20"
                >
                    <article
                        v-for="(feedback, index) in repeatedFeedbacks"
                        :key="`${feedback.name}-${index}`"
                        :ref="(el) => setCardRef(el, index)"
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

.wavy-card {
    border-radius: 1.75rem 2rem 1.6rem 1.9rem / 1.8rem 1.6rem 2rem 1.7rem;
}

.wavy-card-alt {
    border-radius: 1.95rem 1.65rem 1.9rem 1.7rem / 1.65rem 1.95rem 1.7rem 1.9rem;
}
</style>
