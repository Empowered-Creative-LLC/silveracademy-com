<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { computed, watch } from 'vue';
import {
    CalendarIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    grades: Array,
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const form = useForm({
    title: '',
    content: '',
    target_grade_id: props.grades?.[0]?.id || '',
    event_start_date: '',
    event_end_date: '',
    is_all_day: false,
});

const dateOnly = (value) => String(value || '').slice(0, 10);

watch(() => form.is_all_day, (allDay) => {
    if (!allDay) {
        return;
    }
    form.event_start_date = dateOnly(form.event_start_date);
    form.event_end_date = dateOnly(form.event_end_date);
});

const submit = () => {
    if (form.is_all_day) {
        form.event_start_date = dateOnly(form.event_start_date);
        form.event_end_date = dateOnly(form.event_end_date);
    }

    form.transform((data) => ({
        ...data,
        is_all_day: data.is_all_day ? '1' : '0',
    })).post('/portal/teacher-events', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Add Grade Event" />

    <PortalLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link href="/portal/calendar" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <span>Add Event for a Grade</span>
            </div>
        </template>

        <div class="max-w-2xl mx-auto">
            <div v-if="successMessage" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-green-600" />
                    <p class="text-sm text-green-800">{{ successMessage }}</p>
                </div>
            </div>
            <div v-if="errorMessage" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                    <p class="text-sm text-red-800">{{ errorMessage }}</p>
                </div>
            </div>

            <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <CalendarIcon class="w-5 h-5 text-emerald-600 mt-0.5" />
                    <div>
                        <p class="text-sm font-medium text-emerald-800">Add an event for your grade</p>
                        <p class="text-sm text-emerald-700 mt-1">
                            Every teacher assigned to the grade can add an event. Families in that grade see it on their calendar with your name. It does not appear for other grades or on the public website.
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="!grades || grades.length === 0" class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
                <ExclamationTriangleIcon class="w-12 h-12 text-amber-500 mx-auto mb-3" />
                <h3 class="text-lg font-semibold text-amber-800">No Grades Assigned</h3>
                <p class="text-amber-700 mt-2">
                    You are not currently assigned to any grades. Please contact an administrator to be assigned to a grade level.
                </p>
                <Link href="/portal/calendar" class="inline-block mt-4 text-amber-700 hover:text-amber-800 font-medium">
                    ← Back to Calendar
                </Link>
            </div>

            <form v-else @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-semibold text-slate-900">New Grade Event</h2>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <label for="target_grade_id" class="block text-sm font-medium text-slate-700 mb-1">
                            Grade <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="target_grade_id"
                            v-model="form.target_grade_id"
                            required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option v-for="grade in grades" :key="grade.id" :value="grade.id">
                                {{ grade.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.target_grade_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.target_grade_id }}
                        </p>
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-slate-700 mb-1">
                            Event Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="e.g., 1st Grade Field Trip"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.is_all_day"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        <span>
                            <span class="block text-sm font-medium text-slate-800">All day</span>
                            <span class="block text-sm text-slate-500">No time is needed. Use this when the event covers the whole date.</span>
                        </span>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="event_start_date" class="block text-sm font-medium text-slate-700 mb-1">
                                Starts <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="event_start_date"
                                v-model="form.event_start_date"
                                :type="form.is_all_day ? 'date' : 'datetime-local'"
                                required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p v-if="form.errors.event_start_date" class="mt-1 text-sm text-red-600">{{ form.errors.event_start_date }}</p>
                        </div>
                        <div>
                            <label for="event_end_date" class="block text-sm font-medium text-slate-700 mb-1">
                                Ends
                            </label>
                            <input
                                id="event_end_date"
                                v-model="form.event_end_date"
                                :type="form.is_all_day ? 'date' : 'datetime-local'"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p v-if="form.errors.event_end_date" class="mt-1 text-sm text-red-600">{{ form.errors.event_end_date }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-slate-700 mb-1">
                            Details <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="content"
                            v-model="form.content"
                            rows="6"
                            required
                            placeholder="Write the details families should know..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                        ></textarea>
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <Link href="/portal/calendar" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50"
                    >
                        Add Event
                    </button>
                </div>
            </form>
        </div>
    </PortalLayout>
</template>
