<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Tweet from '@/Components/Tweet.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';

const props = defineProps({
    query: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'top', // 'top' | 'latest' | 'people'
    },
    tweets: {
        type: Object,
        default: null,
    },
    users: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const currentSearchTerm = ref(props.query);

const handleSearch = () => {
    if (!currentSearchTerm.value.trim()) return;
    router.visit(route('search', { q: currentSearchTerm.value.trim(), type: props.type }));
};

const switchTab = (newType) => {
    router.visit(route('search', { q: props.query, type: newType }), {
        preserveScroll: true,
        preserveState: true,
    });
};

// Follow state mapping for people search
const followingMap = ref({});
const hoverMap = ref({});

if (props.users?.data) {
    props.users.data.forEach((u) => {
        followingMap.value[u.id] = !!u.is_following;
    });
}

const isFollowing = (userId) => !!followingMap.value[userId];
const isHovering = (userId) => !!hoverMap.value[userId];

const toggleFollow = async (targetUser) => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }

    if (currentUser.value.id === targetUser.id) return;

    const prev = followingMap.value[targetUser.id];
    followingMap.value[targetUser.id] = !prev;

    try {
        const res = await axios.post(route('users.follow', targetUser.id));
        if (res.data && typeof res.data.is_following === 'boolean') {
            followingMap.value[targetUser.id] = res.data.is_following;
        }
    } catch (err) {
        followingMap.value[targetUser.id] = prev;
    }
};

const tweetItems = computed(() => props.tweets?.data || []);
const userItems = computed(() => props.users?.data || []);
</script>

<template>
    <Head :title="`${query} - Search / Twitter`" />

    <TwitterLayout>
        <div class="text-white min-h-screen border-b border-gray-800">
            <!-- Header with Search Input -->
            <div class="sticky top-0 bg-black/80 backdrop-blur-md z-20 px-4 py-2 border-b border-gray-800">
                <div class="flex items-center space-x-4">
                    <Link href="/" class="p-2 rounded-full hover:bg-gray-800 transition cursor-pointer">
                        <ArrowLeft fillColor="#ffffff" :size="20" />
                    </Link>

                    <form @submit.prevent="handleSearch" class="flex-1 flex items-center bg-[#202327] rounded-full px-4 py-1.5 focus-within:ring-1 focus-within:ring-[#1C9CEF]">
                        <Magnify fillColor="#5e5c5c" :size="20" class="mr-2" />
                        <input
                            v-model="currentSearchTerm"
                            type="text"
                            placeholder="Search Twitter"
                            class="w-full bg-transparent border-0 focus:ring-0 text-white placeholder-gray-500 text-sm p-0"
                        />
                    </form>
                </div>

                <!-- Tabs Row -->
                <div class="flex text-sm font-bold text-gray-500 mt-2">
                    <button
                        type="button"
                        @click="switchTab('top')"
                        :class="type === 'top' ? 'text-white' : 'hover:bg-gray-900/50 hover:text-gray-300'"
                        class="flex-1 py-3 text-center transition cursor-pointer relative"
                    >
                        <span>Top</span>
                        <div v-if="type === 'top'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-[#1C9CEF] rounded-full"></div>
                    </button>

                    <button
                        type="button"
                        @click="switchTab('latest')"
                        :class="type === 'latest' ? 'text-white' : 'hover:bg-gray-900/50 hover:text-gray-300'"
                        class="flex-1 py-3 text-center transition cursor-pointer relative"
                    >
                        <span>Latest</span>
                        <div v-if="type === 'latest'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-[#1C9CEF] rounded-full"></div>
                    </button>

                    <button
                        type="button"
                        @click="switchTab('people')"
                        :class="type === 'people' ? 'text-white' : 'hover:bg-gray-900/50 hover:text-gray-300'"
                        class="flex-1 py-3 text-center transition cursor-pointer relative"
                    >
                        <span>People</span>
                        <div v-if="type === 'people'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-12 h-1 bg-[#1C9CEF] rounded-full"></div>
                    </button>
                </div>
            </div>

            <!-- People Results -->
            <div v-if="type === 'people'">
                <div v-if="userItems && userItems.length > 0" class="divide-y divide-gray-800">
                    <div
                        v-for="u in userItems"
                        :key="u.id"
                        class="flex items-start px-4 py-3 hover:bg-gray-950 transition"
                    >
                        <Link :href="route('users.profile', u.username)" class="mr-3 flex-shrink-0">
                            <img
                                :src="u.avatar_url"
                                :alt="u.name"
                                class="w-10 h-10 rounded-full object-cover bg-gray-700"
                            />
                        </Link>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <Link
                                        :href="route('users.profile', u.username)"
                                        class="font-bold text-white text-[15px] hover:underline truncate block"
                                    >
                                        {{ u.name }}
                                    </Link>
                                    <p class="text-sm text-gray-500">
                                        @{{ u.username }}
                                    </p>
                                </div>

                                <button
                                    v-if="currentUser && currentUser.id !== u.id"
                                    type="button"
                                    @click="toggleFollow(u)"
                                    @mouseenter="hoverMap[u.id] = true"
                                    @mouseleave="hoverMap[u.id] = false"
                                    :class="isFollowing(u.id)
                                        ? (isHovering(u.id) ? 'bg-red-950/40 border-red-600 text-red-500' : 'bg-black border-gray-600 text-white')
                                        : 'bg-white hover:bg-gray-200 text-black border-transparent'"
                                    class="border font-bold text-xs px-4 py-1.5 rounded-full transition cursor-pointer min-w-[96px]"
                                >
                                    <span v-if="isFollowing(u.id)">
                                        {{ isHovering(u.id) ? 'Unfollow' : 'Following' }}
                                    </span>
                                    <span v-else>Follow</span>
                                </button>
                            </div>

                            <p v-if="u.bio" class="text-sm text-gray-300 mt-1 whitespace-pre-line leading-snug">
                                {{ u.bio }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="py-24 text-center px-4 max-w-sm mx-auto">
                    <div class="text-2xl font-black text-white mb-2">No results for "{{ query }}"</div>
                    <p class="text-sm text-gray-500">
                        Try searching for people by their display name or @username.
                    </p>
                </div>
            </div>

            <!-- Tweet Results ('top' or 'latest') -->
            <div v-else>
                <div v-if="tweetItems && tweetItems.length > 0" class="divide-y divide-gray-800">
                    <div v-for="t in tweetItems" :key="t.id">
                        <Tweet :tweet="t" />
                    </div>
                </div>

                <div v-else class="py-24 text-center px-4 max-w-sm mx-auto">
                    <div class="text-2xl font-black text-white mb-2">No results for "{{ query }}"</div>
                    <p class="text-sm text-gray-500">
                        Try searching for something else, or check your spelling.
                    </p>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div v-if="(tweets?.links || users?.links) && (tweets?.links?.length > 3 || users?.links?.length > 3)" class="p-6 flex justify-center space-x-2">
                <Link
                    v-for="link in (tweets?.links || users?.links)"
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
