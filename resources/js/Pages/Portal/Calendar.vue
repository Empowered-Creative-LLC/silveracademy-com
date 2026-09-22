<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Menu, MenuButton, MenuItem, MenuItems, Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import {
    ChevronDownIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ClockIcon,
    EllipsisHorizontalIcon,
} from '@heroicons/vue/20/solid';
import { CalendarIcon, XMarkIcon, PencilIcon, TrashIcon, PlusIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const page = usePage();

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    lunchMenus: {
        type: Array,
        default: () => [],
    },
    defaultView: {
        type: String,
        default: 'events',
    },
    user: {
        type: Object,
        default: null,
    },
});

// Role preview toggle (for super admins)
const previewRoles = ['admin', 'teacher', 'parent'];

// Load preview role from localStorage (persists across page navigations)
const getStoredPreviewRole = () => {
    if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('portal_preview_role');
        if (stored && previewRoles.includes(stored)) {
            return stored;
        }
    }
    return 'admin';
};

const previewRole = ref(getStoredPreviewRole());

const isSuperAdmin = computed(() => props.user?.role === 'super_admin');
const isActualAdmin = computed(() => props.user?.role === 'admin' || props.user?.role === 'super_admin');

const isAdmin = computed(() => {
    if (isSuperAdmin.value) {
        return previewRole.value === 'admin';
    }
    return isActualAdmin.value;
});

const onPreviewRoleChanged = (event) => {
    if (event.detail && previewRoles.includes(event.detail)) {
        previewRole.value = event.detail;
    }
};

onMounted(() => {
    window.addEventListener('preview-role-changed', onPreviewRoleChanged);
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        const mode = params.get('displayMode') || params.get('eventsView');
        if (mode === 'calendar' || mode === 'list') {
            displayMode.value = mode;
        }
    }
});

onUnmounted(() => {
    window.removeEventListener('preview-role-changed', onPreviewRoleChanged);
});

// View toggle state: 'events', 'lunch', or 'both'
const currentView = ref(props.defaultView);
// Calendar vs list layout — available to all portal roles and content views
const displayMode = ref('calendar');

// Current date tracking
const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const currentMonthName = computed(() => {
    return `${monthNames[currentMonth.value]} ${currentYear.value}`;
});

// Navigate months
const previousMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
    selectedDate.value = formatDate(new Date(currentYear.value, currentMonth.value, 1));
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
    selectedDate.value = formatDate(new Date(currentYear.value, currentMonth.value, 1));
};

const goToToday = () => {
    currentMonth.value = today.getMonth();
    currentYear.value = today.getFullYear();
    selectedDate.value = formatDate(today);
};

// Generate calendar days
const days = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;
    
    // First day of month
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Start from Monday (adjust for week starting on Monday)
    let startDate = new Date(firstDay);
    const dayOfWeek = startDate.getDay();
    const daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    startDate.setDate(startDate.getDate() - daysToSubtract);
    
    const calendarDays = [];
    const todayStr = formatDate(today);
    
    // Generate 42 days (6 weeks)
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dateStr = formatDate(date);
        const isCurrentMonth = date.getMonth() === month;
        const isToday = dateStr === todayStr;
        const isWeekend = date.getDay() === 0 || date.getDay() === 6;
        
        // Get events and lunch menus for this day
        const dayEvents = getEventsForDate(dateStr);
        const dayLunchMenu = getLunchMenuForDate(dateStr);
        
        calendarDays.push({
            date: dateStr,
            isCurrentMonth,
            isToday,
            isWeekend,
            events: dayEvents,
            lunchMenu: dayLunchMenu,
        });
    }
    
    return calendarDays;
});

// Format date as YYYY-MM-DD
const formatDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const parseDateKeyParts = (dateKey) => {
    if (!dateKey) return null;
    const [year, month, day] = String(dateKey).split('-').map(Number);
    if (!year || !month || !day) return null;
    return { year, month, day };
};

const dateKeyMatchesMonth = (dateKey, month, year) => {
    const parts = parseDateKeyParts(dateKey);
    if (!parts) return false;
    return parts.year === year && parts.month === month + 1;
};

// Get events for a specific date
const getEventsForDate = (dateStr) => {
    const dayEvents = [];
    
    props.events.forEach(event => {
        if (eventOccursOnDate(event, dateStr)) {
            dayEvents.push({
                id: `event-${event.id}`,
                name: event.title,
                time: formatTime(event.event_date),
                datetime: event.event_date,
                href: '#',
                type: 'event',
                isRecurring: event.is_recurring || false,
                recurrenceType: event.recurrence_type || 'none',
                isSchoolClosure: event.is_school_closure || false,
                description: event.description || '',
                buttonText: event.button_text || '',
                buttonUrl: event.button_url || '',
            });
        }
    });
    
    return dayEvents;
};

const eventOccursOnDate = (event, dateStr) => {
    const start = event.event_date_key || eventDateKey(event.event_date);
    if (!start) return false;
    if (event.is_recurring) {
        return start === dateStr;
    }
    const end = event.event_end_date_key || (event.event_end_date ? eventDateKey(event.event_end_date) : start);
    return dateStr >= start && dateStr <= (end || start);
};

const eventDateKey = (datetime) => {
    if (!datetime) return '';
    const raw = String(datetime);
    const date = new Date(raw);
    if (Number.isNaN(date.getTime())) {
        return raw.split('T')[0];
    }
    return date.toLocaleDateString('en-CA', { timeZone: 'America/New_York' });
};

const menuDateKey = (value) => {
    const match = String(value ?? '').match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
};

