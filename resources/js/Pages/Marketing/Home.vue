<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';

const activePanel = ref(5); // Default to Family Partnership (index 5)
const activeGrade = ref(0); // Default to Ganeinu (index 0)
const hoveredPanel = ref(null); // Full expansion on hover (Edit 5)
const hoveredGrade = ref(null);
const isHoveringGrades = ref(false);
const isVideoLoaded = ref(false);

const loadVideo = () => {
    isVideoLoaded.value = true;
};

const panels = [
    {
        title: 'Small Class Sizes',
        description: 'Our low student-to-teacher ratio ensures that every child is seen, heard, and valued. Teachers tailor instruction to meet individual needs, fostering deep understanding and academic confidence.',
        image: '/img/graphics/home/web-silveracademySmall Class Sizes(1).jpg',
        link: '/about#why-silver-academy'
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
        image: '/img/graphics/home/web-silverRestoration.jpg',
        link: '/about#why-silver-academy'
    },
    {
        title: 'Joyful Learning',
        description: 'Education should be exciting. We create a dynamic environment where curiosity thrives, questions are encouraged, and learning is a daily adventure.',
        image: '/img/graphics/home/web-silveracademyJoyful Learning 2.jpg',
        link: '/programs'
    },
    {
        title: 'Accessible Education',
        description: 'We are committed to making a Silver Academy education reachable for every family through generous scholarship programs and tuition assistance.',
        image: '/img/graphics/home/web-silveracademyAccessible.jpg',
        link: '/contact'
    },
    {
        title: 'Family Partnership',
        description: 'Transparent communication and shared decision-making create a welcoming, collaborative community where parents are true partners in their child’s journey.',
        image: '/img/graphics/home/web-silveracademyFamily Parntership.jpg',
        link: '/programs/parent-circle'
    }
];

const gradeLevels = [
    {
        title: 'Ganeinu',
        subtitle: 'Preschool',
        description: 'Our early childhood program fosters creativity, socialization, and a love for Jewish traditions in a warm, nurturing environment.',
        image: '/img/graphics/home/web-silveracademyGaneinu.jpg',
        link: '/programs/ganeinu'
    },
    {
        title: 'Kindergarten',
        subtitle: 'Foundations',
        description: 'Building strong foundations in literacy, numeracy, and Hebrew language through play-based and structured learning.',
        image: '/img/graphics/home/web-silveracademyKindergarten.jpg',
        link: '/programs/kindergarten'
    },
    {
        title: 'Lower School',
        subtitle: 'Grades 1-4',
        description: 'Developing critical thinking skills and independence while deepening connection to Jewish identity and community.',
        image: '/img/graphics/home/web-silveracademyLower School2.jpg',
        link: '/programs/lower-school'
    },
    {
        title: 'Upper School',
        subtitle: 'Grades 5-8',
        description: 'Preparing students for high school and beyond with rigorous academics, leadership opportunities, and ethical growth.',
        image: '/img/graphics/home/web-silveracademyUpper School 2.jpg',
        link: '/programs/upper-school'
    }
];

const features = [
    {
        title: 'Rigorous Academics',
        description: 'Our curriculum challenges students to think critically while building a strong foundation in core subjects.',
        icon: '📚',
    },
    {
        title: 'Jewish Values',
        description: 'We integrate Jewish learning and traditions throughout every aspect of the school experience.',
        icon: '✡️',
    },
    {
        title: 'Small Class Sizes',
        description: 'Individual attention ensures every student receives personalized support and guidance.',
        icon: '👨‍🏫',
    },
    {
        title: 'Character Development',
        description: 'We nurture confidence, curiosity, and character in every child.',
        icon: '🌟',
    },
];

const stats = [
    { value: '61%', label: 'of students receive a scholarship' },
    { value: '12', label: 'average class size' },
    { value: '100%', label: 'high school acceptance' },
];

// CauseMatch 2026 pop-up: show until end of day March 16, 2026; dismissible via localStorage
const CAMPAIGN_POPUP_EXPIRY = new Date(2026, 2, 16, 23, 59, 59, 999); // March 16, 2026 23:59:59 local
const CAMPAIGN_POPUP_STORAGE_KEY = 'silveracademy_causematch_2026_dismissed';
const showCampaignPopup = ref(false);

