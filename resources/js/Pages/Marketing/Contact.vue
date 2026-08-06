<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { 
    EnvelopeIcon, 
    PhoneIcon, 
    MapPinIcon 
} from '@heroicons/vue/24/outline';

const formTimestamp = ref(null);

onMounted(() => {
    // Set timestamp when form is loaded
    formTimestamp.value = Math.floor(Date.now() / 1000);
});

const form = useForm({
    parent_name: '',
    student_name: '',
    email: '',
    phone: '',
    grade_interest: '',
    school_year: '',
    how_heard: '',
    schedule_tour: '',
    message: '',
    subscribe: false,
    form_timestamp: null,
    website_url: '', // Honeypot field - should remain empty
});

const gradeOptions = [
    'Pre-K',
    'Kindergarten',
    '1st Grade',
    '2nd Grade',
    '3rd Grade',
    '4th Grade',
    '5th Grade',
    '6th Grade',
    '7th Grade',
    '8th Grade',
    'Undecided',
];

const schoolYearOptions = [
    '2025-2026',
    '2026-2027',
    '2027-2028',
    'Not sure',
];

const howHeardOptions = [
    'Website Search (Google, etc.)',
    'Facebook',
    'Instagram',
    'Referral – Current Family',
    'Referral – Alumni Family',
    'Referral – Staff Member',
    'Word of Mouth',
    'Synagogue or Community Organization',
    'Event or School Fair',
    'Printed Materials',
    'Direct Email',
    'Phone Call',
    'Walk-In',
    'Other',
];

const submit = () => {
    // Set the timestamp before submitting
    form.form_timestamp = formTimestamp.value;
    
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            // Reset timestamp for potential resubmission
            formTimestamp.value = Math.floor(Date.now() / 1000);
        },
    });
};
</script>