// Get lunch menu for a specific date
const getLunchMenuForDate = (dateStr) => {
    return props.lunchMenus.find(menu => menuDateKey(menu.menu_date) === dateStr) || null;
};

// Format time from datetime
const formatTime = (datetime) => {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        timeZone: 'America/New_York',
    });
};

// Get truncated menu content (preserve line breaks, strip other HTML)
const getMenuPreview = (content, maxLength = 50, preserveBreaks = true) => {
    if (!content) return 'No menu details';
    
    // Convert common HTML line breaks to newlines
    let text = content
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<\/p>\s*<p[^>]*>/gi, '\n\n')
        .replace(/<\/div>\s*<div[^>]*>/gi, '\n')
        .replace(/<\/li>\s*<li[^>]*>/gi, '\n• ')
        .replace(/<li[^>]*>/gi, '• ')
        .replace(/<[^>]*>/g, '') // Strip remaining HTML
        .replace(/&nbsp;/g, ' ')
        .replace(/&amp;/g, '&')
        .trim();
    
    // Collapse multiple newlines to max 2
    text = text.replace(/\n{3,}/g, '\n\n');
    
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
};

// Get first line of menu content for compact display
const getMenuFirstLine = (content) => {
    if (!content) return 'No menu details';
    const preview = getMenuPreview(content, 200);
    const firstLine = preview.split('\n')[0].trim();
    return firstLine || 'Menu available';
};

// Get menu content for calendar cell display (multiple lines, limited)
const getMenuCalendarDisplay = (content, maxLines = 3) => {
    if (!content) return { lines: ['No menu details'], hasMore: false };
    const preview = getMenuPreview(content, 500);
    const allLines = preview.split('\n').filter(line => line.trim());
    const displayLines = allLines.slice(0, maxLines);
    const hasMore = allLines.length > maxLines;
    return { lines: displayLines, hasMore };
};

// Selected day events (for mobile)
const selectedDate = ref(formatDate(today));
const selectedDayEvents = computed(() => {
    const day = days.value.find(d => d.date === selectedDate.value);
    if (!day) return [];
    
    const items = [];
    
    // Add events if showing events or both
    if (currentView.value === 'events' || currentView.value === 'both') {
        items.push(...day.events);
    }
    
    // Add lunch menu if showing lunch or both
    if ((currentView.value === 'lunch' || currentView.value === 'both') && day.lunchMenu) {
        items.push({
            id: `lunch-${day.lunchMenu.id}`,
            name: getMenuFirstLine(day.lunchMenu.content),
            time: day.lunchMenu.short_day_name,
            datetime: day.lunchMenu.menu_date,
            type: 'lunch',
            content: day.lunchMenu.content,
            menuId: day.lunchMenu.id,
        });
    }
    
    return items;
});

const selectDay = (date) => {
    selectedDate.value = date;
};

// Get items for the current week based on view
const currentWeekItems = computed(() => {
    const weekItems = [];
    
    // Find the Monday of the currently selected date's week
    const selectedDateObj = new Date(selectedDate.value + 'T00:00:00');
    const dayOfWeek = selectedDateObj.getDay();
    const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
    const monday = new Date(selectedDateObj);
    monday.setDate(selectedDateObj.getDate() + mondayOffset);
    
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    
    // Filter events if showing events or both
    if (currentView.value === 'events' || currentView.value === 'both') {
        props.events.forEach(event => {
            const eventDate = new Date(event.event_date);
            if (eventDate >= monday && eventDate <= sunday) {
                weekItems.push({
                    id: `event-${event.id}`,
                    name: event.title,
                    time: formatTime(event.event_date),
                    datetime: event.event_date,
                    dayName: eventDate.toLocaleDateString('en-US', { weekday: 'short', timeZone: 'America/New_York' }),
                    formattedDate: eventDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', timeZone: 'America/New_York' }),
                    type: 'event',
                    description: event.description || '',
                    buttonText: event.button_text || '',
                    buttonUrl: event.button_url || '',
                    endDate: event.event_end_date,
                    isRecurring: event.is_recurring || false,
                    recurrenceType: event.recurrence_type || 'none',
                    isSchoolClosure: event.is_school_closure || false,
                });
            }
        });
    }
    
    // Add lunch menus if showing lunch or both
    if (currentView.value === 'lunch' || currentView.value === 'both') {
        props.lunchMenus.forEach(menu => {
            const menuDate = new Date(menu.menu_date + 'T00:00:00');
            if (menuDate >= monday && menuDate <= sunday) {
                weekItems.push({
                    id: `lunch-${menu.id}`,
                    name: getMenuFirstLine(menu.content),
                    fullContent: getMenuPreview(menu.content, 200),
                    time: '',
                    datetime: menu.menu_date,
                    dayName: menu.short_day_name,
                    formattedDate: menu.formatted_date,
                    type: 'lunch',
                    description: menu.content || '',
                    menuId: menu.id,
                });
            }
        });
    }
    
    // Sort by date
    return weekItems.sort((a, b) => new Date(a.datetime) - new Date(b.datetime));
});

// Get the week range for display
const selectedWeekRange = computed(() => {
    const selectedDateObj = new Date(selectedDate.value + 'T00:00:00');
    const dayOfWeek = selectedDateObj.getDay();
    const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
    const monday = new Date(selectedDateObj);
    monday.setDate(selectedDateObj.getDate() + mondayOffset);
    
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    
    const monStr = monday.toLocaleDateString('en-US', { month: 'short', day: 'numeric', timeZone: 'America/New_York' });
    const sunStr = sunday.toLocaleDateString('en-US', { month: 'short', day: 'numeric', timeZone: 'America/New_York' });
    
    return `${monStr} - ${sunStr}`;
});

