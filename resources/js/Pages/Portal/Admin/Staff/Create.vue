<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { computed } from 'vue';
import { 
    ChevronLeftIcon,
    AcademicCapIcon,
    ShieldCheckIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    grades: Array,
    staffEmailDomain: String,
});

const form = useForm({
    name: '',
    email: '',
    role: 'teacher',
    grade_ids: [],
});

const storeUrl = '/portal/admin/staff';

const submit = () => {
    form.post(storeUrl, {
        preserveScroll: false,
    });
};

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

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
    <Head title="Add Staff Member" />

    <PortalLayout>
        <template #header>Add Staff Member</template>

        <div class="max-w-2xl mx-auto space-y-6">
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

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <InformationCircleIcon class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm text-blue-800">
                            <strong>A welcome email will be sent automatically.</strong> When you create this staff member, 
                            a secure password will be generated and sent to their email address with login instructions.
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="hasErrors" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm font-medium text-red-800">Please fix the errors below and try again.</p>
                <ul v-if="Object.keys(form.errors).length > 1" class="mt-2 list-disc list-inside text-sm text-red-700">
                    <li v-for="(message, field) in form.errors" :key="field">{{ message }}</li>
                </ul>
            </div>

            <!-- Form -->
            <form
                @submit.prevent="submit"
                :action="storeUrl"
                method="post"
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6"
            >
                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-3">
                        Role
                    </label>
                    <div class="grid grid-cols-2 gap-4">
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
                        placeholder="Jane Smith"
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
                        :placeholder="`jane.smith@${staffEmailDomain}`"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <!-- Grade Assignment -->
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <label class="block text-sm font-medium text-slate-700">
                            Assign to Grade Levels
                        </label>
                        <div v-if="grades && grades.length > 0" class="flex items-center gap-2">
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
                    <div v-if="grades && grades.length > 0" class="border border-slate-200 rounded-lg divide-y divide-slate-200 max-h-64 overflow-y-auto">
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
                    <p v-if="form.errors.grade_ids" class="mt-2 text-sm text-red-600">{{ form.errors.grade_ids }}</p>
                    <p class="mt-2 text-xs text-slate-500">Select the grade levels this staff member will teach or manage.</p>
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
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Staff Member</span>
                    </button>
                </div>
            </form>
        </div>
    </PortalLayout>
</template>
