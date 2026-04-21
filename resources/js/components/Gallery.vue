<script setup>
const props = defineProps({
    images: {
        type: Array,
        default: () => [
            {
                src: './image/hero.jpg',
                alt: 'A sweet date memory',
                showSticker: true,
            },
            {
                src: './image/two.jpg',
                alt: 'Holding hands under evening sky',
            },
            {
                src: './image/hero.jpg',
                alt: 'A cozy coffee shop moment',
                showSticker: true,
            },
            {
                src: './image/four.jpg',
                alt: 'Laughter and sunshine together',
            },
            {
                src: './image/one.jpg',
                alt: 'Travel memory we cherish',
                showSticker: true,
            },
            {
                src: './image/three.jpg',
                alt: 'A peaceful walk side by side',
            },
            {
                src: './image/two.jpg',
                alt: 'Festival lights and warm smiles',
            },
            {
                src: './image/hero.jpg',
                alt: 'Our favorite little adventure',
                showSticker: true,
            },
            {
                src: './image/four.jpg',
                alt: 'Golden hour by the riverside',
            },
            {
                src: './image/two.jpg',
                alt: 'A candid smile we love forever',
                showSticker: true,
            },
            {
                src: './image/one.jpg',
                alt: 'Late night city lights and dreams',
            },
            {
                src: './image/three.jpg',
                alt: 'Sunday picnic in our favorite park',
                showSticker: true,
            },
        ],
    },
});
</script>

<template>
    <section class="min-h-screen bg-rose-50 px-4 py-16 sm:px-6 md:py-24">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 text-center md:mb-12">
                <p class="text-sm tracking-[0.22em] text-rose-500 uppercase">
                    Our Cute Moments
                </p>
                <h2
                    class="mt-2 font-['Playfair_Display'] text-4xl text-rose-800 sm:text-5xl"
                >
                    Gallery
                </h2>
            </div>

            <div class="columns-2 gap-4 md:columns-3 md:gap-5 lg:columns-4">
                <article
                    v-for="(image, index) in images"
                    :key="image.src ?? `gallery-${index}`"
                    class="group relative mb-4 break-inside-avoid md:mb-5"
                >
                    <img
                        :src="image.src"
                        :alt="image.alt || `Memory photo ${index + 1}`"
                        class="w-full rounded-3xl border border-rose-100 object-cover shadow-[0_12px_34px_rgba(251,113,133,0.22)] transition duration-300 ease-out group-hover:scale-105 group-hover:shadow-[0_20px_48px_rgba(244,114,182,0.34)]"
                    />

                    <span
                        v-if="image.showSticker || index % 3 === 0"
                        class="pointer-events-none absolute top-2 right-2 inline-flex rounded-full bg-white/85 p-1.5 text-base shadow-md shadow-pink-200/80 backdrop-blur-sm"
                    >
                        ❤️
                    </span>
                </article>
            </div>
        </div>
    </section>
</template>
