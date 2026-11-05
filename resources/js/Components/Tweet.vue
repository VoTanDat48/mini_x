<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import FlagOutline from 'vue-material-design-icons/FlagOutline.vue';
import Heart from 'vue-material-design-icons/Heart.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import ChartBar from 'vue-material-design-icons/ChartBar.vue';
import MessageOutline from 'vue-material-design-icons/MessageOutline.vue';
import Sync from 'vue-material-design-icons/Sync.vue';
import Bookmark from 'vue-material-design-icons/Bookmark.vue';
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';
import ShareVariantOutline from 'vue-material-design-icons/ShareVariantOutline.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue';
import PencilOutline from 'vue-material-design-icons/PencilOutline.vue';
import Close from 'vue-material-design-icons/Close.vue';
import ImageOutline from 'vue-material-design-icons/ImageOutline.vue';
import LinkVariant from 'vue-material-design-icons/LinkVariant.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import Send from 'vue-material-design-icons/Send.vue';
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue';

const props = defineProps({
    tweet: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isOwner = computed(() => currentUser.value && currentUser.value.id === props.tweet.user_id);

// Optimistic interaction states
const isLiked = ref(Boolean(props.tweet.is_liked));
const likesCount = ref(Number(props.tweet.likes_count) || 0);

const isRetweeted = ref(Boolean(props.tweet.is_retweeted));
const retweetsCount = ref(Number(props.tweet.retweets_count) || 0);

const isBookmarked = ref(Boolean(props.tweet.is_bookmarked));
const repliesCount = ref(Number(props.tweet.replies_count) || 0);
const viewsCount = ref(Number(props.tweet.views_count) || 0);

// Menu & Modal controls
const openOptions = ref(false);
const optionsRef = ref(null);
const openRetweetMenu = ref(false);
const retweetMenuRef = ref(null);
const openShareMenu = ref(false);
const shareMenuRef = ref(null);

const showSendChatModal = ref(false);
const chatRecipients = ref([]);
const isLoadingRecipients = ref(false);
const searchRecipientQuery = ref('');
const selectedRecipient = ref(null);
const chatComment = ref('');
const isSendingChat = ref(false);
let searchDebounceTimer = null;

const showReplyModal = ref(false);
const showQuoteModal = ref(false);

const replyText = ref('');
const replyFile = ref(null);
const replyPreview = ref('');
const replyType = ref('');
const isSubmittingReply = ref(false);

const quoteText = ref('');
const quoteFile = ref(null);
const quotePreview = ref('');
const quoteType = ref('');
const isSubmittingQuote = ref(false);

const toastMessage = ref('');

const showToast = (msg) => {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 2200);
};

// Navigation to tweet detail
const goToStatus = (e) => {
    // If user clicked a button, link, image, or interactive element, don't navigate
    if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input') || e.target.closest('label') || e.target.closest('video')) {
        return;
    }
    const username = props.tweet.user?.username || 'user';
    router.visit(`/${username}/status/${props.tweet.id}`);
};

// Toggle Like
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

// Toggle Retweet (Direct repost)
const toggleRetweet = async () => {
    openRetweetMenu.value = false;
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
            showToast(isRetweeted.value ? 'Reposted' : 'Undo repost');
        }
    } catch (err) {
        isRetweeted.value = prevRetweeted;
        retweetsCount.value = prevCount;
        showToast('Failed to update repost');
    }
};

// Toggle Bookmark
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

// Share Link & Direct Message
const handleCopyLink = () => {
    openShareMenu.value = false;
    const username = props.tweet.user?.username || 'user';
    const url = window.location.origin + `/${username}/status/${props.tweet.id}`;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url);
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
    showToast('Copied link to clipboard');
};

const copyShareLink = handleCopyLink;

const openSendViaChatModal = async () => {
    openShareMenu.value = false;
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    showSendChatModal.value = true;
    selectedRecipient.value = null;
    chatComment.value = '';
    searchRecipientQuery.value = '';
    await fetchRecipients();
};

const closeSendChatModal = () => {
    showSendChatModal.value = false;
    selectedRecipient.value = null;
    chatComment.value = '';
    searchRecipientQuery.value = '';
};

const fetchRecipients = async (q = '') => {
    isLoadingRecipients.value = true;
    try {
        const res = await axios.get(route('messages.recipients'), {
            params: { q: q.trim() || undefined }
        });
        chatRecipients.value = res.data.recipients || [];
    } catch (err) {
        console.error('Failed to fetch recipients', err);
        chatRecipients.value = [];
    } finally {
        isLoadingRecipients.value = false;
    }
};

const onSearchRecipientInput = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        fetchRecipients(searchRecipientQuery.value);
    }, 250);
};

const toggleSelectRecipient = (user) => {
    if (selectedRecipient.value?.id === user.id) {
        selectedRecipient.value = null;
    } else {
        selectedRecipient.value = user;
    }
};

