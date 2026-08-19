<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, BellIcon, XMarkIcon, ArrowRightOnRectangleIcon, EyeIcon } from '@heroicons/vue/24/outline'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'

const page = usePage()
const user = page.props.auth?.user

const previewRoles = ['admin', 'teacher', 'parent']

// Read preview role from localStorage
const storedPreviewRole = ref('admin')

const updatePreviewRole = () => {
    if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('portal_preview_role')
        if (stored && previewRoles.includes(stored)) {
            storedPreviewRole.value = stored
        }
    }
}

const handlePreviewRoleChanged = (event) => {
    if (event.detail && previewRoles.includes(event.detail)) {
        storedPreviewRole.value = event.detail
    }
}

const showSessionExpiredBanner = ref(false)

onMounted(() => {
    updatePreviewRole()
    if (sessionStorage.getItem('session_expired_message')) {
        showSessionExpiredBanner.value = true
        sessionStorage.removeItem('session_expired_message')
    }
    // Listen for storage changes (in case another tab changes it)
    window.addEventListener('storage', updatePreviewRole)
    // Listen for preview role changes from Dashboard/Calendar
    window.addEventListener('preview-role-changed', handlePreviewRoleChanged)
    // Also update on Inertia navigation
    router.on('navigate', updatePreviewRole)
})

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('storage', updatePreviewRole)
        window.removeEventListener('preview-role-changed', handlePreviewRoleChanged)
    }
})

const isSuperAdmin = computed(() => {
    return user?.role === 'super_admin'
})

const isActualAdmin = computed(() => {
    return user?.role === 'admin' || user?.role === 'super_admin'
})

const isActualTeacher = computed(() => {
    return user?.role === 'teacher'
})

const hasLinkedStudents = computed(() => Boolean(user?.has_linked_students))
const sandboxEnabled = computed(() => Boolean(page.props.portal?.sandbox_enabled))

const canSwitchToParentView = computed(() => {
    return isSuperAdmin.value || ((isActualAdmin.value || isActualTeacher.value) && hasLinkedStudents.value)
})

const previewRoleLabels = {
    admin: 'Admin View',
    teacher: 'Staff View',
    parent: 'Parent View',
}

const previewRoleLabel = computed(() => previewRoleLabels[storedPreviewRole.value] || 'Admin View')

const setPreviewRole = (role) => {
    if (!previewRoles.includes(role)) {
        return
    }
    storedPreviewRole.value = role
    if (typeof window !== 'undefined') {
        localStorage.setItem('portal_preview_role', role)
        window.dispatchEvent(new CustomEvent('preview-role-changed', { detail: role }))
    }
}

// Determine effective role for navigation (respects preview mode)
const effectiveRole = computed(() => {
    if (isSuperAdmin.value && storedPreviewRole.value) {
        return storedPreviewRole.value
    }
    if (canSwitchToParentView.value && storedPreviewRole.value === 'parent') {
        return 'parent'
    }
    if (user?.role === 'super_admin' || user?.role === 'admin') return 'admin'
    if (user?.role === 'teacher') return 'teacher'
    return 'parent'
})

const navigation = computed(() => {
    const items = [
        { name: 'Dashboard', href: '/portal/dashboard' },
        { name: 'Calendar', href: '/portal/calendar' },
    ]

    if (effectiveRole.value === 'admin') {
        // Admin sees full management
        items.push({ name: 'News & Events', href: '/portal/posts' })
        items.push({ name: 'Parents', href: '/portal/admin/parents' })
        items.push({ name: 'Staff', href: '/portal/admin/staff' })
        items.push({ name: 'Staff Directory', href: '/portal/admin/staff-directory' })
        items.push({ name: 'Grades', href: '/portal/admin/grades' })
    } else if (effectiveRole.value === 'teacher') {
        // Staff should land on the portal Events Calendar experience.
        items.push({ name: 'News & Events', href: '/portal/calendar?view=events' })
    }
    // Parents just see Dashboard, Calendar, Settings

    items.push({ name: 'Settings', href: '/portal/settings' })

    return items
})

