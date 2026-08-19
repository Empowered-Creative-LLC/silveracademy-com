<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { 
    AcademicCapIcon,
    UserGroupIcon,
    ArrowUpTrayIcon,
    ArrowDownTrayIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    grades: Array,
    totalStudents: Number,
});

const getTeacherNames = (teachers) => {
    if (!teachers || teachers.length === 0) return 'No teachers assigned';
    if (teachers.length <= 2) return teachers.map(t => t.name).join(', ');
    return teachers.slice(0, 2).map(t => t.name).join(', ') + ` +${teachers.length - 2} more`;
};
</script>

<template>
    <Head title="Grade Management" />

    <PortalLayout>
        <template #header>Grade Management</template>

        <div class="min-w-0 max-w-full space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-slate-600">Manage grades, teachers, and student assignments.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <a 
                        href="/portal/admin/students/template"
                        class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Download Template
                    </a>
                    <a 
                        href="/portal/admin/students/export"
                        class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Export Students
                    </a>
                    <Link
                        href="/portal/admin/students/import"
                        class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors"
                    >
                        <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
                        Import Students
                    </Link>
                </div>
            </div>

            <!-- Grades List -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-serif font-semibold text-slate-900">Grade Levels</h2>
                </div>

                <div v-if="grades.length === 0" class="p-12 text-center">
                    <AcademicCapIcon class="mx-auto h-12 w-12 text-slate-400" />
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No grades found</h3>
                    <p class="mt-1 text-sm text-slate-500">Run the grade seeder to populate grade levels.</p>
                </div>

                <div v-else class="divide-y divide-slate-200">
                    <Link
                        v-for="grade in grades"
                        :key="grade.id"
                        :href="`/portal/admin/grades/${grade.id}`"
                        class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 hover:bg-slate-50 transition-colors group"
                    >
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="p-3 bg-brand-50 rounded-lg group-hover:bg-brand-100 transition-colors flex-shrink-0">
                                <AcademicCapIcon class="w-6 h-6 text-brand-600" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-slate-900">{{ grade.name }}</h3>
                                <p class="text-sm text-slate-500 break-words">
                                    {{ getTeacherNames(grade.teachers) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6">
                            <div class="flex items-center gap-1 text-sm text-slate-600">
                                <UserGroupIcon class="w-4 h-4 flex-shrink-0" />
                                <span>{{ grade.students_count }} student{{ grade.students_count !== 1 ? 's' : '' }}</span>
                            </div>
                            <ChevronRightIcon class="w-5 h-5 text-slate-400 group-hover:text-slate-600 transition-colors flex-shrink-0" />
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <AcademicCapIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ grades.length }}</p>
                            <p class="text-sm text-slate-600">Grade Levels</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <UserGroupIcon class="w-6 h-6 text-purple-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ totalStudents }}</p>
                            <p class="text-sm text-slate-600">Total Students</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
