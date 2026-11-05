<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import MedalOutline from 'vue-material-design-icons/MedalOutline.vue';
import StarOutline from 'vue-material-design-icons/StarOutline.vue';
import VideoOutline from 'vue-material-design-icons/VideoOutline.vue';
import ChartBar from 'vue-material-design-icons/ChartBar.vue';
import StarFourPointsOutline from 'vue-material-design-icons/StarFourPointsOutline.vue';
import BookOpenOutline from 'vue-material-design-icons/BookOpenOutline.vue';
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue';
import HelpCircleOutline from 'vue-material-design-icons/HelpCircleOutline.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';
import Close from 'vue-material-design-icons/Close.vue';

const props = defineProps({
    creatorStats: Object,
});

const showAnalyticsModal = ref(false);
const showEligibilityModal = ref(false);
const eligibilityFeature = ref('');
const toolModal = ref({ open: false, title: '', desc: '' });

const openToolModal = (title, desc) => {
    toolModal.value = { open: true, title, desc };
};

const openEligibility = (featureName) => {
    eligibilityFeature.value = featureName;
    showEligibilityModal.value = true;
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('settings.account'));
    }
};
</script>

<template>
    <Head title="Creator Studio" />

    <TwitterLayout>
        <div class="min-h-full bg-white dark:bg-black text-gray-900 dark:text-white select-none pb-12">
            <!-- Header with Back Arrow and Title -->
            <div class="sticky top-0 z-20 bg-white/80 dark:bg-black/80 backdrop-blur-md px-4 py-3 flex items-center space-x-6 border-b border-gray-200 dark:border-gray-800">
                <button
                    type="button"
                    @click="goBack"
                    class="p-2 hover:bg-gray-200 dark:hover:bg-gray-800 rounded-full transition cursor-pointer"
                    title="Back"
                >
                    <ArrowLeft :size="20" class="text-gray-900 dark:text-white" />
                </button>
                <h1 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Creator Studio
                </h1>
            </div>

            <!-- Group 1: Programs -->
            <div class="mt-4">
                <div class="px-5 py-2 text-base font-extrabold text-gray-900 dark:text-white">
                    Programs
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-900">
                    <!-- Original Content Rewards -->
                    <button
                        type="button"
                        @click="openEligibility('Original Content Rewards')"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <MedalOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Original Content Rewards
                                </div>
                                <div class="text-[13px] text-gray-500">
                                    Earn from your posts
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                :class="creatorStats?.is_eligible
                                    ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300'
                                    : 'bg-gray-200 dark:bg-[#202327] text-gray-600 dark:text-gray-400'"
                                class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                            >
                                {{ creatorStats?.is_eligible ? 'Eligible' : 'Ineligible' }}
                            </span>
                            <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                        </div>
                    </button>

                    <!-- Subscriptions -->
                    <button
                        type="button"
                        @click="openEligibility('Subscriptions')"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <StarOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Subscriptions
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                :class="creatorStats?.is_eligible
                                    ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300'
                                    : 'bg-gray-200 dark:bg-[#202327] text-gray-600 dark:text-gray-400'"
                                class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                            >
                                {{ creatorStats?.is_eligible ? 'Eligible' : 'Ineligible' }}
                            </span>
                            <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                        </div>
                    </button>
                </div>
            </div>

            <!-- Group 2: Tools -->
            <div class="mt-6">
                <div class="px-5 py-2 text-base font-extrabold text-gray-900 dark:text-white">
                    Tools
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-900">
                    <!-- Live Studio -->
                    <button
                        type="button"
                        @click="openToolModal('Live Studio', 'Broadcast live video to your followers with RTMP encoder support and real-time live chat moderation.')"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <VideoOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Live Studio
                                </div>
                                <div class="text-[13px] text-gray-500">
                                    Go live professionally
                                </div>
                            </div>
                        </div>
                        <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                    </button>

                    <!-- Analytics -->
                    <button
                        type="button"
                        @click="showAnalyticsModal = true"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <ChartBar :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Analytics
                                </div>
                            </div>
                        </div>
                        <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                    </button>

                    <!-- Inspiration -->
                    <button
                        type="button"
                        @click="openToolModal('Inspiration', 'Explore top trending content formats, viral conversation starters, and hashtag ideas curated for creators.')"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <StarFourPointsOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Inspiration
                                </div>
                                <div class="text-[13px] text-gray-500">
                                    Top posts by engagement
                                </div>
                            </div>
                        </div>
                        <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                    </button>

                    <!-- Creator Academy -->
                    <button
                        type="button"
                        @click="openToolModal('Creator Academy', 'Comprehensive guides, creator playbooks, and best practices to help you scale your followers and revenue.')"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <BookOpenOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Creator Academy
                                </div>
                                <div class="text-[13px] text-gray-500">
                                    Tips and guides for creators
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-[#1C9CEF] text-white">
                                New
                            </span>
                            <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                        </div>
                    </button>
                </div>
            </div>

            <!-- Group 3: Support -->
            <div class="mt-6">
                <div class="px-5 py-2 text-base font-extrabold text-gray-900 dark:text-white">
                    Support
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-900">
                    <!-- Contact support -->
                    <button
                        type="button"
                        @click="openToolModal('Creator Support', 'Have questions about monetization or payout verification? Send a message to our support specialists.')"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <ChatOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Contact support
                                </div>
                            </div>
                        </div>
                        <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                    </button>

                    <!-- Learn more -->
                    <a
                        href="https://help.twitter.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition cursor-pointer text-left group"
                    >
                        <div class="flex items-center space-x-4">
                            <HelpCircleOutline :size="24" class="text-gray-500 group-hover:text-gray-900 dark:group-hover:text-white transition flex-shrink-0" />
                            <div>
                                <div class="text-[15px] font-bold text-gray-900 dark:text-white">
                                    Learn more
                                </div>
                            </div>
                        </div>
                        <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                    </a>
                </div>
            </div>
        </div>

        <!-- MODAL: Analytics Dashboard -->
        <div v-if="showAnalyticsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showAnalyticsModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Creator Analytics</h3>
                    <button @click="showAnalyticsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-5">
                    <div class="p-4 bg-gray-50 dark:bg-black/50 rounded-2xl border border-gray-100 dark:border-gray-800/60">
                        <span class="text-xs text-gray-500 font-semibold block uppercase">Total Posts</span>
                        <span class="text-2xl font-black text-gray-900 dark:text-white mt-1 block">{{ creatorStats?.tweets_count || 0 }}</span>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-black/50 rounded-2xl border border-gray-100 dark:border-gray-800/60">
                        <span class="text-xs text-gray-500 font-semibold block uppercase">Followers</span>
                        <span class="text-2xl font-black text-gray-900 dark:text-white mt-1 block">{{ creatorStats?.followers_count || 0 }}</span>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-black/50 rounded-2xl border border-gray-100 dark:border-gray-800/60">
                        <span class="text-xs text-gray-500 font-semibold block uppercase">Total Likes Received</span>
                        <span class="text-2xl font-black text-red-500 mt-1 block">{{ creatorStats?.total_likes || 0 }}</span>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-black/50 rounded-2xl border border-gray-100 dark:border-gray-800/60">
                        <span class="text-xs text-gray-500 font-semibold block uppercase">Total Reposts</span>
                        <span class="text-2xl font-black text-green-500 mt-1 block">{{ creatorStats?.total_retweets || 0 }}</span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="showAnalyticsModal = false" class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm rounded-full">
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Eligibility Requirement Details -->
        <div v-if="showEligibilityModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showEligibilityModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-md p-6 shadow-2xl border border-gray-200 dark:border-gray-800 text-center" @click.stop>
                <MedalOutline :size="48" class="text-yellow-500 mx-auto mb-3" />
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ eligibilityFeature }}</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    To be eligible for Creator Studio monetization programs, your account must meet the following criteria:
                </p>

                <div class="mt-4 p-4 bg-gray-50 dark:bg-black/50 rounded-xl text-left space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Active followers:</span>
                        <span :class="creatorStats?.followers_count >= 500 ? 'text-green-500 font-bold' : 'text-red-500 font-bold'">
                            {{ creatorStats?.followers_count }} / 500
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Account status:</span>
                        <span class="text-green-500 font-bold">Good standing</span>
                    </div>
                </div>

                <button @click="showEligibilityModal = false" class="mt-5 w-full py-2 bg-[#1C9CEF] text-white font-bold text-sm rounded-full">
                    Understood
                </button>
            </div>
        </div>

        <!-- MODAL: Tool Detail Notice -->
        <div v-if="toolModal.open" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="toolModal.open = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-sm p-6 shadow-2xl border border-gray-200 dark:border-gray-800 text-center" @click.stop>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ toolModal.title }}</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    {{ toolModal.desc }}
                </p>
                <button @click="toolModal.open = false" class="mt-5 w-full py-2 bg-[#1C9CEF] text-white font-bold text-sm rounded-full">
                    Understood
                </button>
            </div>
        </div>
    </TwitterLayout>
</template>

