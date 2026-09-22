<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import WysiwygEditor from './WysiwygEditor.vue';

const props = defineProps({
    form: Object,
    post: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: 'create',
    },
    grades: {
        type: Array,
        default: () => [],
    },
    teachers: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['submit']);

const editorRef = ref(null);
const localError = ref('');
const imagePreview = ref(props.post?.image_path ? `/storage/${props.post.image_path}` : null);
const dragActive = ref(false);

const isEvent = computed(() => props.form.type === 'event');
const isStaffAnnouncement = computed(() => ['teachers_only', 'grade_teachers', 'specific_teacher'].includes(props.form.audience));

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        props.form.image = file;
        props.form.remove_image = false;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const handleDrop = (event) => {
    event.preventDefault();
    dragActive.value = false;
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        props.form.image = file;
        props.form.remove_image = false;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    props.form.image = null;
    props.form.remove_image = true;
    imagePreview.value = null;
};

// Clear event fields when switching to news
watch(() => props.form.type, (newType) => {
    if (newType === 'event' && !props.form.audience) {
        props.form.audience = 'all';
    }
    if (newType === 'news') {
        props.form.event_start_date = '';
        props.form.event_end_date = '';
        props.form.button_text = '';
        props.form.button_url = '';
        props.form.recurrence_type = 'none';
        props.form.recurrence_end_date = '';
        props.form.is_public = false;
    } else if (!props.form.event_visibility) {
        props.form.event_visibility = 'internal';
    }
});

const plainContent = (html) => String(html || '')
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/g, ' ')
    .trim();

const showFormError = async (message) => {
    localError.value = message;
    await nextTick();
    document.getElementById('post-form-errors')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const submitForm = async () => {
    localError.value = '';
    editorRef.value?.syncToModel?.();

    const start = document.getElementById('event_start_date');
    const end = document.getElementById('event_end_date');
    if (start instanceof HTMLInputElement && start.value) {
        props.form.event_start_date = start.value;
    }
    if (end instanceof HTMLInputElement && end.value) {
        props.form.event_end_date = end.value;
    }

    if (!String(props.form.title || '').trim()) {
        await showFormError('Enter a title.');
        return;
    }
    if (!plainContent(props.form.content)) {
        await showFormError('Enter the post content.');
        return;
    }
    if (props.form.type === 'event' && !props.form.event_start_date) {
        await showFormError('Choose a start date for this event.');
        return;
    }
    if (props.form.type === 'event' && !['internal', 'external'].includes(props.form.event_visibility)) {
        await showFormError('Choose whether this event is internal or external.');
        return;
    }

    emit('submit');
};

// Clear targeting fields when audience changes
watch(() => props.form.audience, (newAudience) => {
    if (newAudience !== 'grade_teachers') {
        props.form.target_grade_id = null;
    }
    if (newAudience !== 'specific_teacher') {
        props.form.target_teacher_id = null;
    }
});

// Recurrence options
const recurrenceOptions = [
    { value: 'none', label: 'Does not repeat' },
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'biweekly', label: 'Every 2 weeks' },
    { value: 'monthly', label: 'Monthly' },
];

const showRecurrenceEndDate = computed(() => {
    return props.form.recurrence_type && props.form.recurrence_type !== 'none';
});

// Audience description for display
const audienceDescription = computed(() => {
    switch (props.form.audience) {
        case 'teachers_only':
            return 'This announcement will be visible to all staff members in their dashboard.';
        case 'grade_teachers':
            const grade = props.grades.find(g => g.id === props.form.target_grade_id);
            return grade 
                ? `This announcement will only be visible to ${grade.name} teachers.`
                : 'Select a grade level to target specific teachers.';
        case 'specific_teacher':
            const teacher = props.teachers.find(t => t.id === props.form.target_teacher_id);
            return teacher 
                ? `This announcement will only be visible to ${teacher.name}.`
                : 'Select a teacher to send a private announcement.';
        default:
            return '';
    }
});
</script>

