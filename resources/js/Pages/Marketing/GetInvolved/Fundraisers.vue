<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { 
    ShoppingBagIcon, 
    TicketIcon, 
    StarIcon, 
    GiftIcon,
    CameraIcon
} from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Fundraisers - The Silver Academy" />

    <MarketingLayout>
        <!-- Hero Section - Image only (Edit 47: header formatting matches other pages) -->
        <section class="relative h-[400px] overflow-hidden">
            <img 
                src="https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=1920&q=80" 
                alt="Fundraisers" 
                class="w-full h-full object-cover object-center"
            />
        </section>
        <!-- Hero Title - Below image -->
        <section class="py-8 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
                <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl text-slate-800 leading-tight tracking-tight">
                    Fundraisers
                </h1>
                <p class="mt-6 text-lg text-slate-600 max-w-2xl mx-auto">
                    Supporting our school is easy—shop, scan, and collect!
                </p>
            </div>
        </section>

        <!-- Intro -->
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-lg text-slate-700 leading-relaxed">
                    Explore these programs to make an impact on our students’ education while engaging in everyday activities. Whether it's grocery shopping or buying gift cards, your daily routine can help our school thrive.
                </p>
            </div>
        </section>

        <!-- Programs Grid -->
        <section class="py-16 bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Giant -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-8">
                            <div class="flex items-center mb-6">
                                <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mr-4">
                                    <ShoppingBagIcon class="w-8 h-8 text-brand-600" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900">Giant Dollar for Dollar & A+</h3>
                            </div>
                            <p class="text-slate-600 mb-6">
                                When you shop at Giant Food Stores, a portion of your purchases can support The Silver Academy. Register your BonusCard to earn contributions directly for our programs.
                            </p>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-bold text-sm text-slate-900 uppercase tracking-wide mb-3">How to Participate</h4>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Register your BonusCard at Giant A+ Rewards.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Select The Silver Academy as your designated school.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Shop as usual—purchases automatically contribute.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Box Tops -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-8">
                            <div class="flex items-center mb-6">
                                <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mr-4">
                                    <TicketIcon class="w-8 h-8 text-brand-600" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900">Box Tops for Education</h3>
                            </div>
                            <p class="text-slate-600 mb-6">
                                Turn everyday purchases into cash! Clip Box Tops or scan your receipt using the app. Each Box Top contributes to school programs, supplies, and enrichment.
                            </p>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-bold text-sm text-slate-900 uppercase tracking-wide mb-3">How to Participate</h4>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Download the Box Tops for Education app.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Scan receipts or clip Box Tops from products.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Track your impact in the app.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Scrip -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-8">
                            <div class="flex items-center mb-6">
                                <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mr-4">
                                    <GiftIcon class="w-8 h-8 text-brand-600" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900">Shop with Scrip</h3>
                            </div>
                            <p class="text-slate-600 mb-6">
                                Support the school while buying gift cards for everyday shopping. A portion of each gift card purchase goes directly to the school at no extra cost to you.
                            </p>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-bold text-sm text-slate-900 uppercase tracking-wide mb-3">How to Participate</h4>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Visit Shop with Scrip or download RaiseRight app.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Register and select The Silver Academy.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Purchase gift cards and earn rebates.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Shoparoo -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-8">
                            <div class="flex items-center mb-6">
                                <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mr-4">
                                    <CameraIcon class="w-8 h-8 text-brand-600" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900">Shoparoo</h3>
                            </div>
                            <p class="text-slate-600 mb-6">
                                Easily raise funds by simply scanning your grocery receipts with the Shoparoo app. Perfect for busy families who want to give back effortlessly.
                            </p>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-bold text-sm text-slate-900 uppercase tracking-wide mb-3">How to Participate</h4>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Download the Shoparoo app.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Join The Silver Academy’s team.
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2 text-brand-500">•</span>
                                        Scan receipts and watch contributions grow!
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section class="py-16 bg-white border-t border-slate-200">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl font-bold text-slate-900 mb-4">Questions or Need Help?</h2>
                <p class="text-slate-600 mb-8">For assistance with any of our fundraising programs, please contact:</p>
                
                <div class="inline-block bg-slate-50 p-8 rounded-xl">
                    <p class="font-bold text-lg text-slate-900">Office of Development</p>
                    <p class="text-brand-600 mb-2"><a href="mailto:development@silveracademypa.org">development@silveracademypa.org</a></p>
                    <p class="text-brand-600"><a href="tel:717-769-5870">717-769-5870</a></p>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
