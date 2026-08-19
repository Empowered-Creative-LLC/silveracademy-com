<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { 
    ChevronLeftIcon,
    AcademicCapIcon,
    UserGroupIcon,
    PencilIcon,
    TrashIcon,
    PlusIcon,
    XMarkIcon,
    CheckIcon,
    KeyIcon,
    ClipboardDocumentIcon,
    EnvelopeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    grade: Object,
    allStaff: Array,
    students: Array,
});

// Parent Code: regenerate confirm modal + show new code when returned from regenerate or add-student
const showRegenerateConfirm = ref(false);
const studentForRegenerate = ref(null);
const regenerating = ref(false);
const showCodeResult = ref(false);
const regeneratedCodePlain = ref('');
const regeneratedCodeLast4 = ref('');
const codeCopied = ref(false);

const openRegenerateConfirm = (student) => {
    studentForRegenerate.value = student;
    showRegenerateConfirm.value = true;
};

const closeRegenerateConfirm = () => {
    showRegenerateConfirm.value = false;
    studentForRegenerate.value = null;
};

const doRegenerate = () => {
    if (!studentForRegenerate.value) return;
    regenerating.value = true;
    const studentId = studentForRegenerate.value.id;
    const redirectUrl = `/portal/admin/grades/${props.grade.id}`;
    closeRegenerateConfirm();
    router.post(`/portal/admin/students/${studentId}/code/regenerate`, { redirect_url: redirectUrl }, {
        preserveScroll: true,
        onFinish: () => { regenerating.value = false; },
    });
};

const copyCode = () => {
    if (!regeneratedCodePlain.value) return;
    navigator.clipboard.writeText(regeneratedCodePlain.value);
    codeCopied.value = true;
    setTimeout(() => { codeCopied.value = false; }, 2000);
};

const closeCodeResult = () => {
    showCodeResult.value = false;
    regeneratedCodePlain.value = '';
    regeneratedCodeLast4.value = '';
    router.reload();
};

const parentCodeDisplay = (student) => {
    if (student.code_status === 'active' && student.code_last4) {
        return `••••${student.code_last4} (${student.code_link_count}/${student.max_links})`;
    }
    return null;
};

// Bulk email codes to parents (per grade)
const showBulkEmailConfirm = ref(false);
const bulkEmailSending = ref(false);
const openBulkEmailConfirm = () => { showBulkEmailConfirm.value = true; };
const closeBulkEmailConfirm = () => { showBulkEmailConfirm.value = false; };
const doBulkEmail = () => {
    bulkEmailSending.value = true;
    closeBulkEmailConfirm();
    router.post(`/portal/admin/grades/${props.grade.id}/send-codes-to-parents`, {}, {
        preserveScroll: true,
        onFinish: () => { bulkEmailSending.value = false; },
    });
};

// Individual email code to parent(s) – confirmation dialog first
const emailingStudentId = ref(null);
const showEmailCodeConfirm = ref(false);
const emailCodeStudent = ref(null);
const openEmailCodeConfirm = (student) => {
    if (!student.can_send_code) return;
    emailCodeStudent.value = student;
    showEmailCodeConfirm.value = true;
};
const closeEmailCodeConfirm = () => {
    showEmailCodeConfirm.value = false;
    emailCodeStudent.value = null;
};
const doSendCodeToParent = () => {
    const student = emailCodeStudent.value;
    if (!student) return;
    emailingStudentId.value = student.id;
    closeEmailCodeConfirm();
    router.post(`/portal/admin/students/${student.id}/send-code-to-parent`, {}, {
        preserveScroll: false,
        onSuccess: () => { emailingStudentId.value = null; },
        onError: () => { emailingStudentId.value = null; },
        onFinish: () => { emailingStudentId.value = null; },
    });
};

// Show new parent code modal when redirected after creating a student or regenerating
onMounted(() => {
    const flash = usePage().props.flash;
    const code = flash?.new_student_code_plain ?? flash?.regenerated_code_plain;
    if (code) {
        regeneratedCodePlain.value = code;
        showCodeResult.value = true;
    }
});

// Teacher assignment
const showTeacherModal = ref(false);
const selectedTeacherIds = ref(props.grade.teachers?.map(t => t.id) || []);

