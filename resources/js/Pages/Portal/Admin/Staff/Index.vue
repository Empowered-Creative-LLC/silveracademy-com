<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ref, computed } from 'vue';
import { 
    UserGroupIcon,
    AcademicCapIcon,
    ShieldCheckIcon,
    PlusIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowUpTrayIcon,
    ArrowDownTrayIcon,
    EnvelopeIcon,
    ClockIcon,
    ClipboardDocumentIcon,
    DocumentArrowDownIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    staff: Array,
    counts: Object,
});

const page = usePage();

// Credentials from flash (after sending welcome emails)
const credentials = computed(() => page.props.flash?.credentials || []);
const hasCredentials = computed(() => credentials.value.length > 0);

// Staff pending credentials
const pendingStaff = computed(() => props.staff.filter(s => !s.has_credentials));
const hasPendingStaff = computed(() => pendingStaff.value.length > 0);

// Delete confirmation
const confirmingDelete = ref(null);
const deleteConfirmText = ref('');

// Sending state
const sendingWelcome = ref(null);
const sendingAllWelcome = ref(false);

const deleteStaff = (staffId) => {
    if (deleteConfirmText.value.toLowerCase() !== 'delete') {
        return;
    }
    router.delete(`/portal/admin/staff/${staffId}`, {
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

const sendWelcomeEmail = (staffId) => {
    sendingWelcome.value = staffId;
    router.post(`/portal/admin/staff/${staffId}/send-welcome`, {}, {
        preserveScroll: true,
        onFinish: () => {
            sendingWelcome.value = null;
        },
    });
};

const sendAllPendingWelcomeEmails = () => {
    sendingAllWelcome.value = true;
    router.post('/portal/admin/staff/send-all-pending-welcome', {}, {
        preserveScroll: true,
        onFinish: () => {
            sendingAllWelcome.value = false;
        },
    });
};

const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'super_admin':
            return 'bg-purple-100 text-purple-800';
        case 'admin':
            return 'bg-blue-100 text-blue-800';
        case 'teacher':
            return 'bg-emerald-100 text-emerald-800';
        default:
            return 'bg-slate-100 text-slate-800';
    }
};

const copyCredentials = () => {
    const text = credentials.value.map(c => `${c.name}\t${c.email}\t${c.password}`).join('\n');
    navigator.clipboard.writeText(text);
};

const downloadCredentials = () => {
    const csv = 'Name,Email,Password\n' + credentials.value.map(c => `"${c.name}","${c.email}","${c.password}"`).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `staff-credentials-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
};
</script>

<template>
    <Head title="Staff Management" />

    <PortalLayout>
        <template #header>Staff Management</template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <p class="text-sm font-medium text-emerald-800">{{ page.props.flash.success }}</p>
            </div>
            <div v-if="page.props.flash?.error" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm font-medium text-red-800">{{ page.props.flash.error }}</p>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-slate-600">Manage staff members, assign roles, and grade level assignments.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a 
                        href="/portal/admin/staff/template"
                        class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Download Template
                    </a>
                    <Link
                        href="/portal/admin/staff/import"
                        class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors"
                    >
                        <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
                        Import Staff
                    </Link>
                    <Link
                        href="/portal/admin/staff/create"
                        class="inline-flex items-center px-4 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Add Staff Member
                    </Link>
                </div>
            </div>

            <!-- Credentials Display (shown after sending welcome emails) -->
            <div v-if="hasCredentials" class="bg-green-50 border border-green-200 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-green-200 flex items-center gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-green-600" />
                    <div>
                        <h3 class="font-semibold text-green-800">Welcome Emails Sent</h3>
                        <p class="text-sm text-green-700">{{ credentials.length }} staff member(s) received their credentials</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-white rounded-lg border border-green-200 overflow-hidden">
                        <div class="max-h-48 overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-slate-600">Name</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-600">Email</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-600">Password</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <tr v-for="cred in credentials" :key="cred.email">
                                        <td class="px-3 py-2 text-slate-900">{{ cred.name }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ cred.email }}</td>
                                        <td class="px-3 py-2 font-mono text-slate-600">{{ cred.password }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="copyCredentials"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-white text-slate-700 font-medium rounded-lg border border-slate-300 hover:bg-slate-50 transition-colors"
                        >
                            <ClipboardDocumentIcon class="w-4 h-4" />
                            Copy to Clipboard
                        </button>
                        <button
                            @click="downloadCredentials"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors"
                        >
                            <DocumentArrowDownIcon class="w-4 h-4" />
                            Download CSV
                        </button>
                    </div>
                    <p class="text-xs text-green-700">
                        <ExclamationTriangleIcon class="w-4 h-4 inline-block mr-1" />
                        Save these credentials securely. Passwords are not stored in plain text and cannot be retrieved later.
                    </p>
                </div>
            </div>

            <!-- Pending Credentials Alert -->
            <div v-if="hasPendingStaff && !hasCredentials" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <ClockIcon class="w-5 h-5 text-amber-700" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-amber-900">
                                {{ counts.pending_credentials }} staff member{{ counts.pending_credentials > 1 ? 's' : '' }} pending credentials
                            </p>
                            <p class="text-sm text-amber-700">
                                These staff members cannot log in until you send them their welcome email with login credentials.
                            </p>
                        </div>
                    </div>
                    <button
                        @click="sendAllPendingWelcomeEmails"
                        :disabled="sendingAllWelcome"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 disabled:opacity-50 transition-colors"
                    >
                        <EnvelopeIcon class="w-4 h-4 mr-2" />
                        {{ sendingAllWelcome ? 'Sending...' : 'Send All Welcome Emails' }}
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <UserGroupIcon class="w-6 h-6 text-slate-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ counts.total }}</p>
                            <p class="text-sm text-slate-600">Total Staff</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-emerald-50 rounded-lg">
                            <AcademicCapIcon class="w-6 h-6 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ counts.teachers }}</p>
                            <p class="text-sm text-slate-600">Staff Members</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <ShieldCheckIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ counts.admins }}</p>
                            <p class="text-sm text-slate-600">Administrators</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-4">
                        <div :class="['p-3 rounded-lg', counts.pending_credentials > 0 ? 'bg-amber-50' : 'bg-green-50']">
                            <ClockIcon :class="['w-6 h-6', counts.pending_credentials > 0 ? 'text-amber-600' : 'text-green-600']" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ counts.pending_credentials }}</p>
                            <p class="text-sm text-slate-600">Pending Credentials</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div v-if="staff.length === 0" class="p-12 text-center">
                    <UserGroupIcon class="mx-auto h-12 w-12 text-slate-400" />
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No staff members</h3>
                    <p class="mt-1 text-sm text-slate-500">Get started by adding your first staff member.</p>
                    <div class="mt-6">
                        <Link
                            href="/portal/admin/staff/create"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors"
                        >
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Add Staff Member
                        </Link>
                    </div>
                </div>

                <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Grade Levels
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Credentials
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-for="member in staff" :key="member.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-brand-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-brand-700">
                                                {{ member.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900">{{ member.name }}</div>
                                        <div class="text-sm text-slate-500">{{ member.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        getRoleBadgeClass(member.role)
                                    ]"
                                >
                                    {{ member.role_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="member.grades && member.grades.length > 0" class="flex flex-wrap gap-1">
                                    <span 
                                        v-for="grade in member.grades.slice(0, 3)" 
                                        :key="grade.id"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700"
                                    >
                                        {{ grade.name }}
                                    </span>
                                    <span 
                                        v-if="member.grades.length > 3"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-500"
                                    >
                                        +{{ member.grades.length - 3 }} more
                                    </span>
                                </div>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="member.has_credentials" class="flex items-center gap-1">
                                    <CheckCircleIcon class="w-4 h-4 text-emerald-500" />
                                    <span class="text-sm text-slate-600">Sent</span>
                                </div>
                                <div v-else class="flex items-center gap-1">
                                    <ClockIcon class="w-4 h-4 text-amber-500" />
                                    <span class="text-sm text-amber-600">Pending</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Send Welcome Email (only for staff without credentials) -->
                                    <button
                                        v-if="!member.has_credentials && member.role !== 'super_admin'"
                                        @click="sendWelcomeEmail(member.id)"
                                        :disabled="sendingWelcome === member.id"
                                        class="px-3 py-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-md transition-colors disabled:opacity-50"
                                        :title="'Send welcome email to ' + member.name"
                                    >
                                        <EnvelopeIcon v-if="sendingWelcome !== member.id" class="w-4 h-4 inline mr-1" />
                                        {{ sendingWelcome === member.id ? 'Sending...' : 'Send Email' }}
                                    </button>
                                    <Link
                                        :href="`/portal/admin/staff/${member.id}/edit`"
                                        class="px-3 py-1 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-md transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <template v-if="member.role !== 'super_admin'">
                                        <button
                                            v-if="confirmingDelete !== member.id"
                                            @click="confirmingDelete = member.id"
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
                                                @keyup.enter="deleteStaff(member.id)"
                                            />
                                            <button
                                                @click="deleteStaff(member.id)"
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
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
