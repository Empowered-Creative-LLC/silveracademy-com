<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { 
    CalendarIcon, 
    MegaphoneIcon, 
    EyeIcon,
    PlusIcon,
    ClipboardDocumentListIcon,
    UserGroupIcon,
    AcademicCapIcon,
    ClockIcon,
    NewspaperIcon,
    PencilSquareIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed, provide, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    user: Object,
    thisWeekLunchMenus: Array,
    upcomingEvents: Array,
    recentAnnouncements: Array,
    teacherAnnouncements: Array,
    gradeNews: Array,
    linkedStudentGradeNames: {
        type: Array,
        default: () => [],
    },
    teacherGrades: Array,
    studentCount: Number,
    staffCount: Number,
    upcomingEventsCount: Number,
});

const previewRoles = ['admin', 'teacher', 'parent'];

const defaultPreviewRole = () => {
    if (props.user?.role === 'teacher') return 'teacher';
    if (props.user?.role === 'parent') return 'parent';
    return 'admin';
};

// Load preview role from localStorage (persists across page navigations)
const getStoredPreviewRole = () => {
    const fallback = defaultPreviewRole();
    if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('portal_preview_role');
        if (stored && previewRoles.includes(stored)) {
            if (props.user?.role === 'super_admin') {
                return stored;
            }
            if (stored === 'parent') {
                return 'parent';
            }
        }
    }
    return fallback;
};

const previewRole = ref(getStoredPreviewRole());

const isSuperAdmin = computed(() => props.user?.role === 'super_admin');
const isAdmin = computed(() => props.user?.role === 'admin' || props.user?.role === 'super_admin');
const isTeacher = computed(() => props.user?.role === 'teacher');
const hasLinkedStudents = computed(() => Boolean(props.user?.has_linked_students));
const canSwitchPortalView = computed(() => isSuperAdmin.value || ((isAdmin.value || isTeacher.value) && hasLinkedStudents.value));

watch(previewRole, (newRole) => {
    if (typeof window !== 'undefined' && canSwitchPortalView.value) {
        localStorage.setItem('portal_preview_role', newRole);
        window.dispatchEvent(new CustomEvent('preview-role-changed', { detail: newRole }));
    }
});

const onPreviewRoleChanged = (event) => {
    if (event.detail && previewRoles.includes(event.detail)) {
        previewRole.value = event.detail;
    }
};

onMounted(() => {
    window.addEventListener('preview-role-changed', onPreviewRoleChanged);
});

onUnmounted(() => {
    window.removeEventListener('preview-role-changed', onPreviewRoleChanged);
});

provide('previewRole', previewRole);
provide('isSuperAdmin', isSuperAdmin);

const showAdminView = computed(() => {
    if (isSuperAdmin.value) {
        return previewRole.value === 'admin';
    }
    if (hasLinkedStudents.value && previewRole.value === 'parent') {
        return false;
    }
    return isAdmin.value;
});

const showTeacherView = computed(() => {
    if (isSuperAdmin.value) {
        return previewRole.value === 'teacher';
    }
    if (hasLinkedStudents.value && previewRole.value === 'parent') {
        return false;
    }
    return props.user?.role === 'teacher';
});

const showParentView = computed(() => {
    if (isSuperAdmin.value) {
        return previewRole.value === 'parent';
    }
    if ((isAdmin.value || isTeacher.value) && hasLinkedStudents.value) {
        return previewRole.value === 'parent';
    }
    return props.user?.role === 'parent';
});

const toggleRole = () => {
    if (isSuperAdmin.value) {
        const currentIndex = previewRoles.indexOf(previewRole.value);
        previewRole.value = previewRoles[(currentIndex + 1) % previewRoles.length];
        return;
    }
    previewRole.value = previewRole.value === 'parent' ? defaultPreviewRole() : 'parent';
};

const previewRoleLabel = computed(() => {
    const labels = {
        'admin': 'Admin View',
        'teacher': 'Staff View',
        'parent': 'Parent View'
    };
    return labels[previewRole.value];
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        weekday: 'short',
        month: 'short', 
        day: 'numeric',
        timeZone: 'America/New_York',
    });
};

const formatWeekDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric',
        year: 'numeric',
        timeZone: 'America/New_York',
    });
};

