<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import SettingsLayout from './SettingsLayout.vue';
import AccountMultipleOutline from 'vue-material-design-icons/AccountMultipleOutline.vue';
import PencilOutline from 'vue-material-design-icons/PencilOutline.vue';
import CardTextOutline from 'vue-material-design-icons/CardTextOutline.vue';
import VolumeOff from 'vue-material-design-icons/VolumeOff.vue';
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue';
import MicrophoneOutline from 'vue-material-design-icons/MicrophoneOutline.vue';
import AccountSearchOutline from 'vue-material-design-icons/AccountSearchOutline.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';
import Close from 'vue-material-design-icons/Close.vue';
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue';

const props = defineProps({
    userSettings: Object,
    blockedCount: Number,
    mutedCount: Number,
});

// Modals
const showAudienceModal = ref(false);
const showMuteBlockModal = ref(false);
const showChatModal = ref(false);
const showSpacesModal = ref(false);
const activeMuteBlockTab = ref('blocked'); // 'blocked' | 'muted'
const infoModal = ref({ open: false, title: '', desc: '' });
const openInfoModal = (title, desc) => {
    infoModal.value = { open: true, title, desc };
};

// Block / Mute lists
const blockedList = ref([]);
const mutedList = ref([]);
const isLoadingList = ref(false);

const openMuteBlock = async (tab = 'blocked') => {
    activeMuteBlockTab.value = tab;
    showMuteBlockModal.value = true;
    isLoadingList.value = true;
    try {
        const [resBlocked, resMuted] = await Promise.all([
            axios.get(route('settings.privacy.blocked')),
            axios.get(route('settings.privacy.muted')),
        ]);
        blockedList.value = resBlocked.data;
        mutedList.value = resMuted.data;
    } catch (e) {
        console.error('Failed to load blocked/muted list', e);
    } finally {
        isLoadingList.value = false;
    }
};

const unblockUser = async (user) => {
    try {
        await axios.post(route('users.block', user.id));
        blockedList.value = blockedList.value.filter(u => u.id !== user.id);
        router.reload({ only: ['blockedCount'] });
    } catch (e) {
        console.error('Failed to unblock user', e);
    }
};

const unmuteUser = async (user) => {
    try {
        await axios.post(route('users.mute', user.id));
        mutedList.value = mutedList.value.filter(u => u.id !== user.id);
        router.reload({ only: ['mutedCount'] });
    } catch (e) {
        console.error('Failed to unmute user', e);
    }
};

// Chat Settings Form
const chatPermission = ref(props.userSettings?.allow_dms_from || 'everyone');
const protectPosts = ref(props.userSettings?.protect_posts || false);
const isSaving = ref(false);
const saveSuccess = ref(false);