// Dynamic section title based on view
const weekSectionTitle = computed(() => {
    switch (currentView.value) {
        case 'events':
            return 'Events This Week';
        case 'lunch':
            return 'Lunch Menus This Week';
        case 'both':
            return 'This Week';
        default:
            return 'This Week';
    }
});

// Modal state
const showEventModal = ref(false);
const selectedEvent = ref(null);

const openEventDetails = (event) => {
    selectedEvent.value = event;
    showEventModal.value = true;
};

const closeEventModal = () => {
    showEventModal.value = false;
    selectedEvent.value = null;
};

const formatFullDate = (datetime) => {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toLocaleDateString('en-US', { 
        weekday: 'long',
        month: 'long', 
        day: 'numeric',
        year: 'numeric',
        timeZone: 'America/New_York',
    });
};

// Lunch menu modal for admin
const showLunchMenuModal = ref(false);
const selectedLunchDate = ref(null);
const selectedLunchMenu = ref(null);

const openLunchMenuModal = (date, existingMenu = null) => {
    selectedLunchDate.value = date;
    selectedLunchMenu.value = existingMenu;
    showLunchMenuModal.value = true;
};

const closeLunchMenuModal = () => {
    showLunchMenuModal.value = false;
    selectedLunchDate.value = null;
    selectedLunchMenu.value = null;
};

// Admin actions for calendar day click
const handleDayClick = (day) => {
    if (isAdmin.value && (currentView.value === 'lunch' || currentView.value === 'both') && !day.isWeekend) {
        if (day.lunchMenu) {
            // Edit existing menu
            router.visit(`/portal/lunch/${day.lunchMenu.id}/edit`);
        } else {
            // Create new menu
            router.visit(`/portal/lunch/create?date=${day.date}`);
        }
    } else {
        selectDay(day.date);
    }
};

// Delete lunch menu
const deleteLunchMenu = (menuId) => {
    if (confirm('Are you sure you want to delete this lunch menu?')) {
        router.delete(`/portal/lunch/${menuId}`, {
            preserveScroll: true,
        });
    }
};

// Get filtered items for calendar day based on view
const getFilteredItemsForDay = (day) => {
    const items = [];
    
    if (currentView.value === 'events' || currentView.value === 'both') {
        items.push(...day.events);
    }
    
    if ((currentView.value === 'lunch' || currentView.value === 'both') && day.lunchMenu) {
        const calendarDisplay = getMenuCalendarDisplay(day.lunchMenu.content, 3);
        items.push({
            id: `lunch-${day.lunchMenu.id}`,
            name: getMenuFirstLine(day.lunchMenu.content),
            calendarLines: calendarDisplay.lines,
            hasMoreContent: calendarDisplay.hasMore,
            type: 'lunch',
            time: '',
            content: day.lunchMenu.content,
            menuId: day.lunchMenu.id,
        });
    }
    
    return items;
};

// List-mode dataset (chronological by selected month/year, respects active content view).
const listItemsForCurrentMonth = computed(() => {
    const month = currentMonth.value;
    const year = currentYear.value;
    const items = [];

    if (currentView.value === 'events' || currentView.value === 'both') {
        props.events.forEach((event) => {
            const dateKey = event.event_date_key || eventDateKey(event.event_date);
            const endKey = event.is_recurring
                ? dateKey
                : (event.event_end_date_key || (event.event_end_date ? eventDateKey(event.event_end_date) : dateKey));
            const monthStart = `${year}-${String(month + 1).padStart(2, '0')}-01`;
            const monthEndDate = new Date(year, month + 1, 0);
            const monthEnd = formatDate(monthEndDate);
            if (!dateKey || endKey < monthStart || dateKey > monthEnd) return;

            items.push({
                id: `event-${event.id}`,
                sortDate: dateKey,
                itemType: 'event',
                title: event.title,
                datetime: event.event_date,
                description: event.description || '',
                buttonText: event.button_text || '',
                buttonUrl: event.button_url || '',
                endDate: event.event_end_date,
                isRecurring: event.is_recurring || false,
                recurrenceType: event.recurrence_type || 'none',
                isSchoolClosure: event.is_school_closure || false,
            });
        });
    }

    if (currentView.value === 'lunch' || currentView.value === 'both') {
        props.lunchMenus.forEach((menu) => {
            const menuKey = menuDateKey(menu.menu_date);
            if (!dateKeyMatchesMonth(menuKey, month, year)) return;

            items.push({
                id: `lunch-${menu.id}`,
                sortDate: menuKey,
                itemType: 'lunch',
                title: getMenuFirstLine(menu.content),
                datetime: menu.menu_date,
                description: menu.content || '',
                menuId: menu.id,
            });
        });
    }

    return items.sort((a, b) => {
        const aParts = parseDateKeyParts(a.sortDate);
        const bParts = parseDateKeyParts(b.sortDate);
        if (!aParts || !bParts) return 0;
        if (aParts.year !== bParts.year) return aParts.year - bParts.year;
        if (aParts.month !== bParts.month) return aParts.month - bParts.month;
        if (aParts.day !== bParts.day) return aParts.day - bParts.day;
        return String(a.sortDate).localeCompare(String(b.sortDate));
    });
});

const listEmptyMessage = computed(() => {
    switch (currentView.value) {
        case 'events':
            return 'No events scheduled for this month.';
        case 'lunch':
            return 'No lunch menus for this month.';
        default:
            return 'Nothing scheduled for this month.';
    }
});
</script>

