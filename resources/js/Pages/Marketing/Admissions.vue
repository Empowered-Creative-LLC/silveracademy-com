<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';

const activePanel = ref(6); // Default to Accessible Education (index 6)
const hoveredPanel = ref(null); // Full expansion on hover (Edit 5)

const panels = [
    {
        title: 'Small Class Sizes',
        description: 'With a maximum of 12 students per class, every child receives individualized attention.',
        image: '/img/graphics/home/web-silveracademySmall Class Sizes(1).jpg',
        link: '/about'
    },
    {
        title: 'Dual Excellence',
        description: 'Integrated general and Judaic curriculum prepares students academically and spiritually.',
        image: '/img/graphics/home/web-silveracademyDual Curriculum.jpg',
        link: '/programs'
    },
    {
        title: 'Holistic Development',
        description: 'Students explore art, music, STEM, and project-based learning.',
        image: '/img/graphics/admissions/web-silveracademyHolistic Development.jpg',
        link: '/programs'
    },
    {
        title: 'Jewish Identity & Pride',
        description: 'Torah, tradition, and values guide daily life, fostering confidence and connection.',
        image: '/img/graphics/admissions/web-silveracademyJewish Pride.jpg',
        link: '/about'
    },
    {
        title: 'Supportive Culture',
        description: 'Restorative practices teach accountability, empathy, and emotional intelligence.',
        image: 'https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?auto=format&fit=crop&w=800&q=80',
        link: '/about'
    },
    {
        title: 'Family Partnership',
        description: 'Transparent communication ensures parents are active partners in education.',
        image: '/img/graphics/home/web-silveracademyFamily Parntership.jpg',
        link: '#'
    },
    {
        title: 'Accessible Education',
        description: 'Generous financial aid and EITC programs ensure every family has access.',
        image: '/img/graphics/home/web-silveracademyAccessible.jpg',
        link: '/contact'
    }
];

const steps = [
    { title: 'Schedule a Tour', description: 'See our classrooms, meet our faculty, and experience the Silver Academy difference firsthand.' },
    { title: 'Submit an Application', description: 'Complete our online application for your child’s grade. Include any supporting documents or recommendations.' },
    { title: 'Meet with Our Team', description: 'An admissions meeting with our Head of School or Enrollment Coordinator allows us to understand your child’s unique needs and goals.' },
    { title: 'Enrollment & Financial Aid', description: 'We review applications alongside financial aid requests to ensure all families can access a Silver Academy education.' },
    { title: 'Welcome to the Community', description: 'Once accepted, families join a vibrant, supportive school community ready to nurture your child’s growth.' },
];

const tuition = [
    { grade: 'Ganeinu', price: '$2,000' },
    { grade: 'Kindergarten', price: '$10,000' },
    { grade: 'Grade 1', price: '$32,500' },
    { grade: 'Grades 2–8', price: '$35,000' },
];

const stats = [
    { value: '61%', label: 'of students receive financial aid' },
    { value: '$8,178', label: 'Average Tuition Paid' },
    { value: '60%', label: 'Average Scholarship Award ($17,105)' },
];

const benefits = [
    { title: 'Integrated Curriculum', description: 'Our general and Judaic studies are fully connected, fostering critical thinking, problem-solving, and creativity.' },
    { title: 'Small Class Sizes', description: 'With no more than 12 students per class, every child receives individualized attention, support, and challenge.' },
    { title: 'Hands-On Learning', description: 'Art, music, STEM, and project-based experiences help students explore, discover, and grow.' },
    { title: 'Jewish Values, Universal Impact', description: 'Lessons rooted in Torah, tradition, and ethical principles nurture empathy, integrity, responsibility, and intentionality—skills that benefit every child.' },
    { title: 'Restorative Practices', description: 'Our approach teaches accountability, conflict resolution, and emotional intelligence.' },
    { title: 'Leadership and Community Service', description: 'Students develop confidence, collaboration skills, and a sense of purpose.' },
];

