<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import PostForm from '@/Components/Portal/PostForm.vue';

const props = defineProps({
    grades: Array,
    teachers: Array,
    initialType: {
        type: String,
        default: 'news',
    },
});

const form = useForm({
    type: props.initialType === 'event' ? 'event' : 'news',
    is_school_closure: false,
    is_public: false,
    event_visibility: 'internal',
    audience: 'all',
    target_grade_id: null,
    target_teacher_id: null,
    title: '',
    content: '',
    image: null,
    event_start_date: '',
    event_end_date: '',
    button_text: '',
    button_url: '',
    recurrence_type: 'none',
    recurrence_end_date: '',
    publish_now: true,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        // Explicitly convert booleans for FormData compatibility
        is_public: data.is_public ? '1' : '0',
        is_school_closure: data.is_school_closure ? '1' : '0',
        publish_now: data.publish_now ? '1' : '0',
    })).post('/portal/posts', {
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
    <Head title="Create Post" />

    <PortalLayout>
        <template #header>Create New Post</template>
        
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
                <p class="text-slate-600">Create a news article or event to share with your community.</p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <PostForm 
                    :form="form" 
                    :grades="grades" 
                    :teachers="teachers" 
                    @submit="submit" 
                    mode="create" 
                />
            </div>
        </div>
    </PortalLayout>
</template>