<template>
    <Head title="Calendar" />

    <PortalLayout>
        <template #header>
            <span>Calendar</span>
        </template>
        
        <div class="min-w-0 max-w-full overflow-x-hidden lg:flex lg:h-full lg:flex-col">
            <div
                v-if="page.props.flash?.success || page.props.flash?.message"
                class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
            >
                {{ typeof page.props.flash.success === 'string' ? page.props.flash.success : page.props.flash.message }}
            </div>
            <div
                v-if="page.props.flash?.import_notices?.length"
                class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
            >
                <p v-for="(notice, index) in page.props.flash.import_notices" :key="index">{{ notice }}</p>
            </div>
            <header class="flex flex-col gap-4 border-b border-slate-200 bg-white px-4 py-4 sm:px-6 lg:flex-none rounded-t-xl">
                <div class="flex w-full flex-col gap-3">
                    <h1 class="text-base font-semibold text-slate-900">
                        <time :datetime="`${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`">{{ currentMonthName }}</time>
                    </h1>
                    
                    <div class="flex flex-wrap items-center gap-2">
                    <!-- View Toggle -->
                    <div class="flex rounded-lg bg-slate-100 p-1">
                        <button
                            type="button"
                            @click="currentView = 'events'"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                currentView === 'events' 
                                    ? 'bg-white shadow text-slate-900' 
                                    : 'text-slate-500 hover:text-slate-900'
                            ]"
                        >
                            Events
                        </button>
                        <button
                            type="button"
                            @click="currentView = 'lunch'"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                currentView === 'lunch' 
                                    ? 'bg-white shadow text-amber-600' 
                                    : 'text-slate-500 hover:text-slate-900'
                            ]"
                        >
                            Lunch Menu
                        </button>
                        <button
                            type="button"
                            @click="currentView = 'both'"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                currentView === 'both' 
                                    ? 'bg-white shadow text-slate-900' 
                                    : 'text-slate-500 hover:text-slate-900'
                            ]"
                        >
                            Both
                        </button>
                    </div>

                    <div class="flex rounded-lg bg-slate-100 p-1">
                        <label class="sr-only">Display mode</label>
                        <button
                            type="button"
                            @click="displayMode = 'calendar'"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                displayMode === 'calendar'
                                    ? 'bg-white shadow text-slate-900'
                                    : 'text-slate-500 hover:text-slate-900',
                            ]"
                        >
                            Calendar
                        </button>
                        <button
                            type="button"
                            @click="displayMode = 'list'"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                displayMode === 'list'
                                    ? 'bg-white shadow text-slate-900'
                                    : 'text-slate-500 hover:text-slate-900',
                            ]"
                        >
                            List
                        </button>
                    </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative flex items-center rounded-md bg-white shadow-sm outline outline-1 -outline-offset-1 outline-slate-300 md:items-stretch">
                        <button 
                            type="button" 
                            @click="previousMonth"
                            class="flex h-9 w-12 items-center justify-center rounded-l-md pr-1 text-slate-400 hover:text-slate-500 focus:relative md:w-9 md:pr-0 md:hover:bg-slate-50"
                        >
                            <span class="sr-only">Previous month</span>
                            <ChevronLeftIcon class="size-5" aria-hidden="true" />
                        </button>
                        <button 
                            type="button" 
                            @click="goToToday"
                            class="hidden px-3.5 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:relative md:block"
                        >
                            Today
                        </button>
                        <span class="relative -mx-px h-5 w-px bg-slate-300 md:hidden"></span>
                        <button 
                            type="button" 
                            @click="nextMonth"
                            class="flex h-9 w-12 items-center justify-center rounded-r-md pl-1 text-slate-400 hover:text-slate-500 focus:relative md:w-9 md:pl-0 md:hover:bg-slate-50"
                        >
                            <span class="sr-only">Next month</span>
                            <ChevronRightIcon class="size-5" aria-hidden="true" />
                        </button>
                    </div>
                    
                    <div class="hidden md:flex md:items-center gap-2">
                        <div class="h-6 w-px bg-slate-300"></div>
                        
                        <!-- Import button for lunch view (admin only) -->
                        <Link 
                            v-if="isAdmin && (currentView === 'lunch' || currentView === 'both')"
                            href="/portal/lunch/import"
                            class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50"
                        >
                            <ArrowUpTrayIcon class="h-4 w-4" />
                            Import Menus
                        </Link>
                        
                        <!-- Add lunch menu button (admin only) -->
                        <Link 
                            v-if="isAdmin && (currentView === 'lunch' || currentView === 'both')"
                            href="/portal/lunch/create"
                            class="rounded-md bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500"
                        >
                            Add menu
                        </Link>
                        
                        <!-- Add event button -->
                        <Link 
                            v-if="currentView === 'events' || currentView === 'both'"
                            href="/portal/posts/create?type=event" 
                            class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                        >
                            Add event
                        </Link>
                    </div>
                    
                    <!-- Mobile menu -->
                    <Menu as="div" class="relative md:hidden">
                        <MenuButton class="-mx-2 flex items-center rounded-full border border-transparent p-2 text-slate-400 hover:text-slate-500">
                            <span class="sr-only">Open menu</span>
                            <EllipsisHorizontalIcon class="size-5" aria-hidden="true" />
                        </MenuButton>

                        <transition 
                            enter-active-class="transition ease-out duration-100" 
                            enter-from-class="transform opacity-0 scale-95" 
                            enter-to-class="transform scale-100" 
                            leave-active-class="transition ease-in duration-75" 
                            leave-from-class="transform scale-100" 
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <MenuItems class="absolute right-0 z-10 mt-3 w-36 origin-top-right divide-y divide-slate-100 overflow-hidden rounded-md bg-white shadow-lg outline outline-1 outline-black/5">
                                <div class="py-1">
                                    <MenuItem v-if="currentView === 'events' || currentView === 'both'" v-slot="{ active }">
                                        <Link 
                                            href="/portal/posts/create?type=event" 
                                            :class="[active ? 'bg-slate-100 text-slate-900 outline-hidden' : 'text-slate-700', 'block px-4 py-2 text-sm']"
                                        >
                                            Create event
                                        </Link>
                                    </MenuItem>
                                    <MenuItem v-if="isAdmin && (currentView === 'lunch' || currentView === 'both')" v-slot="{ active }">
                                        <Link 
                                            href="/portal/lunch/create" 
                                            :class="[active ? 'bg-slate-100 text-slate-900 outline-hidden' : 'text-slate-700', 'block px-4 py-2 text-sm']"
                                        >
                                            Add menu
                                        </Link>
                                    </MenuItem>
                                    <MenuItem v-if="isAdmin && (currentView === 'lunch' || currentView === 'both')" v-slot="{ active }">
                                        <Link 
                                            href="/portal/lunch/import" 
                                            :class="[active ? 'bg-slate-100 text-slate-900 outline-hidden' : 'text-slate-700', 'block px-4 py-2 text-sm']"
                                        >
                                            Import menus
                                        </Link>
                                    </MenuItem>
                                </div>
                                <div class="py-1">
                                    <MenuItem v-slot="{ active }">
                                        <button 
                                            @click="goToToday"
                                            :class="[active ? 'bg-slate-100 text-slate-900 outline-hidden' : 'text-slate-700', 'block w-full text-left px-4 py-2 text-sm']"
                                        >
                                            Go to today
                                        </button>
                                    </MenuItem>
                                </div>
                            </MenuItems>
                        </transition>
                    </Menu>
                </div>
            </header>
            
            <div v-if="displayMode === 'calendar'" class="shadow-sm ring-1 ring-black/5 lg:flex lg:flex-auto lg:flex-col rounded-b-xl overflow-hidden">
                <!-- Day headers -->
                <div class="grid grid-cols-7 gap-px border-b border-slate-300 bg-slate-200 text-center text-xs/6 font-semibold text-slate-700 lg:flex-none">
                    <div class="flex justify-center bg-white py-2">
                        <span>M</span>
                        <span class="sr-only sm:not-sr-only">on</span>
                    </div>
                    <div class="flex justify-center bg-white py-2">
                        <span>T</span>
                        <span class="sr-only sm:not-sr-only">ue</span>
                    </div>
                    <div class="flex justify-center bg-white py-2">
                        <span>W</span>
                        <span class="sr-only sm:not-sr-only">ed</span>
                    </div>
                    <div class="flex justify-center bg-white py-2">
                        <span>T</span>
                        <span class="sr-only sm:not-sr-only">hu</span>
                    </div>
                    <div class="flex justify-center bg-white py-2">
                        <span>F</span>
                        <span class="sr-only sm:not-sr-only">ri</span>
                    </div>
                    <div class="flex justify-center bg-white py-2">
                        <span>S</span>
                        <span class="sr-only sm:not-sr-only">at</span>
                    </div>
                    <div class="flex justify-center bg-white py-2">
                        <span>S</span>
                        <span class="sr-only sm:not-sr-only">un</span>
                    </div>
                </div>
                
                <!-- Calendar grid -->
                <div class="flex bg-slate-200 text-xs/6 text-slate-700 lg:flex-auto">
                    <!-- Desktop view -->
                    <div class="hidden w-full lg:grid lg:grid-cols-7 lg:grid-rows-6 lg:gap-px">
                        <div 
                            v-for="day in days" 
                            :key="day.date" 
                            @click="handleDayClick(day)"
                            :class="[
                                'group relative px-3 py-2 min-h-[100px]',
                                day.isCurrentMonth ? 'bg-white' : 'bg-slate-50 text-slate-500',
                                isAdmin && (currentView === 'lunch' || currentView === 'both') && !day.isWeekend 
                                    ? 'cursor-pointer hover:bg-slate-50' 
                                    : ''
                            ]"
                        >
                            <time 
                                :datetime="day.date" 
                                :class="[
                                    day.isToday ? 'flex size-6 items-center justify-center rounded-full bg-brand-600 font-semibold text-white' : '',
                                    !day.isCurrentMonth ? 'opacity-75' : ''
                                ]"
                            >
                                {{ day.date.split('-').pop().replace(/^0/, '') }}
                            </time>
                            
                            <!-- Events/Menus list -->
                            <ol v-if="getFilteredItemsForDay(day).length > 0" class="mt-2 space-y-0.5">
                                <template v-for="(item, itemIndex) in getFilteredItemsForDay(day)" :key="item.id">
                                    <!-- Lunch menu: show multiple lines -->
                                    <li v-if="item.type === 'lunch'" class="space-y-0.5">
                                        <a 
                                            href="#" 
                                            @click.prevent="openEventDetails({ ...item, description: day.lunchMenu.content, datetime: day.date, menuId: day.lunchMenu.id })"
                                            class="group block hover:bg-amber-50 rounded px-0.5 -mx-0.5"
                                        >
                                            <p 
                                                v-for="(line, lineIndex) in item.calendarLines" 
                                                :key="lineIndex"
                                                class="text-xs text-amber-700 truncate leading-tight"
                                            >
                                                {{ line }}
                                            </p>
                                            <p v-if="item.hasMoreContent" class="text-xs text-amber-500 italic">
                                                View more...
                                            </p>
                                        </a>
                                    </li>
                                    <!-- School Closure: prominent red styling -->
                                    <li v-else-if="item.isSchoolClosure && itemIndex < 2">
                                        <a 
                                            href="#" 
                                            @click.prevent="openEventDetails(item)"
                                            class="group flex items-center bg-red-100 rounded px-1.5 py-0.5 -mx-1"
                                        >
                                            <!-- School Closure icon -->
                                            <svg 
                                                class="w-3 h-3 mr-1 flex-shrink-0 text-red-600" 
                                                fill="none" 
                                                viewBox="0 0 24 24" 
                                                stroke="currentColor"
                                                title="School Closure"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <p class="flex-auto truncate text-xs font-semibold text-red-700">
                                                {{ item.name }}
                                            </p>
                                        </a>
                                    </li>
                                    <!-- Regular event: show as before (limited to first 2) -->
                                    <li v-else-if="itemIndex < 2">
                                        <a 
                                            href="#" 
                                            @click.prevent="openEventDetails(item)"
                                            class="group flex items-center"
                                        >
                                            <!-- Recurring indicator -->
                                            <svg 
                                                v-if="item.isRecurring"
                                                class="w-3 h-3 mr-1 flex-shrink-0 text-amber-500" 
                                                fill="none" 
                                                viewBox="0 0 24 24" 
                                                stroke="currentColor"
                                                :title="`Repeats ${item.recurrenceType}`"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <p class="flex-auto truncate text-xs font-medium text-slate-900 group-hover:text-brand-600">
                                                {{ item.name }}
                                            </p>
                                            <time 
                                                v-if="item.time"
                                                :datetime="item.datetime" 
                                                class="ml-3 hidden flex-none text-xs text-slate-500 group-hover:text-brand-600 xl:block"
                                            >
                                                {{ item.time }}
                                            </time>
                                        </a>
                                    </li>
                                </template>
                                <!-- Show count of additional events (excluding lunch) -->
                                <li v-if="getFilteredItemsForDay(day).filter(i => i.type !== 'lunch').length > 2" class="text-xs text-slate-500">
                                    + {{ getFilteredItemsForDay(day).filter(i => i.type !== 'lunch').length - 2 }} more events
                                </li>
                            </ol>
                            
                            <!-- Admin indicator for empty lunch menu day -->
                            <div 
                                v-if="isAdmin && (currentView === 'lunch' || currentView === 'both') && !day.lunchMenu && !day.isWeekend && day.isCurrentMonth"
                                class="mt-2 opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <span class="text-xs text-amber-500 flex items-center gap-1">
                                    <PlusIcon class="w-3 h-3" />
                                    Add menu
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile view -->
                    <div class="isolate grid w-full grid-cols-7 grid-rows-6 gap-px lg:hidden">
                        <button 
                            v-for="day in days" 
                            :key="day.date" 
                            type="button" 
                            @click="selectDay(day.date)"
                            :class="[
                                'group relative flex h-14 flex-col px-3 py-2 hover:bg-slate-100 focus:z-10',
                                day.isCurrentMonth ? 'bg-white' : 'bg-slate-50',
                                selectedDate === day.date ? 'font-semibold' : '',
                                day.isToday && selectedDate !== day.date ? 'text-brand-600 font-semibold' : '',
                                !day.isCurrentMonth && !day.isToday ? 'text-slate-500' : 'text-slate-900'
                            ]"
                        >
                            <time 
                                :datetime="day.date" 
                                :class="[
                                    'ml-auto',
                                    !day.isCurrentMonth ? 'opacity-75' : '',
                                    selectedDate === day.date && !day.isToday ? 'flex size-6 items-center justify-center rounded-full bg-slate-900 text-white' : '',
                                    selectedDate === day.date && day.isToday ? 'flex size-6 items-center justify-center rounded-full bg-brand-600 text-white' : ''
                                ]"
                            >
                                {{ day.date.split('-').pop().replace(/^0/, '') }}
                            </time>
                            <span class="sr-only">{{ getFilteredItemsForDay(day).length }} items</span>
                            <span v-if="getFilteredItemsForDay(day).length > 0" class="-mx-0.5 mt-auto flex flex-wrap-reverse">
                                <span 
                                    v-for="item in getFilteredItemsForDay(day)" 
                                    :key="item.id" 
                                    :class="[
                                        'mx-0.5 mb-1 size-1.5 rounded-full',
                                        item.type === 'lunch' ? 'bg-amber-400' : item.isSchoolClosure ? 'bg-red-500' : 'bg-slate-400'
                                    ]"
                                ></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="bg-white rounded-b-xl shadow-sm ring-1 ring-black/5 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">{{ currentMonthName }}</h2>
                </div>
                <div v-if="listItemsForCurrentMonth.length > 0" class="divide-y divide-slate-100">
                    <div
                        v-for="item in listItemsForCurrentMonth"
                        :key="item.id"
                        class="px-4 sm:px-6 py-4 hover:bg-slate-50 transition-colors cursor-pointer"
                        @click="openEventDetails({
                            id: item.id,
                            name: item.title,
                            datetime: item.datetime,
                            time: item.itemType === 'event' ? formatTime(item.datetime) : '',
                            type: item.itemType,
                            description: item.description,
                            buttonText: item.buttonText || '',
                            buttonUrl: item.buttonUrl || '',
                            endDate: item.endDate,
                            isRecurring: item.isRecurring || false,
                            recurrenceType: item.recurrenceType || 'none',
                            isSchoolClosure: item.isSchoolClosure || false,
                            menuId: item.menuId,
                        })"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="item.itemType === 'lunch'"
                                        class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700"
                                    >
                                        Lunch Menu
                                    </span>
                                    <span
                                        v-else-if="item.isSchoolClosure"
                                        class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700"
                                    >
                                        School Closure
                                    </span>
                                    <p :class="[
                                        'font-medium break-words',
                                        item.itemType === 'lunch' ? 'text-amber-700' : item.isSchoolClosure ? 'text-red-700' : 'text-slate-900'
                                    ]">
                                        {{ item.title }}
                                    </p>
                                </div>
                                <p class="text-sm text-slate-600 break-words">
                                    {{ formatFullDate(item.datetime) }}
                                    <span v-if="item.itemType === 'event' && item.datetime">at {{ formatTime(item.datetime) }}</span>
                                </p>
                            </div>
                            <span class="self-start sm:self-center text-xs text-brand-700 bg-brand-50 px-2 py-1 rounded-md flex-shrink-0">View</span>
                        </div>
                    </div>
                </div>
                <div v-else class="px-6 py-12 text-center">
                    <CalendarIcon class="mx-auto h-12 w-12 text-slate-300" />
                    <p class="mt-2 text-sm text-slate-500">{{ listEmptyMessage }}</p>
                </div>
            </div>
            
            <!-- Mobile event list (calendar view only) -->
            <div v-if="displayMode === 'calendar' && selectedDayEvents.length > 0" class="relative px-4 py-10 sm:px-6 lg:hidden">
                <ol class="divide-y divide-slate-100 overflow-hidden rounded-lg bg-white text-sm shadow-sm outline outline-1 outline-black/5">
                    <li 
                        v-for="item in selectedDayEvents" 
                        :key="item.id" 
                        class="group flex p-4 pr-6 focus-within:bg-slate-50 hover:bg-slate-50"
                    >
                        <div class="flex-auto">
                            <p 
                                :class="[
                                    'font-semibold',
                                    item.type === 'lunch' ? 'text-amber-600' : 'text-slate-900'
                                ]"
                            >
                                {{ item.name }}
                            </p>
                            <time v-if="item.time" :datetime="item.datetime" class="mt-2 flex items-center text-slate-700">
                                <ClockIcon class="mr-2 size-5 text-slate-400" aria-hidden="true" />
                                {{ item.time }}
                            </time>
                        </div>
                        <button 
                            type="button"
                            @click="openEventDetails(item)"
                            class="ml-6 flex-none self-center rounded-md bg-white px-3 py-2 font-semibold text-slate-900 opacity-0 shadow-sm ring-1 ring-slate-300 ring-inset group-hover:opacity-100 hover:ring-slate-400 focus:opacity-100"
                        >
                            View<span class="sr-only">, {{ item.name }}</span>
                        </button>
                    </li>
                </ol>
            </div>
            
            <!-- Empty state for mobile (calendar view only) -->
            <div v-else-if="displayMode === 'calendar'" class="px-4 py-10 sm:px-6 lg:hidden">
                <p class="text-center text-sm text-slate-500">No items on selected day</p>
            </div>
            
            <!-- Weekly Items List (calendar view only — list view already shows the full month) -->
            <div v-if="displayMode === 'calendar'" class="mt-8 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-serif font-semibold text-slate-900">{{ weekSectionTitle }}</h2>
                        <span class="text-sm text-slate-500">{{ selectedWeekRange }}</span>
                    </div>
                </div>
                
                <div v-if="currentWeekItems.length > 0" class="divide-y divide-slate-100">
                    <div 
                        v-for="item in currentWeekItems" 
                        :key="item.id"
                        class="flex flex-col gap-3 px-4 sm:px-6 py-4 hover:bg-slate-50 transition-colors cursor-pointer"
                        @click="openEventDetails(item)"
                    >
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="text-sm font-medium text-slate-500">{{ item.dayName }}</span>
                            <span class="text-sm font-semibold text-slate-900">{{ item.formattedDate }}</span>
                        </div>
                        <div class="min-w-0">
                            <!-- Lunch Menu -->
                            <template v-if="item.type === 'lunch'">
                                <p class="font-medium text-amber-600 mb-1">Lunch Menu</p>
                                <p 
                                    v-if="item.fullContent"
                                    class="text-sm text-slate-600 whitespace-pre-line"
                                >{{ item.fullContent }}</p>
                            </template>
                            <!-- School Closure Event -->
                            <template v-else-if="item.isSchoolClosure">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    <p class="font-semibold text-red-700">
                                        {{ item.name }}
                                    </p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        No School
                                    </span>
                                </div>
                            </template>
                            <!-- Regular Event -->
                            <template v-else>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-slate-900">
                                        {{ item.name }}
                                    </p>
                                    <!-- Recurring indicator -->
                                    <span 
                                        v-if="item.isRecurring"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"
                                        :title="`Repeats ${item.recurrenceType}`"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        {{ item.recurrenceType === 'daily' ? 'Daily' : item.recurrenceType === 'weekly' ? 'Weekly' : item.recurrenceType === 'biweekly' ? 'Bi-weekly' : 'Monthly' }}
                                    </span>
                                </div>
                            </template>
                        </div>
                        <div class="flex flex-wrap items-center gap-2" @click.stop>
                            <!-- Admin actions for lunch menu -->
                            <template v-if="item.type === 'lunch' && isAdmin">
                                <Link 
                                    :href="`/portal/lunch/${item.menuId}/edit`"
                                    class="p-2 text-slate-400 hover:text-slate-600 transition-colors"
                                    title="Edit menu"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </Link>
                                <button 
                                    type="button"
                                    @click="deleteLunchMenu(item.menuId)"
                                    class="p-2 text-slate-400 hover:text-red-600 transition-colors"
                                    title="Delete menu"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </template>
                            <button 
                                type="button"
                                @click="openEventDetails(item)"
                                class="w-full sm:w-auto flex-shrink-0 px-4 py-2 text-sm font-medium text-brand-600 bg-brand-50 rounded-lg hover:bg-brand-100 transition-colors"
                            >
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                
                <div v-else class="px-4 sm:px-6 py-12 text-center">
                    <CalendarIcon class="mx-auto h-12 w-12 text-slate-300" />
                    <p class="mt-2 text-sm text-slate-500">
                        {{ currentView === 'events' ? 'No events scheduled for this week.' : 
                           currentView === 'lunch' ? 'No lunch menus for this week.' : 
                           'Nothing scheduled for this week.' }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Event/Lunch Details Modal -->
        <TransitionRoot as="template" :show="showEventModal">
            <Dialog class="relative z-50" @close="closeEventModal">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-slate-500/75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                <!-- Header -->
                                <div :class="[
                                    'px-6 py-4',
                                    selectedEvent?.type === 'lunch' ? 'bg-amber-500' : selectedEvent?.isSchoolClosure ? 'bg-red-600' : 'bg-brand-600'
                                ]">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <DialogTitle class="text-lg font-serif font-semibold text-white flex items-center gap-2">
                                                <svg 
                                                    v-if="selectedEvent?.isSchoolClosure"
                                                    class="w-5 h-5" 
                                                    fill="none" 
                                                    viewBox="0 0 24 24" 
                                                    stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                {{ selectedEvent?.type === 'lunch' ? 'Lunch Menu' : selectedEvent?.name }}
                                            </DialogTitle>
                                            <p :class="[
                                                'mt-1 text-sm',
                                                selectedEvent?.type === 'lunch' ? 'text-amber-100' : selectedEvent?.isSchoolClosure ? 'text-red-100' : 'text-brand-100'
                                            ]">
                                                {{ formatFullDate(selectedEvent?.datetime) }}
                                                <span v-if="selectedEvent?.time && selectedEvent?.type !== 'lunch'">
                                                    at {{ selectedEvent?.time }}
                                                </span>
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            :class="[
                                                'rounded-md focus:outline-none',
                                                selectedEvent?.type === 'lunch' ? 'text-amber-200 hover:text-white' : selectedEvent?.isSchoolClosure ? 'text-red-200 hover:text-white' : 'text-brand-200 hover:text-white'
                                            ]"
                                            @click="closeEventModal"
                                        >
                                            <span class="sr-only">Close</span>
                                            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="px-6 py-5">
                                    <!-- Type Badge (only for events, not lunch) -->
                                    <div v-if="selectedEvent?.type !== 'lunch'" class="mb-4 flex items-center gap-2 flex-wrap">
                                        <!-- School Closure Badge -->
                                        <span 
                                            v-if="selectedEvent?.isSchoolClosure"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            School Closure
                                        </span>
                                        <!-- Regular Event Badge -->
                                        <span 
                                            v-else
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800"
                                        >
                                            Event
                                        </span>
                                        <!-- Recurrence Badge -->
                                        <span 
                                            v-if="selectedEvent?.isRecurring"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Repeats {{ selectedEvent?.recurrenceType === 'daily' ? 'Daily' : selectedEvent?.recurrenceType === 'weekly' ? 'Weekly' : selectedEvent?.recurrenceType === 'biweekly' ? 'Bi-weekly' : 'Monthly' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Lunch Menu Content (with preserved formatting) -->
                                    <div v-if="selectedEvent?.type === 'lunch' && selectedEvent?.description" class="text-slate-700">
                                        <p class="whitespace-pre-line text-base leading-relaxed">{{ getMenuPreview(selectedEvent.description, 2000) }}</p>
                                    </div>
                                    <!-- Event Description -->
                                    <div v-else-if="selectedEvent?.description" class="prose prose-sm max-w-none text-slate-700">
                                        <div v-html="selectedEvent.description"></div>
                                    </div>
                                    <p v-else class="text-sm text-slate-500 italic">
                                        No additional details available.
                                    </p>
                                    
                                    <!-- End Date if different -->
                                    <div v-if="selectedEvent?.endDate && selectedEvent?.type !== 'lunch'" class="mt-4 p-3 bg-slate-50 rounded-lg">
                                        <p class="text-sm text-slate-600">
                                            <span class="font-medium">Ends:</span> {{ formatFullDate(selectedEvent.endDate) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Footer with Actions -->
                                <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row gap-3 sm:justify-end">
                                    <!-- Registration/Action Button -->
                                    <a 
                                        v-if="selectedEvent?.buttonUrl"
                                        :href="selectedEvent.buttonUrl"
                                        target="_blank"
                                        class="inline-flex justify-center items-center px-4 py-2 bg-accent-500 text-white font-semibold rounded-lg hover:bg-accent-600 transition-colors"
                                    >
                                        {{ selectedEvent.buttonText || 'Register Here' }}
                                    </a>
                                    
                                    <!-- Admin actions for lunch menu -->
                                    <template v-if="selectedEvent?.type === 'lunch' && isAdmin && selectedEvent?.menuId">
                                        <Link 
                                            :href="`/portal/lunch/${selectedEvent.menuId}/edit`"
                                            class="inline-flex justify-center items-center gap-2 px-4 py-2 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-colors"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                            Edit Menu
                                        </Link>
                                    </template>
                                    
                                    <button
                                        type="button"
                                        class="inline-flex justify-center items-center px-4 py-2 bg-white text-slate-700 font-medium rounded-lg border border-slate-300 hover:bg-slate-50 transition-colors"
                                        @click="closeEventModal"
                                    >
                                        Close
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </PortalLayout>
</template>
