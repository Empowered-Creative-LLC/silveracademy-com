<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    posts: Object,
    filters: Object,
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const confirmingDelete = ref(null);
const deleteConfirmText = ref('');

const deletePost = (postSlug) => {
    if (deleteConfirmText.value.toLowerCase() !== 'delete') {
        return;
    }
    router.delete(`/portal/posts/${postSlug}`, {
        onSuccess: () => {
            confirmingDelete.value = null;
            deleteConfirmText.value = '';
        },
    });
};

const cancelDelete = () => {
    confirmingDelete.value = null;
    deleteConfirmText.value = '';
};

const togglePublish = (postSlug) => {
    router.post(`/portal/posts/${postSlug}/toggle-publish`);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Manage Posts" />

    <PortalLayout>
        <template #header>News & Events</template>
        
        <div class="min-w-0 max-w-full space-y-6">
            <div v-if="successMessage" class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                {{ successMessage }}
            </div>
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                {{ errorMessage }}
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-slate-600">Manage news articles and upcoming events.</p>
                </div>
                <Link
                    href="/portal/posts/create"
                    class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Post
                </Link>
            </div>

            <!-- Posts Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div v-if="posts.data.length === 0" class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No posts yet</h3>
                    <p class="mt-1 text-sm text-slate-500">Get started by creating your first news article or event.</p>
                    <div class="mt-6">
                        <Link
                            href="/portal/posts/create"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors"
                        >
                            Create Post
                        </Link>
                    </div>
                </div>

                <table v-else class="hidden md:table min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Title
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div v-if="post.image_path" class="flex-shrink-0 h-10 w-10">
                                        <img 
                                            :src="`/storage/${post.image_path}`" 
                                            :alt="post.title"
                                            class="h-10 w-10 rounded-lg object-cover"
                                        />
                                    </div>
                                    <div :class="post.image_path ? 'ml-4' : ''">
                                        <div class="text-sm font-medium text-slate-900">{{ post.title }}</div>
                                        <div class="text-sm text-slate-500">{{ post.author?.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <!-- School Closure Badge (takes priority) -->
                                    <span
                                        v-if="post.is_school_closure"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        No School
                                    </span>
                                    <!-- Regular Type Badge -->
                                    <span
                                        v-else
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            post.type === 'news' 
                                                ? 'bg-blue-100 text-blue-800' 
                                                : 'bg-purple-100 text-purple-800'
                                        ]"
                                    >
                                        {{ post.type === 'news' ? 'News' : 'Event' }}
                                    </span>
                                    <!-- Public Event indicator -->
                                    <span 
                                        v-if="post.type === 'event' && post.is_public"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800"
                                        title="Visible on public website"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Public
                                    </span>
                                    <!-- Audience indicator - All Staff -->
                                    <span 
                                        v-if="post.audience === 'teachers_only'"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"
                                        title="Only visible to all staff"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        All Staff
                                    </span>
                                    <!-- Audience indicator - Grade Level -->
                                    <span 
                                        v-if="post.audience === 'grade_teachers'"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"
                                        :title="`Only visible to ${post.target_grade?.name} teachers`"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ post.target_grade?.name || 'Grade' }}
                                    </span>
                                    <!-- Audience indicator - Specific Teacher -->
                                    <span 
                                        v-if="post.audience === 'specific_teacher'"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                        :title="`Only visible to ${post.target_teacher?.name}`"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ post.target_teacher?.name || 'Teacher' }}
                                    </span>
                                    <!-- Recurrence indicator -->
                                    <span 
                                        v-if="post.recurrence_type && post.recurrence_type !== 'none'"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800"
                                        :title="`Repeats ${post.recurrence_type}`"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        {{ post.recurrence_type === 'daily' ? 'Daily' : post.recurrence_type === 'weekly' ? 'Weekly' : post.recurrence_type === 'biweekly' ? 'Bi-weekly' : 'Monthly' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        post.published_at 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ post.published_at ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <div v-if="post.type === 'event' && post.event_start_date">
                                    {{ formatDate(post.event_start_date) }}
                                    <span v-if="post.event_end_date"> - {{ formatDate(post.event_end_date) }}</span>
                                </div>
                                <div v-else>
                                    {{ formatDate(post.created_at) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="togglePublish(post.slug)"
                                        :class="[
                                            'px-3 py-1 text-xs font-medium rounded-md transition-colors',
                                            post.published_at
                                                ? 'text-yellow-700 bg-yellow-50 hover:bg-yellow-100'
                                                : 'text-green-700 bg-green-50 hover:bg-green-100'
                                        ]"
                                    >
                                        {{ post.published_at ? 'Unpublish' : 'Publish' }}
                                    </button>
                                    <Link
                                        :href="`/portal/posts/${post.slug}/edit`"
                                        class="px-3 py-1 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-md transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="confirmingDelete !== post.slug"
                                        @click="confirmingDelete = post.slug"
                                        class="px-3 py-1 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-md transition-colors"
                                    >
                                        Delete
                                    </button>
                                    <div v-else class="flex flex-col items-end gap-2">
                                        <div class="flex items-center gap-2">
                                            <input
                                                v-model="deleteConfirmText"
                                                type="text"
                                                placeholder="Type 'delete'"
                                                class="w-28 px-2 py-1 text-xs border border-red-300 rounded-md focus:ring-red-500 focus:border-red-500"
                                                @keyup.enter="deletePost(post.slug)"
                                            />
                                            <button
                                                @click="deletePost(post.slug)"
                                                :disabled="deleteConfirmText.toLowerCase() !== 'delete'"
                                                :class="[
                                                    'px-3 py-1 text-xs font-medium rounded-md transition-colors',
                                                    deleteConfirmText.toLowerCase() === 'delete'
                                                        ? 'text-white bg-red-600 hover:bg-red-700'
                                                        : 'text-red-300 bg-red-100 cursor-not-allowed'
                                                ]"
                                            >
                                                Delete
                                            </button>
                                            <button
                                                @click="cancelDelete"
                                                class="px-3 py-1 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mobile cards -->
                <div v-if="posts.data.length > 0" class="md:hidden divide-y divide-slate-200">
                    <div v-for="post in posts.data" :key="'mobile-' + post.id" class="p-4 space-y-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <img
                                v-if="post.image_path"
                                :src="`/storage/${post.image_path}`"
                                :alt="post.title"
                                class="h-10 w-10 flex-shrink-0 rounded-lg object-cover"
                            />
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-slate-900 break-words">{{ post.title }}</div>
                                <div class="text-sm text-slate-500 break-all">{{ post.author?.name }}</div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-if="post.is_school_closure"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                            >
                                No School
                            </span>
                            <span
                                v-else
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    post.type === 'news' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800',
                                ]"
                            >
                                {{ post.type === 'news' ? 'News' : 'Event' }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    post.published_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                                ]"
                            >
                                {{ post.published_at ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-500">
                            <template v-if="post.type === 'event' && post.event_start_date">
                                {{ formatDate(post.event_start_date) }}
                            </template>
                            <template v-else>
                                {{ formatDate(post.created_at) }}
                            </template>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="togglePublish(post.slug)"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-md transition-colors',
                                    post.published_at
                                        ? 'text-yellow-700 bg-yellow-50 hover:bg-yellow-100'
                                        : 'text-green-700 bg-green-50 hover:bg-green-100',
                                ]"
                            >
                                {{ post.published_at ? 'Unpublish' : 'Publish' }}
                            </button>
                            <Link
                                :href="`/portal/posts/${post.slug}/edit`"
                                class="px-3 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-md transition-colors"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="confirmingDelete !== post.slug"
                                @click="confirmingDelete = post.slug"
                                class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-md transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                        <div v-if="confirmingDelete === post.slug" class="space-y-2 rounded-lg border border-red-200 bg-red-50 p-3">
                            <input
                                v-model="deleteConfirmText"
                                type="text"
                                placeholder="Type 'delete'"
                                class="w-full px-2 py-1.5 text-sm border border-red-300 rounded-md focus:ring-red-500 focus:border-red-500"
                                @keyup.enter="deletePost(post.slug)"
                            />
                            <div class="flex flex-wrap gap-2">
                                <button
                                    @click="deletePost(post.slug)"
                                    :disabled="deleteConfirmText.toLowerCase() !== 'delete'"
                                    :class="[
                                        'px-3 py-1.5 text-xs font-medium rounded-md transition-colors',
                                        deleteConfirmText.toLowerCase() === 'delete'
                                            ? 'text-white bg-red-600 hover:bg-red-700'
                                            : 'text-red-300 bg-red-100 cursor-not-allowed',
                                    ]"
                                >
                                    Delete
                                </button>
                                <button
                                    @click="cancelDelete"
                                    class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white hover:bg-slate-100 rounded-md transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="posts.data.length > 0 && posts.last_page > 1" class="px-4 sm:px-6 py-4 border-t border-slate-200">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-600">
                            Showing {{ posts.from }} to {{ posts.to }} of {{ posts.total }} results
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="link in posts.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md transition-colors',
                                    link.active
                                        ? 'bg-brand-600 text-white'
                                        : link.url
                                            ? 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                                            : 'bg-slate-50 text-slate-400 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>