const sendTweetViaChat = async (targetUser = null) => {
    const recipient = targetUser || selectedRecipient.value;
    if (!recipient || isSendingChat.value) return;

    isSendingChat.value = true;
    const username = props.tweet.user?.username || 'user';
    const tweetUrl = window.location.origin + `/${username}/status/${props.tweet.id}`;
    const comment = chatComment.value.trim();
    const messageContent = comment ? `${comment}\n\n${tweetUrl}` : tweetUrl;

    try {
        await axios.post(route('messages.store'), {
            receiver_id: recipient.id,
            message: messageContent,
        });

        closeSendChatModal();
        showToast(`Đã gửi bài viết đến @${recipient.username}`);
    } catch (err) {
        const errMsg = err.response?.data?.errors?.message || err.response?.data?.message || 'Không thể gửi tin nhắn';
        showToast(errMsg);
    } finally {
        isSendingChat.value = false;
    }
};

// Reply Handling
const openReply = () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    showReplyModal.value = true;
};

const handleReplyFile = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    replyFile.value = e.target.files[0];
    replyPreview.value = URL.createObjectURL(e.target.files[0]);
    replyType.value = replyFile.value.name.split('.').pop().toLowerCase();
};

const submitReply = () => {
    if (!replyText.value && !replyFile.value) return;

    isSubmittingReply.value = true;
    const formData = new FormData();
    if (replyText.value) formData.append('tweet', replyText.value);
    if (replyFile.value) formData.append('file', replyFile.value);
    formData.append('parent_id', props.tweet.id);

    router.post(route('tweets.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            replyText.value = '';
            replyFile.value = null;
            replyPreview.value = '';
            isSubmittingReply.value = false;
            showReplyModal.value = false;
            repliesCount.value += 1;
            showToast('Your reply was sent');
        },
        onError: () => {
            isSubmittingReply.value = false;
            showToast('Failed to post reply');
        }
    });
};

// Quote Handling
const openQuote = () => {
    openRetweetMenu.value = false;
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    showQuoteModal.value = true;
};

const handleQuoteFile = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    quoteFile.value = e.target.files[0];
    quotePreview.value = URL.createObjectURL(e.target.files[0]);
    quoteType.value = quoteFile.value.name.split('.').pop().toLowerCase();
};

const submitQuote = () => {
    if (!quoteText.value && !quoteFile.value) return;

    isSubmittingQuote.value = true;
    const formData = new FormData();
    if (quoteText.value) formData.append('tweet', quoteText.value);
    if (quoteFile.value) formData.append('file', quoteFile.value);
    formData.append('quote_of_id', props.tweet.id);

    router.post(route('tweets.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            quoteText.value = '';
            quoteFile.value = null;
            quotePreview.value = '';
            isSubmittingQuote.value = false;
            showQuoteModal.value = false;
            retweetsCount.value += 1;
            showToast('Your quote post was sent');
        },
        onError: () => {
            isSubmittingQuote.value = false;
            showToast('Failed to post quote');
        }
    });
};

// Outside click handler
const handleClickOutside = (e) => {
    if (optionsRef.value && !optionsRef.value.contains(e.target)) {
        openOptions.value = false;
    }
    if (retweetMenuRef.value && !retweetMenuRef.value.contains(e.target)) {
        openRetweetMenu.value = false;
    }
    if (shareMenuRef.value && !shareMenuRef.value.contains(e.target)) {
        openShareMenu.value = false;
    }
};

// ─── Report Modal ─────────────────────────────────────────────────────────────
const showReportModal = ref(false);
const reportReason = ref('spam');
const reportDescription = ref('');
const isSubmittingReport = ref(false);
const reportReasons = {
    spam:            'Spam',
    harassment:      'Quấy rối / Xúc phạm',
    hate_speech:     'Ngôn ngữ thù địch',
    misinformation:  'Tin giả / Thông tin sai lệch',
    nsfw:            'Nội dung không phù hợp (NSFW)',
    other:           'Khác',
};

const openReport = () => {
    openOptions.value = false;
    if (!currentUser.value) { router.visit(route('login')); return; }
    showReportModal.value = true;
};

const submitReport = () => {
    isSubmittingReport.value = true;
    router.post(route('reports.store'), {
        type: 'tweet',
        tweet_id: props.tweet.id,
        reason: reportReason.value,
        description: reportDescription.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showReportModal.value = false;
            reportReason.value = 'spam';
            reportDescription.value = '';
            showToast('Báo cáo đã được gửi thành công!');
        },
        onError: (errors) => {
            showToast(errors.report || 'Đã xảy ra lỗi khi gửi báo cáo.');
        },
        onFinish: () => { isSubmittingReport.value = false; },
    });
};

// ─── Edit Tweet Modal ─────────────────────────────────────────────────────────
const showEditModal = ref(false);
const editText = ref('');
const editFile = ref(null);
const editPreview = ref('');
const removeCurrentFile = ref(false);
const isSubmittingEdit = ref(false);

