<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const portalLink = computed(() => isAuthenticated.value ? '/portal/dashboard' : '/login');

const mobileMenuOpen = ref(false);

const utilityNav = computed(() => [
    { name: 'Schedule a Visit', href: 'https://calendar.app.google/Y5NrAjA9RooWgwZ98', external: true },
    { name: 'Apply', href: 'https://app.simpletuitionsolutions.org/register?sc=21760', external: true },
    { name: 'Family Portal', href: portalLink.value },
    { name: 'Give', href: 'https://wl.donorperfect.net/weblink/WebLink.aspx?name=E341196&id=36' },
]);

    const mainNav = [
        { name: 'Welcome', href: '/' },
        {
            name: 'About',
            href: '/about',
            children: [
                { name: 'Who We Are', href: '/about#our-story' },
                { name: 'Leadership, Staff & Faculty', href: '/staff' },
                { name: 'Why The Silver Academy', href: '/why-silver-academy' },
                { name: 'Our Community', href: '/our-community' },
                { name: 'Silver At A Glance', href: '#' },
            ]
        },
        {
            name: 'Admissions',
            href: '/admissions',
            children: [
                { name: 'How to Apply', href: '/admissions#process' },
                { name: 'Why a Jewish Day School?', href: '/admissions#why-jewish-day-school' },
                { name: 'Testimonials & Alumni', href: '/admissions#testimonials' },
            ]
        },
        { 
            name: 'Programs', 
            href: '/programs',
            children: [
                { name: 'Ganeinu (Preschool)', href: '/programs/ganeinu' },
                { name: 'Kindergarten', href: '/programs/kindergarten' },
                { name: 'Lower School', href: '/programs/lower-school' },
                { name: 'Upper School', href: '/programs/upper-school' },
                { name: 'After School', href: '/programs/after-school' },
                { name: 'Parent Circle', href: '/programs/parent-circle' },
            ]
        },
        { name: 'News & Events', href: '/news-events' },
        { 
            name: 'Get Involved', 
            href: '/get-involved',
            children: [
                { name: 'Make a Donation', href: '/get-involved/donate' },
                { name: 'EITC - Corporate', href: '/get-involved/eitc-corporate' },
                { name: 'EITC - Individual', href: '/get-involved/eitc-individual' },
                { name: 'Life and Legacy', href: '/get-involved/life-and-legacy' },
                { name: 'Fundraisers', href: '/get-involved/fundraisers' },
            ]
        },
    ];

const footerNav = [
    { name: 'Why Choose Us', href: '/about' },
    { name: 'Admissions & Tours', href: '#' },
    { name: 'Student Life & Programs', href: '/programs' },
    { name: 'Tuition & Scholarships', href: '#' },
    { name: 'News & Events', href: '/news-events' },
];

