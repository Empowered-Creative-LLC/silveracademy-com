<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';

const activePanel = ref(5); // Default to Family Partnership

const panels = [
    {
        title: 'Small Class Sizes',
        description: 'Our low student-to-teacher ratio ensures that every child is seen, heard, and valued. Teachers tailor instruction to meet individual needs, fostering deep understanding and academic confidence.',
        image: '/img/graphics/home/web-silveracademySmall Class Sizes(1).jpg',
        link: '/why-silver-academy#our-approach'
    },
    {
        title: 'Dual Curriculum',
        description: 'We seamlessly integrate rigorous general studies with rich Jewish education, empowering students to navigate the world with intellectual depth and spiritual grounding.',
        image: '/img/graphics/home/web-silveracademyDual Curriculum.jpg',
        link: '/programs#our-approach'
    },
    {
        title: 'Restorative Culture',
        description: 'We prioritize social-emotional learning and character development, building a community rooted in empathy, respect, and mutual responsibility.',
        image: 'https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?auto=format&fit=crop&w=800&q=80',
        link: '/why-silver-academy#our-approach'
    },
    {
        title: 'Joyful Learning',
        description: 'Education should be exciting. We create a dynamic environment where curiosity thrives, questions are encouraged, and learning is a daily adventure.',
        image: '/img/graphics/home/web-silveracademyJoyful Learning 2.jpg',
        link: '/services'
    },
    {
        title: 'Accessible Education',
        description: 'We are committed to making a Silver Academy education reachable for every family through generous scholarship programs and tuition assistance.',
        image: '/img/graphics/home/web-silveracademyAccessible.jpg',
        link: '/contact'
    },
    {
        title: 'Family Partnership',
        description: "Transparent communication and shared decision-making create a welcoming, collaborative community where parents are true partners in their child's journey.",
        image: '/img/graphics/home/web-silveracademyFamily Parntership.jpg',
        link: '/programs/parent-circle'
    }
];

const approaches = [
    {
        title: 'Dual Excellence',
        description: 'Rigorous general and Judaic studies are fully integrated, giving students a strong academic foundation while nurturing Jewish identity and values.'
    },
    {
        title: 'Small Class Sizes & Personalized Learning',
        description: 'With no more than 12 students per class, every child is seen, heard, and supported. Individualized attention ensures both challenge and support where needed.'
    },
    {
        title: 'Restorative, Supportive Culture',
        description: 'Children learn responsibility, accountability, and emotional intelligence in a safe, inclusive environment.'
    },
    {
        title: 'Holistic, Hands-On Learning',
        description: 'Art, music, science, and project-based experiences foster curiosity, creativity, and joy across subjects.'
    },
    {
        title: 'Family Partnership',
        description: 'Transparent communication, ongoing dialogue, and community programs ensure families are true partners in education.'
    },
    {
        title: 'Accessible Education',
        description: 'Generous financial aid and EITC programs ensure all families can access a high-quality day school experience.'
    }
];

const schoolHighlights = {
    academics: [
        'Integrated general + Judaic curriculum',
        'Hands-on programs in art, music, STEM',
        'Advanced technology program: robotics, coding, engineering design',
        'ADAM Intelligence framework: skills for the AI era'
    ],
    community: [
        'Inclusive, welcoming Jewish community open to all',
        'Mentor Family program and Parent Circle',
        'Intergenerational "Better Together" program with local seniors',
        'Collaborations with local synagogues and PJ Library'
    ],
    faculty: [
        'Passionate educators blending experience with new perspectives',
        'Ongoing professional development, restorative practices, and advanced certifications'
    ],
    facilities: [
        'New playground and expanded athletic opportunities',
        'Exploring creative campus expansions to support growing enrollment'
    ]
};
</script>

