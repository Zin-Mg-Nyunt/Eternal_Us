<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: null,
    },
    journeyItems: {
        type: Array,
        default: () => [],
    },
    galleryItems: {
        type: Array,
        default: () => [],
    },
    initialData: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'submit', 'edit-item']);

const form = reactive({
    journeyFile: null,
    journeyStory: '',
    galleryFiles: [],
    heroFile: null,
});
const journeyPreviewUrl = ref(null);
const heroPreviewUrl = ref(null);
const galleryPreviewUrls = ref([]);

const configByType = {
    journey: {
        title: 'Add New Love Journey',
        submitLabel: 'Save Journey',
    },
    gallery: {
        title: 'Upload Gallery Photos',
        submitLabel: 'Upload Photo',
    },
    hero: {
        title: 'Update Hero Background',
        submitLabel: 'Upload Hero Image',
    },
    manage: {
        title: 'Manage Memories',
        submitLabel: 'Save Changes',
    },
};

const modalConfig = computed(() => configByType[props.type] ?? null);
const isEditMode = computed(() => Boolean(props.initialData?.id));
const journeyCards = computed(() => props.journeyItems ?? []);
const galleryCards = computed(() => props.galleryItems ?? []);

const closeModal = () => {
    emit('update:modelValue', false);
};

const revokePreview = (url) => {
    if (url) URL.revokeObjectURL(url);
};

const resetPreviews = () => {
    revokePreview(journeyPreviewUrl.value);
    revokePreview(heroPreviewUrl.value);
    galleryPreviewUrls.value.forEach((item) => revokePreview(item.url));
    journeyPreviewUrl.value = null;
    heroPreviewUrl.value = null;
    galleryPreviewUrls.value = [];
};

const onJourneyFileChange = (event) => {
    const [file] = event.target.files ?? [];
    revokePreview(journeyPreviewUrl.value);
    form.journeyFile = file ?? null;
    journeyPreviewUrl.value = file ? URL.createObjectURL(file) : null;
};

const onGalleryFileChange = (event) => {
    form.galleryFiles = Array.from(event.target.files ?? []);
    galleryPreviewUrls.value.forEach((item) => revokePreview(item.url));
    galleryPreviewUrls.value = form.galleryFiles.map((file) => ({
        name: file.name,
        url: URL.createObjectURL(file),
    }));
};

const onHeroFileChange = (event) => {
    const [file] = event.target.files ?? [];
    revokePreview(heroPreviewUrl.value);
    form.heroFile = file ?? null;
    heroPreviewUrl.value = file ? URL.createObjectURL(file) : null;
};

const onSubmit = () => {
    if (!props.type) return;

    if (props.type === 'manage') {
        closeModal();
        return;
    }

    const payload = {
        type: props.type,
        id: props.initialData?.id ?? null,
        journeyFile: form.journeyFile,
        journeyStory: form.journeyStory,
        galleryFiles: form.galleryFiles,
        heroFile: form.heroFile,
    };

    emit('submit', payload);
    closeModal();
};

watch(
    () => props.modelValue,
    (open) => {
        if (open) return;
        form.journeyFile = null;
        form.journeyStory = '';
        form.galleryFiles = [];
        form.heroFile = null;
        resetPreviews();
    },
);

watch(
    () => [props.modelValue, props.type, props.initialData],
    ([open, type]) => {
        if (!open || !props.initialData) return;
        if (type === 'journey') {
            form.journeyStory =
                props.initialData.story ?? props.initialData.title ?? '';
            journeyPreviewUrl.value = props.initialData.image ?? null;
        }
        if (type === 'gallery') {
            galleryPreviewUrls.value = props.initialData.image
                ? [
                      {
                          name: props.initialData.title ?? 'Selected photo',
                          url: props.initialData.image,
                      },
                  ]
                : [];
        }
        if (type === 'hero') {
            heroPreviewUrl.value = props.initialData.image ?? null;
        }
    },
);

onBeforeUnmount(() => {
    resetPreviews();
});
</script>

