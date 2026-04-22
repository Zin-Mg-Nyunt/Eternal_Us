<script setup>
import { nextTick, ref } from 'vue';
import HeroSection from '@/components/HeroSection.vue';
import RoadMapJourney from '@/components/RoadMapJourney.vue';
import Gallery from '@/components/Gallery.vue';
import FeedbackWall from '@/components/FeedbackWall.vue';
import FeedbackForm from '@/components/FeedbackForm.vue';
import FloatingRoseFab from '@/components/FloatingRoseFab.vue';
import FabActionModal from '@/components/FabActionModal.vue';

const activeModalType = ref(null);
const isModalOpen = ref(false);
const modalInitialData = ref(null);

const journeyItems = ref([
    {
        id: 'journey-1',
        title: 'First Date Story',
        story: 'The day we met for coffee and talked for hours.',
        image: '/image/one.webp',
    },
    {
        id: 'journey-2',
        title: 'Beach Sunset Promise',
        story: 'A quiet evening by the sea with a forever promise.',
        image: '/image/three.webp',
    },
]);

const galleryItems = ref([
    {
        id: 'gallery-1',
        title: 'Coffee Date Photo',
        image: '/image/two.webp',
    },
    {
        id: 'gallery-2',
        title: 'Festival Night Photo',
        image: '/image/four.webp',
    },
    {
        id: 'gallery-3',
        title: 'Travel Memory Photo',
        image: '/image/hero.webp',
    },
]);

const onFabSelect = (type) => {
    modalInitialData.value = null;
    activeModalType.value = type;
    isModalOpen.value = true;
};

const toPreviewUrl = (file, fallback) =>
    file ? URL.createObjectURL(file) : fallback;

const onModalSubmit = ({
    id,
    type,
    journeyFile,
    journeyStory,
    galleryFiles,
    heroFile,
}) => {
    if (type === 'journey') {
        const payload = {
            id: id ?? `journey-${Date.now()}`,
            title: journeyStory?.trim() || 'Untitled Journey',
            story: journeyStory?.trim() || '',
            image: toPreviewUrl(
                journeyFile,
                modalInitialData.value?.image ?? '/image/one.webp',
            ),
        };

        if (id) {
            journeyItems.value = journeyItems.value.map((item) =>
                item.id === id ? payload : item,
            );
        } else {
            journeyItems.value.unshift(payload);
        }
        return;
    }

    if (type === 'gallery') {
        if (id) {
            const [file] = galleryFiles?.length ? galleryFiles : [null];
            galleryItems.value = galleryItems.value.map((item) =>
                item.id === id
                    ? {
                          ...item,
                          title: file?.name || item.title,
                          image: toPreviewUrl(file, item.image),
                      }
                    : item,
            );
            return;
        }

        const files = galleryFiles?.length ? galleryFiles : [null];
        files.forEach((file, index) => {
            galleryItems.value.unshift({
                id: `gallery-${Date.now()}-${index}`,
                title:
                    file?.name ||
                    `Gallery Photo ${galleryItems.value.length + 1}`,
                image: toPreviewUrl(file, '/image/two.webp'),
            });
        });
        return;
    }

    if (type === 'hero' && heroFile) {
        // Placeholder until HeroSection receives image from store or API.
        console.info('Hero image selected:', heroFile.name);
    }
};

const onEditItem = async ({ item, type }) => {
    isModalOpen.value = false;
    await nextTick();
    modalInitialData.value = item;
    activeModalType.value = type;
    isModalOpen.value = true;
};
</script>

<template>
    <HeroSection />
    <RoadMapJourney />
    <Gallery />
    <FeedbackWall />
    <FeedbackForm />
    <FloatingRoseFab @select="onFabSelect" />

    <FabActionModal
        v-model="isModalOpen"
        :type="activeModalType"
        :journey-items="journeyItems"
        :gallery-items="galleryItems"
        :initial-data="modalInitialData"
        @submit="onModalSubmit"
        @edit-item="onEditItem"
    />
</template>