// Staff announcements toggle (Staff View).
const staffAnnouncementTab = ref('all_staff');
const allStaffAnnouncements = computed(() =>
    (props.teacherAnnouncements ?? []).filter(a => a.audience === 'teachers_only'),
);
const myGradeAnnouncements = computed(() =>
    (props.teacherAnnouncements ?? []).filter(a => a.audience === 'grade_teachers'),
);
const activeStaffAnnouncements = computed(() =>
    staffAnnouncementTab.value === 'all_staff' ? allStaffAnnouncements.value : myGradeAnnouncements.value,
);
</script>

<template>
    <Head title="Dashboard" />

    <PortalLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="break-words">Welcome back, {{ user?.name || 'User' }}!</span>
                
                <!-- Role toggle for staff/admin who are also parents (super admin uses the header dropdown) -->
                <div v-if="canSwitchPortalView && !isSuperAdmin" class="flex items-center gap-3">
                    <span class="text-sm text-slate-500 font-normal">{{ isSuperAdmin ? 'Preview as:' : 'View as:' }}</span>
                    <button
                        @click="toggleRole"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                    >
                        <EyeIcon class="w-4 h-4 mr-2 text-slate-500" />
                        {{ previewRoleLabel }}
                    </button>
                </div>
            </div>
        </template>
        
        <div class="space-y-8">
            <!-- ADMIN VIEW -->
            <template v-if="showAdminView">
                <!-- Admin Stats Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-brand-50 rounded-lg">
                                <UserGroupIcon class="w-6 h-6 text-brand-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ studentCount ?? 0 }}</p>
                                <p class="text-sm text-slate-600">Total Students</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-brand-50 rounded-lg">
                                <AcademicCapIcon class="w-6 h-6 text-brand-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ staffCount ?? 0 }}</p>
                                <p class="text-sm text-slate-600">Staff Members</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-brand-50 rounded-lg">
                                <CalendarIcon class="w-6 h-6 text-brand-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ upcomingEventsCount ?? 0 }}</p>
                                <p class="text-sm text-slate-600">Upcoming Events</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-brand-50 rounded-lg">
                                <MegaphoneIcon class="w-6 h-6 text-brand-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ recentAnnouncements?.length || 0 }}</p>
                                <p class="text-sm text-slate-600">Recent Posts</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-serif font-semibold text-slate-900">Admin Quick Actions</h2>
                    </div>
                    <div class="p-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <Link
                            href="/portal/posts/create"
                            class="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-xl hover:bg-brand-50 transition-colors group"
                        >
                            <div class="p-3 bg-white rounded-full shadow-sm mb-3 group-hover:bg-brand-100 transition-colors">
                                <MegaphoneIcon class="w-6 h-6 text-slate-600 group-hover:text-brand-600" />
                            </div>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-brand-700 text-center">Post News/Event</span>
                        </Link>
                        <Link
                            href="/portal/lunch/create"
                            class="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-xl hover:bg-brand-50 transition-colors group"
                        >
                            <div class="p-3 bg-white rounded-full shadow-sm mb-3 group-hover:bg-brand-100 transition-colors">
                                <ClipboardDocumentListIcon class="w-6 h-6 text-slate-600 group-hover:text-brand-600" />
                            </div>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-brand-700 text-center">Add Lunch Menu</span>
                        </Link>
                        <Link
                            href="/portal/posts"
                            class="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-xl hover:bg-brand-50 transition-colors group"
                        >
                            <div class="p-3 bg-white rounded-full shadow-sm mb-3 group-hover:bg-brand-100 transition-colors">
                                <PlusIcon class="w-6 h-6 text-slate-600 group-hover:text-brand-600" />
                            </div>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-brand-700 text-center">Manage Posts</span>
                        </Link>
                        <Link
                            href="/portal/calendar"
                            class="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-xl hover:bg-brand-50 transition-colors group"
                        >
                            <div class="p-3 bg-white rounded-full shadow-sm mb-3 group-hover:bg-brand-100 transition-colors">
                                <CalendarIcon class="w-6 h-6 text-slate-600 group-hover:text-brand-600" />
                            </div>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-brand-700 text-center">View Calendar</span>
                        </Link>
                    </div>
                </div>
            </template>

            <!-- STAFF VIEW -->
            <template v-else-if="showTeacherView">
                <div class="space-y-6">
                    <!-- Staff Quick Actions Bar -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 px-4 py-2">
                        <div class="flex items-center gap-2">
                            <Link
                                href="/portal/teacher-news/create"
                                class="group relative inline-flex h-10 w-10 items-center justify-center rounded-md text-emerald-700 hover:bg-emerald-50 transition-colors"
                                title="Post Grade News"
                            >
                                <PencilSquareIcon class="w-5 h-5" />
                                <span class="pointer-events-none absolute left-1/2 top-full z-10 mt-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-white opacity-0 shadow transition-opacity group-hover:opacity-100">
                                    Post Grade News
                                </span>
                            </Link>
                            <Link
                                href="/portal/calendar"
                                class="group relative inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-700 hover:bg-slate-100 transition-colors"
                                title="View Calendar"
                            >
                                <CalendarIcon class="w-5 h-5" />
                                <span class="pointer-events-none absolute left-1/2 top-full z-10 mt-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-white opacity-0 shadow transition-opacity group-hover:opacity-100">
                                    View Calendar
                                </span>
                            </Link>
                            <Link
                                href="/portal/calendar?view=lunch"
                                class="group relative inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-700 hover:bg-slate-100 transition-colors"
                                title="Lunch Menu"
                            >
                                <ClipboardDocumentListIcon class="w-5 h-5" />
                                <span class="pointer-events-none absolute left-1/2 top-full z-10 mt-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-white opacity-0 shadow transition-opacity group-hover:opacity-100">
                                    Lunch Menu
                                </span>
                            </Link>
                            <a
                                href="/news-events"
                                target="_blank"
                                class="group relative inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-700 hover:bg-slate-100 transition-colors"
                                title="News & Events"
                            >
                                <MegaphoneIcon class="w-5 h-5" />
                                <span class="pointer-events-none absolute left-1/2 top-full z-10 mt-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-white opacity-0 shadow transition-opacity group-hover:opacity-100">
                                    News & Events
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- Staff Welcome Card -->
                    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
                                <AcademicCapIcon class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h2 class="text-xl font-serif font-semibold">Staff Dashboard</h2>
                                <p class="text-emerald-100 mt-1">Manage your classroom and communicate with parents</p>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Announcements -->
                    <div v-if="teacherAnnouncements && teacherAnnouncements.length > 0" class="bg-slate-50 rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <MegaphoneIcon class="w-5 h-5 text-slate-700" />
                                    <h2 class="text-lg font-serif font-semibold text-slate-900">Staff Announcements</h2>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                        :class="staffAnnouncementTab === 'all_staff' ? 'bg-sky-200 text-sky-900' : 'bg-white/70 text-slate-700 hover:bg-white'"
                                        @click="staffAnnouncementTab = 'all_staff'"
                                    >
                                        All Staff
                                    </button>
                                    <button
                                        type="button"
                                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                        :class="staffAnnouncementTab === 'my_grade' ? 'bg-amber-200 text-slate-900' : 'bg-white/70 text-slate-700 hover:bg-white'"
                                        @click="staffAnnouncementTab = 'my_grade'"
                                    >
                                        My Grade
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="divide-y divide-amber-200">
                            <div
                                v-if="activeStaffAnnouncements.length === 0"
                                class="px-6 py-8 text-sm text-slate-600"
                            >
                                No announcements in this category.
                            </div>
                            <div
                                v-else
                                v-for="announcement in activeStaffAnnouncements"
                                :key="announcement.id"
                                class="px-6 py-4 hover:bg-slate-100/50 transition-colors"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                                        :class="{
                                            'bg-sky-200': announcement.audience === 'teachers_only',
                                            'bg-amber-200': announcement.audience === 'grade_teachers',
                                            'bg-purple-200': announcement.audience === 'specific_teacher',
                                        }"
                                    >
                                        <UserGroupIcon v-if="announcement.audience === 'teachers_only'" class="w-5 h-5 text-sky-700" />
                                        <svg v-else-if="announcement.audience === 'grade_teachers'" class="w-5 h-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <svg v-else class="w-5 h-5 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-medium text-slate-900">{{ announcement.title }}</p>
                                            <!-- Target badge -->
                                            <span 
                                                v-if="announcement.audience === 'teachers_only'"
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700"
                                            >
                                                All Staff
                                            </span>
                                            <span 
                                                v-else-if="announcement.audience === 'grade_teachers'"
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"
                                            >
                                                {{ announcement.target_grade?.name || 'Grade' }}
                                            </span>
                                            <span 
                                                v-else-if="announcement.audience === 'specific_teacher'"
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700"
                                            >
                                                For: {{ announcement.target_teacher?.name || 'You' }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-slate-700 mt-1 line-clamp-2">
                                            {{ announcement.content.replace(/<[^>]*>/g, '').substring(0, 150) }}{{ announcement.content.length > 150 ? '...' : '' }}
                                        </p>
                                        <p class="text-xs text-slate-600 mt-2">
                                            Posted {{ formatDate(announcement.published_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-serif font-semibold text-slate-900">Upcoming Events</h2>
                                <Link href="/portal/calendar" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                    View Calendar →
                                </Link>
                            </div>
                        </div>
                        <div v-if="upcomingEvents && upcomingEvents.length > 0" class="divide-y divide-slate-200">
                            <div
                                v-for="event in upcomingEvents"
                                :key="event.id"
                                class="px-6 py-4 hover:bg-slate-50 transition-colors"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-12 h-12 bg-emerald-50 rounded-lg flex flex-col items-center justify-center">
                                        <span class="text-xs font-semibold text-emerald-600 uppercase">
                                            {{ new Date(event.event_start_date).toLocaleDateString('en-US', { month: 'short', timeZone: 'America/New_York' }) }}
                                        </span>
                                        <span class="text-lg font-bold text-emerald-700">
                                            {{ new Date(new Date(event.event_start_date).toLocaleString('en-US', { timeZone: 'America/New_York' })).getDate() }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900">{{ event.title }}</p>
                                        <p class="text-xs text-slate-500 mt-1 flex items-center">
                                            <ClockIcon class="w-3 h-3 mr-1" />
                                            {{ formatDate(event.event_start_date) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-6 text-center">
                            <CalendarIcon class="mx-auto h-12 w-12 text-slate-300" />
                            <p class="mt-2 text-sm text-slate-500">No upcoming events.</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- PARENT VIEW -->
            <template v-else-if="showParentView">
                <!-- Combined News Section: School-Wide + Grade-Specific (per linked students' grades) -->
                <div v-if="(recentAnnouncements && recentAnnouncements.length > 0) || (gradeNews && gradeNews.length > 0) || (linkedStudentGradeNames && linkedStudentGradeNames.length > 0)" class="mb-8 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-brand-50 to-emerald-50">
                        <div class="flex items-center gap-3">
                            <MegaphoneIcon class="w-5 h-5 text-brand-700" />
                            <div>
                                <h2 class="text-lg font-serif font-semibold text-slate-900">News & Announcements</h2>
                                <p v-if="linkedStudentGradeNames && linkedStudentGradeNames.length > 0" class="text-sm text-slate-600 mt-0.5">
                                    Showing school-wide news and grade-level messages for: {{ linkedStudentGradeNames.join(', ') }}
                                </p>
                                <p v-else class="text-sm text-slate-500 mt-0.5">
                                    Add your children in <Link href="/portal/settings" class="text-brand-600 hover:text-brand-700 font-medium">Settings → Linked Students</Link> to see grade-level news here.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <!-- School-Wide News from Admin -->
                        <div
                            v-for="announcement in recentAnnouncements"
                            :key="'school-' + announcement.id"
                            class="px-6 py-4 hover:bg-slate-50 transition-colors"
                        >
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-brand-100 rounded-lg flex flex-col items-center justify-center">
                                    <span class="text-xs font-semibold text-brand-600 uppercase">
                                        {{ new Date(announcement.published_at).toLocaleDateString('en-US', { month: 'short', timeZone: 'America/New_York' }) }}
                                    </span>
                                    <span class="text-lg font-bold text-brand-700">
                                        {{ new Date(announcement.published_at).getDate() }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-slate-900">{{ announcement.title }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-700">
                                            School-Wide
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                                        {{ announcement.content.replace(/<[^>]*>/g, '').substring(0, 200) }}{{ announcement.content.length > 200 ? '...' : '' }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-2">
                                        <p class="text-xs text-slate-500">
                                            From Silver Academy
                                        </p>
                                        <Link 
                                            :href="`/news/${announcement.slug}`" 
                                            class="text-xs font-medium text-brand-600 hover:text-brand-700"
                                        >
                                            Read more →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grade-Specific News from Teachers -->
                        <div
                            v-for="news in gradeNews"
                            :key="'grade-' + news.id"
                            class="px-6 py-4 hover:bg-emerald-50/50 transition-colors"
                        >
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-lg flex flex-col items-center justify-center">
                                    <span class="text-xs font-semibold text-emerald-600 uppercase">
                                        {{ new Date(news.published_at).toLocaleDateString('en-US', { month: 'short', timeZone: 'America/New_York' }) }}
                                    </span>
                                    <span class="text-lg font-bold text-emerald-700">
                                        {{ new Date(news.published_at).getDate() }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-slate-900">{{ news.title }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                            {{ news.target_grade?.name }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                                        {{ news.content.replace(/<[^>]*>/g, '').substring(0, 200) }}{{ news.content.length > 200 ? '...' : '' }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-2">
                                        From {{ news.author?.name || 'Teacher' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state if somehow both are empty but condition passed -->
                    <div v-if="(!recentAnnouncements || recentAnnouncements.length === 0) && (!gradeNews || gradeNews.length === 0)" class="p-6 text-center">
                        <NewspaperIcon class="mx-auto h-12 w-12 text-slate-300" />
                        <p class="mt-2 text-sm text-slate-500">No news at this time.</p>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- This Week's Lunch Menus -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-amber-50">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-serif font-semibold text-amber-900">This Week's Lunch Menu</h2>
                                <Link href="/portal/calendar?view=lunch" class="text-sm text-amber-700 hover:text-amber-800 font-medium">
                                    View All →
                                </Link>
                            </div>
                        </div>
                        <div v-if="thisWeekLunchMenus && thisWeekLunchMenus.length > 0" class="divide-y divide-slate-100">
                            <div
                                v-for="menu in thisWeekLunchMenus"
                                :key="menu.id"
                                class="px-6 py-4 hover:bg-amber-50/50 transition-colors"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-12 h-12 bg-amber-100 rounded-lg flex flex-col items-center justify-center">
                                        <span class="text-xs font-bold text-amber-600">{{ menu.short_day_name }}</span>
                                        <span class="text-sm font-semibold text-amber-700">{{ menu.formatted_date }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900">{{ menu.day_name }}</p>
                                        <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                                            {{ menu.content.replace(/<[^>]*>/g, '').substring(0, 100) }}{{ menu.content.length > 100 ? '...' : '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-6 text-center">
                            <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-slate-300" />
                            <p class="mt-2 text-sm text-slate-500">No lunch menus posted for this week yet.</p>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-serif font-semibold text-slate-900">Upcoming Events</h2>
                                <Link href="/portal/calendar" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                                    View Calendar →
                                </Link>
                            </div>
                        </div>
                        <div v-if="upcomingEvents && upcomingEvents.length > 0" class="divide-y divide-slate-200">
                            <div
                                v-for="event in upcomingEvents"
                                :key="event.id"
                                class="px-6 py-4 hover:bg-slate-50 transition-colors"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-12 h-12 bg-brand-50 rounded-lg flex flex-col items-center justify-center">
                                        <span class="text-xs font-semibold text-brand-600 uppercase">
                                            {{ new Date(event.event_start_date).toLocaleDateString('en-US', { month: 'short', timeZone: 'America/New_York' }) }}
                                        </span>
                                        <span class="text-lg font-bold text-brand-700">
                                            {{ new Date(new Date(event.event_start_date).toLocaleString('en-US', { timeZone: 'America/New_York' })).getDate() }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900">{{ event.title }}</p>
                                        <p class="text-xs text-slate-500 mt-1 flex items-center">
                                            <ClockIcon class="w-3 h-3 mr-1" />
                                            {{ formatDate(event.event_start_date) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-6 text-center">
                            <CalendarIcon class="mx-auto h-12 w-12 text-slate-300" />
                            <p class="mt-2 text-sm text-slate-500">No upcoming events.</p>
                        </div>
                    </div>
                </div>

                            </template>
        </div>
    </PortalLayout>
</template>
