<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    coverImage: {
        type: String,
        default: null,
    },
});

const relationshipStartedAt = '2020-08-29T23:48:00';
const startDate = new Date(relationshipStartedAt);
const now = ref(new Date());
const formattedStartDate = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
}).format(startDate);

const timer = computed(() => {
    const current = new Date(now.value);

    let years = current.getFullYear() - startDate.getFullYear();
    let months = current.getMonth() - startDate.getMonth();
    let days = current.getDate() - startDate.getDate();

    if (days < 0) {
        months -= 1;
        const previousMonthDays = new Date(
            current.getFullYear(),
            current.getMonth(),
            0,
        ).getDate();
        days += previousMonthDays;
    }

    if (months < 0) {
        years -= 1;
        months += 12;
    }

    return {
        years: Math.max(0, years),
        months: Math.max(0, months),
        days: Math.max(0, days),
    };
});

const petals = Array.from({ length: 16 }, (_, index) => ({
    id: index + 1,
    left: `${Math.random() * 100}%`,
    delay: `${Math.random() * 8}s`,
    duration: `${9 + Math.random() * 9}s`,
    size: `${26 + Math.random() * 24}px`,
    drift: `${(Math.random() * 80 - 40).toFixed(2)}px`,
    opacity: (0.72 + Math.random() * 0.28).toFixed(2),
}));

let intervalId;