const teacherForm = useForm({
    teacher_ids: props.grade.teachers?.map(t => t.id) || [],
});

const openTeacherModal = () => {
    selectedTeacherIds.value = props.grade.teachers?.map(t => t.id) || [];
    showTeacherModal.value = true;
};

const saveTeachers = () => {
    teacherForm.teacher_ids = selectedTeacherIds.value;
    teacherForm.put(`/portal/admin/grades/${props.grade.id}/teachers`, {
        onSuccess: () => {
            showTeacherModal.value = false;
        },
    });
};

const toggleTeacher = (teacherId) => {
    const index = selectedTeacherIds.value.indexOf(teacherId);
    if (index === -1) {
        selectedTeacherIds.value.push(teacherId);
    } else {
        selectedTeacherIds.value.splice(index, 1);
    }
};

// Student management
const showAddStudentForm = ref(false);
const editingStudent = ref(null);

const newStudentForm = useForm({
    name: '',
    parent_email_1: '',
    parent_email_2: '',
    parent_email_3: '',
    parent_email_4: '',
});

const editStudentForm = useForm({
    name: '',
    grade_id: null,
    parent_email_1: '',
    parent_email_2: '',
    parent_email_3: '',
    parent_email_4: '',
});

const addStudentError = ref('');

const addStudent = () => {
    addStudentError.value = '';
    newStudentForm.post(`/portal/admin/grades/${props.grade.id}/students`, {
        onSuccess: () => {
            // Only close and reset when there are no validation errors (Inertia may call onSuccess even when server returned errors)
            if (Object.keys(newStudentForm.errors).length === 0) {
                newStudentForm.reset();
                showAddStudentForm.value = false;
                addStudentError.value = '';
                router.reload({ preserveScroll: false });
            }
        },
        onError: (errors) => {
            addStudentError.value = typeof errors === 'object' && Object.keys(errors).length
                ? Object.values(errors)[0]
                : 'Something went wrong. If the page just refreshed, your session may have expired — try again or refresh the page.';
        },
    });
};

const startEditStudent = (student) => {
    editingStudent.value = student.id;
    editStudentForm.name = student.name;
    editStudentForm.grade_id = props.grade.id;
    const emails = student.contact_emails || [];
    editStudentForm.parent_email_1 = emails[0] ?? '';
    editStudentForm.parent_email_2 = emails[1] ?? '';
    editStudentForm.parent_email_3 = emails[2] ?? '';
    editStudentForm.parent_email_4 = emails[3] ?? '';
};

const cancelEditStudent = () => {
    editingStudent.value = null;
    editStudentForm.reset();
};

const updateStudent = (studentId) => {
    editStudentForm.put(`/portal/admin/grades/${props.grade.id}/students/${studentId}`, {
        onSuccess: () => {
            editingStudent.value = null;
        },
    });
};

const confirmingDelete = ref(null);
const deleteConfirmText = ref('');

