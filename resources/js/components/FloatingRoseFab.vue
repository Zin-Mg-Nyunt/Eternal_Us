<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const emit = defineEmits(['select']);

const actions = [
    { id: 'journey', label: 'Love Journey' },
    { id: 'gallery', label: 'Gallery' },
    { id: 'hero', label: 'Change Background' },
    { id: 'manage', label: 'Manage Photos' },
];

const rootRef = ref(null);
const menuOpen = ref(false);
const isMobile = ref(false);

const updateViewport = () => {
    isMobile.value = window.innerWidth < 768;
};

const actionOffset = (index) => {
    const radius = isMobile.value ? 92 : 110;
    const angles = isMobile.value
        ? [-166, -136, -106, -78]
        : [-170, -143, -116, -89];
    const angle = ((angles[index] ?? -120) * Math.PI) / 180;
    return {
        x: Math.cos(angle) * radius,
        y: Math.sin(angle) * radius,
    };
};

const actionStyle = (index) => {
    const { x, y } = actionOffset(index);
    return {
        '--fab-x': `${x}px`,
        '--fab-y': `${y}px`,
        '--fab-delay': `${index * 55}ms`,
    };
};

const closeMenu = () => {
    if (!menuOpen.value) return;
    menuOpen.value = false;
};

const toggleMenu = () => {
    menuOpen.value = !menuOpen.value;
};

const onActionClick = (id) => {
    emit('select', id);
    closeMenu();
};

const onClickOutside = (event) => {
    if (!menuOpen.value) return;
    if (rootRef.value?.contains(event.target)) return;
    closeMenu();
};

const onKeydown = (event) => {
    if (event.key === 'Escape') closeMenu();
};

onMounted(() => {
    updateViewport();
    document.addEventListener('mousedown', onClickOutside);
    window.addEventListener('keydown', onKeydown);
    window.addEventListener('resize', updateViewport);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside);
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('resize', updateViewport);
});
</script>

<template>
    <div ref="rootRef" class="fixed right-6 bottom-6 z-50">
        <div
            v-for="(action, index) in actions"
            :key="action.id"
            class="action-item absolute right-0 bottom-0"
            :class="{ 'is-open': menuOpen }"
            :style="actionStyle(index)"
        >
            <button
                type="button"
                class="peer/icon grid h-10 w-10 place-items-center rounded-full border border-rose-200 bg-rose-100 text-[#b76e79] shadow-[0_8px_16px_rgba(244,114,182,0.22)] transition hover:scale-105"
                @click="onActionClick(action.id)"
            >
                <svg
                    v-if="action.id === 'journey'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M12 3v18M3 12h18" />
                </svg>
                <svg
                    v-else-if="action.id === 'gallery'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <circle cx="9" cy="10" r="1.5" />
                    <path d="M21 16l-5-5-6 6" />
                </svg>
                <svg
                    v-else-if="action.id === 'hero'"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M3 5h18v6H3zM3 13h8v6H3zM13 13h8v6h-8z" />
                </svg>
                <svg
                    v-else
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        d="M4 20h4l10.5-10.5a1.4 1.4 0 000-2L16.5 5.5a1.4 1.4 0 00-2 0L4 16v4zM13.5 7.5l3 3"
                    />
                </svg>
            </button>
            <span
                class="pointer-events-none absolute top-1/2 right-full mr-2 -translate-x-1 -translate-y-1/2 scale-95 rounded-full border border-rose-200 bg-white/95 px-2.5 py-0.5 text-[11px] font-medium whitespace-nowrap text-rose-700 opacity-0 shadow-[0_6px_14px_rgba(244,114,182,0.2)] backdrop-blur-sm transition-all duration-200 peer-hover/icon:translate-x-0 peer-hover/icon:scale-100 peer-hover/icon:opacity-100 peer-focus-visible/icon:translate-x-0 peer-focus-visible/icon:scale-100 peer-focus-visible/icon:opacity-100"
            >
                {{ action.label }}
            </span>
        </div>

        <button
            type="button"
            class="fab-main group relative grid h-12 w-12 cursor-pointer place-items-center rounded-full border border-rose-200 bg-rose-100/85 shadow-[0_0_0_6px_rgba(244,114,182,0.24),0_14px_24px_rgba(244,114,182,0.34)] backdrop-blur-sm transition hover:scale-110 hover:rotate-6 md:h-15 md:w-15"
            :class="{ 'is-open': menuOpen }"
            aria-label="Open love quick actions"
            @click="toggleMenu"
        >
            <img
                src="/image/pink-rose.png"
                alt="Pink rose action button"
                class="h-8 w-8 object-contain md:h-12 md:w-12"
            />
        </button>
    </div>
</template>

<style scoped>
.action-item {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translate3d(0, 0, 0) scale(0.6);
    transition:
        transform 0.48s cubic-bezier(0.2, 0.85, 0.25, 1.15),
        opacity 0.22s ease,
        visibility 0.22s step-end;
    transition-delay: 0ms;
}

.action-item.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translate3d(var(--fab-x, 0), var(--fab-y, 0), 0) scale(1);
    transition-delay: var(--fab-delay, 0ms);
}

.fab-main {
    animation: fab-bob 1.9s ease-in-out infinite alternate;
}

.fab-main.is-open {
    animation-play-state: paused;
}

@keyframes fab-bob {
    from {
        transform: translateY(0);
    }
    to {
        transform: translateY(-8px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .action-item,
    .action-item.is-open,
    .fab-main {
        animation: none;
        transition-duration: 0.01ms;
        transition-delay: 0ms;
    }
}
</style>