<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="modelValue && modalConfig"
            class="fixed inset-0 z-10000 flex items-center justify-center bg-rose-950/30 px-4 py-6 backdrop-blur-sm sm:px-6"
            @click.self="closeModal"
        >
            <div
                class="w-full max-w-2xl rounded-2xl border border-rose-200 bg-linear-to-b from-rose-50 to-pink-50 shadow-[0_24px_64px_rgba(190,24,93,0.25)]"
            >
                <header
                    class="border-b border-rose-200/80 px-5 py-4 sm:px-6 sm:py-5"
                >
                    <h3 class="text-xl font-semibold text-rose-800 sm:text-2xl">
                        {{ modalConfig.title }}
                    </h3>
                    <p
                        v-if="isEditMode && type !== 'manage'"
                        class="mt-1 text-xs font-medium text-rose-500"
                    >
                        Editing existing memory
                    </p>
                </header>

                <div
                    class="max-h-[70vh] overflow-y-auto px-5 py-4 sm:px-6 sm:py-5"
                >
                    <div v-if="type === 'journey'" class="space-y-4">
                        <label
                            class="block cursor-pointer rounded-2xl border border-dashed border-rose-300 bg-white/80 p-4 transition hover:border-rose-400 hover:bg-rose-50/70"
                        >
                            <span
                                class="mb-2 inline-flex items-center gap-1 text-sm font-semibold text-rose-700"
                            >
                                <span aria-hidden="true">📷</span> Love Journey
                                Image
                            </span>
                            <input
                                type="file"
                                accept="image/*"
                                class="block w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 file:mr-3 file:rounded-lg file:border-0 file:bg-rose-100 file:px-3 file:py-1.5 file:text-rose-700 file:transition hover:file:bg-rose-200"
                                @change="onJourneyFileChange"
                            />
                            <p class="mt-2 text-xs text-rose-500">
                                Upload a sweet memory photo for your love
                                journey.
                            </p>
                        </label>
                        <div
                            v-if="journeyPreviewUrl"
                            class="overflow-hidden rounded-2xl border border-rose-200 bg-white p-2 shadow-sm"
                        >
                            <img
                                :src="journeyPreviewUrl"
                                alt="Journey preview"
                                class="h-44 w-full rounded-xl object-cover"
                            />
                        </div>

                        <label class="block space-y-1.5">
                            <span class="text-sm font-semibold text-rose-700">
                                Story / Description
                            </span>
                            <textarea
                                v-model="form.journeyStory"
                                rows="5"
                                class="w-full rounded-2xl border border-rose-200 bg-white px-4 py-3 text-sm text-rose-800 ring-0 outline-none placeholder:text-rose-300 focus:border-rose-300 focus:ring-2 focus:ring-rose-200"
                                placeholder="Write your beautiful memory..."
                            />
                        </label>
                    </div>

                    <div v-else-if="type === 'gallery'" class="space-y-3">
                        <label
                            class="block cursor-pointer rounded-2xl border border-dashed border-rose-300 bg-white/80 p-4 transition hover:border-rose-400 hover:bg-rose-50/70"
                        >
                            <span
                                class="mb-2 inline-flex items-center gap-1 text-sm font-semibold text-rose-700"
                            >
                                <span aria-hidden="true">🖼️</span> Gallery
                                Photos
                            </span>
                            <input
                                type="file"
                                accept="image/*"
                                multiple
                                class="block w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 file:mr-3 file:rounded-lg file:border-0 file:bg-rose-100 file:px-3 file:py-1.5 file:text-rose-700 file:transition hover:file:bg-rose-200"
                                @change="onGalleryFileChange"
                            />
                            <p class="mt-2 text-xs text-rose-500">
                                Choose one or many lovely photos.
                            </p>
                        </label>
                        <div
                            v-if="galleryPreviewUrls.length"
                            class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                        >
                            <article
                                v-for="item in galleryPreviewUrls"
                                :key="item.url"
                                class="overflow-hidden rounded-xl border border-rose-200 bg-white p-1.5 shadow-sm"
                            >
                                <img
                                    :src="item.url"
                                    :alt="item.name"
                                    class="h-24 w-full rounded-lg object-cover sm:h-28"
                                />
                                <p
                                    class="mt-1 truncate text-[11px] text-rose-500"
                                    :title="item.name"
                                >
                                    {{ item.name }}
                                </p>
                            </article>
                        </div>
                    </div>

                    <div v-else-if="type === 'hero'" class="space-y-3">
                        <label
                            class="block cursor-pointer rounded-2xl border border-dashed border-rose-300 bg-white/80 p-4 transition hover:border-rose-400 hover:bg-rose-50/70"
                        >
                            <span
                                class="mb-2 inline-flex items-center gap-1 text-sm font-semibold text-rose-700"
                            >
                                <span aria-hidden="true">🌸</span> Hero
                                Background Image
                            </span>
                            <input
                                type="file"
                                accept="image/*"
                                class="block w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-rose-700 file:mr-3 file:rounded-lg file:border-0 file:bg-rose-100 file:px-3 file:py-1.5 file:text-rose-700 file:transition hover:file:bg-rose-200"
                                @change="onHeroFileChange"
                            />
                            <p class="mt-2 text-xs text-rose-500">
                                This will replace the top hero image.
                            </p>
                        </label>
                        <div
                            v-if="heroPreviewUrl"
                            class="overflow-hidden rounded-2xl border border-rose-200 bg-white p-2 shadow-sm"
                        >
                            <img
                                :src="heroPreviewUrl"
                                alt="Hero preview"
                                class="h-44 w-full rounded-xl object-cover sm:h-52"
                            />
                        </div>
                    </div>

                    <div v-else-if="type === 'manage'" class="space-y-4">
                        <div
                            v-if="!journeyCards.length && !galleryCards.length"
                            class="rounded-xl border border-dashed border-rose-200 bg-white/70 px-4 py-6 text-center text-sm text-rose-500"
                        >
                            No memories available yet.
                        </div>

                        <div
                            v-else
                            class="max-h-[52vh] space-y-4 overflow-y-auto pr-1"
                        >
                            <section class="space-y-3">
                                <h4
                                    class="text-sm font-semibold tracking-wide text-rose-700 uppercase"
                                >
                                    Journey Memories
                                </h4>
                                <div
                                    v-if="!journeyCards.length"
                                    class="rounded-xl border border-dashed border-rose-200 bg-white/70 px-4 py-4 text-center text-xs text-rose-500"
                                >
                                    No journey memories.
                                </div>
                                <div
                                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                                >
                                    <article
                                        v-for="item in journeyCards"
                                        :key="item.id"
                                        class="group cursor-pointer overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                        @click="
                                            emit('edit-item', {
                                                item,
                                                type: 'journey',
                                            })
                                        "
                                    >
                                        <img
                                            :src="item.image"
                                            :alt="item.title"
                                            class="h-32 w-full object-cover"
                                        />
                                        <div
                                            class="flex items-center justify-between gap-3 px-3 py-2.5"
                                        >
                                            <p
                                                class="truncate text-sm font-semibold text-rose-700"
                                            >
                                                {{ item.title }}
                                            </p>
                                            <span
                                                class="text-xs font-medium text-rose-400"
                                            >
                                                Tap to edit
                                            </span>
                                        </div>
                                    </article>
                                </div>
                            </section>

                            <div class="border-t border-rose-200/90" />

                            <section class="space-y-3">
                                <h4
                                    class="text-sm font-semibold tracking-wide text-rose-700 uppercase"
                                >
                                    Gallery Photos
                                </h4>
                                <div
                                    v-if="!galleryCards.length"
                                    class="rounded-xl border border-dashed border-rose-200 bg-white/70 px-4 py-4 text-center text-xs text-rose-500"
                                >
                                    No gallery photos.
                                </div>
                                <div
                                    class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                                >
                                    <article
                                        v-for="item in galleryCards"
                                        :key="item.id"
                                        class="group cursor-pointer overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                        @click="
                                            emit('edit-item', {
                                                item,
                                                type: 'gallery',
                                            })
                                        "
                                    >
                                        <img
                                            :src="item.image"
                                            :alt="item.title"
                                            class="h-28 w-full object-cover"
                                        />
                                        <div class="px-2 py-2 text-right">
                                            <span
                                                class="text-[11px] font-medium text-rose-400"
                                            >
                                                Tap to edit
                                            </span>
                                        </div>
                                    </article>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <footer
                    class="flex items-center justify-end gap-2 border-t border-rose-200/80 px-5 py-4 sm:px-6"
                >
                    <button
                        type="button"
                        class="rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                        @click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        v-if="type !== 'manage'"
                        type="button"
                        class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-600"
                        @click="onSubmit"
                    >
                        {{ modalConfig.submitLabel }}
                    </button>
                </footer>
            </div>
        </div>
    </transition>
</template>