const deleteStudent = (studentId) => {
    if (deleteConfirmText.value.toLowerCase() !== 'delete') return;
    router.delete(`/portal/admin/grades/${props.grade.id}/students/${studentId}`, {
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
</script>

<template>
    <Head :title="grade.name" />

    <PortalLayout>
        <template #header>{{ grade.name }}</template>

        <div class="min-w-0 max-w-full space-y-6">
            <!-- Breadcrumb -->
            <div>
                <Link 
                    href="/portal/admin/grades"
                    class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900"
                >
                    <ChevronLeftIcon class="w-4 h-4 mr-1" />
                    Back to Grade Management
                </Link>
            </div>

            <!-- Success flash -->
            <div v-if="flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                <p class="text-sm font-medium text-emerald-800">{{ flash.success }}</p>
            </div>
            <!-- Session expired / error flash -->
            <div v-if="flash?.error" class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <p class="text-sm font-medium text-amber-800">{{ flash.error }}</p>
            </div>

            <!-- Grade Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-brand-50 rounded-lg">
                            <AcademicCapIcon class="w-8 h-8 text-brand-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-serif font-semibold text-slate-900">{{ grade.name }}</h2>
                            <p class="text-slate-500">{{ students.length }} student{{ students.length !== 1 ? 's' : '' }} enrolled</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teachers Section -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-serif font-semibold text-slate-900">Assigned Teachers</h2>
                    <button
                        @click="openTeacherModal"
                        class="inline-flex w-full sm:w-auto items-center justify-center px-3 py-1.5 text-sm font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors"
                    >
                        <PencilIcon class="w-4 h-4 mr-1" />
                        Edit Teachers
                    </button>
                </div>
                
                <div v-if="grade.teachers && grade.teachers.length > 0" class="divide-y divide-slate-200">
                    <div
                        v-for="teacher in grade.teachers"
                        :key="teacher.id"
                        class="px-4 sm:px-6 py-4 flex items-center gap-4 min-w-0"
                    >
                        <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-emerald-700">
                                {{ teacher.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ teacher.name }}</p>
                            <p class="text-sm text-slate-500">{{ teacher.email }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="p-6 text-center text-slate-500">
                    No teachers assigned to this grade. Click "Edit Teachers" to assign staff.
                </div>
            </div>

            <!-- Students Section -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-serif font-semibold text-slate-900">Students</h2>
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-if="!showAddStudentForm"
                            type="button"
                            @click="openBulkEmailConfirm"
                            :disabled="bulkEmailSending || students.length === 0"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors disabled:opacity-50"
                        >
                            <EnvelopeIcon class="w-4 h-4 mr-1" />
                            {{ bulkEmailSending ? 'Sending…' : 'Email codes to parents' }}
                        </button>
                        <button
                            v-if="!showAddStudentForm"
                            @click="showAddStudentForm = true"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors"
                        >
                            <PlusIcon class="w-4 h-4 mr-1" />
                            Add Student
                        </button>
                    </div>
                </div>

                <!-- Add Student Form -->
                <div v-if="showAddStudentForm" class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <p v-if="addStudentError" class="mb-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        {{ addStudentError }}
                    </p>
                    <form @submit.prevent="addStudent" class="space-y-4">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="flex-1 min-w-[200px]">
                                <label for="new-student-name" class="block text-xs font-medium text-slate-600 mb-0.5">Student name</label>
                                <input
                                    id="new-student-name"
                                    v-model="newStudentForm.name"
                                    type="text"
                                    placeholder="Student name"
                                    required
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                    :class="{ 'border-red-500': newStudentForm.errors.name }"
                                />
                                <p v-if="newStudentForm.errors.name" class="mt-0.5 text-sm text-red-600">{{ newStudentForm.errors.name }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    :disabled="newStudentForm.processing"
                                    class="px-4 py-2 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 disabled:opacity-50 transition-colors"
                                >
                                    Add
                                </button>
                                <button
                                    type="button"
                                    @click="showAddStudentForm = false; newStudentForm.reset()"
                                    class="px-4 py-2 text-slate-600 hover:text-slate-900 transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-600 mb-2">Parent emails (optional — for sending codes, up to 4)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label for="new-parent-email-1" class="sr-only">Parent Email 1</label>
                                    <input
                                        id="new-parent-email-1"
                                        v-model="newStudentForm.parent_email_1"
                                        type="email"
                                        placeholder="Parent Email 1"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                                        :class="{ 'border-red-500': newStudentForm.errors.parent_email_1 }"
                                    />
                                    <p v-if="newStudentForm.errors.parent_email_1" class="mt-0.5 text-sm text-red-600">{{ newStudentForm.errors.parent_email_1 }}</p>
                                </div>
                                <div>
                                    <label for="new-parent-email-2" class="sr-only">Parent Email 2</label>
                                    <input
                                        id="new-parent-email-2"
                                        v-model="newStudentForm.parent_email_2"
                                        type="email"
                                        placeholder="Parent Email 2"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                                        :class="{ 'border-red-500': newStudentForm.errors.parent_email_2 }"
                                    />
                                    <p v-if="newStudentForm.errors.parent_email_2" class="mt-0.5 text-sm text-red-600">{{ newStudentForm.errors.parent_email_2 }}</p>
                                </div>
                                <div>
                                    <label for="new-parent-email-3" class="sr-only">Parent Email 3</label>
                                    <input
                                        id="new-parent-email-3"
                                        v-model="newStudentForm.parent_email_3"
                                        type="email"
                                        placeholder="Parent Email 3"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                                        :class="{ 'border-red-500': newStudentForm.errors.parent_email_3 }"
                                    />
                                    <p v-if="newStudentForm.errors.parent_email_3" class="mt-0.5 text-sm text-red-600">{{ newStudentForm.errors.parent_email_3 }}</p>
                                </div>
                                <div>
                                    <label for="new-parent-email-4" class="sr-only">Parent Email 4</label>
                                    <input
                                        id="new-parent-email-4"
                                        v-model="newStudentForm.parent_email_4"
                                        type="email"
                                        placeholder="Parent Email 4"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                                        :class="{ 'border-red-500': newStudentForm.errors.parent_email_4 }"
                                    />
                                    <p v-if="newStudentForm.errors.parent_email_4" class="mt-0.5 text-sm text-red-600">{{ newStudentForm.errors.parent_email_4 }}</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Students List -->
                <div v-if="students.length > 0" class="divide-y divide-slate-200">
                    <div
                        v-for="student in students"
                        :key="student.id"
                        class="px-4 sm:px-6 py-4"
                    >
                        <div class="flex flex-col gap-3">
                            <div class="flex items-start gap-4 min-w-0">
                                <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                                    <UserGroupIcon class="w-5 h-5 text-slate-500" />
                                </div>
                                <div v-if="editingStudent !== student.id" class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900 break-words">{{ student.name }}</p>
                                    <p v-if="student.parents && student.parents.length > 0" class="text-xs text-slate-500 break-words">
                                        Parent{{ student.parents.length > 1 ? 's' : '' }}:
                                        {{ student.parents.map(p => p.name).join(', ') }}
                                    </p>
                                </div>
                                <div v-else class="min-w-0 flex-1">
                                    <input
                                        v-model="editStudentForm.name"
                                        type="text"
                                        class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                        :class="{ 'border-red-500': editStudentForm.errors.name }"
                                    />
                                    <p v-if="editStudentForm.errors.name" class="mt-0.5 text-xs text-red-600">{{ editStudentForm.errors.name }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span v-if="parentCodeDisplay(student)" class="text-xs font-mono text-slate-600 break-all">
                                    {{ parentCodeDisplay(student) }}
                                </span>
                                <button
                                    type="button"
                                    @click="openEmailCodeConfirm(student)"
                                    :disabled="!student.can_send_code || emailingStudentId === student.id"
                                    :title="student.can_send_code ? 'Email code to parent(s)' : 'Add parent emails in import or link a parent'"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-700 bg-slate-100 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-200"
                                >
                                    <EnvelopeIcon class="w-3.5 h-3.5 mr-1" />
                                    {{ emailingStudentId === student.id ? 'Sending…' : 'Email code' }}
                                </button>
                                <button
                                    type="button"
                                    @click="openRegenerateConfirm(student)"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded transition-colors"
                                    :title="student.code_status === 'active' ? 'Regenerate code' : 'Generate code'"
                                >
                                    <KeyIcon class="w-3.5 h-3.5 mr-1" />
                                    {{ student.code_status === 'active' ? 'Regenerate' : 'Generate' }}
                                </button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <template v-if="editingStudent !== student.id && confirmingDelete !== student.id">
                                    <button
                                        @click="startEditStudent(student)"
                                        class="p-2 text-slate-400 hover:text-brand-600 transition-colors"
                                        title="Edit student"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="confirmingDelete = student.id"
                                        class="p-2 text-slate-400 hover:text-red-600 transition-colors"
                                        title="Delete student"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </template>
                                <template v-else-if="editingStudent === student.id">
                                    <button
                                        @click="updateStudent(student.id)"
                                        :disabled="editStudentForm.processing"
                                        class="p-2 text-emerald-600 hover:text-emerald-700 transition-colors"
                                        title="Save changes"
                                    >
                                        <CheckIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="cancelEditStudent"
                                        class="p-2 text-slate-400 hover:text-slate-600 transition-colors"
                                        title="Cancel"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </template>
                                <template v-else-if="confirmingDelete === student.id">
                                    <div class="flex flex-col gap-2 w-full sm:w-auto sm:flex-row sm:items-center">
                                        <input
                                            v-model="deleteConfirmText"
                                            type="text"
                                            placeholder="Type 'delete'"
                                            class="w-full sm:w-24 px-2 py-1 text-xs border border-red-300 rounded-md focus:ring-red-500 focus:border-red-500"
                                            @keyup.enter="deleteStudent(student.id)"
                                        />
                                        <button
                                            @click="deleteStudent(student.id)"
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
                        </div>
                        <!-- Parent emails when editing -->
                        <div v-if="editingStudent === student.id" class="mt-3 sm:pl-14 space-y-2">
                            <p class="text-xs font-medium text-slate-600">Parent emails (optional — for sending codes, up to 4)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                <div>
                                    <label for="edit-parent-email-1" class="sr-only">Parent Email 1</label>
                                    <input
                                        id="edit-parent-email-1"
                                        v-model="editStudentForm.parent_email_1"
                                        type="email"
                                        placeholder="Parent Email 1"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                        :class="{ 'border-red-500': editStudentForm.errors.parent_email_1 }"
                                    />
                                    <p v-if="editStudentForm.errors.parent_email_1" class="mt-0.5 text-xs text-red-600">{{ editStudentForm.errors.parent_email_1 }}</p>
                                </div>
                                <div>
                                    <label for="edit-parent-email-2" class="sr-only">Parent Email 2</label>
                                    <input
                                        id="edit-parent-email-2"
                                        v-model="editStudentForm.parent_email_2"
                                        type="email"
                                        placeholder="Parent Email 2"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                        :class="{ 'border-red-500': editStudentForm.errors.parent_email_2 }"
                                    />
                                    <p v-if="editStudentForm.errors.parent_email_2" class="mt-0.5 text-xs text-red-600">{{ editStudentForm.errors.parent_email_2 }}</p>
                                </div>
                                <div>
                                    <label for="edit-parent-email-3" class="sr-only">Parent Email 3</label>
                                    <input
                                        id="edit-parent-email-3"
                                        v-model="editStudentForm.parent_email_3"
                                        type="email"
                                        placeholder="Parent Email 3"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                        :class="{ 'border-red-500': editStudentForm.errors.parent_email_3 }"
                                    />
                                    <p v-if="editStudentForm.errors.parent_email_3" class="mt-0.5 text-xs text-red-600">{{ editStudentForm.errors.parent_email_3 }}</p>
                                </div>
                                <div>
                                    <label for="edit-parent-email-4" class="sr-only">Parent Email 4</label>
                                    <input
                                        id="edit-parent-email-4"
                                        v-model="editStudentForm.parent_email_4"
                                        type="email"
                                        placeholder="Parent Email 4"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                                        :class="{ 'border-red-500': editStudentForm.errors.parent_email_4 }"
                                    />
                                    <p v-if="editStudentForm.errors.parent_email_4" class="mt-0.5 text-xs text-red-600">{{ editStudentForm.errors.parent_email_4 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center">
                    <UserGroupIcon class="mx-auto h-12 w-12 text-slate-400" />
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No students</h3>
                    <p class="mt-1 text-sm text-slate-500">Add students manually or import from a spreadsheet.</p>
                </div>
            </div>
        </div>

        <!-- Teacher Assignment Modal -->
        <div v-if="showTeacherModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="showTeacherModal = false"></div>
                
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Assign Teachers to {{ grade.name }}</h3>
                        
                        <div class="border border-slate-200 rounded-lg divide-y divide-slate-200 max-h-64 overflow-y-auto">
                            <label
                                v-for="staff in allStaff"
                                :key="staff.id"
                                class="flex items-center px-4 py-3 hover:bg-slate-50 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedTeacherIds.includes(staff.id)"
                                    @change="toggleTeacher(staff.id)"
                                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded"
                                />
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-slate-900">{{ staff.name }}</p>
                                    <p class="text-xs text-slate-500">{{ staff.email }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                        <button
                            type="button"
                            @click="saveTeachers"
                            :disabled="teacherForm.processing"
                            class="inline-flex w-full justify-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 sm:w-auto disabled:opacity-50"
                        >
                            Save
                        </button>
                        <button
                            type="button"
                            @click="showTeacherModal = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regenerate code confirm modal -->
        <Teleport to="body">
        <div v-if="showRegenerateConfirm" class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-500/75 transition-opacity" @click="closeRegenerateConfirm" />
                <div class="relative z-10 w-full max-w-md rounded-xl bg-white shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                                <KeyIcon class="h-5 w-5 text-amber-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-900">Regenerate parent code?</h3>
                        </div>
                        <p class="text-sm text-slate-600">
                            The current code for <strong>{{ studentForRegenerate?.name }}</strong> will stop working immediately. A new code will be generated. Share it only through a secure channel.
                        </p>
                    </div>
                    <div class="flex gap-3 px-6 pb-6">
                        <button
                            type="button"
                            @click="doRegenerate"
                            :disabled="regenerating"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg disabled:opacity-50"
                        >
                            {{ regenerating ? 'Regenerating…' : 'Yes, regenerate code' }}
                        </button>
                        <button
                            type="button"
                            @click="closeRegenerateConfirm"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- Bulk email codes confirm modal -->
        <Teleport to="body">
        <div v-if="showBulkEmailConfirm" class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-500/75 transition-opacity" @click="closeBulkEmailConfirm" />
                <div class="relative z-10 w-full max-w-md rounded-xl bg-white shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50">
                                <EnvelopeIcon class="h-5 w-5 text-brand-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-900">Email codes to parents?</h3>
                        </div>
                        <p class="text-sm text-slate-600">
                            One email per parent address for this grade. Parents with multiple children will receive one email with all their codes (up to 5 per email). Continue?
                        </p>
                    </div>
                    <div class="flex gap-3 px-6 pb-6">
                        <button
                            type="button"
                            @click="doBulkEmail"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg"
                        >
                            Yes, send emails
                        </button>
                        <button
                            type="button"
                            @click="closeBulkEmailConfirm"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- Send code to parent confirmation -->
        <Teleport to="body">
        <div v-if="showEmailCodeConfirm && emailCodeStudent" class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-500/75 transition-opacity" @click="closeEmailCodeConfirm" />
                <div class="relative z-10 w-full max-w-md rounded-xl bg-white shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50">
                                <EnvelopeIcon class="h-5 w-5 text-brand-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-900">Email code to parent?</h3>
                        </div>
                        <p class="text-sm text-slate-600 mb-2">
                            Send the parent code for <strong>{{ emailCodeStudent.name }}</strong> to:
                        </p>
                        <ul class="text-sm text-slate-700 list-disc list-inside space-y-0.5 mb-4">
                            <li v-for="email in (emailCodeStudent.contact_emails && emailCodeStudent.contact_emails.length ? emailCodeStudent.contact_emails : ['linked parent account'])" :key="email">{{ email }}</li>
                        </ul>
                        <p class="text-xs text-slate-500">You’ll see a confirmation message after sending. If the email doesn’t arrive, check spam or the server’s SendGrid configuration.</p>
                    </div>
                    <div class="flex gap-3 px-6 pb-6">
                        <button
                            type="button"
                            @click="doSendCodeToParent"
                            :disabled="emailingStudentId === emailCodeStudent.id"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg disabled:opacity-50"
                        >
                            {{ emailingStudentId === emailCodeStudent.id ? 'Sending…' : 'Send code' }}
                        </button>
                        <button
                            type="button"
                            @click="closeEmailCodeConfirm"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- New code shown once modal -->
        <Teleport to="body">
        <div v-if="showCodeResult" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="closeCodeResult"></div>
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
                            <h3 class="text-lg font-semibold text-slate-900">New Parent Code</h3>
                            <p class="mt-2 text-sm text-amber-700 font-medium">This code is shown only once. Copy it now and share it securely with the family.</p>
                            <div class="mt-4 flex items-center gap-2">
                                <code class="flex-1 px-4 py-3 bg-slate-100 rounded-lg font-mono text-lg tracking-wider break-all select-text">{{ regeneratedCodePlain }}</code>
                                <button
                                    type="button"
                                    @click="copyCode"
                                    class="inline-flex items-center px-3 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 text-sm font-medium"
                                >
                                    <ClipboardDocumentIcon class="w-5 h-5 mr-1" />
                                    {{ codeCopied ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:px-6">
                            <button
                                type="button"
                                @click="closeCodeResult"
                                class="w-full inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50"
                            >
                                Done
                            </button>
                        </div>
                    </div>
                </div>
        </div>
        </Teleport>
    </PortalLayout>
</template>