onMounted(() => {
    const beforeExpiry = new Date() < CAMPAIGN_POPUP_EXPIRY;
    const notDismissed = localStorage.getItem(CAMPAIGN_POPUP_STORAGE_KEY) !== '1';
    const previewMode = typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('show_campaign_popup') === '1';
    if (beforeExpiry && (notDismissed || previewMode)) {
        showCampaignPopup.value = true;
    }
});

const dismissCampaignPopup = () => {
    localStorage.setItem(CAMPAIGN_POPUP_STORAGE_KEY, '1');
    showCampaignPopup.value = false;
};
</script>

<template>
    <Head title="Home - Central PA's Jewish Day School" />

    <MarketingLayout>
        <!-- CauseMatch 2026 campaign pop-up (expires end of day March 16, 2026) -->
        <div
            v-if="showCampaignPopup"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60"
            role="dialog"
            aria-modal="true"
            aria-label="Campaign: Silver Academy CauseMatch 2026"
        >
            <div class="relative max-w-2xl w-full animate-popup">
                <button
                    type="button"
                    class="absolute -top-2 -right-2 z-10 rounded-full bg-white/90 p-1.5 text-slate-600 shadow-md hover:bg-white hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    aria-label="Close"
                    @click="dismissCampaignPopup"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <a
                    href="https://causematch.com/silveracademypa26"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block rounded-lg overflow-hidden shadow-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                >
                    <img
                        src="/img/pop-ups/causematch-silveracademy-2026.png"
                        alt="Silver Academy CauseMatch 2026 - Rising Together"
                        class="w-full h-auto object-contain"
                    />
                </a>
            </div>
        </div>

        <!-- Hero Section - Video banner (no overlay), text underneath (Edits 1, 2, 3) -->
        <!-- Video banner - shorter height so text and video visible on load -->
        <section class="relative overflow-hidden bg-slate-900">
            <div class="h-[280px] sm:h-[360px] lg:h-[420px] w-full">
                <video 
                    autoplay 
                    muted 
                    loop 
                    playsinline
                    class="w-full h-full object-cover object-center"
                >
                    <source src="/vid/Broll Header.mp4" type="video/mp4">
                </video>
            </div>
        </section>
        
        <!-- Hero text - below video, no overlay, larger text (Edit 3) -->
        <section class="pt-12 pb-6 sm:py-16 lg:py-20 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto">
                    <!-- Label -->
                    <div class="mb-6">
                        <span class="inline-block px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-brand-700 bg-brand-100 rounded-full">
                            Welcome to The Silver Academy
                        </span>
                    </div>
                    
                    <!-- Main Headline - Increased size for readability (Edit 3) -->
                    <h1 class="font-serif text-5xl sm:text-6xl lg:text-7xl text-slate-600 leading-tight tracking-tight font-bold">
                        Igniting a Lifelong Love of Learning, Jewish Identity, and Purposeful Living
                    </h1>
                    
                    <!-- Subheadline - Increased size -->
                    <p class="mt-8 text-xl sm:text-2xl text-slate-600 max-w-3xl mx-auto leading-relaxed">
                        Rooted in rigorous academics and Jewish values, our small-class learning environment 
                        empowers Pre-K through 8th graders to grow with confidence, curiosity, and character 
                        here in Central Pennsylvania.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            href="/admissions"
                            class="inline-block rounded bg-accent-500 px-8 py-4 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-accent-600 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5"
                        >
                            Learn about Admissions
                        </Link>
                        <a
                            href="https://calendar.app.google/Y5NrAjA9RooWgwZ98"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block rounded bg-accent-500 px-8 py-4 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-accent-600 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5"
                        >
                            Schedule a Visit
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Organizational Video Section (Edit 4: tighter gap on mobile) -->
        <section class="w-full bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 pt-6 pb-4 sm:py-12">
                <div class="aspect-video relative overflow-hidden group rounded-2xl shadow-xl">
                    <!-- YouTube iframe (loaded on click) -->
                    <iframe
                        v-if="isVideoLoaded"
                        class="w-full h-full"
                        src="https://www.youtube.com/embed/cZ_1tVrVBZk?rel=0&modestbranding=1&autoplay=1"
                        title="Watch Our Story - Silver Academy"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                    
                    <!-- Poster image with overlay (shown before click) -->
                    <template v-else>
                        <img 
                            src="/vid/Silver Academy Full Video V3-poster.jpg" 
                            alt="Watch Our Story"
                            class="w-full h-full object-cover"
                        />
                        <!-- Black Overlay for Text Readability -->
                        <div class="absolute inset-0 bg-black/50 z-0"></div>
                        <div 
                            class="absolute inset-0 flex items-center justify-center cursor-pointer z-10"
                            @click="loadVideo"
                        >
                            <div class="text-center">
                                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform duration-300 border-2 border-white/50">
                                    <svg class="w-10 h-10 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-white text-xl font-semibold tracking-wider uppercase drop-shadow-lg">Watch Our Story</h3>
                                <p class="text-white/90 mt-2 font-serif text-lg drop-shadow-md">Life at Silver Academy</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <!-- Value Proposition Section (Edit 4: tighter gap from org video on mobile) -->
        <section class="pt-6 pb-16 sm:py-16 bg-white text-center">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <h2 class="font-serif text-3xl sm:text-4xl text-slate-800 tracking-tight mb-6">
                    A School That Sees, Supports, and Nurtures Every Child
                </h2>
                <p class="text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                    We offer an individualized approach to learning with strong academics, 
                    meaningful support, and options that keep education within reach for families.
                </p>
            </div>
        </section>

        <!-- Interactive Panels Section: tiles fully expand on hover (Edit 5) -->
        <section class="w-full flex flex-col md:flex-row bg-slate-900 overflow-hidden min-h-[840px] md:min-h-0 md:h-[600px]">
            <div 
                v-for="(panel, index) in panels" 
                :key="index"
                class="relative transition-all duration-500 ease-in-out cursor-pointer overflow-hidden group flex-shrink-0 md:flex-shrink border-b border-white/20 last:border-b-0 md:border-b-0 md:border-r md:last:border-r-0 md:min-h-0"
                :class="[
                    (hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'md:flex-grow-[4]' : 'md:flex-grow-[1]',
                    (hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'min-h-[380px]' : 'min-h-[140px]'
                ]"
                @click="activePanel = index"
                @mouseenter="hoveredPanel = index"
                @mouseleave="hoveredPanel = null"
            >
                <!-- Background Image (Edit 7: object-position on mobile for Dual Curriculum & Family Partnership to show faces) -->
                <div class="absolute inset-0 transition-transform duration-700 ease-out"
                     :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'scale-100' : 'scale-110 grayscale group-hover:grayscale-0'">
                    <img 
                        :src="panel.image" 
                        :alt="panel.title" 
                        class="w-full h-full object-cover"
                        :class="(panel.title === 'Dual Curriculum' || panel.title === 'Family Partnership') ? 'max-md:object-top' : ''"
                    />
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-brand-900/40 transition-opacity duration-300"
                         :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'opacity-20' : 'opacity-60 group-hover:opacity-40'"></div>
                     
                     <!-- Blue overlay for inactive tabs -->
                     <div class="absolute inset-0 bg-brand-600/80 mix-blend-multiply transition-opacity duration-300"
                          :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'opacity-0' : 'opacity-100'"></div>
                </div>

                <!-- Title: horizontal at top on mobile, vertical centered on md+ (Inactive State) -->
                <div 
                    class="absolute inset-0 flex items-start pt-4 justify-center md:items-center md:pt-0 pointer-events-none transition-opacity duration-300"
                    :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'opacity-0' : 'opacity-100'"
                >
                    <h3 class="panel-inactive-title text-white font-bold text-lg md:text-xl uppercase tracking-widest select-none drop-shadow-md text-center px-2">
                        {{ panel.title }}
                    </h3>
                </div>

                <!-- Content (Active State) - shown when active or hovered -->
                <div 
                    class="absolute bottom-0 left-0 right-0 p-8 md:p-12 transition-all duration-500 transform"
                    :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
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

        <!-- Stats & Grade Levels Section (Edit 11: tighter gap to "Be Part of a School" on mobile) -->
        <section class="pt-24 pb-12 sm:py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center mb-24">
                    <div v-for="stat in stats" :key="stat.label" class="bg-brand-50 rounded-lg p-6">
                        <div class="text-5xl md:text-6xl font-serif font-bold text-brand-500 mb-4">{{ stat.value }}</div>
                        <div class="text-brand-500 font-medium text-lg leading-tight max-w-[200px] mx-auto">{{ stat.label }}</div>
                    </div>
                </div>

                <!-- Section Title (Edit 7: allow wrap on mobile so "Growing Confident, Compassionate..." isn't cut off) -->
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="font-serif text-3xl sm:text-4xl text-slate-800 tracking-tight mb-6 whitespace-normal sm:whitespace-nowrap">
                        Growing Confident, Compassionate, and Curious Students
                    </h2>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        At The Silver Academy, our small classes and integrated general and Judaic curriculum 
                        give Pre-K through 8th grade students the personalized attention they need to thrive. 
                        We welcome families from all backgrounds and nurture each child’s character, critical 
                        thinking, and sense of purpose as they grow academically, emotionally, and spiritually.
                    </p>
                </div>

                <!-- Grade Levels: tiles fully expand on hover (Edit 5) -->
                <div class="flex flex-col md:flex-row w-full gap-2 min-h-[920px] md:min-h-0 md:h-[500px]" @mouseenter="isHoveringGrades = true" @mouseleave="isHoveringGrades = false; hoveredGrade = null">
                    <div 
                        v-for="(grade, index) in gradeLevels" 
                        :key="index"
                        class="relative rounded-2xl overflow-hidden cursor-pointer transition-all duration-500 ease-in-out group flex-shrink-0 md:flex-shrink md:min-h-0"
                        :class="[
                            (activeGrade === index || hoveredGrade === index) ? 'md:flex-grow-[3]' : 'md:flex-grow-[1]',
                            (activeGrade === index || hoveredGrade === index) ? 'min-h-[420px]' : 'min-h-[230px]'
                        ]"
                        @click="activeGrade = index"
                        @mouseenter="activeGrade = index; hoveredGrade = index"
                        @mouseleave="hoveredGrade = null"
                    >
                        <img 
                            :src="grade.image" 
                            :alt="grade.title" 
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700"
                            :class="activeGrade === index ? 'scale-100' : 'scale-110'"
                        />
                        <!-- Edit 8: No overlay when collapsed; overlay only on hover/expand when text appears -->
                        <div class="absolute inset-0 bg-black transition-opacity duration-300"
                             :class="(activeGrade === index || hoveredGrade === index) ? 'opacity-60' : 'opacity-0'"></div>
                        
                        <!-- Content: extra top padding on mobile only when this panel is expanded -->
                        <div 
                            class="absolute bottom-0 left-0 right-0 p-4 pt-6 md:p-8 flex flex-col justify-end h-full z-10"
                            :class="activeGrade === index ? 'pt-12 md:pt-8' : ''"
                        >
                            <div class="transform transition-all duration-300"
                                 :class="activeGrade === index ? 'translate-y-0' : 'translate-y-4'">
                                <!-- Background Bar for Title - Blue when collapsed, Yellow when expanded -->
                                <Link 
                                    :href="grade.link"
                                    class="inline-block px-4 py-2 mb-2 rounded transition-colors duration-300 hover:opacity-90 cursor-pointer"
                                    :class="activeGrade === index ? 'bg-accent-500/90' : 'bg-brand-500/90'"
                                >
                                    <h3 
                                        class="text-white font-bold uppercase tracking-wider transition-all duration-300 whitespace-nowrap"
                                        :class="activeGrade === index ? 'text-2xl' : 'text-sm md:text-base'"
                                    >
                                        {{ grade.title }}
                                    </h3>
                                </Link>
                                <p 
                                    class="text-white/90 text-xs md:text-sm font-medium uppercase tracking-widest mb-4 transition-opacity duration-300"
                                    :class="activeGrade === index ? 'opacity-100' : 'opacity-100 md:opacity-0'"
                                >
                                    {{ grade.subtitle }}
                                </p>
                                <div 
                                    class="overflow-hidden transition-all duration-500"
                                    :class="activeGrade === index ? 'max-h-40 opacity-100' : 'max-h-0 opacity-0'"
                                >
                                    <p class="text-white/90 mb-6 leading-relaxed">
                                        {{ grade.description }}
                                    </p>
                                    <Link 
                                        :href="grade.link"
                                        class="inline-flex items-center text-white font-semibold text-sm uppercase tracking-wider hover:text-accent-300 transition-colors"
                                    >
                                        Discover More
                                        <svg class="ml-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Family Community Section (Edit 11: tighter gap from programs on mobile) -->
        <section class="pt-12 pb-24 sm:py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto">
                    <!-- Star of David Icon -->
                    <div class="flex justify-center mb-8">
                        <img src="/img/icons/TSA Logo-inverted.svg" alt="Star of David" class="w-[116px] h-[116px] text-brand-500" />
                    </div>
                    
                    <!-- Headline -->
                    <h2 class="font-serif text-4xl sm:text-5xl lg:text-6xl text-slate-800 tracking-tight mb-6">
                        Be Part of a School That Feels Like Family
                    </h2>
                    
                    <!-- Descriptive Text -->
                    <p class="text-lg sm:text-xl text-slate-600 leading-relaxed mb-10 max-w-3xl mx-auto">
                        The Silver Academy welcomes families from all backgrounds into a warm, inclusive community. 
                        Together, we nurture children who are proud of who they are and confident in where they're going.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            href="/contact"
                            class="px-8 py-3 text-sm font-semibold uppercase tracking-wider text-slate-800 bg-white border-2 border-accent-500 rounded hover:bg-accent-500 hover:text-white transition-colors duration-200"
                        >
                            Inquire
                        </Link>
                        <a
                            href="https://calendar.app.google/Y5NrAjA9RooWgwZ98"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-8 py-3 text-sm font-semibold uppercase tracking-wider text-slate-800 bg-white border-2 border-accent-500 rounded hover:bg-accent-500 hover:text-white transition-colors duration-200"
                        >
                            Visit
                        </a>
                        <a
                            href="https://app.simpletuitionsolutions.org/register?sc=21760"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-8 py-3 text-sm font-semibold uppercase tracking-wider text-slate-800 bg-white border-2 border-accent-500 rounded hover:bg-accent-500 hover:text-white transition-colors duration-200"
                        >
                            Apply
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonial Section -->
        <section class="relative py-0">
            <div class="relative h-[600px] md:h-[700px]">
                <!-- Background Image (Edit 9: parent testimonial photo) -->
                <img 
                    src="/img/graphics/home/parenttestimonial.png" 
                    alt="Parent testimonial - Silver Academy" 
                    class="absolute inset-0 w-full h-full object-cover"
                />
                
                <!-- Testimonial Quote Overlay -->
                <div class="absolute inset-0 flex items-center">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8 w-full">
                        <div class="max-w-2xl bg-white/95 backdrop-blur-sm rounded-2xl p-8 md:p-12 shadow-2xl relative">
                            <!-- Large Quotation Mark -->
                            <div class="absolute -top-4 -left-4">
                                <svg class="w-16 h-16 text-brand-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                                </svg>
                            </div>
                            
                            <!-- Quote Text -->
                            <p class="text-lg md:text-xl text-slate-700 leading-relaxed mb-6 pt-4">
                                From the sense of community, small class sizes, warm and amazing staff and faculty—we were 
                                blessed to be a part of the Silver team! As a non-Jewish family, we loved that we could give 
                                our daughter the gift of Inclusion, learning about other cultures and religions and an 
                                opportunity to see new perspectives.
                            </p>
                            
                            <!-- Attribution (Edit 10) -->
                            <p class="text-right text-slate-600 font-medium">
                                —Lindsay J.<br />Parent
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </MarketingLayout>
</template>

<style scoped>
.writing-vertical-lr {
    writing-mode: vertical-lr;
}

/* Panel inactive title: horizontal on mobile, vertical on desktop only */
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

@keyframes popup-in {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-popup {
    animation: popup-in 0.3s ease-out forwards;
}
</style>