<template>
    <form @submit.prevent="submitForm" class="space-y-6">
        <!-- General Errors Display -->
        <div v-if="localError || Object.keys(form.errors).length > 0" id="post-form-errors" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p v-if="localError" class="text-sm text-red-700">{{ localError }}</p>
            <h4 v-if="Object.keys(form.errors).length > 0" class="text-red-800 font-medium mb-2">Please fix the following errors:</h4>
            <ul v-if="Object.keys(form.errors).length > 0" class="list-disc list-inside text-sm text-red-600 space-y-1">
                <li v-for="(error, field) in form.errors" :key="field">
                    <strong>{{ field }}:</strong> {{ error }}
                </li>
            </ul>
        </div>

        <!-- Post Type -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Post Type</label>
            <div class="flex gap-4">
                <label
                    :class="[
                        'flex-1 flex items-center justify-center px-4 py-3 border-2 rounded-lg cursor-pointer transition-colors',
                        form.type === 'news'
                            ? 'border-brand-500 bg-brand-50 text-brand-700'
                            : 'border-slate-200 hover:border-slate-300'
                    ]"
                >
                    <input
                        type="radio"
                        v-model="form.type"
                        value="news"
                        class="sr-only"
                    />
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <span class="font-medium">News Article</span>
                </label>
                <label
                    :class="[
                        'flex-1 flex items-center justify-center px-4 py-3 border-2 rounded-lg cursor-pointer transition-colors',
                        form.type === 'event'
                            ? 'border-brand-500 bg-brand-50 text-brand-700'
                            : 'border-slate-200 hover:border-slate-300'
                    ]"
                >
                    <input
                        type="radio"
                        v-model="form.type"
                        value="event"
                        class="sr-only"
                    />
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium">Event</span>
                </label>
            </div>
        </div>

        <!-- Audience Selection (for news posts) -->
        <div v-if="form.type === 'news'">
            <label class="block text-sm font-medium text-slate-700 mb-2">Who can see this?</label>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Everyone -->
                <label
                    :class="[
                        'flex flex-col items-center justify-center px-3 py-3 border-2 rounded-lg cursor-pointer transition-colors text-center',
                        form.audience === 'all'
                            ? 'border-brand-500 bg-brand-50 text-brand-700'
                            : 'border-slate-200 hover:border-slate-300'
                    ]"
                >
                    <input
                        type="radio"
                        v-model="form.audience"
                        value="all"
                        class="sr-only"
                    />
                    <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium text-sm">Everyone</span>
                </label>

                <!-- All Staff -->
                <label
                    :class="[
                        'flex flex-col items-center justify-center px-3 py-3 border-2 rounded-lg cursor-pointer transition-colors text-center',
                        form.audience === 'teachers_only'
                            ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                            : 'border-slate-200 hover:border-slate-300'
                    ]"
                >
                    <input
                        type="radio"
                        v-model="form.audience"
                        value="teachers_only"
                        class="sr-only"
                    />
                    <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="font-medium text-sm">All Staff</span>
                </label>

                <!-- Grade Level -->
                <label
                    :class="[
                        'flex flex-col items-center justify-center px-3 py-3 border-2 rounded-lg cursor-pointer transition-colors text-center',
                        form.audience === 'grade_teachers'
                            ? 'border-amber-500 bg-amber-50 text-amber-700'
                            : 'border-slate-200 hover:border-slate-300'
                    ]"
                >
                    <input
                        type="radio"
                        v-model="form.audience"
                        value="grade_teachers"
                        class="sr-only"
                    />
                    <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-medium text-sm">Grade Level</span>
                </label>

                <!-- Specific Teacher -->
                <label
                    :class="[
                        'flex flex-col items-center justify-center px-3 py-3 border-2 rounded-lg cursor-pointer transition-colors text-center',
                        form.audience === 'specific_teacher'
                            ? 'border-purple-500 bg-purple-50 text-purple-700'
                            : 'border-slate-200 hover:border-slate-300'
                    ]"
                >
                    <input
                        type="radio"
                        v-model="form.audience"
                        value="specific_teacher"
                        class="sr-only"
                    />
                    <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-medium text-sm">Specific Teacher</span>
                </label>
            </div>

            <!-- Grade Selection (when Grade Level is selected) -->
            <div v-if="form.audience === 'grade_teachers'" class="mt-4">
                <label for="target_grade_id" class="block text-sm font-medium text-slate-700 mb-1">
                    Select Grade Level <span class="text-red-500">*</span>
                </label>
                <select
                    id="target_grade_id"
                    v-model="form.target_grade_id"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
                    required
                >
                    <option :value="null" disabled>Choose a grade level...</option>
                    <option v-for="grade in grades" :key="grade.id" :value="grade.id">
                        {{ grade.name }}
                    </option>
                </select>
            </div>

            <!-- Teacher Selection (when Specific Teacher is selected) -->
            <div v-if="form.audience === 'specific_teacher'" class="mt-4">
                <label for="target_teacher_id" class="block text-sm font-medium text-slate-700 mb-1">
                    Select Teacher <span class="text-red-500">*</span>
                </label>
                <select
                    id="target_teacher_id"
                    v-model="form.target_teacher_id"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white"
                    required
                >
                    <option :value="null" disabled>Choose a teacher...</option>
                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                        {{ teacher.name }}
                    </option>
                </select>
                <p v-if="teachers.length === 0" class="mt-1 text-sm text-slate-500">
                    No teachers have been added yet. Add teachers in the user management section.
                </p>
            </div>

            <!-- Audience Description -->
            <p v-if="isStaffAnnouncement" class="mt-3 text-sm" :class="{
                'text-emerald-600': form.audience === 'teachers_only',
                'text-amber-600': form.audience === 'grade_teachers',
                'text-purple-600': form.audience === 'specific_teacher',
            }">
                {{ audienceDescription }}
            </p>
        </div>

        <!-- Title -->
        <div>
            <label for="title" class="block text-sm font-medium text-slate-700 mb-1">
                Title <span class="text-red-500">*</span>
            </label>
            <input
                id="title"
                type="text"
                v-model="form.title"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                placeholder="Enter a title..."
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
        </div>

        <!-- Content -->
        <div>
            <label for="content" class="block text-sm font-medium text-slate-700 mb-1">
                Content <span class="text-red-500">*</span>
            </label>
            <WysiwygEditor
                ref="editorRef"
                v-model="form.content"
                placeholder="Write your content here..."
                min-height="240px"
            />
            <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <!-- Featured Image -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Featured Image</label>
            <div
                v-if="!imagePreview"
                :class="[
                    'border-2 border-dashed rounded-lg p-8 text-center transition-colors',
                    dragActive ? 'border-brand-500 bg-brand-50' : 'border-slate-300 hover:border-slate-400'
                ]"
                @dragover.prevent="dragActive = true"
                @dragleave="dragActive = false"
                @drop="handleDrop"
            >
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-slate-600">
                    Drag and drop an image, or
                    <label class="text-brand-600 hover:text-brand-700 cursor-pointer">
                        browse
                        <input
                            type="file"
                            accept="image/*"
                            class="sr-only"
                            @change="handleImageChange"
                        />
                    </label>
                </p>
                <p class="mt-1 text-xs text-slate-500">PNG, JPG, GIF up to 2MB</p>
            </div>
            <div v-else class="relative inline-block">
                <img
                    :src="imagePreview"
                    alt="Preview"
                    class="max-h-48 rounded-lg"
                />
                <button
                    type="button"
                    @click="removeImage"
                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
        </div>

        <!-- Event-specific fields -->
        <template v-if="isEvent">
            <div class="border-t border-slate-200 pt-6">
                <h3 class="text-lg font-serif font-semibold text-slate-900 mb-4">Event Details</h3>
                
                <div class="mb-4">
                    <p class="block text-sm font-medium text-slate-700 mb-2">Who can see this event?</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label
                            :class="[
                                'flex flex-col px-4 py-3 border-2 rounded-lg cursor-pointer transition-colors',
                                form.event_visibility === 'internal'
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-800'
                                    : 'border-slate-200 hover:border-slate-300'
                            ]"
                        >
                            <input type="radio" v-model="form.event_visibility" value="internal" class="sr-only" />
                            <span class="font-medium">Internal</span>
                            <span class="text-sm mt-1">Staff only. Teachers and administrators can see this in the portal.</span>
                        </label>
                        <label
                            :class="[
                                'flex flex-col px-4 py-3 border-2 rounded-lg cursor-pointer transition-colors',
                                form.event_visibility === 'external'
                                    ? 'border-brand-500 bg-brand-50 text-brand-800'
                                    : 'border-slate-200 hover:border-slate-300'
                            ]"
                        >
                            <input type="radio" v-model="form.event_visibility" value="external" class="sr-only" />
                            <span class="font-medium">External</span>
                            <span class="text-sm mt-1">Shown on the public News &amp; Events page and to families in the portal.</span>
                        </label>
                    </div>
                    <p v-if="form.is_school_closure" class="mt-2 text-sm text-slate-500">
                        School closures are also shown to families in the portal.
                    </p>
                </div>
                
                <!-- School Closure Toggle -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.is_school_closure"
                            class="h-5 w-5 text-red-600 focus:ring-red-500 border-red-300 rounded"
                        />
                        <div class="ml-3">
                            <span class="font-medium text-red-700">School Closure / No School</span>
                            <p class="text-sm text-red-600 mt-0.5">
                                Mark this event as a school closure (holiday, snow day, etc.). This will display prominently on all calendars.
                            </p>
                        </div>
                    </label>
                </div>
                
                <!-- Event Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="event_start_date" class="block text-sm font-medium text-slate-700 mb-1">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="event_start_date"
                            type="datetime-local"
                            v-model="form.event_start_date"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                        <p v-if="form.errors.event_start_date" class="mt-1 text-sm text-red-600">{{ form.errors.event_start_date }}</p>
                    </div>
                    <div>
                        <label for="event_end_date" class="block text-sm font-medium text-slate-700 mb-1">
                            End Date <span class="text-slate-400">(optional)</span>
                        </label>
                        <input
                            id="event_end_date"
                            type="datetime-local"
                            v-model="form.event_end_date"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                        <p v-if="form.errors.event_end_date" class="mt-1 text-sm text-red-600">{{ form.errors.event_end_date }}</p>
                    </div>
                </div>

                <!-- Recurrence Options -->
                <div class="bg-slate-50 rounded-lg p-4 mb-4">
                    <h4 class="text-sm font-medium text-slate-700 mb-3">
                        <svg class="inline-block w-4 h-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Repeat Event
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="recurrence_type" class="block text-sm font-medium text-slate-600 mb-1">
                                Frequency
                            </label>
                            <select
                                id="recurrence_type"
                                v-model="form.recurrence_type"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
                            >
                                <option 
                                    v-for="option in recurrenceOptions" 
                                    :key="option.value" 
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div v-if="showRecurrenceEndDate">
                            <label for="recurrence_end_date" class="block text-sm font-medium text-slate-600 mb-1">
                                Repeat Until
                            </label>
                            <input
                                id="recurrence_end_date"
                                type="date"
                                v-model="form.recurrence_end_date"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            />
                            <p v-if="form.errors.recurrence_end_date" class="mt-1 text-sm text-red-600">{{ form.errors.recurrence_end_date }}</p>
                        </div>
                    </div>
                    <p v-if="showRecurrenceEndDate" class="mt-2 text-xs text-slate-500">
                        This event will repeat {{ form.recurrence_type === 'daily' ? 'every day' : form.recurrence_type === 'weekly' ? 'every week' : form.recurrence_type === 'biweekly' ? 'every 2 weeks' : 'every month' }} until the specified end date.
                    </p>
                </div>

                <!-- Registration Button -->
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-700 mb-3">Registration Button (optional)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="button_text" class="block text-sm font-medium text-slate-600 mb-1">
                                Button Text
                            </label>
                            <input
                                id="button_text"
                                type="text"
                                v-model="form.button_text"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                placeholder="e.g., Register Here"
                            />
                        </div>
                        <div>
                            <label for="button_url" class="block text-sm font-medium text-slate-600 mb-1">
                                Button Link
                            </label>
                            <input
                                id="button_url"
                                type="url"
                                v-model="form.button_url"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                placeholder="https://..."
                            />
                        </div>
                    </div>
                    <p v-if="form.errors.button_url" class="mt-1 text-sm text-red-600">{{ form.errors.button_url }}</p>
                </div>
            </div>
        </template>

        <!-- Publish Options -->
        <div class="border-t border-slate-200 pt-6">
            <div v-if="mode === 'create'" class="flex items-center">
                <input
                    id="publish_now"
                    type="checkbox"
                    v-model="form.publish_now"
                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded"
                />
                <label for="publish_now" class="ml-2 text-sm text-slate-700">
                    Publish immediately
                </label>
            </div>
            <div v-else class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-700">Publication Status</p>
                    <p class="text-sm text-slate-500">
                        {{ form.published_at ? 'This post is published' : 'This post is a draft' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-200">
            <a
                href="/portal/posts"
                class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900"
            >
                Cancel
            </a>
            <button
                type="button"
                :disabled="form.processing"
                @click="submitForm"
                class="px-6 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <span v-if="form.processing">Saving...</span>
                <span v-else>{{ mode === 'create' ? 'Create Post' : 'Save Changes' }}</span>
            </button>
        </div>
    </form>
</template>