const openEditModal = () => {
    openOptions.value = false;
    editText.value = props.tweet.tweet || '';
    editFile.value = null;
    editPreview.value = props.tweet.file || '';
    removeCurrentFile.value = false;
    showEditModal.value = true;
};

const handleEditFile = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    editFile.value = e.target.files[0];
    editPreview.value = URL.createObjectURL(e.target.files[0]);
    removeCurrentFile.value = false;
};

const removeEditMedia = () => {
    editFile.value = null;
    editPreview.value = '';
    removeCurrentFile.value = true;
};

const submitEdit = () => {
    if (!editText.value.trim() && !editPreview.value) {
        showToast('Bài viết không được để trống.');
        return;
    }

    isSubmittingEdit.value = true;
    const formData = new FormData();
    formData.append('_method', 'PATCH');
    formData.append('tweet', editText.value);
    if (removeCurrentFile.value) {
        formData.append('remove_file', '1');
    }
    if (editFile.value) {
        formData.append('file', editFile.value);
    }

    router.post(route('tweets.update', props.tweet.id), formData, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            isSubmittingEdit.value = false;
            showToast('Đã cập nhật bài viết thành công!');
        },
        onError: (errs) => {
            isSubmittingEdit.value = false;
            showToast(errs.tweet || errs.file || 'Cập nhật thất bại.');
        },
        onFinish: () => {
            isSubmittingEdit.value = false;
        }
    });
};

