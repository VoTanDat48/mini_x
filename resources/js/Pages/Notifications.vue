<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Heart from 'vue-material-design-icons/Heart.vue';
import Cached from 'vue-material-design-icons/Cached.vue';
import MessageOutline from 'vue-material-design-icons/MessageOutline.vue';
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue';
import CheckAll from 'vue-material-design-icons/CheckAll.vue';
import FormatQuoteClose from 'vue-material-design-icons/FormatQuoteClose.vue';
import At from 'vue-material-design-icons/At.vue';

const props = defineProps({
    notifications: {
        type: Object,
        default: () => ({ data: [] }),
    },
    unreadCount: {
        type: Number,
        default: 0,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const notifList = computed(() => {
    return props.notifications?.data || [];
});

const markAllRead = () => {
    router.post(route('notifications.read_all'), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Notifications / Twitter" />

    <TwitterLayout>
        <div class="text-white min-h-screen border-b border-gray-800">
            <!-- Header bar -->
            <div class="sticky top-0 bg-black/80 backdrop-blur-md z-20 flex items-center justify-between px-4 py-3 border-b border-gray-800">
                <div>
                    <h1 class="text-xl font-bold leading-tight">Notifications</h1>
                </div>

                <button
                    v-if="notifList && notifList.length > 0"
                    type="button"
                    @click="markAllRead"
                    class="flex items-center space-x-1.5 text-xs font-semibold text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 px-3 py-1.5 rounded-full transition cursor-pointer"
                >
                    <CheckAll :size="16" />
                    <span>Mark all as read</span>
                </button>
            </div>

            <!-- Notifications List -->
            <div v-if="notifList && notifList.length > 0" class="divide-y divide-gray-800">
                <div
                    v-for="notif in notifList"
                    :key="notif.id"
                    :class="!notif.read_at ? 'bg-blue-950/20' : ''"
                    class="flex p-4 hover:bg-gray-950 transition"
                >
                    <!-- Notification Type Icon -->
                    <div class="mr-4 flex-shrink-0 flex justify-end w-8 pt-0.5">
                        <Heart v-if="notif.data.type === 'like'" fillColor="#F91880" :size="24" />
                        <Cached v-else-if="notif.data.type === 'retweet'" fillColor="#00BA7C" :size="24" />
                        <MessageOutline v-else-if="notif.data.type === 'reply'" fillColor="#1C9CEF" :size="24" />
                        <AccountPlus v-else-if="notif.data.type === 'follow'" fillColor="#8B5CF6" :size="24" />
                        <FormatQuoteClose v-else-if="notif.data.type === 'quote'" fillColor="#F59E0B" :size="24" />
                        <At v-else-if="notif.data.type === 'mention'" fillColor="#10B981" :size="24" />
                        <div v-else class="w-6 h-6 rounded-full bg-blue-500"></div>
                    </div>

                    <!-- Notification Body -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 mb-2">
                            <Link :href="route('users.profile', notif.data.sender_username)">
                                <img
                                    :src="notif.data.sender_avatar || 'https://ui-avatars.com/api/?name=User&background=1C9CEF&color=fff'"
                                    :alt="notif.data.sender_name"
                                    class="w-8 h-8 rounded-full object-cover bg-gray-700"
                                />
                            </Link>
                        </div>

                        <div class="text-[15px] text-gray-300">
                            <Link :href="route('users.profile', notif.data.sender_username)" class="font-bold text-white hover:underline">
                                {{ notif.data.sender_name }}
                            </Link>
                            <span v-if="notif.data.type === 'like'"> liked your post</span>
                            <span v-else-if="notif.data.type === 'retweet'"> reposted your post</span>
                            <span v-else-if="notif.data.type === 'reply'"> replied to your post</span>
                            <span v-else-if="notif.data.type === 'follow'"> followed you</span>
                            <span v-else-if="notif.data.type === 'quote'"> quoted your post</span>
                            <span v-else-if="notif.data.type === 'mention'"> mentioned you in a post</span>
                        </div>

                        <!-- Tweet text preview and click-through link if available -->
                        <Link
                            v-if="notif.data.tweet_id"
                            :href="`/${notif.data.sender_username}/status/${notif.data.tweet_id}`"
                            class="block text-sm text-gray-400 hover:text-white mt-1 line-clamp-2 transition bg-gray-900/50 p-2.5 rounded-xl border border-gray-800"
                        >
                            {{ notif.data.tweet_text || 'Xem bài viết...' }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-24 px-6 text-center max-w-sm mx-auto">
                <div class="text-2xl font-black text-white mb-2">
                    Nothing to see here — yet
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    From likes to reposts, mentions and a whole lot more, this is where all the action about your posts and account will happen.
                </p>
            </div>

            <!-- Pagination Controls -->
            <div v-if="notifications?.links && notifications.links.length > 3" class="p-6 flex justify-center space-x-2">
                <Link
                    v-for="link in notifications.links"
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
