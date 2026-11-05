<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue';

const page = usePage();
const searchQuery = ref('');

const allMenuItems = [
    {
        title: 'Your account',
        route: 'settings.account',
        url: '/settings/account',
        isExternal: false,
    },
    {
        title: 'Monetization',
        route: 'creator_studio',
        url: '/creator-studio',
        isExternal: false,
    },
    {
        title: 'Premium',
        route: null,
        url: null,
        isExternal: false,
        placeholder: 'Premium features coming soon',
    },
    {
        title: 'Security and account access',
        route: null,
        url: null,
        isExternal: false,
        placeholder: 'Manage your two-factor authentication and security keys',
    },
    {
        title: 'Privacy and safety',
        route: 'settings.privacy',
        url: '/settings/privacy',
        isExternal: false,
    },
    {
        title: 'Notifications',
        route: 'settings.notifications',
        url: '/settings/notifications',
        isExternal: false,
    },
    {
        title: 'Accessibility, display, and languages',
        route: null,
        url: null,
        isExternal: false,
        placeholder: 'Manage your color contrast, fonts and language preferences',
    },
    {
        title: 'Additional resources',
        route: null,
        url: null,
        isExternal: false,
        placeholder: 'Release notes, terms of service and developer documentation',
    },
    {
        title: 'Help Center',
        route: null,
        url: 'https://help.twitter.com',
        isExternal: true,
    },
];

const filteredMenuItems = computed(() => {
    if (!searchQuery.value.trim()) return allMenuItems;
    const q = searchQuery.value.toLowerCase().trim();
    return allMenuItems.filter(item => item.title.toLowerCase().includes(q));
});

const isCurrentRoute = (item) => {
    if (!item.route && !item.url) return false;
    if (item.route && route().current(item.route)) return true;
    if (item.url && page.url === item.url) return true;
    return false;
};
const noticeItem = ref(null);
const openNotice = (item) => {
    noticeItem.value = item;
};
</script>

<template>
    <TwitterLayout>
        <div class="flex h-full w-full bg-white dark:bg-black text-gray-900 dark:text-white select-none">
            <!-- Left Submenu (Settings List) -->
            <div class="w-full md:w-[350px] lg:w-[380px] flex-shrink-0 border-r border-gray-200 dark:border-gray-800 flex flex-col h-full overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 z-10 bg-white/80 dark:bg-black/80 backdrop-blur-md px-4 pt-3 pb-2">
                    <h1 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        Settings
                    </h1>

                    <!-- Search Input -->
                    <div class="relative mt-3">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <Magnify :size="18" />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search Settings"
                            class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-[#16181c] border border-transparent focus:border-[#1C9CEF] focus:bg-white dark:focus:bg-black text-sm text-gray-900 dark:text-white rounded-full focus:outline-none transition-all placeholder-gray-500"
                        />
                    </div>
                </div>

                <!-- Menu Items List -->
                <div class="flex-1 py-2 divide-y-0">
                    <template v-for="item in filteredMenuItems" :key="item.title">
                        <!-- Normal Route Link -->
                        <Link
                            v-if="item.route && !item.isExternal"
                            :href="route(item.route)"
                            :class="[
                                isCurrentRoute(item)
                                    ? 'bg-gray-100 dark:bg-[#16181c] border-r-2 border-[#1C9CEF] text-gray-900 dark:text-white font-bold'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/60 font-medium'
                            ]"
                            class="flex items-center justify-between px-4 py-3.5 text-[15px] transition cursor-pointer group"
                        >
                            <span>{{ item.title }}</span>
                            <ChevronRight
                                :size="20"
                                :class="isCurrentRoute(item) ? 'text-gray-900 dark:text-white' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'"
                            />
                        </Link>

                        <!-- External Link (Help Center) -->
                        <a
                            v-else-if="item.isExternal"
                            :href="item.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center justify-between px-4 py-3.5 text-[15px] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/60 font-medium transition cursor-pointer group"
                        >
                            <span>{{ item.title }}</span>
                            <OpenInNew :size="18" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                        </a>

                        <!-- Informational / Upcoming item -->
                        <div
                            v-else
                            @click="openNotice(item)"
                            class="flex items-center justify-between px-4 py-3.5 text-[15px] text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-900/40 font-medium transition cursor-pointer group"
                            :title="item.placeholder"
                        >
                            <div class="flex flex-col">
                                <span>{{ item.title }}</span>
                            </div>
                            <ChevronRight :size="20" class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                        </div>
                    </template>

                    <div v-if="filteredMenuItems.length === 0" class="p-6 text-center text-sm text-gray-500">
                        No settings found matching "{{ searchQuery }}"
                    </div>
                </div>
            </div>

            <!-- Right Detail Panel -->
            <div class="flex-1 flex flex-col h-full overflow-y-auto">
                <slot />
            </div>
        </div>

        <!-- Informational Notice Modal -->
        <div v-if="noticeItem" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="noticeItem = null">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-sm p-6 shadow-2xl border border-gray-200 dark:border-gray-800 text-center" @click.stop>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ noticeItem.title }}</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    {{ noticeItem.placeholder }}
                </p>
                <button @click="noticeItem = null" class="mt-5 w-full py-2 bg-[#1C9CEF] text-white font-bold text-sm rounded-full">
                    Understood
                </button>
            </div>
        </div>
    </TwitterLayout>
</template>

