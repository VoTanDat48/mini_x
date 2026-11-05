<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import SettingsLayout from './SettingsLayout.vue';
import Tune from 'vue-material-design-icons/Tune.vue';
import Cellphone from 'vue-material-design-icons/Cellphone.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';
import Close from 'vue-material-design-icons/Close.vue';
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue';
import BellOutline from 'vue-material-design-icons/BellOutline.vue';

const props = defineProps({
    userSettings: Object,
});

const showFiltersModal = ref(false);
const showPreferencesModal = ref(false);
const isSaving = ref(false);
const saveSuccess = ref(false);

// State
const qualityFilter = ref(props.userSettings?.quality_filter ?? true);
const notifyLikes = ref(props.userSettings?.notify_likes ?? true);
const notifyRetweets = ref(props.userSettings?.notify_retweets ?? true);
const notifyFollows = ref(props.userSettings?.notify_follows ?? true);
const notifyMentions = ref(props.userSettings?.notify_mentions ?? true);
const notifyEmail = ref(props.userSettings?.notify_email ?? false);

const updateSetting = (key, value) => {
    isSaving.value = true;
    saveSuccess.value = false;

    if (key === 'quality_filter') qualityFilter.value = value;
    if (key === 'notify_likes') notifyLikes.value = value;
    if (key === 'notify_retweets') notifyRetweets.value = value;
    if (key === 'notify_follows') notifyFollows.value = value;
    if (key === 'notify_mentions') notifyMentions.value = value;
    if (key === 'notify_email') notifyEmail.value = value;

    router.patch(route('settings.notifications.update'), {
        [key]: value,
    }, {
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
    <Head title="Notifications / Settings" />

    <SettingsLayout>
        <!-- Top Sticky Header -->
        <div class="sticky top-0 z-10 bg-white/80 dark:bg-black/80 backdrop-blur-md px-6 pt-3 pb-3 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Notifications
            </h2>
        </div>

        <!-- Description Subtitle -->
        <div class="px-6 py-4 text-[13px] text-gray-500 border-b border-gray-100 dark:border-gray-900 leading-relaxed">
            Select the kinds of notifications you get about your activities, interests, and recommendations.
        </div>

        <!-- Setting Rows List -->
        <div class="divide-y divide-gray-100 dark:divide-gray-900">
            <!-- 1. Filters -->
            <button
                type="button"
                @click="showFiltersModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <Tune :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Filters
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Choose the notifications you'd like to see — and those you don't.
                        </div>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0 ml-4" />
            </button>

            <!-- 2. Preferences -->
            <button
                type="button"
                @click="showPreferencesModal = true"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
            >
                <div class="flex items-start space-x-4">
                    <Cellphone :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                            Preferences
                        </div>
                        <div class="text-[13px] text-gray-500 mt-0.5">
                            Select your preferences by notification type.
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

        <!-- MODAL 1: Filters -->
        <div v-if="showFiltersModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showFiltersModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notification Filters</h3>
                    <button @click="showFiltersModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="flex items-start justify-between p-3.5 rounded-xl bg-gray-50 dark:bg-black/50">
                        <div class="pr-4">
                            <div class="font-bold text-sm text-gray-900 dark:text-white">Quality filter</div>
                            <div class="text-xs text-gray-500 mt-1">
                                Filter out lower-quality notifications from accounts that appear to be automated or duplicate.
                            </div>
                        </div>
                        <input
                            type="checkbox"
                            :checked="qualityFilter"
                            @change="updateSetting('quality_filter', $event.target.checked)"
                            class="w-5 h-5 accent-[#1C9CEF] rounded cursor-pointer mt-1"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="showFiltersModal = false" class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm rounded-full">
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 2: Preferences -->
        <div v-if="showPreferencesModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showPreferencesModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notification Preferences</h3>
                    <button @click="showPreferencesModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">In-App Activity Notifications</div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-black/50">
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Likes</div>
                            <div class="text-xs text-gray-500">When someone likes one of your posts</div>
                        </div>
                        <input
                            type="checkbox"
                            :checked="notifyLikes"
                            @change="updateSetting('notify_likes', $event.target.checked)"
                            class="w-5 h-5 accent-[#1C9CEF] rounded cursor-pointer"
                        />
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-black/50">
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Retweets</div>
                            <div class="text-xs text-gray-500">When someone reposts your post</div>
                        </div>
                        <input
                            type="checkbox"
                            :checked="notifyRetweets"
                            @change="updateSetting('notify_retweets', $event.target.checked)"
                            class="w-5 h-5 accent-[#1C9CEF] rounded cursor-pointer"
                        />
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-black/50">
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">New Followers</div>
                            <div class="text-xs text-gray-500">When someone follows your profile</div>
                        </div>
                        <input
                            type="checkbox"
                            :checked="notifyFollows"
                            @change="updateSetting('notify_follows', $event.target.checked)"
                            class="w-5 h-5 accent-[#1C9CEF] rounded cursor-pointer"
                        />
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-black/50">
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Mentions</div>
                            <div class="text-xs text-gray-500">When someone mentions you in a post</div>
                        </div>
                        <input
                            type="checkbox"
                            :checked="notifyMentions"
                            @change="updateSetting('notify_mentions', $event.target.checked)"
                            class="w-5 h-5 accent-[#1C9CEF] rounded cursor-pointer"
                        />
                    </div>

                    <div class="pt-2 text-xs font-bold uppercase tracking-wider text-gray-400">Email Notifications</div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-black/50">
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Email activity digest</div>
                            <div class="text-xs text-gray-500">Receive periodic email digests for account notifications</div>
                        </div>
                        <input
                            type="checkbox"
                            :checked="notifyEmail"
                            @change="updateSetting('notify_email', $event.target.checked)"
                            class="w-5 h-5 accent-[#1C9CEF] rounded cursor-pointer"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="showPreferencesModal = false" class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm rounded-full">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>

