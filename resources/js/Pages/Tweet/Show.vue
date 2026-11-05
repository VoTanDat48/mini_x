<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Tweet from '@/Components/Tweet.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Heart from 'vue-material-design-icons/Heart.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import Sync from 'vue-material-design-icons/Sync.vue';
import MessageOutline from 'vue-material-design-icons/MessageOutline.vue';
import Bookmark from 'vue-material-design-icons/Bookmark.vue';
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';
import ShareVariantOutline from 'vue-material-design-icons/ShareVariantOutline.vue';
import ImageOutline from 'vue-material-design-icons/ImageOutline.vue';
import Close from 'vue-material-design-icons/Close.vue';
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue';

const props = defineProps({
    tweet: {
        type: Object,
        required: true,
    },
    replies: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isOwner = computed(() => currentUser.value && currentUser.value.id === props.tweet.user_id);

// Interaction states with optimistic update
const isLiked = ref(Boolean(props.tweet.is_liked));
const likesCount = ref(Number(props.tweet.likes_count) || 0);

const isRetweeted = ref(Boolean(props.tweet.is_retweeted));
const retweetsCount = ref(Number(props.tweet.retweets_count) || 0);

const isBookmarked = ref(Boolean(props.tweet.is_bookmarked));
const bookmarksCount = ref(Number(props.tweet.bookmarks_count) || 0);

const repliesCount = ref(Number(props.tweet.replies_count) || 0);
const viewsCount = ref(Number(props.tweet.views_count) || 0);

// Inline reply form state
const replyText = ref('');
const replyFile = ref(null);
const replyPreview = ref('');
const replyType = ref('');
const isSubmittingReply = ref(false);
const replyError = ref('');
const toastMessage = ref('');
const replyTextareaRef = ref(null);

const focusReply = () => {
    if (replyTextareaRef.value) {
        replyTextareaRef.value.focus();
        replyTextareaRef.value.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};

const showToast = (msg) => {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 2500);
};

const toggleLike = async () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    const prevLiked = isLiked.value;
    const prevCount = likesCount.value;

    isLiked.value = !prevLiked;
    likesCount.value = prevLiked ? Math.max(0, prevCount - 1) : prevCount + 1;

    try {
        const res = await axios.post(route('tweets.like', props.tweet.id));
        if (res.data && typeof res.data.likes_count === 'number') {
            likesCount.value = res.data.likes_count;
            isLiked.value = res.data.liked;
        }
    } catch (err) {
        isLiked.value = prevLiked;
        likesCount.value = prevCount;
        showToast('Failed to update like');
    }
};

const toggleRetweet = async () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    const prevRetweeted = isRetweeted.value;
    const prevCount = retweetsCount.value;

    isRetweeted.value = !prevRetweeted;
    retweetsCount.value = prevRetweeted ? Math.max(0, prevCount - 1) : prevCount + 1;

    try {
        const res = await axios.post(route('tweets.retweet', props.tweet.id));
        if (res.data && typeof res.data.retweets_count === 'number') {
            retweetsCount.value = res.data.retweets_count;
            isRetweeted.value = res.data.retweeted;
        }
    } catch (err) {
        isRetweeted.value = prevRetweeted;
        retweetsCount.value = prevCount;
        showToast('Failed to update repost');
    }
};

const toggleBookmark = async () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    const prevBookmarked = isBookmarked.value;
    isBookmarked.value = !prevBookmarked;

    try {
        const res = await axios.post(route('tweets.bookmark', props.tweet.id));
        if (res.data) {
            isBookmarked.value = res.data.bookmarked;
            showToast(isBookmarked.value ? 'Added to your Bookmarks' : 'Removed from your Bookmarks');
        }
    } catch (err) {
        isBookmarked.value = prevBookmarked;
        showToast('Failed to update bookmark');
    }
};

const copyShareLink = () => {
    navigator.clipboard?.writeText(window.location.href);
    showToast('Copied link to clipboard');
};

const handleReplyFile = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    replyFile.value = e.target.files[0];
    replyPreview.value = URL.createObjectURL(e.target.files[0]);
    replyType.value = replyFile.value.name.split('.').pop().toLowerCase();
};

const clearReplyFile = () => {
    replyFile.value = null;
    replyPreview.value = '';
    replyType.value = '';
};

const submitReply = () => {
    if (!replyText.value && !replyFile.value) return;
    if (replyText.value && replyText.value.length > 280) {
        replyError.value = 'Reply cannot exceed 280 characters.';
        return;
    }

    isSubmittingReply.value = true;
    replyError.value = '';

    const formData = new FormData();
    if (replyText.value) formData.append('tweet', replyText.value);
    if (replyFile.value) formData.append('file', replyFile.value);
    formData.append('parent_id', props.tweet.id);

    router.post(route('tweets.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            replyText.value = '';
            clearReplyFile();
            isSubmittingReply.value = false;
            repliesCount.value += 1;
            showToast('Your reply was sent');
        },
        onError: (errs) => {
            isSubmittingReply.value = false;
            replyError.value = errs.tweet || errs.file || 'Failed to send reply.';
        },
        onFinish: () => {
            isSubmittingReply.value = false;
        }
    });
};
</script>

