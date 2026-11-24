<script setup>
import { Head } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';

defineProps({
    staff: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Leadership, Staff & Faculty - The Silver Academy" />

    <MarketingLayout>
        <!-- Hero Section: taller to fit teacher and students; positioned to minimize head space -->
        <section class="relative h-[500px] overflow-hidden">
            <div class="absolute inset-0">
                <img
                    src="/img/graphics/admissions/web-silveracademyWhy A Jewish Day School.jpg"
                    alt="Meet our amazing staff"
                    class="w-full h-full object-cover"
                    style="object-position: 50% 22%;"
                />
            </div>
        </section>

        <!-- Intro Section -->
        <section class="pt-20 pb-10 bg-white text-center">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl text-slate-800 leading-none tracking-tight mb-6">
                    Meet Our Amazing Staff
                </h1>
                <p class="text-lg sm:text-xl text-slate-600 leading-relaxed max-w-3xl mx-auto">
                    Meet the educators and leaders who make The Silver Academy a place of excellence and belonging.
                </p>
            </div>
        </section>

        <!-- Staff Grid -->
        <section class="pt-10 pb-20 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div v-if="staff.length === 0" class="text-center py-16 text-slate-500">
                    <p class="text-lg">Staff listings will be updated soon.</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <article
                        v-for="member in staff"
                        :key="member.name"
                        class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-100 hover:shadow-lg transition-shadow duration-300"
                    >
                        <!-- Photo -->
                        <div class="aspect-[3/4] bg-slate-200 flex items-center justify-center overflow-hidden">
                            <img
                                v-if="member.photo"
                                :src="member.photo"
                                :alt="member.name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-slate-200 text-slate-400"
                            >
                                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                            </div>
                        </div>
                        <!-- Info -->
                        <div class="p-6 border-t-4 border-accent-500">
                            <h2 class="font-serif text-xl font-semibold text-slate-800">
                                {{ member.name }}
                            </h2>
                            <p class="mt-1 text-brand-600 font-medium">
                                {{ member.title }}
                            </p>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ member.department }}
                            </p>
                        </div>
                    </article>
                </div>

            </div>
        </section>
    </MarketingLayout>
</template>
