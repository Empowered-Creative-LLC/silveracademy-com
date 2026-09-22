<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

const page = usePage();
const portalComingSoon = computed(() => page.props.portal?.coming_soon);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const firstValidationError = computed(() => {
    const err = form.errors;
    const key = err && Object.keys(err).length ? Object.keys(err)[0] : null;
    return key ? form.errors[key] : '';
});

const genericError = ref('');
const flashError = computed(() => page.props.flash?.error || '');

const submit = () => {
    genericError.value = '';
    form.post('/login', {
        onFinish: () => form.reset('password'),
        onError: (errors) => {
            genericError.value = typeof errors === 'object' && Object.keys(errors).length
                ? Object.values(errors)[0]
                : 'Something went wrong. Please try again.';
        },
    });
};
</script>

<template>
    <Head title="Log in" />

    <GuestLayout>
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
            <p class="text-slate-600 mt-2">Sign in to access your parent portal</p>
        </div>

        <!-- Coming Soon Notice (optional, off while the portal is in testing) -->
        <div v-if="portalComingSoon" class="mb-6 p-4 bg-accent-50 border border-accent-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-accent-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-accent-900 mb-1">Family Portal Coming Soon</p>
                    <p class="text-sm text-accent-700">The Family Portal is currently under development. We're working hard to bring you this feature soon. Thank you for your patience!</p>
                </div>
            </div>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center">
            {{ status }}
        </div>

        <div v-if="genericError || firstValidationError || flashError" class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 text-center">
            {{ genericError || firstValidationError || flashError }}
        </div>

        <form @submit.prevent="submit" class="space-y-6" novalidate>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                    Email address
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                    :class="{ 'border-red-500': form.errors.email }"
                    placeholder="you@example.com"
                />
                <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                    Password
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-3 border"
                    :class="{ 'border-red-500': form.errors.password }"
                    placeholder="••••••••"
                />
                <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500"
                    />
                    <span class="ml-2 text-sm text-slate-600">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    href="/forgot-password"
                    class="text-sm text-brand-600 hover:text-brand-500"
                >
                    Forgot password?
                </Link>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-lg bg-brand-600 px-6 py-4 text-base font-semibold text-white shadow-sm hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
            >
                <span v-if="!form.processing">Sign in</span>
                <span v-else>Signing in...</span>
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <p class="text-sm text-slate-600">
                First time? Sign up with a
                <Link href="/parent/signup" class="font-medium text-brand-600 hover:text-brand-500">
                    Parent Code
                </Link>
            </p>
            <p class="text-sm text-slate-600">
                Don't have a code?
                <Link href="/register" class="font-medium text-brand-600 hover:text-brand-500">
                    Register for approval
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>