<template>
    <Head title="Why Silver Academy? - The Silver Academy" />

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="relative h-[400px] overflow-hidden">
            <div class="absolute inset-0">
                <img 
                    src="/img/graphics/about/web-silveracademyWhy Silver.jpg" 
                    alt="Students at The Silver Academy" 
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black/20"></div>
            </div>
        </section>

        <!-- Intro Section -->
        <section class="py-20 bg-white text-center">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl text-slate-800 leading-tight tracking-tight mb-6">
                    A School Where Every Child is Seen, Supported, and Inspired
                </h1>
                <p class="text-lg sm:text-xl text-slate-600 leading-relaxed max-w-3xl mx-auto">
                    Small classes, dual excellence, and a values-driven education prepare Jewish students to thrive academically, socially, and spiritually.
                </p>
            </div>
        </section>

        <!-- Our Approach Section -->
        <section id="our-approach" class="py-24 bg-slate-50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="font-serif text-3xl text-slate-800 font-bold mb-4">Our Approach</h2>
                    <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                        Choosing a school is one of the most important decisions a family makes. At The Silver Academy, we understand that families want more than academics, they want a place where children are known, challenged, and empowered to grow into confident, compassionate, and capable young people.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="(approach, index) in approaches" 
                        :key="index"
                        class="relative bg-white p-8 rounded-xl shadow-sm border-t-4 border-brand-500 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group overflow-hidden"
                    >
                        <!-- Logo Watermark -->
                        <img 
                            src="/img/icons/safavicon.png" 
                            alt=""
                            class="absolute -right-6 -bottom-6 w-40 h-40 opacity-5 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none select-none z-0 grayscale"
                        />

                        <div class="relative z-10">
                            <h3 class="font-serif font-bold text-brand-700 text-2xl mb-3 group-hover:text-brand-600 transition-colors duration-300">
                                {{ approach.title }}
                            </h3>
                            <p class="text-slate-600 leading-relaxed">
                                {{ approach.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- The Silver Academy Difference - Interactive Panels: vertical on mobile, horizontal on md+ -->
        <section class="w-full flex flex-col md:flex-row bg-slate-900 overflow-hidden min-h-[840px] md:min-h-0 md:h-[600px]">
            <div 
                v-for="(panel, index) in panels" 
                :key="index"
                class="relative transition-all duration-500 ease-in-out cursor-pointer overflow-hidden group flex-shrink-0 md:flex-shrink border-b border-white/20 last:border-b-0 md:border-b-0 md:border-r md:last:border-r-0 md:min-h-0"
                :class="[
                    activePanel === index ? 'md:flex-grow-[4]' : 'md:flex-grow-[1] md:hover:flex-grow-[1.2]',
                    activePanel === index ? 'min-h-[380px]' : 'min-h-[140px]'
                ]"
                @click="activePanel = index"
            >
                <!-- Background Image -->
                <div class="absolute inset-0 transition-transform duration-700 ease-out"
                     :class="activePanel === index ? 'scale-100' : 'scale-110 grayscale group-hover:grayscale-0'">
                    <img :src="panel.image" :alt="panel.title" class="w-full h-full object-cover" />
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-brand-900/40 transition-opacity duration-300"
                         :class="activePanel === index ? 'opacity-20' : 'opacity-60 group-hover:opacity-40'"></div>
                     
                     <!-- Blue overlay for inactive tabs -->
                     <div class="absolute inset-0 bg-brand-600/80 mix-blend-multiply transition-opacity duration-300"
                          :class="activePanel === index ? 'opacity-0' : 'opacity-100'"></div>
                </div>

                <!-- Title: horizontal at top on mobile, vertical on md+ (Inactive State) -->
                <div 
                    class="absolute inset-0 flex items-start pt-4 justify-center md:items-center md:pt-0 pointer-events-none transition-opacity duration-300"
                    :class="activePanel === index ? 'opacity-0' : 'opacity-100'"
                >
                    <h3 class="panel-inactive-title text-white font-bold text-lg md:text-xl uppercase tracking-widest select-none drop-shadow-md text-center px-2">
                        {{ panel.title }}
                    </h3>
                </div>

                <!-- Content (Active State) -->
                <div 
                    class="absolute bottom-0 left-0 right-0 p-8 md:p-12 transition-all duration-500 transform"
                    :class="activePanel === index ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
                >
                    <div class="bg-white/95 backdrop-blur-sm p-8 rounded-lg shadow-xl max-w-xl mx-auto md:mx-0">
                        <h3 class="text-2xl font-serif font-bold text-slate-800 mb-4">{{ panel.title }}</h3>
                        <p class="text-slate-600 mb-6 leading-relaxed">{{ panel.description }}</p>
                        <Link 
                            :href="panel.link" 
                            class="inline-flex items-center text-sm font-bold text-brand-600 uppercase tracking-wider hover:text-brand-800 transition-colors"
                        >
                            {{ panel.title === 'Accessible Education' ? 'Contact us to learn more' : 
                               (panel.title === 'Family Partnership' ? 'Silver Parent Circle' : 'Learn More') }}
                            <svg class="ml-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section - Meet Our Students -->
        <section class="py-16 bg-white">
            <div class="mx-auto max-w-4xl px-6 lg:px-8 text-center">
                <Link 
                    href="/programs" 
                    class="inline-block bg-accent-500 text-white px-8 py-4 rounded font-semibold uppercase tracking-wider hover:bg-accent-600 transition-colors duration-200"
                >
                    Meet Our Students →
                </Link>
            </div>
        </section>

        <!-- Tuition & Financial Aid Section -->
        <section class="py-24 bg-slate-50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="max-w-4xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="font-serif text-4xl text-slate-800 tracking-wide uppercase mb-4">
                            Accessible Education
                        </h2>
                        <p class="text-lg text-slate-600">
                            Generous financial aid and EITC programs ensure all families can access a high-quality day school experience.
                        </p>
                    </div>

                    <!-- Financial Aid Statistics - Prominent -->
                    <div class="bg-brand-500 text-white p-10 md:p-12 rounded-2xl shadow-xl mb-8">
                        <div class="text-center mb-8">
                            <h3 class="font-serif text-3xl md:text-4xl font-bold text-white mb-2">
                                Making Education Accessible
                            </h3>
                            <p class="text-white/90 text-lg">
                                Most families receive significant financial assistance
                            </p>
                        </div>
                        <div class="grid md:grid-cols-3 gap-8 text-center mb-8">
                            <div>
                                <div class="text-5xl md:text-6xl font-bold mb-3">61%</div>
                                <div class="text-white/90 text-base font-medium">of students receive a scholarship</div>
                            </div>
                            <div>
                                <div class="text-5xl md:text-6xl font-bold mb-3">60.2%</div>
                                <div class="text-white/90 text-base font-medium">Average scholarship award</div>
                            </div>
                            <div>
                                <div class="text-5xl md:text-6xl font-bold mb-3">$17,105</div>
                                <div class="text-white/90 text-base font-medium">Average scholarship amount</div>
                            </div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-6 text-center">
                            <p class="text-white text-sm uppercase tracking-wider mb-2">Average Tuition Paid</p>
                            <p class="text-white text-4xl font-bold">$8,178.90</p>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-white p-8 md:p-10 rounded-2xl shadow-lg text-center">
                        <p class="text-slate-700 text-lg leading-relaxed">
                            We are committed to making a Silver Academy education accessible to families.
                            We encourage you to connect with our office to learn more about tuition and personalized support options.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- School Highlights -->
        <section class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="font-serif text-4xl text-slate-800 tracking-wide uppercase mb-4">
                        School Highlights
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Academics & Curriculum -->
                    <div class="bg-slate-50 p-8 rounded-xl">
                        <h3 class="font-serif text-2xl text-brand-600 mb-6">Academics & Curriculum</h3>
                        <ul class="space-y-3">
                            <li 
                                v-for="(item, index) in schoolHighlights.academics" 
                                :key="index"
                                class="flex items-start"
                            >
                                <span class="text-accent-500 mr-3 mt-1">•</span>
                                <span class="text-slate-700">{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Community & Families -->
                    <div class="bg-slate-50 p-8 rounded-xl">
                        <h3 class="font-serif text-2xl text-brand-600 mb-6">Community & Families</h3>
                        <ul class="space-y-3">
                            <li 
                                v-for="(item, index) in schoolHighlights.community" 
                                :key="index"
                                class="flex items-start"
                            >
                                <span class="text-accent-500 mr-3 mt-1">•</span>
                                <span class="text-slate-700">{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Faculty & Staff -->
                    <div class="bg-slate-50 p-8 rounded-xl">
                        <h3 class="font-serif text-2xl text-brand-600 mb-6">Faculty & Staff</h3>
                        <ul class="space-y-3">
                            <li 
                                v-for="(item, index) in schoolHighlights.faculty" 
                                :key="index"
                                class="flex items-start"
                            >
                                <span class="text-accent-500 mr-3 mt-1">•</span>
                                <span class="text-slate-700">{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Facilities & Campus Updates -->
                    <div class="bg-slate-50 p-8 rounded-xl">
                        <h3 class="font-serif text-2xl text-brand-600 mb-6">Facilities & Campus Updates</h3>
                        <ul class="space-y-3">
                            <li 
                                v-for="(item, index) in schoolHighlights.facilities" 
                                :key="index"
                                class="flex items-start"
                            >
                                <span class="text-accent-500 mr-3 mt-1">•</span>
                                <span class="text-slate-700">{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section - Support Silver -->
        <section class="py-16 bg-brand-600">
            <div class="mx-auto max-w-4xl px-6 lg:px-8 text-center">
                <Link 
                    href="/get-involved" 
                    class="inline-block bg-white text-brand-600 px-8 py-4 rounded font-semibold uppercase tracking-wider hover:bg-slate-50 transition-colors duration-200"
                >
                    Support Silver →
                </Link>
            </div>
        </section>
    </MarketingLayout>
</template>

<style scoped>
.writing-vertical-lr {
    writing-mode: vertical-lr;
}

.panel-inactive-title {
    white-space: normal;
}
@media (min-width: 768px) {
    .panel-inactive-title {
        writing-mode: vertical-lr;
        transform: rotate(180deg);
        white-space: nowrap;
    }
}
</style>