const accountNavigation = computed(() => {
    const items = []

    if (sandboxEnabled.value && (isSuperAdmin.value || isActualAdmin.value)) {
        items.push({ name: 'Test Lab', href: '/portal/sandbox' })
    }
    
    // For super admins in preview mode, pass the preview role to help page
    if (isSuperAdmin.value && storedPreviewRole.value && storedPreviewRole.value !== 'admin') {
        items.push({ name: 'Help', href: `/portal/help?preview=${storedPreviewRole.value}` })
    } else {
        items.push({ name: 'Help', href: '/portal/help' })
    }
    
    return items
})

const logout = () => {
    router.post('/logout', {}, {
        onError: () => {
            window.location.href = '/login'
        },
    })
}

// Get user initials for avatar
const userInitials = computed(() => {
    if (!user?.name) return 'U'
    return user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

// Check if route is active
const isActive = (href) => {
    return page.url === href || page.url.startsWith(href + '/')
}
</script>

<template>
    <div class="min-h-full bg-slate-50">
        <Disclosure as="nav" class="border-b border-slate-200 bg-white" v-slot="{ open }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <Link href="/">
                                <img 
                                    class="h-8 w-auto" 
                                    src="/img/logo/silveracademylogo.png" 
                                    alt="Silver Academy" 
                                />
                            </Link>
                        </div>
                        <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                            <template v-for="item in navigation" :key="item.name">
                                <a 
                                    v-if="item.external"
                                    :href="item.href"
                                    target="_blank"
                                    :class="[
                                        'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700', 
                                        'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium'
                                    ]"
                                >
                                    {{ item.name }}
                                </a>
                                <Link 
                                    v-else
                                    :href="item.href" 
                                    :class="[
                                        isActive(item.href)
                                            ? 'border-brand-600 text-slate-900' 
                                            : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700', 
                                        'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium'
                                    ]"
                                >
                                    {{ item.name }}
                                </Link>
                            </template>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center sm:gap-3">
                        <Menu v-if="isSuperAdmin" as="div" class="relative">
                            <MenuButton class="inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-800 hover:bg-brand-100">
                                <EyeIcon class="size-4" aria-hidden="true" />
                                {{ previewRoleLabel }}
                            </MenuButton>
                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <MenuItems class="absolute right-0 z-20 mt-2 w-44 origin-top-right rounded-md bg-white py-1 shadow-lg outline outline-black/5">
                                    <MenuItem v-for="role in previewRoles" :key="role" v-slot="{ active }">
                                        <button
                                            type="button"
                                            @click="setPreviewRole(role)"
                                            :class="[
                                                active ? 'bg-slate-100' : '',
                                                storedPreviewRole === role ? 'font-semibold text-brand-800' : 'text-slate-700',
                                                'block w-full px-4 py-2 text-left text-sm'
                                            ]"
                                        >
                                            {{ previewRoleLabels[role] }}
                                        </button>
                                    </MenuItem>
                                </MenuItems>
                            </transition>
                        </Menu>

                        <button 
                            type="button" 
                            class="relative rounded-full p-1 text-slate-400 hover:text-slate-500 focus:outline-2 focus:outline-offset-2 focus:outline-brand-600"
                        >
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">View notifications</span>
                            <BellIcon class="size-6" aria-hidden="true" />
                        </button>

                        <button
                            type="button"
                            @click="logout"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        >
                            <ArrowRightOnRectangleIcon class="size-4" aria-hidden="true" />
                            Sign out
                        </button>

                        <!-- Profile dropdown -->
                        <Menu as="div" class="relative ml-3">
                            <MenuButton class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                                <span class="absolute -inset-1.5"></span>
                                <span class="sr-only">Open user menu</span>
                                <img 
                                    v-if="user?.avatar_url" 
                                    :src="user.avatar_url" 
                                    alt="Profile" 
                                    class="size-8 rounded-full object-cover outline -outline-offset-1 outline-black/5"
                                />
                                <div v-else class="size-8 rounded-full bg-brand-600 flex items-center justify-center outline -outline-offset-1 outline-black/5">
                                    <span class="text-sm font-medium text-white">{{ userInitials }}</span>
                                </div>
                            </MenuButton>

                            <transition 
                                enter-active-class="transition ease-out duration-200" 
                                enter-from-class="transform opacity-0 scale-95" 
                                enter-to-class="transform scale-100" 
                                leave-active-class="transition ease-in duration-75" 
                                leave-from-class="transform scale-100" 
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg outline outline-black/5">
                                    <MenuItem v-for="item in accountNavigation" :key="item.name" v-slot="{ active }">
                                        <Link 
                                            :href="item.href" 
                                            :class="[
                                                active ? 'bg-slate-100 outline-hidden' : '', 
                                                'block px-4 py-2 text-sm text-slate-700'
                                            ]"
                                        >
                                            {{ item.name }}
                                        </Link>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            @click="logout"
                                            :class="[
                                                active ? 'bg-red-50 outline-hidden' : '', 
                                                'block w-full text-left px-4 py-2 text-sm font-medium text-red-700'
                                            ]"
                                        >
                                            Sign out
                                        </button>
                                    </MenuItem>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">
                        <!-- Mobile menu button -->
                        <DisclosureButton class="relative inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-2 focus:outline-offset-2 focus:outline-brand-600">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <Bars3Icon v-if="!open" class="block size-6" aria-hidden="true" />
                            <XMarkIcon v-else class="block size-6" aria-hidden="true" />
                        </DisclosureButton>
                    </div>
                </div>
            </div>

            <DisclosurePanel class="sm:hidden border-t border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-4">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <img 
                                v-if="user?.avatar_url" 
                                :src="user.avatar_url" 
                                alt="Profile" 
                                class="size-10 rounded-full object-cover outline -outline-offset-1 outline-black/5"
                            />
                            <div v-else class="size-10 rounded-full bg-brand-600 flex items-center justify-center outline -outline-offset-1 outline-black/5">
                                <span class="text-sm font-medium text-white">{{ userInitials }}</span>
                            </div>
                        </div>
                        <div class="ml-3 min-w-0">
                            <div class="truncate text-base font-semibold text-slate-900">{{ user?.name || 'User' }}</div>
                            <div class="truncate text-sm text-slate-600">{{ user?.email || '' }}</div>
                        </div>
                        <button 
                            type="button" 
                            class="relative ml-auto shrink-0 rounded-full p-1 text-slate-400 hover:text-slate-600 focus:outline-2 focus:outline-offset-2 focus:outline-brand-600"
                        >
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">View notifications</span>
                            <BellIcon class="size-6" aria-hidden="true" />
                        </button>
                    </div>
                    <div v-if="isSuperAdmin" class="mt-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Preview as</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="role in previewRoles"
                                :key="role"
                                type="button"
                                @click="setPreviewRole(role)"
                                :class="[
                                    storedPreviewRole === role
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-slate-100 text-slate-700',
                                    'rounded-full px-3 py-1 text-sm font-medium'
                                ]"
                            >
                                {{ previewRoleLabels[role] }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-1 py-2">
                    <template v-for="item in navigation" :key="item.name">
                        <DisclosureButton v-if="item.external" as="template">
                            <a 
                                :href="item.href"
                                target="_blank"
                                class="block border-l-4 border-transparent py-2 pr-4 pl-3 text-base font-medium text-slate-700 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                            >
                                {{ item.name }}
                            </a>
                        </DisclosureButton>
                        <DisclosureButton v-else as="template">
                            <Link 
                                :href="item.href" 
                                :class="[
                                    isActive(item.href)
                                        ? 'border-brand-600 bg-brand-50 text-brand-800' 
                                        : 'border-transparent text-slate-700 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900', 
                                    'block border-l-4 py-2 pr-4 pl-3 text-base font-medium'
                                ]"
                            >
                                {{ item.name }}
                            </Link>
                        </DisclosureButton>
                    </template>
                </div>

                <div class="space-y-1 border-t border-slate-200 py-2">
                    <DisclosureButton 
                        v-for="item in accountNavigation" 
                        :key="item.name" 
                        as="template"
                    >
                        <Link 
                            :href="item.href" 
                            class="block px-4 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900"
                        >
                            {{ item.name }}
                        </Link>
                    </DisclosureButton>
                    <DisclosureButton as="template">
                        <button
                            @click="logout"
                            class="block w-full text-left px-4 py-2 text-base font-medium text-red-700 hover:bg-red-50"
                        >
                            Sign out
                        </button>
                    </DisclosureButton>
                </div>
            </DisclosurePanel>
        </Disclosure>

        <div class="py-10 bg-white min-h-screen">
            <header v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-serif font-bold tracking-tight text-slate-900">
                        <slot name="header" />
                    </h1>
                </div>
            </header>
            <main>
                <div v-if="showSessionExpiredBanner" class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                    <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                        Your session expired. Please try your action again.
                    </div>
                </div>
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
