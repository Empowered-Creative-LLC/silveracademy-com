<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { 
    HeartIcon, 
    BookOpenIcon, 
    UserGroupIcon,
    GlobeAmericasIcon 
} from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Make a Donation - The Silver Academy" />

    <MarketingLayout>
        <!-- Hero Section - Image only (Edit 38) -->
        <section class="relative h-[400px] overflow-hidden">
            <img 
                src="/img/graphics/programs/lower school/web-silveracademy2025_Silver Academy-718.jpg" 
                alt="Children learning" 
                class="w-full h-full object-cover"
                style="object-position: center 30%;"
            />
        </section>
        <!-- Hero Title - Below image (Edit 38) -->
        <section class="py-8 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
                <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl text-slate-800 leading-tight tracking-tight">
                    Make a Donation
                </h1>
                <p class="mt-6 text-lg text-slate-600 max-w-2xl mx-auto">
                    Support Tomorrow’s Leaders
                </p>
            </div>
        </section>

        <!-- Mission Intro -->
        <section class="py-20 bg-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-lg text-slate-700 leading-relaxed mb-8">
                    Since Rabbi David L. Silver founded The Silver Academy in 1944, our School has been committed to nurturing students who live with integrity, compassion, and purpose. Our teachers guide students to strive for excellence in a safe, warm, and values-driven environment, helping them explore their heritage and engage in meaningful cultural experiences.
                </p>
            </div>
        </section>

        <!-- Core Values Grid -->
        <section class="py-16 bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-2xl font-serif font-bold text-slate-900">We instill in our students the belief that:</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 text-center hover:shadow-md transition-shadow">
                        <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <HeartIcon class="w-8 h-8 text-brand-600" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Make a Difference</h3>
                        <p class="text-slate-600">Every person can make a difference in their community.</p>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 text-center hover:shadow-md transition-shadow">
                        <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <UserGroupIcon class="w-8 h-8 text-brand-600" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Responsible Citizenship</h3>
                        <p class="text-slate-600">Every person should be a responsible and engaged citizen.</p>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 text-center hover:shadow-md transition-shadow">
                        <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <GlobeAmericasIcon class="w-8 h-8 text-brand-600" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Improve the World</h3>
                        <p class="text-slate-600">Everyone should seek ways to improve the world around them.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Impact Section -->
        <section class="py-20 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-serif font-bold text-slate-900 mb-6">Your Impact</h2>
                        <p class="text-lg text-slate-700 mb-6 leading-relaxed">
                            Your gift ensures that no child is ever turned away because of financial hardship. With scholarship requests at an all-time high, your support directly impacts students’ lives, giving them the opportunity to thrive academically, socially, and spiritually.
                        </p>
                        <p class="text-lg text-slate-700 leading-relaxed">
                            When you support The Silver Academy, you give a gift that lasts a lifetime. Your donation strengthens our programs, empowers our students, and benefits the entire Greater Harrisburg community.
                        </p>
                    </div>
                    <div class="bg-brand-50 p-8 rounded-2xl border border-brand-100 text-center">
                        <h3 class="text-2xl font-bold text-brand-900 mb-6">Give Today</h3>
                        <p class="text-brand-800 mb-8">
                            Join us in building a bright future for our students and community.
                        </p>
                        <a 
                            href="https://causematch.com/silveracademy"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold rounded-lg text-white bg-accent-500 hover:bg-accent-600 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all"
                        >
                            Donate Now
                            <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>