const testimonials = [
    {
        quote: "From the moment we joined Silver, we knew our children would be seen and supported. The small class sizes and individualized attention made all the difference. Our kids not only excel academically but also grow into kind, confident, and thoughtful young people.",
        author: "Parent of 3 Silver Academy Students"
    },
    {
        quote: "Silver gave me the tools to succeed academically and the values to navigate life with integrity and purpose. The sense of community and support I experienced has stayed with me through college and into my career.",
        author: "Alumni Reflection"
    },
    {
        quote: "Watching students grow and knowing we’ve contributed to their academic and personal journeys is the most rewarding part of being part of Silver Academy. The partnership with families and the dedication of our staff make this a truly special school.",
        author: "Faculty & Volunteer Perspective"
    }
];
</script>

<template>
    <Head title="Admissions - The Silver Academy" />

    <MarketingLayout>
        <!-- Hero Section (Edit 15: admissions header image) -->
        <section class="relative h-[400px] overflow-hidden">
            <div class="absolute inset-0">
                <img 
                    src="/img/graphics/admissions/websilver-admissions.png" 
                    alt="Silver Academy community" 
                    class="w-full h-full object-cover opacity-100" 
                />
            </div>
        </section>

        <!-- Intro Section -->
        <section class="py-20 bg-white text-center">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="max-w-4xl mx-auto">
                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl text-slate-800 leading-none tracking-tight mb-6">
                        Nurturing Confidence, Character,<br>and a Love of Learning
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-600 max-w-3xl mx-auto leading-relaxed">
                        Join a small, values-driven school where students thrive academically, socially, and spiritually from Pre-K through 8th grade.
                    </p>
                    <div class="mt-8 flex justify-center gap-4">
                        <Link href="#process" class="bg-accent-500 text-white px-8 py-3 rounded font-semibold uppercase tracking-wider hover:bg-accent-600 transition-colors">
                            How to Apply
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Silver - Interactive Panels: tiles fully expand on hover (Edit 5) -->
        <section class="w-full flex flex-col md:flex-row bg-slate-900 overflow-hidden min-h-[980px] md:min-h-0 md:h-[600px]">
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
                <!-- Background Image -->
                <div class="absolute inset-0 transition-transform duration-700 ease-out"
                     :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'scale-100' : 'scale-110 grayscale group-hover:grayscale-0'">
                    <img :src="panel.image" :alt="panel.title" class="w-full h-full object-cover" />
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-brand-900/40 transition-opacity duration-300"
                         :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'opacity-20' : 'opacity-60 group-hover:opacity-40'"></div>
                     
                     <!-- Blue overlay for inactive tabs -->
                     <div class="absolute inset-0 bg-brand-600/80 mix-blend-multiply transition-opacity duration-300"
                          :class="(hoveredPanel !== null ? hoveredPanel === index : activePanel === index) ? 'opacity-0' : 'opacity-100'"></div>
                </div>

                <!-- Title: horizontal at top on mobile, vertical on md+ (Inactive State) -->
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
                            {{ panel.title === 'Accessible Education' ? 'Contact us to learn more' : 'Learn More' }}
                            <svg class="ml-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why A Jewish Day School -->
        <section id="why-jewish-day-school" class="py-24 bg-slate-50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="text-brand-500 font-bold tracking-wider uppercase mb-2 block">Why A Jewish Day School</span>
                        <h2 class="font-serif text-3xl sm:text-4xl text-slate-800 font-bold mb-6">
                            Learning, Character, and Community for Every Student
                        </h2>
                        <p class="text-lg text-slate-600 mb-6">
                            At The Silver Academy, Jewish values guide everything we do, but the benefits extend to all students. Our integrated approach to learning combines rigorous academics, character development, and a supportive, inclusive community.
                        </p>
                        <p class="text-lg text-slate-600 mb-8">
                            Children of all backgrounds gain the tools, confidence, and resilience they need to succeed in school and in life.
                        </p>
                        
                        <div class="space-y-6 mb-8">
                            <div>
                                <h4 class="font-bold text-slate-800 mb-1">Integrated Academic Excellence</h4>
                                <p class="text-slate-600 text-sm">A connected general and Judaic curriculum, small class sizes, and hands-on learning ensure every student is supported, challenged, and engaged.</p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 mb-1">Character & Values That Matter</h4>
                                <p class="text-slate-600 text-sm">Jewish values and restorative practices foster empathy, integrity, accountability, and emotional intelligence, skills that benefit all children.</p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 mb-1">An Inclusive, Supportive Community</h4>
                                <p class="text-slate-600 text-sm">A welcoming environment for families of all backgrounds, built on strong partnerships, mentorship, and meaningful relationships.</p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 mb-1">Prepared for What's Next</h4>
                                <p class="text-slate-600 text-sm">Students graduate with confidence, resilience, leadership skills, and a love of learning—ready for high school and beyond.</p>
                            </div>
                        </div>
                        
                        <a 
                            href="https://calendar.app.google/Y5NrAjA9RooWgwZ98"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block bg-accent-500 text-white px-8 py-3 rounded font-semibold uppercase tracking-wider hover:bg-accent-600 transition-colors"
                        >
                            Schedule A Visit
                        </a>
                    </div>
                    <div class="relative h-full min-h-[500px]">
                        <div class="absolute inset-0 bg-brand-500 rounded-2xl transform rotate-3"></div>
                        <img 
                            src="/img/graphics/admissions/web-silveracademyWhy A Jewish Day School(2).jpg" 
                            alt="Students learning" 
                            class="absolute inset-0 w-full h-full object-cover rounded-2xl transform -rotate-3 hover:rotate-0 transition-transform duration-500 shadow-xl"
                            style="object-position: center 25%;"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Virtual Conversation Video (Edit 18) -->
        <section class="w-full py-16 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="font-serif text-3xl sm:text-4xl text-slate-800 tracking-tight mb-2">
                        A Virtual Conversation with the Head of School
                    </h2>
                    <p class="text-lg text-slate-600">
                        Why Harrisburg and The Silver Academy are a good fit for your family
                    </p>
                </div>
                <div class="aspect-video relative overflow-hidden rounded-2xl shadow-xl max-w-4xl mx-auto">
                    <iframe
                        class="w-full h-full"
                        src="https://www.youtube.com/embed/CHzoyAzwsxo?rel=0&modestbranding=1"
                        title="A Virtual Conversation with the Head of School - The Silver Academy"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </section>

        <!-- Admissions Process -->
        <section id="process" class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="font-serif text-3xl text-slate-800 font-bold mb-4">Our Admissions Process</h2>
                    <p class="text-lg text-slate-600">We strive to make the admissions experience welcoming, transparent, and straightforward.</p>
                </div>

                <div class="max-w-4xl mx-auto">
                    <div class="relative">
                        <!-- Line -->
                        <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-brand-100 hidden md:block"></div>
                        
                        <div class="space-y-12">
                            <div v-for="(step, index) in steps" :key="index" class="relative flex gap-8 items-start">
                                <div class="flex-shrink-0 w-16 h-16 bg-brand-500 rounded-full flex items-center justify-center text-white font-bold text-xl z-10 border-4 border-white shadow-md">
                                    {{ index + 1 }}
                                </div>
                                <div class="pt-3">
                                    <h3 class="text-xl font-bold text-slate-800 mb-2">{{ step.title }}</h3>
                                    <p class="text-slate-600 leading-relaxed">{{ step.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-16 text-center">
                        <div class="flex flex-wrap justify-center gap-4">
                            <a href="https://calendar.app.google/Y5NrAjA9RooWgwZ98" target="_blank" rel="noopener noreferrer" class="inline-block bg-accent-500 text-white px-10 py-4 rounded font-bold uppercase tracking-wider hover:bg-accent-600 transition-colors shadow-lg hover:shadow-xl">
                                Schedule a Visit
                            </a>
                            <a href="https://app.simpletuitionsolutions.org/register?sc=21760" target="_blank" rel="noopener noreferrer" class="inline-block bg-white text-brand-600 px-10 py-4 rounded font-bold uppercase tracking-wider hover:bg-slate-50 transition-colors shadow-lg border-2 border-brand-200">
                                Apply Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tuition Section (hidden for now) -->
        <section v-if="false" id="tuition" class="py-24 bg-slate-50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="font-serif text-3xl text-slate-800 font-bold mb-4">Tuition & Financial Support</h2>
                    <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                        We are committed to making an excellent education accessible for all families.
                        Finances are never a barrier to a high-quality Jewish education.
                    </p>
                </div>

                <div class="max-w-4xl mx-auto">
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
                                <div class="text-white/90 text-base font-medium">of students receive financial aid</div>
                            </div>
                            <div>
                                <div class="text-5xl md:text-6xl font-bold mb-3">60%</div>
                                <div class="text-white/90 text-base font-medium">Average scholarship award</div>
                            </div>
                            <div>
                                <div class="text-5xl md:text-6xl font-bold mb-3">$17,105</div>
                                <div class="text-white/90 text-base font-medium">Average scholarship amount</div>
                            </div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-6 text-center">
                            <p class="text-white text-sm uppercase tracking-wider mb-2">Average Tuition Paid</p>
                            <p class="text-white text-4xl font-bold">$8,178</p>
                        </div>
                    </div>

                    <!-- Full-Cost Tuition - De-emphasized -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-8">
                        <h3 class="font-serif text-lg text-slate-600 mb-4 text-center">Full-Cost Tuition (Before Financial Aid)</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div 
                                v-for="item in tuition" 
                                :key="item.grade"
                                class="border-b border-slate-100 pb-3"
                            >
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500 text-sm">{{ item.grade }}</span>
                                    <span class="text-slate-400 font-medium text-sm">{{ item.price }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EITC & OSTC Programs -->
                    <div class="bg-white p-8 rounded-2xl border-l-4 border-accent-500 shadow-sm">
                        <h4 class="font-bold text-slate-800 mb-2">EITC & OSTC Programs</h4>
                        <p class="text-slate-600 text-sm mb-4">Redirect PA state taxes to support scholarships.</p>
                        <Link href="/get-involved/eitc-individual" class="text-brand-600 font-semibold hover:text-brand-700 text-sm flex items-center">
                            Learn more about EITC
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Alumni & Testimonials -->
        <section id="testimonials" class="py-24 bg-brand-500 text-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4">Celebrating Generations of Lifelong Learners</h2>
                    <p class="text-white/90 max-w-3xl mx-auto">
                        For more than 80 years, The Silver Academy has nurtured students who thrive academically, socially, and spiritually.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="(testimonial, index) in testimonials" :key="index" class="bg-white p-8 rounded-xl relative shadow-lg hover:shadow-xl hover:-translate-y-2 transition-all duration-300 cursor-pointer">
                        <svg class="w-10 h-10 text-brand-500 mb-4 opacity-50" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                        <p class="text-slate-700 mb-6 italic leading-relaxed">"{{ testimonial.quote }}"</p>
                        <div class="text-brand-600 font-bold text-sm tracking-wide uppercase">{{ testimonial.author }}</div>
                    </div>
                </div>

                <div class="mt-16 text-center">
                    <div class="bg-white p-8 rounded-2xl max-w-4xl mx-auto shadow-lg">
                        <div class="flex flex-col md:flex-row items-center gap-8">
                            <img src="/img/graphics/admissions/web-silveracademyAlexandra.jpg" alt="Alexandra" class="w-24 h-24 rounded-full object-cover border-4 border-accent-500" />
                            <div class="text-left flex-1">
                                <h3 class="text-xl font-bold text-slate-800 mb-2">Student Story: Alexandra</h3>
                                <p class="text-slate-600 text-sm leading-relaxed">
                                    Alexandra joined The Silver Academy as the youngest of four siblings. Initially shy, she thrived thanks to nurturing teachers. Now a first grader, Alexandra is a top reader, confident in her abilities, and proud of her Jewish identity.
                                </p>
                            </div>
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

