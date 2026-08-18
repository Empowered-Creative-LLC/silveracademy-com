<script setup>
import { Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { BeakerIcon, ClipboardDocumentListIcon, KeyIcon } from '@heroicons/vue/24/outline';

defineProps({
    accounts: {
        type: Array,
        default: () => [],
    },
    checklist: {
        type: Array,
        default: () => [],
    },
    signupCodes: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Portal Test Lab" />

    <PortalLayout>
        <template #header>
            Portal Test Lab
        </template>

        <div class="space-y-8">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                <div class="flex items-start gap-3">
                    <BeakerIcon class="h-6 w-6 text-amber-700 mt-0.5" />
                    <div>
                        <p class="font-semibold text-amber-900">Local testing only</p>
                        <p class="mt-1 text-sm text-amber-800">
                            These accounts exist so you can walk through parent, staff, and admin flows
                            before anything is pushed live. They are not created in production.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center gap-2">
                    <KeyIcon class="h-5 w-5 text-slate-500" />
                    <h2 class="text-lg font-serif font-semibold text-slate-900">Test accounts</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <div v-for="account in accounts" :key="account.email" class="px-6 py-4">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="font-semibold text-slate-900">{{ account.role }} — {{ account.name }}</p>
                            <p class="text-sm font-mono text-slate-700">{{ account.email }}</p>
                        </div>
                        <p class="mt-1 text-sm font-mono text-slate-600">Password: {{ account.password }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ account.notes }}</p>
                    </div>
                </div>
            </div>

            <div v-if="signupCodes.length" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-serif font-semibold text-slate-900">New parent signup</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Sign out, then open the signup page and use this unused student code.
                        After you create the account, the page will show the password
                        <span class="font-mono">Sandbox123!</span>
                        (the test lab does not send email).
                    </p>
                </div>
                <div v-for="row in signupCodes" :key="row.name" class="px-6 py-4 space-y-2">
                    <p class="font-semibold text-slate-900">{{ row.name }} <span class="font-normal text-slate-500">({{ row.grade }})</span></p>
                    <p class="text-sm text-slate-600">Signup URL: <a :href="row.signup_url" class="font-medium text-brand-600 hover:text-brand-500">{{ row.signup_url }}</a></p>
                    <p class="text-sm text-slate-600">Suggested email: <span class="font-mono">newparent@sandbox.silver.test</span></p>
                    <p class="text-sm text-slate-600">Parent code: <span class="font-mono text-base text-slate-900">{{ row.code }}</span></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center gap-2">
                    <ClipboardDocumentListIcon class="h-5 w-5 text-slate-500" />
                    <h2 class="text-lg font-serif font-semibold text-slate-900">Test checklist</h2>
                </div>
                <ol class="divide-y divide-slate-100">
                    <li v-for="(item, index) in checklist" :key="index" class="px-6 py-3 text-sm text-slate-700 flex gap-3">
                        <span class="font-semibold text-slate-400">{{ index + 1 }}.</span>
                        <span>{{ item }}</span>
                    </li>
                </ol>
            </div>
        </div>
    </PortalLayout>
</template>
