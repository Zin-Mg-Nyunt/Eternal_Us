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
    journeyTitle,
    journeyDescription,
    galleryFile,
    coverFile,
}) => {
    if (type === 'journey') {
        const payload = {
            id: id ?? `journey-${Date.now()}`,
            title: journeyTitle?.trim() || 'Untitled Journey',
            story: journeyDescription?.trim() || 'No story yet',
            image: toPreviewUrl(
                journeyFile,
                modalInitialData.value?.image ?? '/image/one.webp',
            ),
        };

        if (id) {
            journeyItems.value = journeyItems.value.map((item) =>
                item.id === id ? payload : item,
            );
            console.log('I am updating the journey item');
        } else {
            journeyItems.value.unshift(payload);
            console.log('I am creating a new journey item');
        }
        return;
    }

    if (type === 'gallery') {
        if (id) {
            const file = galleryFile ?? null;
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

        const file = galleryFile ?? null;
        galleryItems.value.unshift({
            id: `gallery-${Date.now()}`,
            title:
                file?.name || `Gallery Photo ${galleryItems.value.length + 1}`,
            image: toPreviewUrl(file, '/image/two.webp'),
        });
        return;
    }

    if (type === 'cover' && coverFile) {
        // Placeholder until HeroSection receives image from store or API.
        console.info('Cover image selected:', coverFile.name);
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
