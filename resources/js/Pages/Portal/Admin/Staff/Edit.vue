<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { computed } from 'vue';
import { 
    ChevronLeftIcon,
    AcademicCapIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    staff: Object,
    grades: Array,
});

const form = useForm({
    name: props.staff.name,
    email: props.staff.email,
    password: '',
    password_confirmation: '',
    role: props.staff.role === 'super_admin' ? 'admin' : props.staff.role,
    grade_ids: props.staff.grade_ids || [],
});

const updateUrl = computed(() => `/portal/admin/staff/${props.staff.id}`);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(updateUrl.value);
};

const isTeacher = computed(() => form.role === 'teacher');
const isSuperAdmin = computed(() => props.staff.role === 'super_admin');

const toggleGrade = (gradeId) => {
    const index = form.grade_ids.indexOf(gradeId);
    if (index === -1) {
        form.grade_ids.push(gradeId);
    } else {
        form.grade_ids.splice(index, 1);
    }
};

const selectAllGrades = () => {
    form.grade_ids = props.grades.map((grade) => grade.id);
};

const clearAllGrades = () => {
    form.grade_ids = [];
};
</script>

<template>
    <Head :title="`Edit ${staff.name}`" />

    <PortalLayout>
        <template #header>Edit Staff Member</template>

        <div class="min-w-0 max-w-full mx-auto space-y-6">
            <!-- Breadcrumb -->
            <div>
                <Link 
                    href="/portal/admin/staff"
                    class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900"
                >
                    <ChevronLeftIcon class="w-4 h-4 mr-1" />
                    Back to Staff Management
                </Link>
            </div>

            <!-- Super Admin Warning -->
            <div v-if="isSuperAdmin" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <p class="text-sm text-purple-800">
                    <strong>Super Admin Account:</strong> This account has full system access. Role cannot be changed.
                </p>
            </div>

            <!-- Form -->
            <form
                @submit.prevent="submit"
                :action="updateUrl"
                method="post"
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 space-y-6"
            >
                <input type="hidden" name="_method" value="PUT" />
                <!-- Role Selection -->
                <div v-if="!isSuperAdmin">
                    <label class="block text-sm font-medium text-slate-700 mb-3">
                        Role
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label 
                            :class="[
                                'relative flex flex-col items-center p-4 border-2 rounded-xl cursor-pointer transition-all',
                                form.role === 'teacher' 
                                    ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/20' 
                                    : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
                            ]"
                        >
                            <input 
                                type="radio" 
                                v-model="form.role" 
                                value="teacher" 
                                class="sr-only" 
                            />
                            <AcademicCapIcon 
                                :class="[
                                    'w-8 h-8 mb-2',
                                    form.role === 'teacher' ? 'text-emerald-600' : 'text-slate-400'
                                ]" 
                            />
                            <span 
                                :class="[
                                    'font-semibold',
                                    form.role === 'teacher' ? 'text-emerald-700' : 'text-slate-700'
                                ]"
                            >
                                Staff Member
                            </span>
                            <span class="text-xs text-slate-500 mt-1 text-center">
                                Can manage assigned grades
                            </span>
                        </label>
                        <label 
                            :class="[
                                'relative flex flex-col items-center p-4 border-2 rounded-xl cursor-pointer transition-all',
                                form.role === 'admin' 
                                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500/20' 
                                    : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
                            ]"
                        >
                            <input 
                                type="radio" 
                                v-model="form.role" 
                                value="admin" 
                                class="sr-only" 
                            />
                            <ShieldCheckIcon 
                                :class="[
                                    'w-8 h-8 mb-2',
                                    form.role === 'admin' ? 'text-blue-600' : 'text-slate-400'
                                ]" 
                            />
                            <span 
                                :class="[
                                    'font-semibold',
                                    form.role === 'admin' ? 'text-blue-700' : 'text-slate-700'
                                ]"
                            >
                                Administrator
                            </span>
                            <span class="text-xs text-slate-500 mt-1 text-center">
                                Full portal access
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="{ 'border-red-500': form.errors.name }"
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
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="{ 'border-red-500': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    <p v-if="!staff.email_verified_at" class="mt-1 text-xs text-amber-600">
                        Email not verified. Changing the email will require re-verification.
                    </p>
                </div>

                <!-- Password -->
                <div class="border-t border-slate-200 pt-6">
                    <h3 class="text-sm font-medium text-slate-700 mb-4">Change Password (leave blank to keep current)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                                New Password
                            </label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                :class="{ 'border-red-500': form.errors.password }"
                                placeholder="••••••••"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                                Confirm New Password
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>
                </div>

                <!-- Grade Assignment -->
                <div class="border-t border-slate-200 pt-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-2">
                        <label class="block text-sm font-medium text-slate-700">
                            Assign to Grade Levels
                        </label>
                        <div v-if="grades.length > 0" class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="selectAllGrades"
                                class="text-xs font-medium text-brand-700 hover:text-brand-800"
                            >
                                Select all
                            </button>
                            <span class="text-slate-300">|</span>
                            <button
                                type="button"
                                @click="clearAllGrades"
                                class="text-xs font-medium text-slate-600 hover:text-slate-800"
                            >
                                Clear all
                            </button>
                        </div>
                    </div>
                    <div v-if="grades.length > 0" class="border border-slate-200 rounded-lg divide-y divide-slate-200 max-h-64 overflow-y-auto">
                        <label 
                            v-for="grade in grades" 
                            :key="grade.id"
                            class="flex items-center px-4 py-3 hover:bg-slate-50 cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                :checked="form.grade_ids.includes(grade.id)"
                                @change="toggleGrade(grade.id)"
                                class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded"
                            />
                            <span class="ml-3 text-sm text-slate-700">{{ grade.name }}</span>
                        </label>
                    </div>
                    <p v-else class="text-sm text-slate-500 italic">No grade levels available.</p>
                    <p class="mt-2 text-xs text-slate-500">Select the grade levels this staff member teaches or manages.</p>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-200">
                    <Link
                        href="/portal/admin/staff"
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
