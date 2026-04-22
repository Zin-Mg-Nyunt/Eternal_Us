<script setup>
import { nextTick, onBeforeUnmount, ref } from 'vue';
import { gsap } from 'gsap';

const PETAL_COUNT = 20;

const name = ref('');
const message = ref('');
const showFieldError = ref(false);
const petals = ref([]);
const successBalloons = ref([]);

const sectionRef = ref(null);
const formCardRef = ref(null);
const formShellRef = ref(null);
const petalContainerRef = ref(null);
const petalRefs = ref([]);
const balloonRefs = ref([]);
let petalBurstTimeline = null;
let successTimeline = null;

const setPetalRef = (el, index) => {
    if (!el) return;
    petalRefs.value[index] = el;
};

const setBalloonRef = (el, index) => {
    if (!el) return;
    balloonRefs.value[index] = el;
};

const clearRefCollections = () => {
    petalRefs.value = [];
    balloonRefs.value = [];
};

const createPetals = () => {
    petals.value = Array.from({ length: PETAL_COUNT }, (_, index) => ({
        id: `${Date.now()}-${index}`,
        size: `${25 + Math.random() * 14}px`,
        opacity: (0.45 + Math.random() * 0.35).toFixed(2),
    }));
};

const createSuccessBalloons = () => {
    const total = Math.floor(gsap.utils.random(8, 13));
    successBalloons.value = Array.from({ length: total }, (_, index) => ({
        id: `${Date.now()}-balloon-${index}`,
        left: `${gsap.utils.random(6, 92)}%`,
        scale: gsap.utils.random(0.8, 1.2).toFixed(2),
        rotation: gsap.utils.random(-15, 15).toFixed(2),
    }));
};

const getBorderSpawnPoint = (width, height) => {
    const margin = 10;
    const cornerBias = Math.random() < 0.7;

    if (cornerBias) {
        const corner = Math.floor(Math.random() * 4);
        const near = gsap.utils.random(0, Math.min(width, height) * 0.2);

        if (corner === 0) {
            if (Math.random() < 0.5)
                return { x: margin + near, y: margin, side: 'top' };
            return { x: margin, y: margin + near, side: 'left' };
        }
        if (corner === 1) {
            if (Math.random() < 0.5)
                return { x: width - margin - near, y: margin, side: 'top' };
            return { x: width - margin, y: margin + near, side: 'right' };
        }
        if (corner === 2) {
            if (Math.random() < 0.5)
                return {
                    x: width - margin - near,
                    y: height - margin,
                    side: 'bottom',
                };
            return {
                x: width - margin,
                y: height - margin - near,
                side: 'right',
            };
        }
        if (Math.random() < 0.5)
            return { x: margin + near, y: height - margin, side: 'bottom' };
        return { x: margin, y: height - margin - near, side: 'left' };
    }

    const side = ['top', 'right', 'bottom', 'left'][
        Math.floor(Math.random() * 4)
    ];
    if (side === 'top')
        return {
            x: gsap.utils.random(margin, width - margin),
            y: margin,
            side,
        };
    if (side === 'right')
        return {
            x: width - margin,
            y: gsap.utils.random(margin, height - margin),
            side,
        };
    if (side === 'bottom')
        return {
            x: gsap.utils.random(margin, width - margin),
            y: height - margin,
            side,
        };
    return { x: margin, y: gsap.utils.random(margin, height - margin), side };
};

const getLaunchAngleBySide = (side) => {
    if (side === 'top') return gsap.utils.random(-145, -35);
    if (side === 'right') return gsap.utils.random(-55, 55);
    if (side === 'bottom') return gsap.utils.random(35, 145);
    return gsap.utils.random(125, 235);
};

