<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Tweet from '@/Components/Tweet.vue';
import ImageOutline from 'vue-material-design-icons/ImageOutline.vue';
import Close from 'vue-material-design-icons/Close.vue';
import Earth from 'vue-material-design-icons/Earth.vue';

const props = defineProps({
    tweets: {
        type: [Object, Array],
        default: () => ({ data: [] }),
    },
    currentTab: {
        type: String,
        default: 'for-you',
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

// Accumulated tweets for seamless pagination
const initialList = Array.isArray(props.tweets) ? props.tweets : (props.tweets?.data || []);
const tweetList = ref([...initialList]);
const isLoadingMore = ref(false);

watch(
    () => props.tweets,
    (newVal) => {
        const incomingData = Array.isArray(newVal) ? newVal : (newVal?.data || []);
        const pageNum = newVal?.current_page || 1;

        if (pageNum === 1) {
            tweetList.value = [...incomingData];
        } else {
            const existingIds = new Set(tweetList.value.map((t) => t.id));
            const fresh = incomingData.filter((t) => !existingIds.has(t.id));
            tweetList.value.push(...fresh);
        }
    },
    { deep: true }
);

// Inline tweet composition state
const tweetText = ref('');
const fileInput = ref(null);
const file = ref(null);
const previewUrl = ref('');
const isVideo = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref('');

const onFileSelect = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    const selectedFile = e.target.files[0];
    file.value = selectedFile;
    previewUrl.value = URL.createObjectURL(selectedFile);
    const ext = selectedFile.name.split('.').pop().toLowerCase();
    isVideo.value = ['mp4', 'mov', 'webm'].includes(ext);
};

const removeFile = () => {
    file.value = null;
    previewUrl.value = '';
    isVideo.value = false;
    if (fileInput.value) fileInput.value.value = '';
};

const submitTweet = () => {
    if (!tweetText.value && !file.value) return;
    if (tweetText.value && tweetText.value.length > 280) {
        errorMessage.value = 'Tweet cannot exceed 280 characters.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    const formData = new FormData();
    if (tweetText.value) formData.append('tweet', tweetText.value);
    if (file.value) formData.append('file', file.value);

    router.post(route('tweets.store'), formData, {
        onSuccess: () => {
            tweetText.value = '';
            removeFile();
            errorMessage.value = '';
            isSubmitting.value = false;
        },
        onError: (errs) => {
            isSubmitting.value = false;
            errorMessage.value = errs.tweet || errs.file || 'Failed to post tweet. Please try again.';
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

// Pagination load more
const loadMore = () => {
    if (!props.tweets?.next_page_url || isLoadingMore.value) return;

    isLoadingMore.value = true;
    router.visit(props.tweets.next_page_url, {
        preserveScroll: true,
        preserveState: true,
        only: ['tweets'],
        onFinish: () => {
            isLoadingMore.value = false;
        },
    });
};
</script>

<template>
    <Head title="Home / Twitter" />

    <TwitterLayout :showHomeHeader="true" :currentTab="currentTab">
        <div class="text-gray-900 dark:text-white divide-y divide-gray-200 dark:divide-gray-800">
            <!-- Inline Tweet Composer (Home feed top) -->
            <div v-if="currentUser" class="p-4 border-b border-gray-200 dark:border-gray-800">
                <div class="flex space-x-3">
                    <Link :href="route('users.profile', currentUser.username)" class="flex-shrink-0">
                        <img
                            :src="currentUser.avatar_url"
                            :alt="currentUser.name"
                            class="w-10 h-10 rounded-full object-cover bg-gray-700"
                        />
                    </Link>

                    <div class="flex-1 min-w-0">
                        <textarea
                            v-model="tweetText"
                            placeholder="What is happening?!"
                            rows="3"
                            class="w-full bg-transparent border-0 focus:ring-0 text-gray-900 dark:text-white placeholder-gray-500 text-[19px] resize-none p-0 leading-relaxed"
                        ></textarea>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="bg-red-900/40 border border-red-700 text-red-300 text-xs px-3 py-2 rounded-xl my-2">
                            {{ errorMessage }}
                        </div>

                        <!-- Media Preview -->
                        <div v-if="previewUrl" class="relative my-2 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 max-h-[300px]">
                            <button
                                type="button"
                                @click="removeFile"
                                class="absolute top-2 right-2 bg-black/70 hover:bg-black/90 p-1.5 rounded-full text-white cursor-pointer z-10 transition"
                            >
                                <Close fillColor="#ffffff" :size="18" />
                            </button>
                            <video v-if="isVideo" controls :src="previewUrl" class="w-full max-h-[300px] object-cover" />
                            <img v-else :src="previewUrl" class="w-full max-h-[300px] object-cover" alt="Upload preview" />
                        </div>

                        <div class="flex items-center text-[#1C9CEF] text-xs font-bold py-2 border-b border-gray-200 dark:border-gray-800/80">
                            <Earth class="mr-1.5" fillColor="#1C9CEF" :size="16" />
                            <span>Everyone can reply</span>
                        </div>

                        <!-- Composer bottom toolbar -->
                        <div class="flex items-center justify-between pt-3">
                            <div class="flex items-center space-x-1">
                                <label for="inlineFeedUpload" class="p-2 rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/30 text-[#1C9CEF] cursor-pointer transition">
                                    <ImageOutline fillColor="#1C9CEF" :size="20" />
                                </label>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    id="inlineFeedUpload"
                                    class="hidden"
                                    accept="image/png,image/jpeg,image/jpg,image/webp,image/gif,video/mp4,video/webm"
                                    @change="onFileSelect"
                                />
                            </div>

                            <div class="flex items-center space-x-3">
                                <span
                                    v-if="tweetText"
                                    :class="(280 - tweetText.length) < 0 ? 'text-red-500 font-bold' : (280 - tweetText.length) <= 20 ? 'text-yellow-500' : 'text-gray-500'"
                                    class="text-xs"
                                >
                                    {{ 280 - tweetText.length }}
                                </span>

                                <button
                                    type="button"
                                    :disabled="(!tweetText && !file) || (tweetText && tweetText.length > 280) || isSubmitting"
                                    @click="submitTweet"
                                    :class="(!tweetText && !file) || (tweetText && tweetText.length > 280) || isSubmitting ? 'bg-gray-300 dark:bg-white/50 text-gray-500 dark:text-black/50 cursor-not-allowed' : 'bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-black cursor-pointer'"
                                    class="font-extrabold text-sm px-5 py-1.5 rounded-full transition shadow"
                                >
                                    <span v-if="isSubmitting">Posting...</span>
                                    <span v-else>Post</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest CTA banner on Home feed -->
            <div v-else class="p-6 bg-gray-50 dark:bg-black border-b border-gray-200 dark:border-gray-800 text-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Don't miss what's happening</h3>
                <p class="text-sm text-gray-500 mb-4 max-w-md mx-auto">
                    People on Twitter are the first to know. Log in or sign up to join the conversation and customize your timeline.
                </p>
                <div class="flex justify-center space-x-3">
                    <Link
                        :href="route('login')"
                        class="px-5 py-1.5 rounded-full font-bold text-sm text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-900 transition"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="route('register')"
                        class="px-5 py-1.5 rounded-full font-bold text-sm text-white dark:text-black bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 transition shadow"
                    >
                        Sign up
                    </Link>
                </div>
            </div>

            <!-- Empty Feed State -->
            <div v-if="!tweetList || tweetList.length === 0" class="py-20 px-6 text-center max-w-sm mx-auto">
                <div class="text-xl font-bold text-gray-300 mb-2">
                    {{ currentTab === 'following' ? 'Welcome to your timeline!' : 'Welcome to your Twitter feed!' }}
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    {{
                        currentTab === 'following'
                            ? 'Focus on what you care about. Follow accounts to populate this tab with posts from people you follow.'
                            : 'This is the best place to see what’s happening in your world. Post a tweet above to get started.'
                    }}
                </p>
            </div>

            <!-- Tweet List -->
            <div v-for="tweet in tweetList" :key="tweet.id">
                <Tweet :tweet="tweet" />
            </div>

            <!-- Pagination / Load More Button -->
            <div v-if="tweets?.next_page_url" class="p-6 text-center">
                <button
                    type="button"
                    @click="loadMore"
                    :disabled="isLoadingMore"
                    class="border border-gray-700 hover:bg-gray-900 text-[#1C9CEF] font-bold text-sm px-6 py-2.5 rounded-full transition cursor-pointer"
                >
                    <span v-if="isLoadingMore">Loading more posts...</span>
                    <span v-else>Show more posts</span>
                </button>
            </div>
            <div v-else-if="tweetList && tweetList.length > 0" class="py-10 text-center text-gray-600 text-xs">
                You're all caught up!
            </div>
        </div>
    </TwitterLayout>
</template>

<style>
body {
    background-color: black;
}
</style>