<template>
    <Head title="Contact Us" />

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="bg-brand-600 py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl text-white leading-tight tracking-tight">
                    Contact Us
                </h1>
                <p class="mt-4 text-lg text-brand-100 max-w-2xl mx-auto">
                    We'd love to hear from you. Reach out with questions or request more information about The Silver Academy.
                </p>
            </div>
        </section>

        <section class="py-16 sm:py-20 bg-slate-50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid lg:grid-cols-5 gap-12">
                    <!-- Contact Form (Left - Larger) -->
                    <div class="lg:col-span-3 bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
                        <h2 class="font-serif text-2xl text-slate-800 mb-2">Request More Information</h2>
                        <p class="text-slate-600 text-sm mb-6">Please complete this brief form and our admissions team will reach out with information, next steps, and an invitation to schedule a tour.</p>
                        
                        <!-- Success Message -->
                        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-800">{{ $page.props.flash.success }}</p>
                        </div>
                        
                        <!-- Error Message -->
                        <div v-if="$page.props.flash?.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-800">{{ $page.props.flash.error }}</p>
                        </div>
                        
                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Parent/Guardian Name (Edit 50: mandatory) -->
                            <div>
                                <label for="parent_name" class="block text-sm font-medium text-slate-700 mb-2">
                                    Parent/Guardian Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="parent_name"
                                    v-model="form.parent_name"
                                    type="text"
                                    required
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                                />
                            </div>

                            <!-- Student Name -->
                            <div>
                                <label for="student_name" class="block text-sm font-medium text-slate-700 mb-2">
                                    Student Name
                                </label>
                                <input
                                    id="student_name"
                                    v-model="form.student_name"
                                    type="text"
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                                />
                            </div>

                            <!-- Email (Edit 50: mandatory) -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                                    Parent/Guardian Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                                />
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">
                                    Parent/Guardian Phone Number
                                </label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                                />
                            </div>

                            <!-- Grade of Interest -->
                            <div>
                                <label for="grade_interest" class="block text-sm font-medium text-slate-700 mb-2">
                                    Grade of Interest
                                </label>
                                <select
                                    id="grade_interest"
                                    v-model="form.grade_interest"
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                                >
                                    <option value="">Choose</option>
                                    <option v-for="grade in gradeOptions" :key="grade" :value="grade">{{ grade }}</option>
                                </select>
                            </div>

                            <!-- School Year Interested In -->
                            <div>
                                <label for="school_year" class="block text-sm font-medium text-slate-700 mb-2">
                                    School Year Interested In
                                </label>
                                <select
                                    id="school_year"
                                    v-model="form.school_year"
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                                >
                                    <option value="">Choose</option>
                                    <option v-for="year in schoolYearOptions" :key="year" :value="year">{{ year }}</option>
                                </select>
                            </div>

                            <!-- How did you hear about us? -->
                            <div>
                                <label for="how_heard" class="block text-sm font-medium text-slate-700 mb-2">
                                    How did you hear about us?
                                </label>
                                <select
                                    id="how_heard"
                                    v-model="form.how_heard"
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                                >
                                    <option value="">Choose</option>
                                    <option v-for="option in howHeardOptions" :key="option" :value="option">{{ option }}</option>
                                </select>
                            </div>

                            <!-- Would you like to schedule a tour? -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-3">
                                    Would you like to schedule a tour?
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="form.schedule_tour"
                                            value="Yes, please send me the link."
                                            class="w-4 h-4 text-brand-600 border-slate-300 focus:ring-brand-500"
                                        />
                                        <span class="text-slate-700">Yes, please send me the link.</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="form.schedule_tour"
                                            value="Not yet — I'd like more information first."
                                            class="w-4 h-4 text-brand-600 border-slate-300 focus:ring-brand-500"
                                        />
                                        <span class="text-slate-700">Not yet — I'd like more information first.</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Questions or Anything You'd Like Us to Know -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-slate-700 mb-2">
                                    Questions or Anything You'd Like Us to Know
                                </label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="4"
                                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border resize-none"
                                    placeholder="Share any specific questions or details you'd like us to be aware of."
                                ></textarea>
                            </div>

                            <!-- Subscribe to email list -->
                            <div>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.subscribe"
                                        class="w-4 h-4 mt-1 text-brand-600 border-slate-300 rounded focus:ring-brand-500"
                                    />
                                    <span class="text-slate-700 text-sm">Yes, please add me to the email list to receive occasional updates from The Silver Academy.</span>
                                </label>
                            </div>

                            <!-- Honeypot field - hidden from users but visible to bots -->
                            <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
                                <label for="website_url">Website URL (leave blank)</label>
                                <input
                                    id="website_url"
                                    v-model="form.website_url"
                                    type="text"
                                    name="website_url"
                                    tabindex="-1"
                                    autocomplete="off"
                                />
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full rounded-lg bg-brand-600 px-6 py-4 text-base font-semibold text-white shadow-sm hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
                            >
                                {{ form.processing ? 'Submitting...' : 'Submit' }}
                            </button>
                        </form>
                    </div>

                    <!-- Contact Info (Right - Smaller) -->
                    <div class="lg:col-span-2">
                        <div class="sticky top-8">
                            <h2 class="font-serif text-2xl text-slate-800 mb-2">Information</h2>
                            <p class="text-slate-600 text-sm mb-8">
                                Have questions? Reach out directly or fill out the form.
                            </p>

                            <div class="space-y-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-brand-100 rounded-full flex items-center justify-center shrink-0">
                                        <EnvelopeIcon class="w-6 h-6 text-brand-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-sm">Email</h3>
                                        <a href="mailto:info@silveracademypa.org" class="text-brand-600 hover:text-brand-700 transition-colors">info@silveracademypa.org</a>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-brand-100 rounded-full flex items-center justify-center shrink-0">
                                        <PhoneIcon class="w-6 h-6 text-brand-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-sm">Phone</h3>
                                        <a href="tel:717-769-5870" class="text-brand-600 hover:text-brand-700 transition-colors">717-769-5870</a>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-brand-100 rounded-full flex items-center justify-center shrink-0">
                                        <MapPinIcon class="w-6 h-6 text-brand-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-sm">Physical Address</h3>
                                        <p class="text-slate-600">2637 N. Front St<br>Harrisburg, PA 17110</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-brand-100 rounded-full flex items-center justify-center shrink-0">
                                        <MapPinIcon class="w-6 h-6 text-brand-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-sm">Mailing Address</h3>
                                        <p class="text-slate-600">PO Box 60097<br>Harrisburg, PA 17106</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule a Tour CTA -->
                            <div class="mt-8 p-6 bg-brand-50 rounded-xl">
                                <h3 class="font-semibold text-slate-800 mb-2">Ready to Visit?</h3>
                                <p class="text-slate-600 text-sm mb-4">Schedule a tour to see our campus and meet our team.</p>
                                <a 
                                    href="https://calendar.app.google/Y5NrAjA9RooWgwZ98"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-block w-full text-center rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-accent-600 transition-colors"
                                >
                                    Schedule a Tour
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>

