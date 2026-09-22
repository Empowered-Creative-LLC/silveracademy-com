<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { computed } from 'vue';
import { 
    PlusIcon,
    TrashIcon,
    NewspaperIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    posts: Array,
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);
const currentUserId = computed(() => page.props.auth?.user?.id);
const canDelete = (post) => post.user_id === currentUserId.value || ['admin', 'super_admin'].includes(page.props.auth?.user?.role);

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric',
        timeZone: 'America/New_York',
    });
};

const deletePost = (post) => {
    if (confirm('Are you sure you want to delete this news post?')) {
        router.delete(`/portal/teacher-news/${post.id}`);
    }
};
</script>

<template>
    <Head title="My Grade News" />

    <PortalLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <span>My Grade News</span>
                <Link
                    href="/portal/teacher-news/create"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors text-sm"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Post News
                </Link>
            </div>
        </template>

        <div class="max-w-4xl mx-auto">
            <!-- Success Message -->
            <div v-if="successMessage" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-green-600" />
                    <p class="text-sm text-green-800">{{ successMessage }}</p>
                </div>
            </div>

            <!-- Posts List -->
            <div v-if="posts && posts.length > 0" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="divide-y divide-slate-200">
                    <div
                        v-for="post in posts"
                        :key="post.id"
                        class="px-6 py-4 hover:bg-slate-50 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-semibold text-slate-900">{{ post.title }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                        {{ post.target_grade?.name }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                                    {{ post.content.replace(/<[^>]*>/g, '').substring(0, 150) }}{{ post.content.length > 150 ? '...' : '' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-2">
                                    Posted {{ formatDate(post.published_at) }} by {{ post.author?.name || 'Staff' }}
                                </p>
                            </div>
                            <button
                                v-if="canDelete(post)"
                                @click="deletePost(post)"
                                class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                title="Delete"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <NewspaperIcon class="w-12 h-12 text-slate-300 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No News Posted Yet</h3>
                <p class="text-slate-500 mb-6">Grade messages sent to families will show up here.</p>
                <Link
                    href="/portal/teacher-news/create"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Post Your First News
                </Link>
            </div>
        </div>
    </PortalLayout>
</template>
