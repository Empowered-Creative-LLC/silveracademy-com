<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ref, computed } from 'vue';
import { 
    ArrowLeftIcon, 
    ArrowUpTrayIcon, 
    DocumentArrowDownIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';

const page = usePage();

const form = useForm({
    file: null,
});

const dragActive = ref(false);
const fileInput = ref(null);
const selectedFileName = ref('');

const flash = computed(() => page.props.flash || {});
const hasErrors = computed(() => flash.value.import_notices && flash.value.import_notices.length > 0);

const handleDragEnter = (e) => {
    e.preventDefault();
    dragActive.value = true;
};

const handleDragLeave = (e) => {
    e.preventDefault();
    dragActive.value = false;
};

const handleDrop = (e) => {
    e.preventDefault();
    dragActive.value = false;
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
};

const handleFileSelect = (e) => {
    const files = e.target.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
};

const handleFile = (file) => {
    const validTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv'
    ];
    
    if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
        alert('Please select a valid Excel file (.xlsx, .xls) or CSV file.');
        return;
    }
    
    form.file = file;
    selectedFileName.value = file.name;
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const clearFile = () => {
    form.file = null;
    selectedFileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post('/portal/lunch/import', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Import Lunch Menus" />

    <PortalLayout>
        <template #header>Import Lunch Menus</template>
        
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
                <p class="text-slate-600">
                    Upload an Excel file to bulk import lunch menus. This is perfect for planning an entire month at once.
                </p>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="flash.success === true" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-medium text-green-800">{{ flash.message }}</p>
                        <div v-if="flash.import_stats" class="mt-2 text-sm text-green-700">
                            <p>Created: {{ flash.import_stats.created }} menus</p>
                            <p>Updated: {{ flash.import_stats.updated }} menus</p>
                            <p v-if="flash.import_stats.skipped">Skipped: {{ flash.import_stats.skipped }} rows</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="flash.success === false || flash.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <ExclamationCircleIcon class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-medium text-red-800">{{ flash.error || flash.message }}</p>
                    </div>
                </div>
            </div>

            <div v-if="hasErrors" class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <InformationCircleIcon class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-medium text-amber-800 mb-2">Import Notices</p>
                        <ul class="text-sm text-amber-700 space-y-1">
                            <li v-for="(error, index) in flash.import_notices" :key="index">{{ error }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Template Download -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-serif font-semibold text-slate-900">Download Template</h3>
                        <p class="text-sm text-slate-500 mt-1">
                            Start with our template file that includes sample data and instructions.
                        </p>
                    </div>
                    <a
                        href="/portal/lunch/template"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors"
                    >
                        <DocumentArrowDownIcon class="w-5 h-5" />
                        Download Template
                    </a>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-serif font-semibold text-slate-900 mb-4">Upload File</h3>
                
                <form @submit.prevent="submit">
                    <!-- Drop Zone -->
                    <div
                        @dragenter="handleDragEnter"
                        @dragover.prevent
                        @dragleave="handleDragLeave"
                        @drop="handleDrop"
                        @click="triggerFileInput"
                        :class="[
                            'relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-colors',
                            dragActive 
                                ? 'border-amber-400 bg-amber-50' 
                                : selectedFileName 
                                    ? 'border-green-300 bg-green-50' 
                                    : 'border-slate-300 hover:border-slate-400 hover:bg-slate-50'
                        ]"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            @change="handleFileSelect"
                            class="hidden"
                        />
                        
                        <div v-if="selectedFileName" class="space-y-2">
                            <CheckCircleIcon class="w-12 h-12 text-green-500 mx-auto" />
                            <p class="font-medium text-green-700">{{ selectedFileName }}</p>
                            <button
                                type="button"
                                @click.stop="clearFile"
                                class="text-sm text-slate-500 hover:text-slate-700 underline"
                            >
                                Choose a different file
                            </button>
                        </div>
                        
                        <div v-else class="space-y-2">
                            <ArrowUpTrayIcon class="w-12 h-12 text-slate-400 mx-auto" />
                            <p class="font-medium text-slate-700">
                                Drop your Excel file here, or click to browse
                            </p>
                            <p class="text-sm text-slate-500">
                                Supports .xlsx, .xls, and .csv files (max 5MB)
                            </p>
                        </div>
                    </div>

                    <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>

                    <!-- Instructions -->
                    <div class="mt-6 p-4 bg-slate-50 rounded-lg">
                        <h4 class="font-medium text-slate-900 mb-2">Required Columns</h4>
                        <ul class="text-sm text-slate-600 space-y-1">
                            <li><strong>Date</strong> - The menu date (MM/DD/YYYY or YYYY-MM-DD format)</li>
                            <li><strong>Menu Content</strong> - The lunch menu description for that day</li>
                            <li><span class="text-slate-400">(Optional)</span> <strong>Day of Week</strong> - Will be calculated automatically</li>
                        </ul>
                        <p class="mt-3 text-sm text-slate-500">
                            <strong>Note:</strong> Weekend dates will be skipped. If a menu already exists for a date, it will be updated.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-200">
                        <Link
                            href="/portal/calendar?view=lunch"
                            class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="!form.file || form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <ArrowUpTrayIcon v-if="!form.processing" class="w-5 h-5" />
                            <span v-if="form.processing">Importing...</span>
                            <span v-else>Import Menus</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>

