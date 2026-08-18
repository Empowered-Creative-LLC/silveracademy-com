<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    sandboxPassword: {
        type: String,
        default: null,
    },
    sandboxEmail: {
        type: String,
        default: null,
    },
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    email: '',
    code: '',
});

const studentHint = ref(null);
const validating = ref(false);

const page = usePage();
const inSandbox = computed(() => Boolean(page.props.portal?.sandbox_enabled));
const labPassword = computed(() => page.props.portal?.sandbox_password || 'Sandbox123!');
const successFromQuery = computed(() => new URLSearchParams(window.location.search).get('created') === '1');
const shownPassword = computed(
    () => props.sandboxPassword || page.props.flash?.sandbox_password || (inSandbox.value && successFromQuery.value ? labPassword.value : ''),
);
const shownEmail = computed(
    () => props.sandboxEmail || page.props.flash?.sandbox_email || new URLSearchParams(window.location.search).get('email') || form.email,
);
const statusMessage = computed(() => props.status || page.props.flash?.status || '');
const success = computed(() => Boolean(shownPassword.value || statusMessage.value || successFromQuery.value));

const hintText = computed(() => {
    if (!studentHint.value) return null;
    const h = studentHint.value;
    const grade = h.grade?.name ?? '';
    const namePart = h.last_initial ? `${h.first_name} ${h.last_initial}.` : h.first_name;
    return grade ? `Code for ${namePart} (${grade})` : `Code for ${namePart}`;
});

const validateCode = async () => {
    const c = (form.code || '').trim();
    if (!c) {
        studentHint.value = null;
        return;
    }
    validating.value = true;
    studentHint.value = null;
    try {
        const { data } = await axios.post('/api/parent-code/validate', { code: c });
        if (data.valid && data.student_hint) {
            studentHint.value = data.student_hint;
        }
    } catch {
        studentHint.value = null;
    } finally {
        validating.value = false;
    }
};

const submit = () => {
    form.post('/parent/signup', { preserveState: false });
};
</script>

<template>
    <Head title="Parent signup" />

    <GuestLayout>
        <div v-if="success" class="text-center space-y-4">
            <h1 class="text-2xl font-bold text-slate-900">{{ shownPassword ? 'Account created' : 'Check your email' }}</h1>
            <template v-if="shownPassword">
                <p class="text-slate-600">
                    The local test lab does not send email. Use these credentials to log in.
                </p>
                <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-left space-y-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</p>
                        <p class="mt-1 font-mono text-slate-900 break-all">{{ shownEmail }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Password</p>
                        <p class="mt-1 font-mono text-lg text-slate-900 break-all">{{ shownPassword }}</p>
                    </div>
                </div>
            </template>
            <p v-else class="text-slate-600">
                {{ statusMessage || "We've sent your password and a link to log in. Use them to sign in to the Family Portal." }}
            </p>
            <Link
                href="/login"
                class="inline-block rounded-lg bg-brand-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-brand-500"
            >
                Go to login
            </Link>
        </div>

        <template v-else>
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Create your account</h1>
                <p class="text-slate-600 mt-2">Sign up with your email and Parent Code</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <p v-if="inSandbox" class="text-sm text-amber-900 bg-amber-50 border border-amber-200 p-3 rounded-lg">
                    Test lab: new accounts use password <span class="font-mono font-semibold">{{ labPassword }}</span>. It will also be shown after you create the account (no email is sent).
                </p>
                <p v-if="page.props.flash?.error" class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                    {{ page.props.flash.error }}
                </p>
                <p v-if="form.errors.email || form.errors.code" class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                    {{ form.errors.email || form.errors.code }}
                </p>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                        Email address
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                        placeholder="you@example.com"
                    />
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-slate-700 mb-2">
                        Parent Code
                    </label>
                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        required
                        autocomplete="off"
                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border font-mono uppercase"
                        placeholder="Enter the code from the school"
                        @blur="validateCode"
                    />
                    <p v-if="validating" class="mt-2 text-sm text-slate-500">Checking code...</p>
                    <p v-else-if="hintText" class="mt-2 text-sm text-green-700">
                        {{ hintText }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-brand-600 px-6 py-4 text-base font-semibold text-white shadow-sm hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
                >
                    <span v-if="!form.processing">Create my account</span>
                    <span v-else>Creating account...</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-slate-600">
                    Already have an account?
                    <Link href="/login" class="font-medium text-brand-600 hover:text-brand-500">
                        Log in
                    </Link>
                </p>
            </div>
        </template>
    </GuestLayout>
</template>
