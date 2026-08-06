<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { 
    BuildingOfficeIcon, 
    CalculatorIcon, 
    UserGroupIcon, 
    CheckCircleIcon,
    ArrowDownTrayIcon,
    PlayCircleIcon,
} from '@heroicons/vue/24/outline';

const donors = [
    'Abel Personnel', 'BHC Holdings LLC', 'Bravo Group, Inc.', 'Capital Advantage Insurance Company',
    'Daisy Data Displays, Inc', 'Fulton Bank Corp.', 'Information Network Associates, Inc.', 'M&T Bank',
    'Norman Gras Kosher Food Consultant, LLC', 'Oak Tree Development Group', 'Orrstown Bank', 'PNC Bank N.A.',
    'PNC Financial Services Group Inc.', 'Rubin Industrial Co., Inc.', 'UGI Energy Services',
    'UHS of Pennsylvania, Inc.', 'UPMC Health Benefits', 'W & L Sales Co., Inc.',
    'Weis Markets, Inc.', 'York Haven Fabricators'
];
</script>

<template>
    <Head title="Corporate EITC - The Silver Academy" />

    <MarketingLayout>
        <!-- Hero Section - Image only (Edit 40: header formatting matches other pages) -->
        <section class="relative h-[400px] overflow-hidden">
            <img 
                src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1920&q=80" 
                alt="Corporate EITC" 
                class="w-full h-full object-cover object-center"
            />
        </section>
        <!-- Hero Title - Below image -->
        <section class="py-8 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
                <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl text-slate-800 leading-tight tracking-tight">
                    Educational Improvement Tax Credit (EITC)
                </h1>
                <p class="mt-6 text-lg text-slate-600 max-w-2xl mx-auto">
                    A Win-Win for Businesses and Students
                </p>
            </div>
        </section>

        <!-- Intro (Edit 41: center align) -->
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-lg text-slate-700 leading-relaxed mb-8">
                    The Educational Improvement Tax Credit (EITC) program allows Pennsylvania businesses to support Jewish education at The Silver Academy while receiving substantial tax benefits. Your contribution helps provide need-based scholarships to low- and middle-income families, ensuring that students can access a values-driven, high-quality education.
                </p>
            </div>
        </section>

        <!-- How It Works (Edit 41: center align heading) -->
        <section class="py-16 bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-serif font-bold text-slate-800 mb-12 text-center">How It Works</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <p class="text-slate-600 mb-6">
                            Businesses fund scholarships for students at The Silver Academy or the Brenner Family Early Learning Center. In return, businesses receive tax credits:
                        </p>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <CheckCircleIcon class="h-6 w-6 text-brand-600" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-slate-900">75% Credit</h3>
                                    <p class="text-slate-600">For a one-year commitment</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <CheckCircleIcon class="h-6 w-6 text-brand-600" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-slate-900">90% Credit</h3>
                                    <p class="text-slate-600">For a two-year commitment</p>
                                </div>
                            </div>
                        </div>
                        <p class="mt-6 text-slate-600 italic">
                            Donations can cost as little as 10 cents on the dollar after tax benefits, and additional federal charitable deductions may further reduce the cost.
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-xl font-bold text-slate-900 mb-4 flex items-center">
                            <CalculatorIcon class="w-6 h-6 text-brand-600 mr-2" />
                            Why Support EITC?
                        </h3>
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <span class="bg-brand-100 text-brand-700 rounded-full p-1 mr-3 mt-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <div>
                                    <strong class="text-slate-900">Empowers Families:</strong>
                                    <span class="text-slate-600 block">Scholarships make a Silver Academy education accessible to students who otherwise may not afford it.</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-brand-100 text-brand-700 rounded-full p-1 mr-3 mt-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <div>
                                    <strong class="text-slate-900">Strengthens Community:</strong>
                                    <span class="text-slate-600 block">Jewish day schools cultivate the next generation of community-minded, ethical individuals.</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-brand-100 text-brand-700 rounded-full p-1 mr-3 mt-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <div>
                                    <strong class="text-slate-900">Visible Recognition:</strong>
                                    <span class="text-slate-600 block">Businesses gain public acknowledgment and appreciation for supporting local education.</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Participation (Edit 41: center align) -->
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-serif font-bold text-slate-800 mb-6">Who Can Participate?</h2>
                <p class="text-slate-600 text-center mb-8">
                    The EITC program is available to businesses registered in Pennsylvania. Applications must be submitted online on or after July 1 for new applicants. Renewal applications open May 15.
                </p>
                
                <div class="bg-brand-50 border-l-4 border-brand-500 p-6 mb-12">
                    <p class="font-medium text-brand-900">
                        Important: Designate the Jewish Federation of Greater Harrisburg – SO (Scholarship Organization) as the recipient of your contribution.
                    </p>
                </div>

                <div class="space-y-8">
                    <h3 class="text-2xl font-bold text-slate-800 mb-4">Steps to Apply</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                        <div class="flex items-start p-4 bg-slate-50 rounded-lg">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 text-white font-bold mr-4">1</span>
                            <div>
                                <h4 class="font-bold text-slate-900">Confirm Eligibility</h4>
                                <p class="text-sm text-slate-600">Visit the DCED EITC page to check requirements.</p>
                            </div>
                        </div>
                        <div class="flex items-start p-4 bg-slate-50 rounded-lg">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 text-white font-bold mr-4">2</span>
                            <div>
                                <h4 class="font-bold text-slate-900">Submit Application</h4>
                                <p class="text-sm text-slate-600">Online after July 1 (or May 15 for renewals).</p>
                            </div>
                        </div>
                        <div class="flex items-start p-4 bg-slate-50 rounded-lg">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 text-white font-bold mr-4">3</span>
                            <div>
                                <h4 class="font-bold text-slate-900">Designate Contribution</h4>
                                <p class="text-sm text-slate-600">To: Jewish Federation of Greater Harrisburg – SO</p>
                            </div>
                        </div>
                        <div class="flex items-start p-4 bg-slate-50 rounded-lg">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 text-white font-bold mr-4">4</span>
                            <div>
                                <h4 class="font-bold text-slate-900">Fund Scholarships</h4>
                                <p class="text-sm text-slate-600">Direct impact for Silver Academy students.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 transition-colors">
                        <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
                        Download Fact Sheet
                    </a>
                    <a href="#" class="inline-flex items-center justify-center px-6 py-3 border border-brand-200 text-base font-medium rounded-md text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors">
                        <PlayCircleIcon class="w-5 h-5 mr-2" />
                        Watch Videos
                    </a>
                </div>
            </div>
        </section>

        <!-- Donors -->
        <section class="py-16 bg-slate-50 border-t border-slate-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-serif font-bold text-slate-900 mb-8">Meet Some of Our Generous EITC Donors</h2>
                <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 text-slate-600 leading-relaxed max-w-5xl mx-auto">
                    <template v-for="(donor, index) in donors" :key="donor">
                        <span class="font-medium">{{ donor }}</span>
                        <span v-if="index < donors.length - 1" class="text-brand-300">|</span>
                    </template>
                </div>
                <p class="mt-8 text-lg font-medium text-brand-700">
                    Your business can join these generous supporters and make a profound difference in the lives of children.
                </p>
            </div>
        </section>

        <!-- Contact -->
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl font-bold text-slate-900 mb-8">Questions?</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="p-6 bg-slate-50 rounded-xl">
                        <h3 class="font-serif font-bold text-lg mb-2">Contact Us</h3>
                        <p class="font-medium text-slate-900">Gila Ogle, Head of School</p>
                        <p class="text-brand-600"><a href="tel:717-769-5870">717-769-5870</a></p>
                        <p class="text-brand-600"><a href="mailto:gogle@silveracademypa.org">gogle@silveracademypa.org</a></p>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-xl">
                        <h3 class="font-serif font-bold text-lg mb-2">Contact DCED Directly</h3>
                        <p class="text-slate-600">PA Department of Community & Economic Development</p>
                        <p class="text-brand-600"><a href="tel:717-787-7120">717-787-7120</a></p>
                        <p class="text-brand-600"><a href="mailto:RA-EITC@state.pa.us">RA-EITC@state.pa.us</a></p>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
