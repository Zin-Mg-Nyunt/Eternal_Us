<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

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
        class="relative min-h-screen overflow-hidden bg-rose-50 text-white"
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
            class="hero-wave-clip absolute inset-x-0 top-0 -bottom-4 md:-bottom-32"
        >
            <img
                src="/image/hero.webp"
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
            class="relative z-10 mx-auto flex min-h-screen max-w-6xl items-center justify-center px-6 py-16 sm:px-10"
        >
            <div
                class="w-full rounded-3xl border border-white/70 bg-white/55 p-7 shadow-2xl shadow-rose-200/50 backdrop-blur-md sm:p-10 lg:max-w-3xl"
            >
                <p class="mb-3 text-sm tracking-[0.26em] text-rose-700/90">
                    OUR LOVE STORY
                </p>

                <h1
                    class="font-romantic text-5xl leading-tight text-rose-900 drop-shadow-sm sm:text-6xl lg:text-7xl"
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
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Great+Vibes&display=swap');

.font-romantic {
    font-family: 'Great Vibes', 'Playfair Display', serif;
}

.timer-card {
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.75);
    background: rgba(255, 255, 255, 0.65);
    box-shadow: 0 10px 24px rgba(255, 155, 188, 0.2);
    padding: 0.9rem 0.65rem;
    text-align: center;
    backdrop-filter: blur(3px);
}

.timer-value {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.4rem, 3vw, 2.2rem);
    line-height: 1;
    color: #881337;
}

.timer-label {
    margin-top: 0.45rem;
    font-size: 0.73rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(136, 19, 55, 0.85);
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

@media (max-width: 767px) {
    .hero-wave-clip {
        clip-path: url(#hero-wave-clip-mobile);
        -webkit-clip-path: url(#hero-wave-clip-mobile);
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
