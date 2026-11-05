<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Tweet from '@/Components/Tweet.vue';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';

const props = defineProps({
    tweets: {
        type: [Object, Array],
        default: () => ({ data: [] }),
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const tweetItems = computed(() => Array.isArray(props.tweets) ? props.tweets : (props.tweets?.data || []));
</script>

<template>
    <Head title="Bookmarks / Twitter" />

    <TwitterLayout>
        <div class="text-white min-h-screen">
            <!-- Header -->
            <div class="p-4 border-b border-gray-800 sticky top-0 bg-black/80 backdrop-blur-md z-10">
                <h1 class="text-xl font-bold text-white">Bookmarks</h1>
                <p class="text-xs text-gray-500">@{{ user?.username }}</p>
            </div>

            <!-- Empty State -->
            <div v-if="!tweetItems || tweetItems.length === 0" class="py-20 px-8 text-center max-w-sm mx-auto">
                <div class="text-2xl font-black text-white mb-2">Save posts for later</div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Don’t let the good ones fly away! Bookmark posts to easily find them again in the future.
                </p>
            </div>

            <!-- Bookmarked Tweets List -->
            <div v-else class="divide-y divide-gray-800">
                <div v-for="tweet in tweetItems" :key="tweet.id">
                    <Tweet :tweet="tweet" />
                </div>
            </div>

            <!-- Pagination Controls -->
            <div v-if="tweets?.links && tweets.links.length > 3" class="p-6 flex justify-center space-x-2">
                <Link
                    v-for="link in tweets.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    :class="[
                        'px-3.5 py-1.5 rounded-full text-xs font-semibold border transition',
                        link.active ? 'bg-[#1C9CEF] border-[#1C9CEF] text-white' : 'border-gray-700 text-gray-400 hover:text-white',
                        !link.url ? 'opacity-40 pointer-events-none' : ''
                    ]"
                />
            </div>
        </div>
    </TwitterLayout>
</template>
