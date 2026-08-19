<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ref, computed } from 'vue';
import { 
    UserIcon,
    EnvelopeIcon,
    KeyIcon,
    UsersIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    parent: Object,
    students: Array,
});

const form = useForm({
    name: props.parent.name,
    email: props.parent.email,
    password: '',
    password_confirmation: '',
    children_ids: props.parent.children_ids || [],
});

const showPasswordFields = ref(false);

const submit = () => {
    form.put(`/portal/admin/parents/${props.parent.id}`);
};

// Filter students by search
const studentSearch = ref('');
const filteredStudents = computed(() => {
    if (!studentSearch.value) return props.students;
    const search = studentSearch.value.toLowerCase();
    return props.students.filter(s => 
        s.name.toLowerCase().includes(search) || 
        s.grade_name?.toLowerCase().includes(search)
    );
});

const toggleChild = (studentId) => {
    const index = form.children_ids.indexOf(studentId);
    if (index === -1) {
        form.children_ids.push(studentId);
    } else {
        form.children_ids.splice(index, 1);
    }
};

const isChildSelected = (studentId) => {
    return form.children_ids.includes(studentId);
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Edit Parent" />

    <PortalLayout>
        <template #header>Edit Parent Account</template>

        <div class="min-w-0 max-w-full mx-auto">
            <!-- Back Link -->
            <div class="mb-6">
                <Link
                    href="/portal/admin/parents"
                    class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Parents
                </Link>
            </div>

            <!-- Account Status Banner -->
            <div 
                :class="[
                    'mb-6 rounded-xl p-4 flex items-center gap-3',
                    parent.email_verified_at ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200'
                ]"
            >
                <CheckCircleIcon v-if="parent.email_verified_at" class="w-5 h-5 text-emerald-600" />
                <XCircleIcon v-else class="w-5 h-5 text-amber-600" />
                <div>
                    <p :class="[
                        'font-medium',
                        parent.email_verified_at ? 'text-emerald-800' : 'text-amber-800'
                    ]">
                        {{ parent.email_verified_at ? 'Email Verified' : 'Email Pending Verification' }}
                    </p>
                    <p :class="[
                        'text-sm',
                        parent.email_verified_at ? 'text-emerald-600' : 'text-amber-600'
                    ]">
                        Account created: {{ formatDate(parent.created_at) }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Account Information -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-serif font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <UserIcon class="w-5 h-5 text-slate-500" />
                        Account Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                required
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Password Change -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h2 class="text-lg font-serif font-semibold text-slate-900 flex items-center gap-2">
                            <KeyIcon class="w-5 h-5 text-slate-500" />
                            Password
                        </h2>
                        <button
                            type="button"
                            @click="showPasswordFields = !showPasswordFields"
                            class="text-sm text-brand-600 hover:text-brand-700"
                        >
                            {{ showPasswordFields ? 'Cancel' : 'Change Password' }}
                        </button>
                    </div>
                    
                    <div v-if="showPasswordFields" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                                New Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                placeholder="Enter new password"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                                Confirm Password
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                placeholder="Confirm new password"
                            />
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500">
                        Click "Change Password" to reset this parent's password.
                    </p>
                </div>

                <!-- Linked Children -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-serif font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <UsersIcon class="w-5 h-5 text-slate-500" />
                        Linked Children
                        <span class="text-sm font-normal text-slate-500">({{ form.children_ids.length }} selected)</span>
                    </h2>

                    <!-- Search Students -->
                    <div class="mb-4">
                        <input
                            v-model="studentSearch"
                            type="text"
                            placeholder="Search students..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                    </div>

                    <!-- Students List -->
                    <div v-if="students.length > 0" class="max-h-64 overflow-y-auto border border-slate-200 rounded-lg divide-y divide-slate-200">
                        <label
                            v-for="student in filteredStudents"
                            :key="student.id"
                            :class="[
                                'flex items-center px-4 py-3 cursor-pointer transition-colors',
                                isChildSelected(student.id) ? 'bg-brand-50' : 'hover:bg-slate-50'
                            ]"
                        >
                            <input
                                type="checkbox"
                                :checked="isChildSelected(student.id)"
                                @change="toggleChild(student.id)"
                                class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded"
                            />
                            <div class="ml-3">
                                <p class="text-sm font-medium text-slate-900">{{ student.name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ student.grade_name || 'No Grade' }}
                                    <span v-if="student.classroom_name" class="ml-1">• {{ student.classroom_name }}</span>
                                </p>
                            </div>
                        </label>
                    </div>
                    <p v-else class="text-sm text-slate-500 text-center py-8">
                        No students have been added to the system yet.
                    </p>

                    <p v-if="form.errors.children_ids" class="mt-2 text-sm text-red-600">{{ form.errors.children_ids }}</p>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-4">
                    <Link
                        href="/portal/admin/parents"
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </PortalLayout>
</template>