<template>
    <Head :title="`${tweet.user?.name || 'User'} on Twitter: &quot;${tweet.tweet?.substring(0, 30) || 'Post'}&quot;`" />

    <TwitterLayout>
        <div class="text-white min-h-screen">
            <!-- Header bar with Back button -->
            <div class="sticky top-0 bg-black/80 backdrop-blur-md z-20 flex items-center px-4 py-3 border-b border-gray-800 space-x-6">
                <Link href="/" class="p-2 rounded-full hover:bg-gray-800 transition cursor-pointer">
                    <ArrowLeft fillColor="#ffffff" :size="20" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold">Post</h1>
                </div>
            </div>

            <!-- Main Tweet Content Area -->
            <article class="p-4 border-b border-gray-800">
                <!-- Author Header -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <img
                            :src="tweet.user?.avatar_url || 'https://ui-avatars.com/api/?name=User'"
                            class="rounded-full w-12 h-12 object-cover bg-gray-700"
                            :alt="tweet.user?.name"
                        />
                        <div>
                            <div class="font-bold text-white hover:underline cursor-pointer leading-tight">
                                {{ tweet.user?.name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                @{{ tweet.user?.username }}
                            </div>
                        </div>
                    </div>

                    <!-- Delete button if owner -->
                    <div v-if="isOwner">
                        <Link
                            as="button"
                            method="delete"
                            :href="route('tweets.destroy', { tweet: tweet.id })"
                            class="p-2 rounded-full hover:bg-red-950/40 text-red-500 hover:text-red-400 transition cursor-pointer"
                            title="Delete post"
                        >
                            <TrashCanOutline :size="20" />
                        </Link>
                    </div>
                </div>

                <!-- Tweet Text -->
                <div class="text-white text-[19px] leading-relaxed break-words whitespace-pre-line my-3">
                    {{ tweet.tweet }}
                </div>

                <!-- Attached Media -->
                <div v-if="tweet.file" class="my-3 rounded-2xl overflow-hidden border border-gray-800 max-h-[500px]">
                    <video
                        v-if="tweet.is_video"
                        controls
                        class="w-full max-h-[500px] object-cover rounded-2xl"
                        :src="tweet.file"
                    ></video>
                    <img
                        v-else
                        :src="tweet.file"
                        class="w-full max-h-[500px] object-cover rounded-2xl"
                        alt="Attachment"
                    />
                </div>

                <!-- Quoted Tweet Preview Card if exists -->
                <div
                    v-if="tweet.quote_of"
                    class="my-3 border border-gray-700 rounded-2xl p-3.5 hover:bg-gray-900/50 transition cursor-pointer"
                    @click="router.visit(`/${tweet.quote_of.user?.username}/status/${tweet.quote_of.id}`)"
                >
                    <div class="flex items-center space-x-2 mb-1.5">
                        <img
                            :src="tweet.quote_of.user?.avatar_url"
                            class="rounded-full w-5 h-5 object-cover"
                        />
                        <span class="font-bold text-sm text-white">{{ tweet.quote_of.user?.name }}</span>
                        <span class="text-xs text-gray-500">@{{ tweet.quote_of.user?.username }}</span>
                    </div>
                    <p class="text-sm text-gray-200 line-clamp-3">{{ tweet.quote_of.tweet }}</p>
                </div>

                <!-- Timestamp & View Count -->
                <div class="py-3 text-[14px] text-gray-500 border-b border-gray-800 flex items-center space-x-2">
                    <span>{{ tweet.created_at_human }}</span>
                    <span>·</span>
                    <span class="font-bold text-white">{{ viewsCount }}</span>
                    <span>Views</span>
                </div>

                <!-- Counts Breakdown Row -->
                <div class="py-3 border-b border-gray-800 flex items-center space-x-6 text-sm text-gray-500">
                    <div><strong class="text-white">{{ retweetsCount }}</strong> Reposts</div>
                    <div><strong class="text-white">{{ repliesCount }}</strong> Replies</div>
                    <div><strong class="text-white">{{ likesCount }}</strong> Likes</div>
                    <div><strong class="text-white">{{ bookmarksCount }}</strong> Bookmarks</div>
                </div>

                <!-- Interactive Action Buttons Bar -->
                <div class="flex items-center justify-around py-2 border-b border-gray-800 text-gray-500">
                    <!-- Reply Action -->
                    <button
                        type="button"
                        @click="focusReply"
                        class="p-2.5 rounded-full hover:bg-blue-900/30 hover:text-[#1C9CEF] transition cursor-pointer"
                        title="Reply"
                    >
                        <MessageOutline :size="20" />
                    </button>

                    <!-- Retweet Action -->
                    <button
                        type="button"
                        @click="toggleRetweet"
                        :class="isRetweeted ? 'text-green-500' : 'hover:text-green-500'"
                        class="p-2.5 rounded-full hover:bg-green-900/30 transition cursor-pointer"
                        title="Repost"
                    >
                        <Sync :size="20" />
                    </button>

                    <!-- Like Action -->
                    <button
                        type="button"
                        @click="toggleLike"
                        :class="isLiked ? 'text-[#F91880]' : 'hover:text-[#F91880]'"
                        class="p-2.5 rounded-full hover:bg-pink-900/30 transition cursor-pointer"
                        title="Like"
                    >
                        <Heart v-if="isLiked" fillColor="#F91880" :size="20" />
                        <HeartOutline v-else :size="20" />
                    </button>

                    <!-- Bookmark Action -->
                    <button
                        type="button"
                        @click="toggleBookmark"
                        :class="isBookmarked ? 'text-[#1C9CEF]' : 'hover:text-[#1C9CEF]'"
                        class="p-2.5 rounded-full hover:bg-blue-900/30 transition cursor-pointer"
                        title="Bookmark"
                    >
                        <Bookmark v-if="isBookmarked" fillColor="#1C9CEF" :size="20" />
                        <BookmarkOutline v-else :size="20" />
                    </button>

                    <!-- Share Action -->
                    <button
                        type="button"
                        @click="copyShareLink"
                        class="p-2.5 rounded-full hover:bg-blue-900/30 hover:text-[#1C9CEF] transition cursor-pointer"
                        title="Share"
                    >
                        <ShareVariantOutline :size="20" />
                    </button>
                </div>

                <!-- Inline Reply Box -->
                <div v-if="currentUser" class="pt-4 flex items-start space-x-3">
                    <img
                        :src="currentUser.avatar_url"
                        class="rounded-full w-10 h-10 object-cover bg-gray-700"
                        :alt="currentUser.name"
                    />
                    <div class="flex-1">
                        <div class="text-xs text-gray-500 mb-1">
                            Replying to <span class="text-[#1C9CEF]">@{{ tweet.user?.username }}</span>
                        </div>
                        <textarea
                            ref="replyTextareaRef"
                            v-model="replyText"
                            placeholder="Post your reply"
                            rows="2"
                            class="w-full bg-black border-0 text-white placeholder-gray-500 focus:ring-0 resize-none text-[17px] p-0"
                        ></textarea>

                        <!-- Attached file preview in reply -->
                        <div v-if="replyPreview" class="relative my-2 rounded-xl overflow-hidden border border-gray-800 max-h-48">
                            <button
                                type="button"
                                @click="clearReplyFile"
                                class="absolute top-1.5 right-1.5 bg-black/80 hover:bg-black p-1 rounded-full text-white cursor-pointer"
                            >
                                <Close :size="16" />
                            </button>
                            <video v-if="replyType === 'mp4'" :src="replyPreview" class="w-full max-h-48 object-cover" controls />
                            <img v-else :src="replyPreview" class="w-full max-h-48 object-cover" />
                        </div>

                        <div v-if="replyError" class="text-red-400 text-xs py-1">{{ replyError }}</div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-800 mt-2">
                            <div>
                                <label for="replyMediaUpload" class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF] inline-block transition">
                                    <ImageOutline fillColor="#1C9CEF" :size="20" />
                                </label>
                                <input type="file" id="replyMediaUpload" class="hidden" accept="image/*,video/mp4" @change="handleReplyFile">
                            </div>

                            <button
                                :disabled="(!replyText && !replyFile) || isSubmittingReply"
                                :class="(!replyText && !replyFile) || isSubmittingReply ? 'bg-white/50 text-black/50 cursor-not-allowed' : 'bg-white hover:bg-gray-200 text-black cursor-pointer'"
                                @click="submitReply"
                                class="font-extrabold text-sm px-4 py-1.5 rounded-full transition shadow"
                            >
                                <span v-if="isSubmittingReply">Replying...</span>
                                <span v-else>Reply</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="py-4 text-center border-t border-gray-800 mt-3">
                    <p class="text-sm text-gray-500 mb-2">Want to reply?</p>
                    <Link :href="route('login')" class="text-sm font-bold text-[#1C9CEF] hover:underline">
                        Log in or sign up to join the conversation
                    </Link>
                </div>
            </article>

            <!-- Replies Thread List -->
            <div class="divide-y divide-gray-800">
                <div v-if="replies && replies.length > 0">
                    <div v-for="reply in replies" :key="reply.id">
                        <Tweet :tweet="reply" />
                    </div>
                </div>
                <div v-else class="py-12 text-center text-gray-500 text-sm">
                    No replies yet. Be the first to join the conversation!
                </div>
            </div>
        </div>

        <!-- Floating Toast Message -->
        <transition name="fade">
            <div
                v-if="toastMessage"
                class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-[#1C9CEF] text-white font-semibold text-sm px-5 py-2.5 rounded-full shadow-2xl z-50 transition"
            >
                {{ toastMessage }}
            </div>
        </transition>
    </TwitterLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
