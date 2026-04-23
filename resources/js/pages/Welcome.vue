<script setup>
import { nextTick, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import HeroSection from '@/components/HeroSection.vue';
import RoadMapJourney from '@/components/RoadMapJourney.vue';
import Gallery from '@/components/Gallery.vue';
import FeedbackWall from '@/components/FeedbackWall.vue';
import FeedbackForm from '@/components/FeedbackForm.vue';
import FloatingRoseFab from '@/components/FloatingRoseFab.vue';
import FabActionModal from '@/components/FabActionModal.vue';

const props = defineProps({
    journeyItems: {
        type: Array,
        default: () => [],
    },
    galleryItems: {
        type: Array,
        default: () => [],
    },
    coverImage: {
        type: String,
        default: null,
    },
    wishes: {
        type: Array,
        default: () => [],
    },
});

const activeModalType = ref(null);
const isModalOpen = ref(false);
const modalInitialData = ref(null);
const errors = ref({});

const journeyItems = ref(props.journeyItems ?? []);

const galleryItems = ref(props.galleryItems ?? []);
const wishes = ref(props.wishes ?? []);

watch(
    () => props.journeyItems,
    (incoming) => {
        journeyItems.value = incoming ?? [];
    },
    { deep: true },
);

watch(
    () => props.galleryItems,
    (incoming) => {
        galleryItems.value = incoming ?? [];
    },
    { deep: true },
);

watch(
    () => props.wishes,
    (incoming) => {
        wishes.value = incoming ?? [];
    },
    { deep: true },
);

const onFabSelect = (type) => {
    errors.value = {};
    modalInitialData.value = null;
    activeModalType.value = type;
    isModalOpen.value = true;
};

const onModalSubmit = ({
    id,
    type,
    journeyFile,
    journeyTitle,
    journeyDescription,
    journeyDate,
    galleryFile,
    coverFile,
}) => {
    errors.value = {};

    if (type === 'journey') {
        const payload = {
            id: id ?? null,
            title: journeyTitle?.trim() || 'Untitled Journey',
            story: journeyDescription?.trim() || 'No story yet',
            journey_date: journeyDate,
            image: journeyFile,
        };

        if (id) {
            router.post(route('journey.update'), payload, {
                preserveScroll: true,
                onSuccess: () => {
                    errors.value = {};
                    isModalOpen.value = false;
                },
                onError: (backendErrors) => {
                    errors.value = backendErrors ?? {};
                },
            });
        } else {
            router.post(route('journey.add'), payload, {
                preserveScroll: true,
                onSuccess: () => {
                    errors.value = {};
                    isModalOpen.value = false;
                },
                onError: (backendErrors) => {
                    errors.value = backendErrors ?? {};
                },
            });
        }
        return;
    }

    if (type === 'gallery') {
        const payload = {
            id: id ?? null,
            image: galleryFile,
        };

        if (id) {
            router.post(route('gallery.update'), payload, {
                preserveScroll: true,
                onSuccess: () => {
                    errors.value = {};
                    isModalOpen.value = false;
                },
                onError: (backendErrors) => {
                    errors.value = backendErrors ?? {};
                },
            });
        } else {
            router.post(route('gallery.add'), payload, {
                preserveScroll: true,
                onSuccess: () => {
                    errors.value = {};
                    isModalOpen.value = false;
                },
                onError: (backendErrors) => {
                    errors.value = backendErrors ?? {};
                },
            });
        }
        return;
    }

    if (type === 'cover') {
        const payload = {
            image: coverFile,
        };

        router.post(route('cover.update'), payload, {
            preserveScroll: true,
            onSuccess: () => {
                errors.value = {};
                isModalOpen.value = false;
            },
            onError: (backendErrors) => {
                errors.value = backendErrors ?? {};
            },
        });
    }
};

const onEditItem = async ({ item, type }) => {
    errors.value = {};
    isModalOpen.value = false;
    await nextTick();
    modalInitialData.value = item;
    activeModalType.value = type;
    isModalOpen.value = true;
};
</script>

<template>
    <HeroSection :cover-image="coverImage" />
    <RoadMapJourney
        :memories="
            journeyItems.map((item) => ({
                ...item,
                description: item.story ?? '',
            }))
        "
    />
    <Gallery :images="galleryItems.map((item) => ({ src: item.image, alt: item.title ?? 'Gallery photo' }))" />
    <FeedbackWall :feedbacks="wishes" />
    <FeedbackForm />
    <FloatingRoseFab @select="onFabSelect" />

    <FabActionModal
        v-model="isModalOpen"
        :type="activeModalType"
        :journey-items="journeyItems"
        :gallery-items="galleryItems"
        :initial-data="modalInitialData"
        :errors="errors"
        @submit="onModalSubmit"
        @edit-item="onEditItem"
    />
</template>
