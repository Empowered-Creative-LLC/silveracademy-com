<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { 
    SparklesIcon, 
    HeartIcon, 
    ShieldCheckIcon,
    HomeIcon,
    DocumentTextIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Life & Legacy - The Silver Academy" />

    <MarketingLayout>
        <!-- Hero Section - Image only (Edit 46: header formatting matches other pages) -->
        <section class="relative h-[400px] overflow-hidden">
            <img 
                src="https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=1920&q=80" 
                alt="Generations" 
                class="w-full h-full object-cover object-center"
            />
        </section>
        <!-- Hero Title - Below image -->
        <section class="py-8 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
                <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl text-slate-800 leading-tight tracking-tight">
                    Life & Legacy
                </h1>
                <p class="mt-6 text-lg text-slate-600 max-w-2xl mx-auto">
                    Build a Lasting Legacy for Jewish Education
                </p>
            </div>
        </section>

        <!-- Quotes -->
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center space-y-8">
                <blockquote class="text-xl md:text-2xl font-serif italic text-slate-700">
                    “Every individual is a pillar on which the future of Judaism rests.”
                    <footer class="text-base font-sans font-medium text-brand-600 mt-2">– Abraham Joshua Heschel</footer>
                </blockquote>
                <blockquote class="text-xl md:text-2xl font-serif italic text-slate-700">
                    “By the breath of children, God sustains the world.”
                    <footer class="text-base font-sans font-medium text-brand-600 mt-2">– Talmud Bavli, Shabbat 119b</footer>
                </blockquote>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-16 bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-serif font-bold text-slate-900 mb-6">Our Legacy Vision</h2>
                        <p class="text-lg text-slate-700 mb-6 leading-relaxed">
                            Our Life & Legacy donors are individuals who dream of a strong Jewish community for generations to come. By creating a legacy gift, you ensure that students at The Silver Academy continue to receive a values-driven education rooted in Jewish tradition, ethics, and culture.
                        </p>
                        <p class="text-lg text-slate-700 mb-6">
                            When you make a legacy gift, you become a pillar for the future of Jewish life, supporting students and the broader community for years to come.
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-xl font-bold text-slate-900 mb-6">Your Support Instills:</h3>
                        <ul class="space-y-6">
                            <li class="flex items-start">
                                <HeartIcon class="w-6 h-6 text-brand-600 mr-4 flex-shrink-0" />
                                <span class="text-slate-700">A love of Judaism and connection to heritage</span>
                            </li>
                            <li class="flex items-start">
                                <ShieldCheckIcon class="w-6 h-6 text-brand-600 mr-4 flex-shrink-0" />
                                <span class="text-slate-700">Ethical values that guide choices and interactions</span>
                            </li>
                            <li class="flex items-start">
                                <SparklesIcon class="w-6 h-6 text-brand-600 mr-4 flex-shrink-0" />
                                <span class="text-slate-700">Confidence to positively impact their communities</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- How to Leave a Legacy -->
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-serif font-bold text-slate-900 mb-8">How to Leave Your Legacy</h2>
                <p class="text-lg text-slate-600 mb-8">Consider remembering The Silver Academy in your:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="p-6 bg-slate-50 rounded-xl">
                        <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <DocumentTextIcon class="w-8 h-8 text-brand-600" />
                        </div>
                        <h3 class="font-bold text-slate-900">Will or Trust</h3>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-xl">
                        <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <ChartBarIcon class="w-8 h-8 text-brand-600" />
                        </div>
                        <h3 class="font-bold text-slate-900">Retirement Account</h3>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-xl">
                        <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <ShieldCheckIcon class="w-8 h-8 text-brand-600" />
                        </div>
                        <h3 class="font-bold text-slate-900">Life Insurance Policy</h3>
                    </div>
                </div>

                <div class="bg-brand-50 border border-brand-100 p-8 rounded-xl mb-12">
                    <h3 class="font-bold text-xl text-brand-900 mb-4">For Guidance</h3>
                    <p class="text-slate-700 mb-4">
                        Please contact <strong>Gila Ogle, Head of School</strong>:<br>
                        <a href="tel:717-769-5870" class="text-brand-600 font-medium hover:underline">717-769-5870</a> | 
                        <a href="mailto:gogle@silveracademypa.org" class="text-brand-600 font-medium hover:underline">gogle@silveracademypa.org</a>
                    </p>
                    <p class="text-sm text-slate-500">
                        The Jewish Community Foundation of Central PA sponsors and supports our community-wide legacy initiative, helping donors create lasting impact.
                    </p>
                </div>
            </div>
        </section>

        <!-- Make a Gift -->
        <section class="py-16 bg-slate-50 border-t border-slate-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-serif font-bold text-slate-900">Make a Gift Today</h2>
                    <p class="mt-4 text-lg text-slate-600">Your legacy gift is an investment in the Jewish future.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <!-- Online -->
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 text-center">
                        <h3 class="font-bold text-xl text-slate-900 mb-4">Give Online</h3>
                        <p class="text-slate-600 mb-6">Accepts Visa, MasterCard, AmEx, and Discover.</p>
                        <a 
                            href="https://wl.donorperfect.net/weblink/WebLink.aspx?name=E341196&id=36" 
                            target="_blank"
                            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 transition-colors"
                        >
                            Donate Now
                        </a>
                        <p class="mt-4 text-sm text-slate-500 italic">
                            For honor or memorial gifts, check the "I would like to dedicate this donation" box to include special instructions.
                        </p>
                    </div>

                    <!-- By Mail -->
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 text-center flex flex-col justify-center">
                        <h3 class="font-bold text-xl text-slate-900 mb-4">By Mail</h3>
                        <div class="text-slate-600 space-y-1">
                            <p class="font-medium text-slate-900">The Silver Academy</p>
                            <p>PO Box 60097</p>
                            <p>Harrisburg, PA 17106</p>
                        </div>
                        <div class="mt-6 flex justify-center">
                            <HomeIcon class="w-12 h-12 text-slate-300" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
