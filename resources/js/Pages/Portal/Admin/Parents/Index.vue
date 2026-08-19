<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ref, watch } from 'vue';
import { 
    UserGroupIcon,
    CheckCircleIcon,
    XCircleIcon,
    MagnifyingGlassIcon,
    PencilIcon,
    TrashIcon,
    EnvelopeIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';

// Simple debounce function
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    parents: Object,
    counts: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const confirmingDelete = ref(null);
const deleteConfirmText = ref('');

// Debounced search
const performSearch = debounce(() => {
    router.get('/portal/admin/parents', { search: search.value }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, () => {
    performSearch();
});

const deleteParent = (parentId) => {
    if (deleteConfirmText.value.toLowerCase() !== 'delete') {
        return;
    }
    router.delete(`/portal/admin/parents/${parentId}`, {
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

const resendVerification = (parentId) => {
    router.post(`/portal/admin/parents/${parentId}/resend-verification`);
};

const verifyEmail = (parentId) => {
    router.post(`/portal/admin/parents/${parentId}/verify-email`);
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Parent Management" />

    <PortalLayout>
        <template #header>Parent Management</template>

        <div class="min-w-0 max-w-full space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-slate-600">View and manage parent accounts and their linked students.</p>
                </div>
                <!-- Search -->
                <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search parents..."
                        class="pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 w-full sm:w-64"
                    />
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <UsersIcon class="w-6 h-6 text-slate-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ counts.total }}</p>
                            <p class="text-sm text-slate-600">Total Parents</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-emerald-50 rounded-lg">
                            <CheckCircleIcon class="w-6 h-6 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ counts.verified }}</p>
                            <p class="text-sm text-slate-600">Verified</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <XCircleIcon class="w-6 h-6 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ counts.pending }}</p>
                            <p class="text-sm text-slate-600">Pending Verification</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parents Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div v-if="parents.data.length === 0" class="p-12 text-center">
                    <UsersIcon class="mx-auto h-12 w-12 text-slate-400" />
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No parents found</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ search ? 'Try adjusting your search.' : 'Parents will appear here when they register.' }}
                    </p>
                </div>

                <table v-else class="hidden md:table min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Parent
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Children
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Joined
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-for="parent in parents.data" :key="parent.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-amber-700">
                                                {{ parent.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900">{{ parent.name }}</div>
                                        <div class="text-sm text-slate-500">{{ parent.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="parent.children.length > 0" class="space-y-1">
                                    <div 
                                        v-for="child in parent.children.slice(0, 2)" 
                                        :key="child.id"
                                        class="text-sm"
                                    >
                                        <span class="font-medium text-slate-700">{{ child.name }}</span>
                                        <span class="text-slate-400 text-xs ml-1">
                                            ({{ child.grade_name || 'No Grade' }})
                                        </span>
                                    </div>
                                    <div 
                                        v-if="parent.children.length > 2"
                                        class="text-xs text-slate-400"
                                    >
                                        +{{ parent.children.length - 2 }} more
                                    </div>
                                </div>
                                <span v-else class="text-sm text-slate-400 italic">No children linked</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <CheckCircleIcon 
                                        v-if="parent.email_verified_at" 
                                        class="w-4 h-4 text-emerald-500" 
                                    />
                                    <XCircleIcon 
                                        v-else 
                                        class="w-4 h-4 text-amber-500" 
                                    />
                                    <span :class="[
                                        'text-sm',
                                        parent.email_verified_at ? 'text-emerald-600' : 'text-amber-600'
                                    ]">
                                        {{ parent.email_verified_at ? 'Verified' : 'Pending' }}
                                    </span>
                                </div>
                                <!-- Quick verify/resend for pending -->
                                <div v-if="!parent.email_verified_at" class="mt-1 flex gap-2">
                                    <button
                                        @click="verifyEmail(parent.id)"
                                        class="text-xs text-emerald-600 hover:text-emerald-700"
                                    >
                                        Verify
                                    </button>
                                    <button
                                        @click="resendVerification(parent.id)"
                                        class="text-xs text-brand-600 hover:text-brand-700"
                                    >
                                        Resend
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ formatDate(parent.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/portal/admin/parents/${parent.id}/edit`"
                                        class="px-3 py-1 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-md transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="confirmingDelete !== parent.id"
                                        @click="confirmingDelete = parent.id"
                                        class="px-3 py-1 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-md transition-colors"
                                    >
                                        Delete
                                    </button>
                                    <div v-else class="flex items-center gap-2">
                                        <input
                                            v-model="deleteConfirmText"
                                            type="text"
                                            placeholder="Type 'delete'"
                                            class="w-24 px-2 py-1 text-xs border border-red-300 rounded-md focus:ring-red-500 focus:border-red-500"
                                            @keyup.enter="deleteParent(parent.id)"
                                        />
                                        <button
                                            @click="deleteParent(parent.id)"
                                            :disabled="deleteConfirmText.toLowerCase() !== 'delete'"
                                            :class="[
                                                'px-3 py-1 text-xs font-medium rounded-md transition-colors',
                                                deleteConfirmText.toLowerCase() === 'delete'
                                                    ? 'text-white bg-red-600 hover:bg-red-700'
                                                    : 'text-red-300 bg-red-100 cursor-not-allowed'
                                            ]"
                                        >
                                            Confirm
                                        </button>
                                        <button
                                            @click="cancelDelete"
                                            class="px-3 py-1 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mobile cards -->
                <div v-if="parents.data.length > 0" class="md:hidden divide-y divide-slate-200">
                    <div v-for="parent in parents.data" :key="'mobile-' + parent.id" class="p-4 space-y-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-amber-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-amber-700">
                                    {{ parent.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-slate-900 break-words">{{ parent.name }}</div>
                                <div class="text-sm text-slate-500 break-all">{{ parent.email }}</div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Children</p>
                            <div v-if="parent.children.length > 0" class="space-y-1">
                                <div v-for="child in parent.children" :key="child.id" class="text-sm break-words">
                                    <span class="font-medium text-slate-700">{{ child.name }}</span>
                                    <span class="text-slate-400 text-xs ml-1">({{ child.grade_name || 'No Grade' }})</span>
                                </div>
                            </div>
                            <span v-else class="text-sm text-slate-400 italic">No children linked</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <CheckCircleIcon v-if="parent.email_verified_at" class="w-4 h-4 text-emerald-500" />
                            <XCircleIcon v-else class="w-4 h-4 text-amber-500" />
                            <span :class="['text-sm', parent.email_verified_at ? 'text-emerald-600' : 'text-amber-600']">
                                {{ parent.email_verified_at ? 'Verified' : 'Pending' }}
                            </span>
                            <span class="text-sm text-slate-400">· Joined {{ formatDate(parent.created_at) }}</span>
                        </div>
                        <div v-if="!parent.email_verified_at" class="flex flex-wrap gap-2">
                            <button @click="verifyEmail(parent.id)" class="text-xs text-emerald-600 hover:text-emerald-700">
                                Verify
                            </button>
                            <button @click="resendVerification(parent.id)" class="text-xs text-brand-600 hover:text-brand-700">
                                Resend
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                :href="`/portal/admin/parents/${parent.id}/edit`"
                                class="px-3 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-md transition-colors"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="confirmingDelete !== parent.id"
                                @click="confirmingDelete = parent.id"
                                class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-md transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                        <div v-if="confirmingDelete === parent.id" class="space-y-2 rounded-lg border border-red-200 bg-red-50 p-3">
                            <input
                                v-model="deleteConfirmText"
                                type="text"
                                placeholder="Type 'delete'"
                                class="w-full px-2 py-1.5 text-sm border border-red-300 rounded-md focus:ring-red-500 focus:border-red-500"
                                @keyup.enter="deleteParent(parent.id)"
                            />
                            <div class="flex flex-wrap gap-2">
                                <button
                                    @click="deleteParent(parent.id)"
                                    :disabled="deleteConfirmText.toLowerCase() !== 'delete'"
                                    :class="[
                                        'px-3 py-1.5 text-xs font-medium rounded-md transition-colors',
                                        deleteConfirmText.toLowerCase() === 'delete'
                                            ? 'text-white bg-red-600 hover:bg-red-700'
                                            : 'text-red-300 bg-red-100 cursor-not-allowed',
                                    ]"
                                >
                                    Confirm
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
                <div v-if="parents.last_page > 1" class="px-4 sm:px-6 py-4 border-t border-slate-200 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        Showing {{ parents.from }} to {{ parents.to }} of {{ parents.total }} parents
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in parents.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-3 py-1 text-sm rounded-md',
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
    </PortalLayout>
</template>

