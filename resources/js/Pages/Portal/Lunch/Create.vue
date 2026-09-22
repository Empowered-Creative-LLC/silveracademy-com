<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    prefilledDate: {
        type: String,
        default: null,
    },
});

const form = useForm({
    menu_date: props.prefilledDate || getTodayOrNextWeekday(),
    content: '',
});

// Get today's date, or next weekday if today is a weekend
function getTodayOrNextWeekday() {
    const today = new Date();
    const dayOfWeek = today.getDay();
    
    // If weekend, get next Monday
    if (dayOfWeek === 0) {
        today.setDate(today.getDate() + 1);
    } else if (dayOfWeek === 6) {
        today.setDate(today.getDate() + 2);
    }
    
    return today.toISOString().split('T')[0];
}

const localError = ref('');

const submit = () => {
    localError.value = '';

    const dateInput = document.getElementById('menu_date');
    const contentInput = document.getElementById('content');

    if (dateInput instanceof HTMLInputElement && dateInput.value) {
        form.menu_date = dateInput.value;
    }
    if (contentInput instanceof HTMLTextAreaElement) {
        form.content = contentInput.value;
    }

    if (dateInput instanceof HTMLInputElement && dateInput.validity?.badInput) {
        localError.value = 'Choose a valid menu date.';
        return;
    }
    if (!form.menu_date) {
        localError.value = 'Choose a menu date.';
        return;
    }
    if (!String(form.content || '').trim()) {
        localError.value = 'Enter the lunch menu for this day.';
        return;
    }

    form.post('/portal/lunch');
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('en-US', { 
        weekday: 'long',
        month: 'long', 
        day: 'numeric',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Add Lunch Menu" />

    <PortalLayout>
        <template #header>Add Lunch Menu</template>
        
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/portal/calendar?view=lunch"
                    class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 mb-4"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-1" />
                    Back to Calendar
                </Link>
                <p class="text-slate-600">Create a lunch menu for a specific day.</p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div v-if="localError || form.errors.menu_date || form.errors.content" id="lunch-form-errors" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ localError || form.errors.menu_date || form.errors.content }}
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Menu Date -->
                    <div>
                        <label for="menu_date" class="block text-sm font-medium text-slate-700 mb-1">
                            Menu Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="menu_date"
                            type="date"
                            v-model="form.menu_date"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                        <p class="mt-1 text-sm text-slate-500">
                            Selected: {{ formatDate(form.menu_date) }}
                        </p>
                        <p v-if="form.errors.menu_date" class="mt-1 text-sm text-red-600">{{ form.errors.menu_date }}</p>
                    </div>

                    <!-- Menu Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-slate-700 mb-1">
                            Menu Content <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="content"
                            v-model="form.content"
                            rows="8"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            placeholder="Example: pizza, salad bar, and fruit"
                        ></textarea>
                        <p class="mt-1 text-sm text-slate-500">Describe the lunch menu for this day. HTML formatting is supported.</p>
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-200">
                        <Link
                            href="/portal/calendar?view=lunch"
                            class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900"
                        >
                            Cancel
                        </Link>
                        <button
                            type="button"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            @click="submit"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Menu</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>
