<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import SettingsLayout from './SettingsLayout.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import KeyOutline from 'vue-material-design-icons/KeyOutline.vue';
import DownloadOutline from 'vue-material-design-icons/DownloadOutline.vue';
import HeartBrokenOutline from 'vue-material-design-icons/HeartBrokenOutline.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';
import Close from 'vue-material-design-icons/Close.vue';
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue';
import AlertCircleOutline from 'vue-material-design-icons/AlertCircleOutline.vue';

const props = defineProps({
    accountInfo: Object,
    userSettings: Object,
});

// Modals state
const showInfoModal = ref(false);
const showPasswordModal = ref(false);
const showDeactivateModal = ref(false);
const showExportSuccess = ref(false);

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const passwordUpdated = ref(false);

const updatePassword = () => {
    passwordUpdated.value = false;
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordUpdated.value = true;
            setTimeout(() => {
                showPasswordModal.value = false;
                passwordUpdated.value = false;
            }, 1800);
        },
        onError: () => {
            passwordForm.reset('password', 'password_confirmation');
        },
    });
};

// Deactivate Form
const deactivateForm = useForm({
    password: '',
});

const deactivateAccount = () => {
    deactivateForm.post(route('settings.deactivate'), {
        preserveScroll: true,
        onError: () => {
            deactivateForm.reset('password');
        },
    });
};

// Export Archive
const downloadArchive = () => {
    window.location.href = route('settings.export');
    showExportSuccess.value = true;
    setTimeout(() => {
        showExportSuccess.value = false;
    }, 4000);
};
</script>