const formatTweetText = (text) => {
    if (!text) return '';
    let escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    escaped = escaped.replace(/@([a-zA-Z0-9_]{3,15})/g, (match, username) => {
        return `<a href="/${username}" class="text-[#1C9CEF] hover:underline" onclick="event.stopPropagation()">${match}</a>`;
    });

    escaped = escaped.replace(/#([a-zA-Z0-9_\u00C0-\u024F\u1E00-\u1EFF]+)/gu, (match, tag) => {
        return `<a href="/search?q=%23${encodeURIComponent(tag)}" class="text-[#1C9CEF] hover:underline" onclick="event.stopPropagation()">${match}</a>`;
    });

    return escaped;
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div
        @click="goToStatus"
        class="flex w-full border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-[#070707] transition duration-150 px-4 py-3 cursor-pointer text-gray-900 dark:text-white"
    >
        <!-- Author Avatar -->
        <div class="mr-3 flex-shrink-0">
            <img
                class="rounded-full w-10 h-10 object-cover bg-gray-700 hover:opacity-90 transition cursor-pointer"
                :src="tweet.user?.avatar_url || 'https://ui-avatars.com/api/?name=User&background=1C9CEF&color=fff'"
                :alt="tweet.user?.name || 'User'"
                @click.stop="router.visit(`/${tweet.user?.username || 'user'}`)"
            >
        </div>

        <!-- Main Body -->
        <div class="w-full min-w-0">
            <!-- Header: Author Name, Handle, Time, Options -->
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center space-x-1.5 truncate">
                    <span
                        @click.stop="router.visit(`/${tweet.user?.username || 'user'}`)"
                        class="font-bold text-gray-900 dark:text-white text-[15px] hover:underline cursor-pointer truncate"
                    >
                        {{ tweet.user?.name || 'User' }}
                    </span>
                    <span class="text-gray-500 text-[14px] truncate">
                        @{{ tweet.user?.username || 'user' }}
                    </span>
                    <span class="text-gray-600 text-xs">·</span>
                    <span class="text-gray-500 text-[13px] hover:underline whitespace-nowrap">
                        {{ tweet.created_at_human || 'now' }}
                    </span>
                </div>

                <!-- Three dots options dropdown -->
                <div class="relative" ref="optionsRef">
                    <button
                        type="button"
                        @click.stop="openOptions = !openOptions"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-gray-500 hover:text-[#1C9CEF] transition cursor-pointer"
                    >
                        <DotsHorizontal :size="18" />
                    </button>

                    <div
                        v-if="openOptions"
                        class="absolute right-0 mt-1 w-52 bg-white dark:bg-black border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl py-1 z-30"
                    >
                        <Link
                            v-if="isOwner"
                            as="button"
                            method="delete"
                            :href="route('tweets.destroy', { tweet: tweet.id })"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-900 text-red-500 text-sm font-semibold cursor-pointer transition"
                            @click.stop="openOptions = false"
                        >
                            <TrashCanOutline class="mr-2.5" fillColor="#EF4444" :size="18"/>
                            <span>Delete tweet</span>
                        </Link>

                        <button
                            v-if="isOwner"
                            type="button"
                            @click.stop="openEditModal"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-900 text-[#1C9CEF] text-sm font-semibold cursor-pointer transition"
                        >
                            <PencilOutline class="mr-2.5" fillColor="#1C9CEF" :size="18"/>
                            <span>Sửa bài viết</span>
                        </button>

                        <button
                            type="button"
                            @click.stop="copyShareLink(); openOptions = false;"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 text-sm cursor-pointer transition"
                        >
                            <ShareVariantOutline class="mr-2.5 text-gray-400" :size="18"/>
                            <span>Copy link to tweet</span>
                        </button>

                        <!-- Report button (only for non-owners who are logged in) -->
                        <button
                            v-if="currentUser && !isOwner"
                            type="button"
                            @click.stop="openReport"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-yellow-500 text-sm cursor-pointer transition"
                        >
                            <FlagOutline class="mr-2.5" fillColor="#EAB308" :size="18"/>
                            <span>Báo cáo bài viết</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tweet text content with clickable #hashtags and @mentions -->
            <div
                v-if="tweet.tweet"
                class="text-gray-900 dark:text-white text-[15px] leading-relaxed break-words whitespace-pre-line mb-2"
                v-html="formatTweetText(tweet.tweet)"
            ></div>

            <!-- Attached Media -->
            <div v-if="tweet.file" class="my-2 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 max-h-[450px]">
                <video
                    v-if="tweet.is_video"
                    controls
                    class="w-full max-h-[450px] object-cover rounded-2xl"
                    :src="tweet.file"
                ></video>
                <img
                    v-else
                    :src="tweet.file"
                    class="w-full max-h-[450px] object-cover rounded-2xl"
                    alt="Attachment"
                />
            </div>

            <!-- Quoted Tweet Card (Nested preview) -->
            <div
                v-if="tweet.quote_of"
                class="my-2.5 border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 rounded-2xl p-3 bg-gray-50 dark:bg-black transition"
                @click.stop="router.visit(`/${tweet.quote_of.user?.username}/status/${tweet.quote_of.id}`)"
            >
                <div class="flex items-center space-x-2 mb-1.5">
                    <img
                        :src="tweet.quote_of.user?.avatar_url || 'https://ui-avatars.com/api/?name=User'"
                        class="rounded-full w-5 h-5 object-cover"
                    />
                    <span class="font-bold text-sm text-white">{{ tweet.quote_of.user?.name }}</span>
                    <span class="text-xs text-gray-500">@{{ tweet.quote_of.user?.username }}</span>
                </div>
                <div class="text-sm text-gray-200 line-clamp-3">{{ tweet.quote_of.tweet }}</div>
                <div v-if="tweet.quote_of.file" class="mt-2 rounded-xl overflow-hidden max-h-40">
                    <img v-if="!tweet.quote_of.is_video" :src="tweet.quote_of.file" class="w-full object-cover max-h-40" />
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="flex items-center justify-between text-gray-500 max-w-md pt-2 text-xs">
                <!-- Reply Button -->
                <button
                    type="button"
                    @click.stop="openReply"
                    class="flex items-center space-x-2 group hover:text-[#1C9CEF] transition cursor-pointer"
                >
                    <div class="p-1.5 rounded-full group-hover:bg-blue-900/30 transition">
                        <MessageOutline :size="18" />
                    </div>
                    <span>{{ repliesCount }}</span>
                </button>

                <!-- Retweet / Repost Button with Popover -->
                <div class="relative" ref="retweetMenuRef">
                    <button
                        type="button"
                        @click.stop="openRetweetMenu = !openRetweetMenu"
                        :class="isRetweeted ? 'text-green-500 font-bold' : 'group hover:text-green-500'"
                        class="flex items-center space-x-2 transition cursor-pointer"
                    >
                        <div class="p-1.5 rounded-full group-hover:bg-green-900/30 transition">
                            <Sync :size="18" />
                        </div>
                        <span>{{ retweetsCount }}</span>
                    </button>

                    <!-- Retweet / Quote Dropdown -->
                    <div
                        v-if="openRetweetMenu"
                        class="absolute bottom-8 left-0 w-44 bg-black border border-gray-700 rounded-2xl shadow-2xl py-1 z-30"
                    >
                        <button
                            type="button"
                            @click.stop="toggleRetweet"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-sm font-semibold cursor-pointer transition"
                            :class="isRetweeted ? 'text-red-400' : 'text-gray-200'"
                        >
                            <Sync class="mr-2" :size="18"/>
                            <span>{{ isRetweeted ? 'Undo repost' : 'Repost' }}</span>
                        </button>
                        <button
                            type="button"
                            @click.stop="openQuote"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-gray-200 text-sm font-semibold cursor-pointer transition"
                        >
                            <PencilOutline class="mr-2" :size="18"/>
                            <span>Quote</span>
                        </button>
                    </div>
                </div>

                <!-- Like Button -->
                <button
                    type="button"
                    @click.stop="toggleLike"
                    :class="isLiked ? 'text-[#F91880] font-bold' : 'group hover:text-[#F91880]'"
                    class="flex items-center space-x-2 transition cursor-pointer"
                >
                    <div class="p-1.5 rounded-full group-hover:bg-pink-900/30 transition">
                        <Heart v-if="isLiked" fillColor="#F91880" :size="18" />
                        <HeartOutline v-else :size="18" />
                    </div>
                    <span>{{ likesCount }}</span>
                </button>

                <!-- Views Count -->
                <div class="flex items-center space-x-1.5 text-gray-600">
                    <div class="p-1.5">
                        <ChartBar :size="18" />
                    </div>
                    <span>{{ viewsCount }}</span>
                </div>

                <!-- Bookmark Button -->
                <button
                    type="button"
                    @click.stop="toggleBookmark"
                    :class="isBookmarked ? 'text-[#1C9CEF]' : 'hover:text-[#1C9CEF]'"
                    class="p-1.5 rounded-full hover:bg-blue-900/30 transition cursor-pointer"
                    title="Bookmark"
                >
                    <Bookmark v-if="isBookmarked" fillColor="#1C9CEF" :size="18" />
                    <BookmarkOutline v-else :size="18" />
                </button>

                <!-- Share Button & Popover -->
                <div class="relative" ref="shareMenuRef">
                    <button
                        type="button"
                        @click.stop="openShareMenu = !openShareMenu"
                        :class="openShareMenu ? 'text-[#1C9CEF] bg-blue-900/20' : 'hover:text-[#1C9CEF] hover:bg-blue-900/30'"
                        class="p-1.5 rounded-full transition cursor-pointer flex items-center justify-center"
                        title="Share"
                    >
                        <ShareVariantOutline :size="18" />
                    </button>

                    <!-- Share Popover Menu (Matching User's Screenshot) -->
                    <transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <div
                            v-if="openShareMenu"
                            class="absolute right-0 bottom-full mb-2 w-48 bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl py-1.5 z-40 overflow-hidden"
                            @click.stop
                        >
                            <!-- Send via Chat -->
                            <button
                                type="button"
                                @click.stop="openSendViaChatModal"
                                class="w-full flex items-center px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-[#16181c] text-gray-900 dark:text-white text-[15px] font-semibold transition cursor-pointer text-left group"
                            >
                                <MessageOutline class="mr-3 text-gray-700 dark:text-white flex-shrink-0 group-hover:text-[#1C9CEF]" :size="19" />
                                <span>Send via Chat</span>
                            </button>

                            <!-- Copy link -->
                            <button
                                type="button"
                                @click.stop="handleCopyLink"
                                class="w-full flex items-center px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-[#16181c] text-gray-900 dark:text-white text-[15px] font-semibold transition cursor-pointer text-left group"
                            >
                                <LinkVariant class="mr-3 text-gray-700 dark:text-white flex-shrink-0 group-hover:text-[#1C9CEF]" :size="19" />
                                <span>Copy link</span>
                            </button>
                        </div>
                    </transition>
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

        <!-- Reply Modal -->
        <div
            v-if="showReplyModal"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-start justify-center p-3"
            @click.self="showReplyModal = false"
        >
            <div class="w-full max-w-lg mt-14 bg-black border border-gray-800 rounded-2xl p-4 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                    <button
                        type="button"
                        @click="showReplyModal = false"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-white"
                    >
                        <Close :size="20" />
                    </button>
                    <button
                        :disabled="(!replyText && !replyFile) || isSubmittingReply"
                        :class="(!replyText && !replyFile) || isSubmittingReply ? 'bg-[#124D77] text-gray-400 cursor-not-allowed' : 'bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white cursor-pointer'"
                        @click="submitReply"
                        class="font-bold text-sm px-5 py-1.5 rounded-full transition"
                    >
                        <span v-if="isSubmittingReply">Replying...</span>
                        <span v-else>Reply</span>
                    </button>
                </div>

                <!-- Original Tweet in Reply Modal -->
                <div class="flex pt-3 pb-2 text-sm">
                    <img :src="tweet.user?.avatar_url" class="rounded-full w-9 h-9 object-cover mr-3" />
                    <div class="flex-1">
                        <div class="flex items-center space-x-1.5">
                            <span class="font-bold text-white">{{ tweet.user?.name }}</span>
                            <span class="text-xs text-gray-500">@{{ tweet.user?.username }}</span>
                        </div>
                        <div class="text-gray-300 line-clamp-3 mt-1">{{ tweet.tweet }}</div>
                        <div class="text-xs text-gray-500 mt-2">
                            Replying to <span class="text-[#1C9CEF]">@{{ tweet.user?.username }}</span>
                        </div>
                    </div>
                </div>

                <!-- Current User Reply Input -->
                <div class="flex pt-3 border-t border-gray-800/80">
                    <img :src="currentUser?.avatar_url" class="rounded-full w-9 h-9 object-cover mr-3" />
                    <div class="flex-1">
                        <textarea
                            v-model="replyText"
                            placeholder="Post your reply"
                            rows="3"
                            class="w-full bg-black border-0 text-white placeholder-gray-500 focus:ring-0 resize-none text-[16px] p-0"
                        ></textarea>

                        <div v-if="replyPreview" class="relative my-2 rounded-xl overflow-hidden border border-gray-800 max-h-40">
                            <button
                                type="button"
                                @click="replyFile = null; replyPreview = '';"
                                class="absolute top-1.5 right-1.5 bg-black/80 hover:bg-black p-1 rounded-full text-white cursor-pointer"
                            >
                                <Close :size="16" />
                            </button>
                            <img :src="replyPreview" class="w-full max-h-40 object-cover" />
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-800">
                            <label for="replyModalFile" class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF]">
                                <ImageOutline fillColor="#1C9CEF" :size="20" />
                            </label>
                            <input type="file" id="replyModalFile" class="hidden" accept="image/*,video/mp4" @change="handleReplyFile">

                            <span class="text-xs text-gray-500">{{ 280 - (replyText ? replyText.length : 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quote Modal -->
        <div
            v-if="showQuoteModal"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-start justify-center p-3"
            @click.self="showQuoteModal = false"
        >
            <div class="w-full max-w-lg mt-14 bg-black border border-gray-800 rounded-2xl p-4 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                    <button
                        type="button"
                        @click="showQuoteModal = false"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-white"
                    >
                        <Close :size="20" />
                    </button>
                    <button
                        :disabled="(!quoteText && !quoteFile) || isSubmittingQuote"
                        :class="(!quoteText && !quoteFile) || isSubmittingQuote ? 'bg-[#124D77] text-gray-400 cursor-not-allowed' : 'bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white cursor-pointer'"
                        @click="submitQuote"
                        class="font-bold text-sm px-5 py-1.5 rounded-full transition"
                    >
                        <span v-if="isSubmittingQuote">Posting...</span>
                        <span v-else>Quote</span>
                    </button>
                </div>

                <!-- Current User Input -->
                <div class="flex pt-3">
                    <img :src="currentUser?.avatar_url" class="rounded-full w-9 h-9 object-cover mr-3" />
                    <div class="flex-1">
                        <textarea
                            v-model="quoteText"
                            placeholder="Add a comment"
                            rows="3"
                            class="w-full bg-black border-0 text-white placeholder-gray-500 focus:ring-0 resize-none text-[16px] p-0"
                        ></textarea>

                        <!-- Attached file preview -->
                        <div v-if="quotePreview" class="relative my-2 rounded-xl overflow-hidden border border-gray-800 max-h-40">
                            <button
                                type="button"
                                @click="quoteFile = null; quotePreview = '';"
                                class="absolute top-1.5 right-1.5 bg-black/80 hover:bg-black p-1 rounded-full text-white cursor-pointer"
                            >
                                <Close :size="16" />
                            </button>
                            <img :src="quotePreview" class="w-full max-h-40 object-cover" />
                        </div>

                        <!-- Quoted Tweet Card Box -->
                        <div class="my-3 border border-gray-800 rounded-2xl p-3 bg-[#0d0d0d]">
                            <div class="flex items-center space-x-1.5 mb-1">
                                <img :src="tweet.user?.avatar_url" class="rounded-full w-4 h-4 object-cover" />
                                <span class="font-bold text-xs text-white">{{ tweet.user?.name }}</span>
                                <span class="text-xs text-gray-500">@{{ tweet.user?.username }}</span>
                            </div>
                            <div class="text-xs text-gray-300 line-clamp-3">{{ tweet.tweet }}</div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-800">
                            <label for="quoteModalFile" class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF]">
                                <ImageOutline fillColor="#1C9CEF" :size="20" />
                            </label>
                            <input type="file" id="quoteModalFile" class="hidden" accept="image/*,video/mp4" @change="handleQuoteFile">

                            <span class="text-xs text-gray-500">{{ 280 - (quoteText ? quoteText.length : 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Report Modal ──────────────────────────────────────────────────── -->
    <Teleport to="body">
        <div
            v-if="showReportModal"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click.self="showReportModal = false"
        >
            <div class="bg-gray-950 border border-gray-700 rounded-2xl w-full max-w-sm shadow-2xl">
                <div class="px-6 py-5 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="text-base font-bold">🚨 Báo cáo bài viết</h3>
                    <button @click="showReportModal = false" class="p-1.5 hover:bg-gray-800 rounded-full text-gray-400">
                        <Close :size="18" />
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Lý do báo cáo:</label>
                        <div class="space-y-2">
                            <label
                                v-for="(label, key) in reportReasons"
                                :key="key"
                                class="flex items-center space-x-3 cursor-pointer group"
                            >
                                <input
                                    type="radio"
                                    :value="key"
                                    v-model="reportReason"
                                    class="accent-[#1C9CEF]"
                                />
                                <span class="text-sm text-gray-300 group-hover:text-white transition">{{ label }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Mô tả thêm (tuỳ chọn):</label>
                        <textarea
                            v-model="reportDescription"
                            rows="2"
                            maxlength="500"
                            placeholder="Thêm thông tin về vấn đề..."
                            class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-[#1C9CEF] resize-none"
                        />
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                    <button
                        @click="showReportModal = false"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-gray-700 rounded-full transition"
                    >
                        Hủy
                    </button>
                    <button
                        @click="submitReport"
                        :disabled="isSubmittingReport"
                        class="px-5 py-2 text-sm bg-yellow-600 hover:bg-yellow-500 text-white font-semibold rounded-full transition disabled:opacity-50"
                    >
                        {{ isSubmittingReport ? 'Đang gửi...' : 'Gửi báo cáo' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- ─── Edit Tweet Modal ──────────────────────────────────────────────── -->
    <Teleport to="body">
        <div
            v-if="showEditModal"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click.self="showEditModal = false"
        >
            <div class="bg-gray-950 border border-gray-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-white">Chỉnh sửa bài viết</h3>
                    <button @click="showEditModal = false" class="p-1.5 hover:bg-gray-800 rounded-full text-gray-400">
                        <Close :size="18" />
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <textarea
                        v-model="editText"
                        rows="4"
                        maxlength="280"
                        placeholder="Nội dung bài viết..."
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#1C9CEF] resize-none"
                    ></textarea>

                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Hỗ trợ thẻ #hashtag và @mention</span>
                        <span>{{ 280 - (editText ? editText.length : 0) }} ký tự</span>
                    </div>

                    <!-- Current / New Media Preview -->
                    <div v-if="editPreview" class="relative rounded-xl overflow-hidden border border-gray-800 max-h-48">
                        <button
                            type="button"
                            @click="removeEditMedia"
                            class="absolute top-2 right-2 bg-black/80 hover:bg-black p-1.5 rounded-full text-white cursor-pointer z-10 transition"
                            title="Xóa ảnh này"
                        >
                            <Close :size="16" />
                        </button>
                        <img :src="editPreview" class="w-full max-h-48 object-cover" />
                    </div>

                    <!-- File input -->
                    <div class="flex items-center space-x-2 pt-2">
                        <label class="cursor-pointer inline-flex items-center space-x-2 text-xs font-semibold text-[#1C9CEF] hover:bg-blue-950/40 px-3 py-1.5 rounded-full border border-blue-900/50 transition">
                            <ImageOutline :size="18" />
                            <span>{{ editPreview ? 'Thay ảnh khác' : 'Thêm ảnh' }}</span>
                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                class="hidden"
                                @change="handleEditFile"
                            />
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3 bg-gray-950">
                    <button
                        @click="showEditModal = false"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-gray-700 rounded-full transition"
                    >
                        Hủy
                    </button>
                    <button
                        @click="submitEdit"
                        :disabled="isSubmittingEdit || (!editText.trim() && !editPreview)"
                        class="px-5 py-2 text-sm bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white font-semibold rounded-full transition disabled:opacity-50"
                    >
                        {{ isSubmittingEdit ? 'Đang lưu...' : 'Lưu thay đổi' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- ─── Send via Chat Modal ───────────────────────────────────────────── -->
    <Teleport to="body">
        <div
            v-if="showSendChatModal"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4"
            @click.self="closeSendChatModal"
        >
            <div
                class="w-full max-w-lg bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl flex flex-col max-h-[88vh] overflow-hidden"
                @click.stop
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-center space-x-3">
                        <button
                            type="button"
                            @click="closeSendChatModal"
                            class="p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white transition cursor-pointer"
                        >
                            <Close :size="20" />
                        </button>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">
                            Gửi bài viết qua Tin nhắn
                        </h2>
                    </div>

                    <button
                        type="button"
                        v-if="selectedRecipient"
                        @click="sendTweetViaChat()"
                        :disabled="isSendingChat"
                        :class="isSendingChat ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#1A8CD8] cursor-pointer'"
                        class="bg-[#1C9CEF] text-white text-xs sm:text-sm font-bold px-4 py-1.5 rounded-full transition shadow flex items-center space-x-1.5"
                    >
                        <Send :size="16" />
                        <span>{{ isSendingChat ? 'Đang gửi...' : 'Gửi' }}</span>
                    </button>
                </div>

                <!-- Search Input Bar -->
                <div class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-800/80">
                    <div class="relative flex items-center">
                        <Magnify class="absolute left-3 text-gray-400 dark:text-gray-500" :size="20" />
                        <input
                            v-model="searchRecipientQuery"
                            @input="onSearchRecipientInput"
                            type="text"
                            placeholder="Tìm kiếm bạn bè theo tên hoặc @username..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-900 border border-transparent focus:border-[#1C9CEF] focus:bg-white dark:focus:bg-black text-gray-900 dark:text-white placeholder-gray-500 rounded-full text-sm outline-none transition"
                        />
                    </div>
                </div>

                <!-- Recipient List -->
                <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800/50 min-h-[160px] max-h-[260px]">
                    <!-- Loading State -->
                    <div v-if="isLoadingRecipients" class="py-10 text-center text-gray-500 text-sm">
                        <div class="inline-block w-6 h-6 border-2 border-[#1C9CEF] border-t-transparent rounded-full animate-spin mb-2"></div>
                        <p>Đang tải danh sách...</p>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="chatRecipients.length === 0" class="py-10 text-center text-gray-500 text-sm px-4">
                        Không tìm thấy người dùng phù hợp.
                    </div>

                    <!-- List of Candidates -->
                    <div
                        v-else
                        v-for="user in chatRecipients"
                        :key="user.id"
                        @click="toggleSelectRecipient(user)"
                        :class="selectedRecipient?.id === user.id ? 'bg-blue-50 dark:bg-blue-950/40' : 'hover:bg-gray-50 dark:hover:bg-gray-900/60'"
                        class="px-4 py-3 flex items-center justify-between cursor-pointer transition select-none"
                    >
                        <div class="flex items-center space-x-3 min-w-0 pr-2">
                            <img
                                :src="user.avatar_url"
                                :alt="user.name"
                                class="w-10 h-10 rounded-full object-cover bg-gray-200 dark:bg-gray-800 flex-shrink-0"
                            />
                            <div class="min-w-0">
                                <div class="font-bold text-gray-900 dark:text-white text-sm truncate">
                                    {{ user.name }}
                                </div>
                                <span class="text-xs text-gray-500 truncate block">
                                    @{{ user.username }}
                                </span>
                            </div>
                        </div>

                        <!-- Action: Quick Send button or Check selection -->
                        <div class="flex items-center space-x-2 flex-shrink-0" @click.stop>
                            <button
                                type="button"
                                @click="sendTweetViaChat(user)"
                                :disabled="isSendingChat"
                                class="px-3 py-1 bg-[#1C9CEF]/10 hover:bg-[#1C9CEF] text-[#1C9CEF] hover:text-white text-xs font-bold rounded-full transition cursor-pointer"
                            >
                                Gửi ngay
                            </button>

                            <button
                                type="button"
                                @click="toggleSelectRecipient(user)"
                                class="p-1 rounded-full transition"
                            >
                                <CheckCircle
                                    v-if="selectedRecipient?.id === user.id"
                                    class="text-[#1C9CEF]"
                                    :size="22"
                                />
                                <div
                                    v-else
                                    class="w-[20px] h-[20px] rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-[#1C9CEF] transition"
                                ></div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tweet Preview Snippet & Comment Input (Bottom Section) -->
                <div class="p-3 border-t border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-[#070707]">
                    <!-- Selected Recipient Tag (if selected) -->
                    <div v-if="selectedRecipient" class="mb-2 flex items-center justify-between bg-blue-100/60 dark:bg-blue-900/40 text-blue-900 dark:text-blue-200 px-3 py-1.5 rounded-lg text-xs font-medium">
                        <span class="truncate">
                            Gửi đến: <strong>{{ selectedRecipient.name }}</strong> (@{{ selectedRecipient.username }})
                        </span>
                        <button
                            type="button"
                            @click="selectedRecipient = null"
                            class="text-blue-500 hover:text-blue-700 ml-2 font-bold cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Mini Tweet Preview Card -->
                    <div class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/60 flex items-start space-x-2.5 mb-2.5">
                        <img
                            :src="tweet.user?.avatar_url || 'https://ui-avatars.com/api/?name=User'"
                            class="w-7 h-7 rounded-full object-cover flex-shrink-0"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center space-x-1.5 text-xs truncate">
                                <span class="font-bold text-gray-900 dark:text-white truncate">{{ tweet.user?.name }}</span>
                                <span class="text-gray-500">@{{ tweet.user?.username }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 mt-0.5 break-words">
                                {{ tweet.tweet || (tweet.file ? '[File đính kèm]' : '') }}
                            </p>
                        </div>
                    </div>

                    <!-- Optional Comment Input -->
                    <div class="flex items-center space-x-2">
                        <input
                            v-model="chatComment"
                            type="text"
                            maxlength="500"
                            placeholder="Viết thêm tin nhắn (tùy chọn)..."
                            class="flex-1 px-3.5 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-full text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-500 outline-none focus:border-[#1C9CEF] transition"
                            @keydown.enter.prevent="selectedRecipient ? sendTweetViaChat() : null"
                        />

                        <button
                            type="button"
                            @click="sendTweetViaChat()"
                            :disabled="!selectedRecipient || isSendingChat"
                            :class="!selectedRecipient || isSendingChat ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#1A8CD8] cursor-pointer shadow'"
                            class="bg-[#1C9CEF] text-white p-2 sm:px-4 sm:py-2 rounded-full font-bold text-xs sm:text-sm flex items-center space-x-1 transition flex-shrink-0"
                            title="Gửi"
                        >
                            <Send :size="16" />
                            <span class="hidden sm:inline">Gửi</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