const playErrorAnimation = async () => {
    if (!formCardRef.value || !formShellRef.value || !petalContainerRef.value)
        return;

    await nextTick();
    createPetals();
    await nextTick();

    const formBox = formCardRef.value.getBoundingClientRect();
    const shellBox = formShellRef.value.getBoundingClientRect();
    const formOffsetX = formBox.left - shellBox.left;
    const formOffsetY = formBox.top - shellBox.top;

    gsap.killTweensOf(formCardRef.value);
    gsap.killTweensOf(petalRefs.value);
    petalBurstTimeline?.kill();

    gsap.set(petalRefs.value, {
        opacity: 0,
        scale: () => gsap.utils.random(0.4, 0.8),
        transformOrigin: 'center center',
        x: 0,
        y: 0,
        rotation: () => gsap.utils.random(0, 360),
    });

    petalBurstTimeline = gsap.timeline({
        onComplete: () => {
            petals.value = [];
            clearRefCollections();
            petalBurstTimeline = null;
        },
    });

    petalBurstTimeline
        .to(
            formCardRef.value,
            {
                keyframes: [{ x: -5 }, { x: 5 }],
                duration: 0.09,
                repeat: 4,
                yoyo: true,
                ease: 'power1.inOut',
            },
            0,
        )
        .to(formCardRef.value, {
            x: 0,
            duration: 0.1,
            ease: 'power2.out',
            clearProps: 'x',
        });

    petalRefs.value.forEach((petal, index) => {
        if (!petal) return;
        const spawn = getBorderSpawnPoint(formBox.width, formBox.height);
        const angleDeg = getLaunchAngleBySide(spawn.side);
        const angleRad = (angleDeg * Math.PI) / 180;
        const distance = gsap.utils.random(150, 340);
        const burstX = Math.cos(angleRad) * distance;
        const burstY = Math.sin(angleRad) * distance;
        const arcX1 = burstX * 0.52;
        const arcY1 = burstY * 0.52;
        const arcX2 = burstX * 0.42 + gsap.utils.random(-38, 38);
        const arcY2 = Math.abs(burstY) * 0.9 + gsap.utils.random(130, 220);
        const launchAt = index * 0.02;

        gsap.set(petal, {
            x: formOffsetX + spawn.x,
            y: formOffsetY + spawn.y,
            opacity: 0,
            scale: gsap.utils.random(0.45, 1),
            rotation: gsap.utils.random(-120, 120),
        });

        petalBurstTimeline.to(
            petal,
            {
                keyframes: [
                    {
                        opacity: gsap.utils.random(0.8, 0.96),
                        x: `+=${arcX1.toFixed(2)}`,
                        y: `+=${arcY1.toFixed(2)}`,
                        rotation: `+=${gsap.utils.random(-170, 170).toFixed(2)}`,
                        scale: gsap.utils.random(0.75, 1.08),
                        duration: gsap.utils.random(0.62, 0.82),
                        ease: 'sine.out',
                    },
                    {
                        x: `+=${arcX2.toFixed(2)}`,
                        y: `+=${arcY2.toFixed(2)}`,
                        opacity: 0,
                        scale: gsap.utils.random(0.48, 0.82),
                        rotation: `+=${gsap.utils.random(-180, 180).toFixed(2)}`,
                        duration: gsap.utils.random(0.68, 0.88),
                        ease: 'sine.in',
                    },
                ],
            },
            launchAt,
        );
    });
};

const playSuccessAnimation = async () => {
    createSuccessBalloons();
    await nextTick();
    if (!sectionRef.value || !balloonRefs.value.length) return;

    successTimeline?.kill();
    gsap.killTweensOf(balloonRefs.value);

    gsap.set(balloonRefs.value, {
        opacity: 0.96,
        y: '100vh',
        xPercent: -50,
        transformOrigin: 'center center',
    });

    successTimeline = gsap.timeline({
        onComplete: () => {
            successBalloons.value = [];
            balloonRefs.value = [];
            successTimeline = null;
        },
    });

    successTimeline.to(balloonRefs.value, {
        y: '-20vh',
        opacity: 0.98,
        duration: () => gsap.utils.random(4.8, 7.2),
        ease: 'none',
        stagger: { amount: 1.5, from: 'random' },
    });

    gsap.to(balloonRefs.value, {
        x: '+=30',
        repeat: -1,
        yoyo: true,
        duration: () => gsap.utils.random(1, 1.6),
        ease: 'sine.inOut',
        stagger: { amount: 1.5, from: 'random' },
    });

    successTimeline.to(
        balloonRefs.value,
        {
            opacity: 0,
            duration: 0.55,
            ease: 'power1.in',
        },
        '>-0.5',
    );
};

const onSubmit = async () => {
    const valid = name.value.trim() && message.value.trim();
    if (!valid) {
        showFieldError.value = true;
        await playErrorAnimation();
        return;
    }

    showFieldError.value = false;
    name.value = '';
    message.value = '';
    await playSuccessAnimation();
};

onBeforeUnmount(() => {
    petalBurstTimeline?.kill();
    successTimeline?.kill();
    gsap.killTweensOf(formCardRef.value);
    gsap.killTweensOf(petalRefs.value);
    gsap.killTweensOf(balloonRefs.value);
    clearRefCollections();
});
</script>

