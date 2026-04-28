<script setup>
import { nextTick, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import HeroSection from '@/components/HeroSection.vue';
import RoadMapJourney from '@/components/RoadMapJourney.vue';
import Gallery from '@/components/Gallery.vue';
import FeedbackWall from '@/components/FeedbackWall.vue';
import FeedbackForm from '@/components/FeedbackForm.vue';
import FloatingRoseFab from '@/components/FloatingRoseFab.vue';
import FabActionModal from '@/components/FabActionModal.vue';
import EmailVerificationBanner from '@/components/EmailVerificationBanner.vue';

const props = defineProps({
    journeyItems: {
        type: Array,
        default: () => [],
    },
    galleryItems: {
        type: Array,
        default: () => [],
    },
    journeyPagination: {
        type: Object,
        default: () => ({
            currentPage: 1,
            lastPage: 1,
            prevPage: null,
            nextPage: null,
        }),
    },
    galleryPages: {
        type: Object,
        default: () => ({ data: [] }),
    },
    coverImage: {
        type: String,
        default: null,
    },
    wishes: {
        type: Array,
        default: () => [],
    },
    wishPages: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const activeModalType = ref(null);
const isModalOpen = ref(false);
const modalInitialData = ref(null);
const errors = ref({});

const onFabSelect = (type) => {
    errors.value = {};
    modalInitialData.value = null;
    activeModalType.value = type;
    isModalOpen.value = true;
};

const compressImage = async (file) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = () => {
            const image = new Image();
            image.src = reader.result;

            image.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const maxSize = 900;
                let width = image.width;
                let height = image.height;

                if (width > maxSize || height > maxSize) {
                    if (width > height) {
                        height *= maxSize / width;
                        width = maxSize;
                    } else {
                        width *= maxSize / height;
                        height = maxSize;
                    }
                }

                canvas.width = width;
                canvas.height = height;

                ctx.drawImage(image, 0, 0, width, height);
                canvas.toBlob(
                    (blob) => {
                        resolve(blob);
                    },
                    'image/webp',
                    0.7,
                );
            };
        };
    });
};

const onModalSubmit = async ({
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
        const compressedImage = await compressImage(journeyFile);
        const payload = {
            id: id ?? null,
            title: journeyTitle?.trim() || 'Untitled Journey',
            story: journeyDescription?.trim() || 'No story yet',
            journey_date: journeyDate,
            image: compressedImage,
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
        const compressedImage = await compressImage(galleryFile);
        const payload = {
            id: id ?? null,
            image: compressedImage,
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
        const compressedImage = await compressImage(coverFile);
        const payload = {
            image: compressedImage,
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
        :memories="props.journeyItems"
        :pagination="props.journeyPagination"
    />
    <Gallery
        :images="
            props.galleryItems.map((item) => ({
                src: item.image,
                alt: item.title ?? 'Gallery photo',
            }))
        "
        :gallery-pages="props.galleryPages"
    />
    <FeedbackWall :feedbacks="props.wishes" :wish-pages="props.wishPages" />
    <FeedbackForm />
    <EmailVerificationBanner />
    <FloatingRoseFab @select="onFabSelect" />

    <FabActionModal
        v-model="isModalOpen"
        :type="activeModalType"
        :journey-items="props.journeyItems"
        :gallery-items="props.galleryItems"
        :initial-data="modalInitialData"
        :errors="errors"
        @submit="onModalSubmit"
        @edit-item="onEditItem"
    />
</template>