<template>
    <Head title="Your Account / Settings" />

    <SettingsLayout>
        <!-- Top Sticky Header -->
        <div class="sticky top-0 z-10 bg-white/80 dark:bg-black/80 backdrop-blur-md px-6 pt-3 pb-3 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Your Account
            </h2>
        </div>

        <!-- Description Subtitle -->
        <div class="px-6 py-4 text-[13px] text-gray-500 border-b border-gray-100 dark:border-gray-900 leading-relaxed">
            See information about your account, download an archive of your data, or learn about your account deactivation options
        </div>

        <!-- Setting Rows List -->
        <div class="divide-y divide-gray-100 dark:divide-gray-900">
            <!-- 1. Account Information -->
            <button
                type="button"
                @click="showInfoModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <AccountOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Account information
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            See your account information like your phone number and email address.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 2. Change your password -->
            <button
                type="button"
                @click="showPasswordModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <KeyOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Change your password
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Change your password at any time.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 3. Download an archive of your data -->
            <button
                type="button"
                @click="downloadArchive"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <DownloadOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Download an archive of your data
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Get insights into the type of information stored for your account.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 4. Deactivate your account -->
            <button
                type="button"
                @click="showDeactivateModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-red-50/50 dark:hover:bg-red-950/20 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <HeartBrokenOutline :size="24" class="text-gray-500 group-hover:text-red-500 transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition">
                            Deactivate your account
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Find out how you can deactivate your account.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-red-500 flex-shrink-0 ml-4" />
            </button>
        </div>

        <!-- Download Alert Toast -->
        <div
            v-if="showExportSuccess"
            class="fixed bottom-6 right-6 bg-[#1C9CEF] text-white px-5 py-3 rounded-full shadow-2xl flex items-center space-x-3 z-50 animate-bounce"
        >
            <CheckCircle :size="22" />
            <span class="text-sm font-bold">Data archive download started!</span>
        </div>

        <!-- MODAL 1: Account Information -->
        <div v-if="showInfoModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showInfoModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Account Information</h3>
                    <button @click="showInfoModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>
                <div class="mt-4 space-y-4 text-sm">
                    <div class="p-3 bg-gray-50 dark:bg-black/50 rounded-xl">
                        <span class="text-xs text-gray-500 uppercase tracking-wider block">Username</span>
                        <span class="font-semibold text-gray-900 dark:text-white text-base">@{{ accountInfo.username }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-black/50 rounded-xl">
                        <span class="text-xs text-gray-500 uppercase tracking-wider block">Display Name</span>
                        <span class="font-semibold text-gray-900 dark:text-white text-base">{{ accountInfo.name }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-black/50 rounded-xl">
                        <span class="text-xs text-gray-500 uppercase tracking-wider block">Email Address</span>
                        <span class="font-semibold text-gray-900 dark:text-white text-base">{{ accountInfo.email }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-black/50 rounded-xl">
                        <span class="text-xs text-gray-500 uppercase tracking-wider block">Date Joined</span>
                        <span class="font-semibold text-gray-900 dark:text-white text-base">{{ accountInfo.joined }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-black/50 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-500 uppercase tracking-wider block">Account Role</span>
                            <span class="font-semibold capitalize text-gray-900 dark:text-white text-base">{{ accountInfo.role || 'User' }}</span>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300">
                            Active
                        </span>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button
                        @click="showInfoModal = false"
                        class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm rounded-full hover:bg-gray-800 dark:hover:bg-gray-200 transition"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 2: Change Password -->
        <div v-if="showPasswordModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showPasswordModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Change your password</h3>
                    <button @click="showPasswordModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>

                <div v-if="passwordUpdated" class="mt-4 p-4 bg-green-100 dark:bg-green-950/60 border border-green-300 dark:border-green-800 rounded-xl flex items-center space-x-3 text-green-800 dark:text-green-200">
                    <CheckCircle :size="22" />
                    <span class="text-sm font-semibold">Password changed successfully!</span>
                </div>

                <form v-else @submit.prevent="updatePassword" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Current Password</label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            required
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:border-[#1C9CEF]"
                        />
                        <p v-if="passwordForm.errors.current_password" class="text-xs text-red-500 mt-1">
                            {{ passwordForm.errors.current_password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">New Password</label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            required
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:border-[#1C9CEF]"
                        />
                        <p v-if="passwordForm.errors.password" class="text-xs text-red-500 mt-1">
                            {{ passwordForm.errors.password }}
                        </p>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Must be 8-16 characters with at least 1 uppercase, 1 lowercase and 1 special symbol.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Confirm New Password</label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            required
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:border-[#1C9CEF]"
                        />
                        <p v-if="passwordForm.errors.password_confirmation" class="text-xs text-red-500 mt-1">
                            {{ passwordForm.errors.password_confirmation }}
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 pt-2">
                        <button
                            type="button"
                            @click="showPasswordModal = false"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-full hover:bg-gray-100 dark:hover:bg-gray-900 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="px-5 py-2 bg-[#1C9CEF] hover:bg-[#1A8CD8] disabled:opacity-50 text-white font-bold text-sm rounded-full transition shadow"
                        >
                            {{ passwordForm.processing ? 'Saving...' : 'Save Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: Deactivate Account -->
        <div v-if="showDeactivateModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showDeactivateModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center space-x-3 pb-3 border-b border-gray-200 dark:border-gray-800">
                    <AlertCircleOutline :size="26" class="text-red-500 flex-shrink-0" />
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">This will deactivate your account</h3>
                        <p class="text-xs text-gray-500">@{{ accountInfo.username }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    <p>
                        You are about to start the process of deactivating your X account. Your display name, @username, and public profile will no longer be viewable on X.
                    </p>
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800/40 rounded-xl text-xs text-yellow-800 dark:text-yellow-200">
                        <strong>What else you should know:</strong> You can restore your X account if it was accidentally or wrongfully deactivated for up to 30 days after deactivation by simply logging back in.
                    </div>
                </div>

                <form @submit.prevent="deactivateAccount" class="mt-5">
                    <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Confirm your password to proceed</label>
                    <input
                        v-model="deactivateForm.password"
                        type="password"
                        required
                        placeholder="Enter your password"
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:border-red-500"
                    />
                    <p v-if="deactivateForm.errors.password" class="text-xs text-red-500 mt-1">
                        {{ deactivateForm.errors.password }}
                    </p>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showDeactivateModal = false"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-full hover:bg-gray-100 dark:hover:bg-gray-900 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="deactivateForm.processing"
                            class="px-5 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-bold text-sm rounded-full transition shadow"
                        >
                            {{ deactivateForm.processing ? 'Deactivating...' : 'Deactivate' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SettingsLayout>
</template>