const savePrivacySettings = (key, value) => {
    isSaving.value = true;
    saveSuccess.value = false;

    const payload = {};
    if (key === 'allow_dms_from') {
        chatPermission.value = value;
        payload.allow_dms_from = value;
    } else if (key === 'protect_posts') {
        protectPosts.value = value;
        payload.protect_posts = value;
    }

    router.patch(route('settings.privacy.update'), payload, {
        preserveScroll: true,
        onSuccess: () => {
            isSaving.value = false;
            saveSuccess.value = true;
            setTimeout(() => {
                saveSuccess.value = false;
            }, 2000);
        },
        onError: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <Head title="Privacy and safety / Settings" />

    <SettingsLayout>
        <!-- Top Sticky Header -->
        <div class="sticky top-0 z-10 bg-white/80 dark:bg-black/80 backdrop-blur-md px-6 pt-3 pb-3 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Privacy and safety
            </h2>
        </div>

        <!-- Description Subtitle -->
        <div class="px-6 py-4 text-[13px] text-gray-500 border-b border-gray-100 dark:border-gray-900 leading-relaxed">
            Manage what information you see and share on X.
        </div>

        <!-- Section Heading: Your X activity -->
        <div class="px-6 pt-5 pb-2 text-[17px] font-extrabold text-gray-900 dark:text-white">
            Your X activity
        </div>

        <!-- Setting Rows List -->
        <div class="divide-y divide-gray-100 dark:divide-gray-900">
            <!-- 1. Audience, media and tagging -->
            <button
                type="button"
                @click="showAudienceModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <AccountMultipleOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Audience, media and tagging
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Manage what information you allow other people on X to see.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 2. Your posts -->
            <button
                type="button"
                @click="openInfoModal('Your posts', 'Manage sensitive media warnings, post geolocation tagging, and tweet interaction permissions.')"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <PencilOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Your posts
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Manage the information associated with your posts.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 3. Content you see -->
            <button
                type="button"
                @click="openInfoModal('Content you see', 'Decide what you see on X based on your topics, interests, and explore search preferences.')"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <CardTextOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Content you see
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Decide what you see on X based on your preferences like interests.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 4. Mute and block -->
            <button
                type="button"
                @click="openMuteBlock('blocked')"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <VolumeOff :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span>Mute and block</span>
                            <span v-if="blockedCount > 0 || mutedCount > 0" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                {{ blockedCount + mutedCount }}
                            </span>
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Manage the accounts, words, and notifications that you've muted or blocked.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 5. Chat -->
            <button
                type="button"
                @click="showChatModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <ChatOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Chat
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Manage who can message you directly. Current: <span class="text-[#1C9CEF] font-semibold capitalize">{{ chatPermission }}</span>
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 6. Spaces -->
            <button
                type="button"
                @click="showSpacesModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <MicrophoneOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Spaces
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Manage who can see your Spaces listening activity.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 7. Discoverability and contacts -->
            <button
                type="button"
                @click="openInfoModal('Discoverability and contacts', 'Control whether people who have your email address or phone number can find you on X, and manage imported contacts.')"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <AccountSearchOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Discoverability and contacts
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Control your discoverability settings and manage contacts you've imported.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>
        </div>

        <!-- Success Toast -->
        <div
            v-if="saveSuccess"
            class="fixed bottom-6 right-6 bg-[#1C9CEF] text-white px-5 py-3 rounded-full shadow-2xl flex items-center space-x-3 z-50 animate-bounce"
        >
            <CheckCircle :size="22" />
            <span class="text-sm font-bold">Preferences saved!</span>
        </div>

        <!-- MODAL: Audience & Tagging (Protect Posts) -->
        <div v-if="showAudienceModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showAudienceModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Audience, media and tagging</h3>
                    <button @click="showAudienceModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>
                <div class="mt-5 space-y-4">
                    <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50 dark:bg-black/50">
                        <div class="pr-4">
                            <div class="font-bold text-sm text-gray-900 dark:text-white">Protect your posts</div>
                            <div class="text-xs text-gray-500 mt-1">
                                When selected, your posts and other account information are only visible to people who follow you.
                            </div>
                        </div>
                        <input
                            type="checkbox"
                            :checked="protectPosts"
                            @change="savePrivacySettings('protect_posts', $event.target.checked)"
                            class="w-5 h-5 accent-[#1C9CEF] rounded cursor-pointer mt-1"
                        />
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="showAudienceModal = false" class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm rounded-full">
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Chat Direct Messages Permission -->
        <div v-if="showChatModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showChatModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Direct Messages</h3>
                    <button @click="showChatModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>

                <div class="mt-4 text-sm text-gray-500">
                    Control who is allowed to send you direct chat messages on X.
                </div>

                <div class="mt-4 space-y-3">
                    <label
                        @click="savePrivacySettings('allow_dms_from', 'everyone')"
                        :class="chatPermission === 'everyone' ? 'border-[#1C9CEF] bg-[#1C9CEF]/5' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50'"
                        class="flex items-start justify-between p-4 border rounded-xl cursor-pointer transition"
                    >
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">Everyone</div>
                            <div class="text-xs text-gray-500 mt-0.5">Let any user on X send you a direct message.</div>
                        </div>
                        <input type="radio" name="chat_permission" :checked="chatPermission === 'everyone'" class="accent-[#1C9CEF] mt-1" />
                    </label>

                    <label
                        @click="savePrivacySettings('allow_dms_from', 'following')"
                        :class="chatPermission === 'following' ? 'border-[#1C9CEF] bg-[#1C9CEF]/5' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50'"
                        class="flex items-start justify-between p-4 border rounded-xl cursor-pointer transition"
                    >
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">People you follow</div>
                            <div class="text-xs text-gray-500 mt-0.5">Only allow direct messages from accounts that you follow.</div>
                        </div>
                        <input type="radio" name="chat_permission" :checked="chatPermission === 'following'" class="accent-[#1C9CEF] mt-1" />
                    </label>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="showChatModal = false" class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm rounded-full">
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Mute and Block List -->
        <div v-if="showMuteBlockModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showMuteBlockModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800 max-h-[80vh] flex flex-col" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Mute and Block</h3>
                    <button @click="showMuteBlockModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-800 mt-2">
                    <button
                        @click="activeMuteBlockTab = 'blocked'"
                        :class="activeMuteBlockTab === 'blocked' ? 'border-[#1C9CEF] text-[#1C9CEF] font-bold' : 'border-transparent text-gray-500'"
                        class="flex-1 py-3 text-sm border-b-2 transition"
                    >
                        Blocked accounts ({{ blockedList.length }})
                    </button>
                    <button
                        @click="activeMuteBlockTab = 'muted'"
                        :class="activeMuteBlockTab === 'muted' ? 'border-[#1C9CEF] text-[#1C9CEF] font-bold' : 'border-transparent text-gray-500'"
                        class="flex-1 py-3 text-sm border-b-2 transition"
                    >
                        Muted accounts ({{ mutedList.length }})
                    </button>
                </div>

                <!-- List Content -->
                <div class="flex-1 overflow-y-auto py-3 divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-if="isLoadingList" class="p-8 text-center text-sm text-gray-500">
                        Loading accounts...
                    </div>

                    <!-- Blocked Tab List -->
                    <template v-else-if="activeMuteBlockTab === 'blocked'">
                        <div v-if="blockedList.length === 0" class="py-8 text-center text-sm text-gray-500">
                            You haven't blocked any accounts.
                        </div>
                        <div
                            v-for="u in blockedList"
                            :key="u.id"
                            class="py-3 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-3">
                                <img
                                    :src="u.avatar_path ? (u.avatar_path.startsWith('http') ? u.avatar_path : '/' + u.avatar_path) : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name) + '&background=1C9CEF&color=fff'"
                                    class="w-10 h-10 rounded-full object-cover"
                                />
                                <div>
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">{{ u.name }}</div>
                                    <div class="text-xs text-gray-500">@{{ u.username }}</div>
                                </div>
                            </div>
                            <button
                                @click="unblockUser(u)"
                                class="px-3 py-1 border border-red-500 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-full text-xs font-bold transition"
                            >
                                Unblock
                            </button>
                        </div>
                    </template>

                    <!-- Muted Tab List -->
                    <template v-else-if="activeMuteBlockTab === 'muted'">
                        <div v-if="mutedList.length === 0" class="py-8 text-center text-sm text-gray-500">
                            You haven't muted any accounts.
                        </div>
                        <div
                            v-for="u in mutedList"
                            :key="u.id"
                            class="py-3 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-3">
                                <img
                                    :src="u.avatar_path ? (u.avatar_path.startsWith('http') ? u.avatar_path : '/' + u.avatar_path) : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name) + '&background=1C9CEF&color=fff'"
                                    class="w-10 h-10 rounded-full object-cover"
                                />
                                <div>
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">{{ u.name }}</div>
                                    <div class="text-xs text-gray-500">@{{ u.username }}</div>
                                </div>
                            </div>
                            <button
                                @click="unmuteUser(u)"
                                class="px-3 py-1 border border-gray-400 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full text-xs font-bold transition"
                            >
                                Unmute
                            </button>
                        </div>
                    </template>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-800 flex justify-end">
                    <button @click="showMuteBlockModal = false" class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm rounded-full">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Spaces Feature Notice -->
        <div v-if="showSpacesModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showSpacesModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-sm p-6 shadow-2xl border border-gray-200 dark:border-gray-800 text-center" @click.stop>
                <MicrophoneOutline :size="40" class="text-[#1C9CEF] mx-auto mb-3" />
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Spaces on X</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    Spaces audio broadcasting is currently available on mobile apps. Web listening features will arrive in an upcoming release.
                </p>
                <button @click="showSpacesModal = false" class="mt-5 w-full py-2 bg-[#1C9CEF] text-white font-bold text-sm rounded-full">
                    Got it
                </button>
            </div>
        </div>
        <!-- MODAL: Setting Info Detail -->
        <div v-if="infoModal.open" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="infoModal.open = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-sm p-6 shadow-2xl border border-gray-200 dark:border-gray-800 text-center" @click.stop>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ infoModal.title }}</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    {{ infoModal.desc }}
                </p>
                <button @click="infoModal.open = false" class="mt-5 w-full py-2 bg-[#1C9CEF] text-white font-bold text-sm rounded-full">
                    Done
                </button>
            </div>
        </div>
    </SettingsLayout>
</template>