const socialLinks = [
    { 
        name: 'Facebook', 
        href: 'https://www.facebook.com/silveracademy.yeshiva',
        icon: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'
    },
    { 
        name: 'Instagram', 
        href: '#',
        icon: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'
    },
];
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Utility Navigation Bar -->
        <div class="bg-brand-500 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-end h-8 text-xs">
                    <nav class="hidden md:flex items-center space-x-4">
                        <template v-for="item in utilityNav" :key="item.name">
                            <a
                                v-if="item.external"
                                :href="item.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="uppercase tracking-wide hover:text-accent-300 transition-colors duration-200"
                            >
                                {{ item.name }}
                            </a>
                            <Link
                                v-else
                                :href="item.href"
                                class="uppercase tracking-wide hover:text-accent-300 transition-colors duration-200"
                            >
                                {{ item.name }}
                            </Link>
                        </template>
                        <!-- Social Icons -->
                        <div class="flex items-center space-x-2 ml-2 pl-2 border-l border-brand-500">
                            <a
                                v-for="social in socialLinks"
                                :key="social.name"
                                :href="social.href"
                                class="hover:text-accent-300 transition-colors duration-200"
                                :aria-label="social.name"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path :d="social.icon" />
                                </svg>
                            </a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Global">
                <div class="flex items-center justify-between py-4">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <Link href="/" class="flex items-center">
                            <img 
                                src="/img/logo/silveracademylogo.png" 
                                alt="The Silver Academy - Central PA's Jewish Day School" 
                                class="h-12 sm:h-14 w-auto"
                            />
                        </Link>
                    </div>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex lg:items-center lg:gap-x-1">
                        <template v-for="item in mainNav" :key="item.name">
                            <!-- Standard Link -->
                            <Link
                                v-if="!item.children"
                                :href="item.href"
                                class="px-3 py-2 text-sm font-medium uppercase tracking-wide text-slate-700 hover:text-brand-600 transition-colors duration-200"
                            >
                                {{ item.name }}
                            </Link>

                            <!-- Dropdown Menu -->
                            <div v-else class="relative group">
                                <Link
                                    :href="item.href"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium uppercase tracking-wide text-slate-700 hover:text-brand-600 transition-colors duration-200"
                                >
                                    {{ item.name }}
                                    <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </Link>

                                <!-- Dropdown Content -->
                                <div class="absolute left-0 mt-0 w-56 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                    <div class="rounded-md shadow-lg ring-1 ring-black ring-opacity-5 bg-white mt-2 py-1">
                                        <Link
                                            v-for="child in item.children"
                                            :key="child.name"
                                            :href="child.href"
                                            class="block px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-600 uppercase tracking-wide"
                                        >
                                            {{ child.name }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Contact Button -->
                    <div class="hidden lg:flex lg:items-center">
                        <Link
                            href="/contact"
                            class="rounded bg-accent-500 px-6 py-2.5 text-sm font-semibold uppercase tracking-wide text-white shadow-sm hover:bg-accent-600 transition-all duration-200"
                        >
                            Contact
                        </Link>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex lg:hidden">
                        <button
                            type="button"
                            class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-slate-700"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div v-if="mobileMenuOpen" class="lg:hidden pb-4">
                    <div class="space-y-1">
                        <template v-for="item in mainNav" :key="item.name">
                            <Link
                                v-if="!item.children"
                                :href="item.href"
                                class="block px-3 py-2 text-base font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-md"
                                @click="mobileMenuOpen = false"
                            >
                                {{ item.name }}
                            </Link>
                            <div v-else>
                                <Link
                                    :href="item.href"
                                    class="block px-3 py-2 text-base font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-md"
                                    @click="mobileMenuOpen = false"
                                >
                                    {{ item.name }}
                                </Link>
                                <div class="pl-4 space-y-1">
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.name"
                                        :href="child.href"
                                        class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-brand-50 hover:text-brand-600 rounded-md"
                                        @click="mobileMenuOpen = false"
                                    >
                                        {{ child.name }}
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <div class="space-y-1">
                            <template v-for="item in utilityNav" :key="item.name">
                                <a
                                    v-if="item.external"
                                    :href="item.href"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="block px-3 py-2 text-sm text-slate-500 hover:bg-brand-50 hover:text-brand-600 rounded-md"
                                    @click="mobileMenuOpen = false"
                                >
                                    {{ item.name }}
                                </a>
                                <Link
                                    v-else
                                    :href="item.href"
                                    class="block px-3 py-2 text-sm text-slate-500 hover:bg-brand-50 hover:text-brand-600 rounded-md"
                                    @click="mobileMenuOpen = false"
                                >
                                    {{ item.name }}
                                </Link>
                            </template>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Link
                            href="/contact"
                            class="block w-full text-center rounded bg-accent-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-sm hover:bg-accent-600"
                            @click="mobileMenuOpen = false"
                        >
                            Contact
                        </Link>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer>
            <!-- Main Footer Section -->
            <div class="bg-slate-100 border-t border-slate-200">
                <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <!-- Logo -->
                        <div class="lg:col-span-2 flex justify-center lg:justify-start">
                            <img 
                                src="/img/logo/TSA-Logo-Round.png" 
                                alt="The Silver Academy Jewish Day School" 
                                class="h-[159px] w-[159px] object-contain"
                            />
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="lg:col-span-6 flex flex-col justify-center py-6">
                            <div class="text-left">
                                <p class="text-sm font-semibold uppercase tracking-wider text-slate-700">
                                    A Values-Driven Preschool thru 8th Grade Jewish Day School
                                </p>
                                <p class="mt-3 text-sm text-slate-600">
                                    Physical: 2637 N. Front St, Harrisburg, PA 17110
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Mailing: PO Box 60097, Harrisburg, PA 17106
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    <a href="mailto:info@silveracademypa.org" class="text-brand-600 hover:text-brand-700 transition-colors">info@silveracademypa.org</a>
                                    <span class="mx-2">|</span>
                                    <a href="tel:717-769-5870" class="text-brand-600 hover:text-brand-700 transition-colors">717-769-5870</a>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Footer Navigation -->
                        <div class="lg:col-span-4">
                            <ul class="flex flex-col items-center lg:items-end space-y-2">
                                <li v-for="item in footerNav" :key="item.name">
                                    <Link 
                                        :href="item.href" 
                                        class="text-sm uppercase tracking-wide text-slate-600 hover:text-brand-600 transition-colors"
                                    >
                                        {{ item.name }}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar: vertical stack on mobile, horizontal on md+ -->
            <div class="bg-brand-500">
                <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 md:gap-4">
                        <!-- Left: Social + Legal (stacked vertically on mobile) -->
                        <div class="flex flex-col items-center gap-3 md:flex-row md:items-center md:gap-4">
                            <div class="flex items-center gap-3">
                                <a
                                    v-for="social in socialLinks"
                                    :key="social.name"
                                    :href="social.href"
                                    class="text-white hover:text-accent-300 transition-colors duration-200"
                                    :aria-label="social.name"
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path :d="social.icon" />
                                    </svg>
                                </a>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 text-xs text-white uppercase tracking-wide text-center">
                                <a href="#" class="hover:text-accent-300 transition-colors">Privacy Policy</a>
                                <span class="hidden sm:inline">|</span>
                                <a href="#" class="hover:text-accent-300 transition-colors">Accessibility</a>
                            </div>
                        </div>
                        
                        <!-- Right: Portal Links + Contact (stacked vertically on mobile) -->
                        <div class="flex flex-col items-center gap-3 md:flex-row md:items-center md:gap-6">
                            <Link 
                                :href="portalLink" 
                                class="text-sm uppercase tracking-wide text-white hover:text-accent-300 transition-colors font-medium"
                            >
                                Family Portal
                            </Link>
                            <Link 
                                :href="portalLink" 
                                class="text-sm uppercase tracking-wide text-white hover:text-accent-300 transition-colors font-medium"
                            >
                                Staff Login
                            </Link>
                            <a 
                                href="https://wl.donorperfect.net/weblink/WebLink.aspx?name=E341196&id=36" 
                                class="text-sm uppercase tracking-wide text-white hover:text-accent-300 transition-colors font-medium"
                            >
                                Give a Gift
                            </a>
                            <Link
                                href="/contact"
                                class="rounded bg-accent-500 px-6 py-2 text-sm font-semibold uppercase tracking-wide text-white hover:bg-accent-600 transition-all duration-200"
                            >
                                Contact
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Copyright Bar -->
            <div class="bg-white border-t border-slate-200">
                <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <p class="text-xs text-slate-600">
                            © 2026 The Silver Academy
                        </p>
                        <p class="text-xs text-slate-600">
                            Powered by <a href="https://www.empoweredcreative.co" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:text-brand-700 transition-colors font-medium">Empowered Creative</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