onMounted(() => {
    intervalId = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onBeforeUnmount(() => {
    clearInterval(intervalId);
});
</script>

<template>
    <section
        class="hero-shell relative -mb-1 overflow-hidden bg-rose-50 text-white"
    >
        <svg class="absolute h-0 w-0" aria-hidden="true" focusable="false">
            <defs>
                <clipPath
                    id="hero-wave-clip-desktop"
                    clipPathUnits="objectBoundingBox"
                >
                    <path
                        d="M0,0 L1,0 L1,0.78 C0.95,0.84 0.9,0.9 0.84,0.86 C0.78,0.82 0.72,0.74 0.66,0.78 C0.6,0.82 0.54,0.9 0.48,0.84 C0.42,0.78 0.36,0.7 0.3,0.76 C0.24,0.82 0.18,0.92 0.12,0.86 C0.06,0.8 0.03,0.74 0,0.8 Z"
                    />
                </clipPath>
                <clipPath
                    id="hero-wave-clip-mobile"
                    clipPathUnits="objectBoundingBox"
                >
                    <path
                        d="M0,0 L1,0 L1,0.9 C0.84,0.94 0.68,0.86 0.52,0.88 C0.36,0.9 0.2,0.96 0,0.91 Z"
                    />
                </clipPath>
            </defs>
        </svg>

        <div
            class="hero-wave-clip hero-wave-layer absolute inset-x-0 top-0 bottom-0"
        >
            <img
                v-if="props.coverImage"
                :src="props.coverImage"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
                loading="eager"
                fetchpriority="high"
                decoding="async"
            />
            <div
                class="absolute inset-0 bg-linear-to-br from-white/55 via-rose-100/40 to-rose-200/45"
            />

            <div class="pointer-events-none absolute inset-0 z-2">
                <img
                    v-for="petal in petals"
                    :key="petal.id"
                    src="/image/pink_rose_petal.png"
                    alt="pink rose petal"
                    class="rose-petal object-contain"
                    :style="{
                        left: petal.left,
                        animationDelay: petal.delay,
                        animationDuration: petal.duration,
                        width: petal.size,
                        height: petal.size,
                        '--drift': petal.drift,
                        opacity: petal.opacity,
                    }"
                />
            </div>
        </div>

        <div
            class="hero-content relative z-10 mx-auto flex min-h-screen max-w-6xl items-center justify-center px-6 py-16 sm:px-10"
        >
            <div
                class="hero-card w-full rounded-3xl border border-white/70 bg-white/55 p-7 shadow-2xl shadow-rose-200/50 backdrop-blur-md sm:p-10 lg:max-w-3xl"
            >
                <p class="mb-3 text-sm tracking-[0.26em] text-rose-700/90">
                    OUR LOVE STORY
                </p>

                <h1
                    class="font-['Great_Vibes'] text-5xl leading-tight font-normal text-rose-900 drop-shadow-sm sm:text-6xl lg:text-7xl"
                >
                    Eternal Us
                </h1>

                <p
                    class="mt-6 max-w-2xl text-base leading-relaxed text-rose-800/90 sm:text-lg"
                >
                    Where every second counts in our journey together.
                </p>
                <p class="mt-3 text-sm text-rose-700/85">
                    Since {{ formattedStartDate }}
                </p>

                <div class="mt-10 grid grid-cols-3 gap-3 sm:gap-4">
                    <div class="timer-card">
                        <p class="timer-value">{{ timer.years }}</p>
                        <p class="timer-label">Years</p>
                    </div>
                    <div class="timer-card">
                        <p class="timer-value">{{ timer.months }}</p>
                        <p class="timer-label">Months</p>
                    </div>
                    <div class="timer-card">
                        <p class="timer-value">{{ timer.days }}</p>
                        <p class="timer-label">Days</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap');

.timer-card {
    position: relative;
    isolation: isolate;
    display: grid;
    align-content: center;
    justify-items: center;
    gap: clamp(0.16rem, 0.6vw, 0.35rem);
    aspect-ratio: 1 / 1;
    min-height: clamp(8.6rem, 23vw, 10.6rem);
    padding: clamp(1.8rem, 5.8vw, 2.7rem) clamp(0.5rem, 1.8vw, 0.9rem)
        clamp(1rem, 2.8vw, 1.35rem);
    text-align: center;
    overflow: hidden;
}

.timer-card::before {
    content: '';
    position: absolute;
    inset: clamp(0rem, 0.45vw, 0.24rem);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 110'%3E%3Cpath d='M60 100C43 84 14 63 14 37C14 22 26 10 41 10C50 10 57 14 60 21C63 14 70 10 79 10C94 10 106 22 106 37C106 63 77 84 60 100Z' fill='none' stroke='rgba(255,255,255,0.92)' stroke-width='3'/%3E%3C/svg%3E");
    background-position: center;
    background-repeat: no-repeat;
    background-size: 106% 106%;
    filter: drop-shadow(0 10px 20px rgba(255, 155, 188, 0.26));
    z-index: 0;
}

.timer-value {
    position: relative;
    z-index: 1;
    font-family: 'Playfair Display', serif;
    width: 100%;
    line-height: 1.05;
    font-size: clamp(0.98rem, 2.85vw, 1.72rem);
    color: #881337;
}

.timer-label {
    position: relative;
    z-index: 1;
    width: 100%;
    line-height: 1.05;
    font-size: clamp(0.72rem, 1.45vw, 0.95rem);
    font-family: 'Great Vibes', cursive;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: #881337;
}

.rose-petal {
    position: absolute;
    top: -10%;
    animation: petal-fall linear infinite;
    filter: drop-shadow(0 6px 10px rgba(236, 72, 153, 0.38));
}

.hero-wave-clip {
    clip-path: url(#hero-wave-clip-desktop);
    -webkit-clip-path: url(#hero-wave-clip-desktop);
}

.hero-shell {
    min-height: 100svh;
}

.hero-content {
    min-height: 100svh;
}

@supports (height: 100dvh) {
    .hero-shell,
    .hero-content {
        min-height: 100dvh;
    }
}

.hero-wave-layer {
    transform: translateZ(0);
    will-change: clip-path;
}

@media (max-width: 767px) {
    .hero-wave-clip {
        clip-path: url(#hero-wave-clip-mobile);
        -webkit-clip-path: url(#hero-wave-clip-mobile);
    }
}

@media (max-height: 760px) {
    .hero-content {
        align-items: flex-start;
        padding-top: 4.75rem;
        padding-bottom: 6.5rem;
    }

    .hero-card {
        padding: 1.2rem;
    }
}

@media (min-width: 640px) {
    .timer-card {
        min-height: clamp(9.5rem, 16vw, 11rem);
        padding: clamp(2.1rem, 3.6vw, 2.7rem) 0.9rem clamp(1.25rem, 2vw, 1.5rem);
    }
}

@media (max-width: 420px) {
    .timer-card {
        min-height: 8.35rem;
        padding: 1.65rem 0.42rem 0.9rem;
    }

    .timer-value {
        font-size: clamp(0.9rem, 4.1vw, 1.26rem);
    }

    .timer-label {
        font-size: clamp(0.66rem, 3vw, 0.8rem);
    }
}

@media (max-height: 700px) {
    .hero-content {
        padding-top: 4.15rem;
        padding-bottom: 6rem;
    }

    .hero-wave-layer {
        bottom: -0.75rem;
    }
}

@keyframes petal-fall {
    0% {
        transform: translate3d(0, -8vh, 0) rotate(0deg) scale(0.95);
    }

    25% {
        transform: translate3d(calc(var(--drift) * 0.4), 22vh, 0) rotate(75deg)
            scale(1);
    }

    65% {
        transform: translate3d(calc(var(--drift) * -0.5), 66vh, 0)
            rotate(215deg) scale(0.92);
    }

    100% {
        transform: translate3d(var(--drift), 115vh, 0) rotate(325deg)
            scale(0.86);
    }
}
</style>