<template>
    <section
        ref="sectionRef"
        class="relative isolate overflow-hidden bg-rose-50 px-4 py-16 sm:px-6 md:py-24"
    >
        <div class="mx-auto max-w-4xl">
            <div class="mb-8 text-center md:mb-12">
                <p class="text-sm tracking-[0.22em] text-rose-500 uppercase">
                    A Petal for Your Thoughts
                </p>
                <h2 class="font-title mt-2 text-4xl text-rose-800 sm:text-5xl">
                    Sign Our Love Story
                </h2>
            </div>

            <div ref="formShellRef" class="relative mx-auto max-w-2xl">
                <div
                    ref="petalContainerRef"
                    class="pointer-events-none absolute inset-0 z-5 overflow-visible"
                >
                    <img
                        v-for="(petal, index) in petals"
                        :key="petal.id"
                        :ref="(el) => setPetalRef(el, index)"
                        src="/image/pink_rose_petal.png"
                        alt="pink rose petal"
                        class="rose-petal burst-petal absolute"
                        :style="{
                            width: petal.size,
                            height: petal.size,
                            opacity: petal.opacity,
                        }"
                    />
                </div>
                <form
                    ref="formCardRef"
                    class="love-card relative z-10 p-5 sm:p-7"
                    style="
                        border-radius: 24px 30px 20px 28px / 22px 24px 30px 20px;
                    "
                    @submit.prevent="onSubmit"
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
                    <div class="relative z-10 space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold tracking-[0.14em] text-rose-600 uppercase"
                                >Name</label
                            >
                            <input
                                v-model="name"
                                type="text"
                                class="love-input"
                                placeholder="Your lovely name"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold tracking-[0.14em] text-rose-600 uppercase"
                                >Message</label
                            >
                            <textarea
                                v-model="message"
                                rows="4"
                                class="love-input love-textarea"
                                placeholder="Write your sweet love message..."
                            />
                        </div>
                        <p v-if="showFieldError" class="text-sm text-rose-600">
                            Please fill in both name and message.
                        </p>
                        <button
                            type="submit"
                            class="rounded-full border border-rose-300 bg-linear-to-r from-rose-500 to-pink-400 px-6 py-2 font-semibold text-white shadow-[0_10px_22px_rgba(244,114,182,0.35)] transition hover:scale-[1.02] hover:from-rose-600 hover:to-pink-500"
                        >
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div
            v-if="successBalloons.length"
            class="pointer-events-none absolute inset-0 z-20 overflow-hidden"
        >
            <div
                v-for="(balloon, index) in successBalloons"
                :key="balloon.id"
                :ref="(el) => setBalloonRef(el, index)"
                class="absolute top-0"
                :style="{
                    left: balloon.left,
                    transform: `translateX(-50%) scale(${balloon.scale}) rotate(${balloon.rotation}deg)`,
                }"
            >
                <svg
                    class="h-40 w-32 text-pink-400 drop-shadow-[0_10px_16px_rgba(244,114,182,0.34)]"
                    viewBox="0 0 24 32"
                    fill="currentColor"
                >
                    <path
                        d="M12 2c5.4 0 9 4.3 9 9.1C21 16.8 16.6 21 12 25 7.4 21 3 16.8 3 11.1 3 6.3 6.6 2 12 2z"
                    />
                    <path
                        d="M12 25v6"
                        stroke="#be123c"
                        stroke-width="1.5"
                        fill="none"
                    />
                </svg>
                <span
                    class="thank-you-balloon-text absolute top-[36%] left-1/2 -translate-x-1/2 -translate-y-1/2 text-xl font-bold text-wrap text-white"
                >
                    Thank You
                </span>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Soft, cute love-note style card */
.love-card {
    background:
        radial-gradient(
            540px 260px at 20% 5%,
            rgba(251, 207, 232, 0.35),
            transparent 65%
        ),
        radial-gradient(
            480px 220px at 88% 92%,
            rgba(254, 205, 211, 0.28),
            transparent 68%
        ),
        linear-gradient(155deg, #fff8fb 0%, #fff1f2 100%);
    border: 2px dashed #fbcfe8;
    box-shadow:
        0 0 0 6px rgba(253, 164, 175, 0.15),
        0 18px 40px rgba(244, 114, 182, 0.16);
}

.love-input {
    width: 100%;
    border: 1.5px solid #f9a8d4;
    outline: none;
    background: rgba(255, 255, 255, 0.92);
    color: rgba(136, 19, 55, 0.92);
    padding: 0.72rem 1rem;
    border-radius: 1rem;
    position: relative;
    box-shadow: 0 8px 16px rgba(251, 113, 133, 0.08);
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease,
        transform 180ms ease;
}

.love-textarea {
    resize: none;
}

.love-input:focus {
    border-color: #f472b6;
    transform: translateY(-1px);
    box-shadow:
        0 0 0 4px rgba(244, 114, 182, 0.22),
        0 14px 24px rgba(251, 113, 133, 0.14);
}

.rose-petal {
    display: block;
    object-fit: contain;
    filter: drop-shadow(0 3px 8px rgba(255, 163, 198, 0.38));
}

.burst-petal {
    transform-origin: center center;
}

.thank-you-balloon-text {
    font-family: 'Great Vibes', cursive;
    white-space: nowrap;
}
</style>
