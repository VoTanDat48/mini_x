<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Tweet from '@/Components/Tweet.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import CogOutline from 'vue-material-design-icons/CogOutline.vue';

const props = defineProps({
    tweets: {
        type: Array,
        default: () => [],
    },
    trends: {
        type: Array,
        default: () => [],
    },
});

const searchTerm = ref('');

const search = () => {
    if (!searchTerm.value.trim()) return;
    router.visit(route('search', { q: searchTerm.value.trim() }));
};

const clickTrend = (topic) => {
    router.visit(route('search', { q: topic }));
};
</script>

<template>
    <Head title="Explore / Twitter" />

    <TwitterLayout>
        <div class="text-white min-h-screen border-b border-gray-800">
            <!-- Header bar with search input -->
            <div class="sticky top-0 bg-black/80 backdrop-blur-md z-20 px-4 py-2 border-b border-gray-800 flex items-center space-x-4">
                <form @submit.prevent="search" class="flex-1 flex items-center bg-[#202327] rounded-full px-4 py-2 focus-within:ring-1 focus-within:ring-[#1C9CEF]">
                    <Magnify fillColor="#5e5c5c" :size="20" class="mr-2.5" />
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Search Twitter"
                        class="w-full bg-transparent border-0 focus:ring-0 text-white placeholder-gray-500 text-sm p-0"
                    />
                </form>

                <Link
                    :href="route('settings.account')"
                    class="p-2 rounded-full hover:bg-gray-800 text-gray-400 hover:text-white transition cursor-pointer"
                    title="Explore settings"
                >
                    <CogOutline :size="20" />
                </Link>
            </div>

            <!-- Trends List Section -->
            <div class="border-b border-gray-800 pb-2">
                <div class="px-4 pt-4 pb-2 text-xl font-black text-white">
                    Trends for you
                </div>

                <div v-if="trends && trends.length > 0" class="divide-y divide-gray-900">
                    <div
                        v-for="(trend, idx) in trends"
                        :key="idx"
                        @click="clickTrend(trend.topic)"
                        class="px-4 py-3 hover:bg-gray-900 transition cursor-pointer flex justify-between items-center"
                    >
                        <div>
                            <div class="text-xs text-gray-500">{{ trend.category }}</div>
                            <div class="text-white font-bold text-[15px] mt-0.5">{{ trend.topic }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ trend.tweets_count }}</div>
                        </div>
                    </div>
                </div>

                <div v-else class="px-4 py-6 text-sm text-gray-500 text-center">
                    Chưa có chủ đề xu hướng nào. Hãy đăng bài viết kèm thẻ #hashtag để tạo xu hướng mới!
                </div>
            </div>

            <!-- Explore Tweets Section -->
            <div class="divide-y divide-gray-800">
                <div class="px-4 pt-4 pb-2 text-xl font-black text-white">
                    Explore posts
                </div>

                <div v-if="tweets && tweets.length > 0">
                    <div v-for="tweet in tweets" :key="tweet.id">
                        <Tweet :tweet="tweet" />
                    </div>
                </div>
                <div v-else class="py-16 text-center text-gray-500 text-sm">
                    No explore posts available right now.
                </div>
            </div>
        </div>
    </TwitterLayout>
</template>
