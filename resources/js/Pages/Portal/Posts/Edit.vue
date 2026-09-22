<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import PostForm from '@/Components/Portal/PostForm.vue';

const props = defineProps({
    post: Object,
    grades: Array,
    teachers: Array,
});

// Format datetime for input fields (YYYY-MM-DDTHH:MM)
// Backend stores Eastern wall-clock time; display in Eastern using formatToParts so we don't re-parse in local TZ
const formatDateTimeLocal = (dateString) => {
    if (!dateString) return '';
    // If string has no timezone (e.g. from DB), treat as UTC so Date parses correctly
    const normalized = String(dateString).endsWith('Z') || /[+-]\d{2}:?\d{2}$/.test(dateString)
        ? dateString
        : dateString.replace(/\.\d+$/, '') + 'Z';
    const date = new Date(normalized);
    const formatter = new Intl.DateTimeFormat('en-CA', {
        timeZone: 'America/New_York',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
    const parts = formatter.formatToParts(date);
    const get = (type) => parts.find((p) => p.type === type)?.value ?? '';
    return `${get('year')}-${get('month')}-${get('day')}T${get('hour')}:${get('minute')}`;
};

// Format date for input fields (YYYY-MM-DD)
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const form = useForm({
    type: props.post.type,
    is_school_closure: props.post.is_school_closure || false,
    is_public: props.post.is_public || false,
    event_visibility: props.post.is_public
        ? 'external'
        : (['teachers_only', 'grade_teachers', 'specific_teacher'].includes(props.post.audience) ? 'internal' : 'external'),
    audience: props.post.audience || 'all',
    target_grade_id: props.post.target_grade_id || null,
    target_teacher_id: props.post.target_teacher_id || null,
    title: props.post.title,
    content: props.post.content,
    image: null,
    remove_image: false,
    event_start_date: formatDateTimeLocal(props.post.event_start_date),
    event_end_date: formatDateTimeLocal(props.post.event_end_date),
    button_text: props.post.button_text || '',
    button_url: props.post.button_url || '',
    recurrence_type: props.post.recurrence_type || 'none',
    recurrence_end_date: formatDate(props.post.recurrence_end_date),
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
        // Explicitly convert booleans for FormData compatibility
        is_public: data.is_public ? '1' : '0',
        is_school_closure: data.is_school_closure ? '1' : '0',
    })).post(`/portal/posts/${props.post.slug}`, {
        forceFormData: true,
        onError: () => {
            nextTick(() => {
                document.getElementById('post-form-errors')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        },
    });
};
</script>

<template>
    <Head title="Edit Post" />

    <PortalLayout>
        <template #header>Edit Post</template>
        
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/portal/posts"
                    class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 mb-4"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Posts
                </Link>
                <p class="text-slate-600">Update your news article or event.</p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <PostForm 
                    :form="form" 
                    :post="post"
                    :grades="grades"
                    :teachers="teachers"
                    @submit="submit" 
                    mode="edit" 
                />
            </div>
        </div>
    </PortalLayout>
</template>

