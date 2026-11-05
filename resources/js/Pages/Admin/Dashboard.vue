<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentReports: Array,
    recentUsers: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash);

const statCards = computed(() => [
    { label: 'Tổng người dùng',       value: props.stats.total_users,      color: 'blue',   icon: '👤' },
    { label: 'Tổng bài viết',          value: props.stats.total_tweets,     color: 'green',  icon: '📝' },
    { label: 'Báo cáo chờ xử lý',     value: props.stats.pending_reports,  color: 'yellow', icon: '🚨' },
    { label: 'Tài khoản đình chỉ',    value: props.stats.suspended_users,  color: 'red',    icon: '🚫' },
    { label: 'User mới hôm nay',      value: props.stats.new_users_today,  color: 'purple', icon: '🆕' },
    { label: 'Bài viết mới hôm nay',  value: props.stats.new_tweets_today, color: 'indigo', icon: '✍️' },
]);

const colorMap = {
    blue:   'bg-blue-900/40 border-blue-700 text-blue-300',
    green:  'bg-green-900/40 border-green-700 text-green-300',
    yellow: 'bg-yellow-900/40 border-yellow-600 text-yellow-300',
    red:    'bg-red-900/40 border-red-700 text-red-300',
    purple: 'bg-purple-900/40 border-purple-700 text-purple-300',
    indigo: 'bg-indigo-900/40 border-indigo-700 text-indigo-300',
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="min-h-screen bg-black text-white">
        <!-- Top bar -->
        <div class="border-b border-gray-800 bg-gray-950 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">🛡️</span>
                <h1 class="text-xl font-bold">Admin Dashboard</h1>
            </div>
            <div class="flex items-center space-x-4">
                <Link :href="route('tweets.index')" class="text-sm text-gray-400 hover:text-white transition">
                    ← Về trang chính
                </Link>
                <span class="text-gray-600">|</span>
                <Link :href="route('admin.reports')" class="text-sm text-[#1C9CEF] hover:underline">Báo cáo</Link>
                <Link :href="route('admin.users')" class="text-sm text-[#1C9CEF] hover:underline">Người dùng</Link>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Flash message -->
            <div v-if="flash?.success" class="mb-6 bg-green-900/40 border border-green-700 text-green-300 rounded-xl px-4 py-3 text-sm">
                ✅ {{ flash.success }}
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-10">
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    :class="colorMap[card.color]"
                    class="border rounded-2xl p-5 flex flex-col"
                >
                    <div class="text-3xl mb-1">{{ card.icon }}</div>
                    <div class="text-3xl font-bold">{{ card.value.toLocaleString() }}</div>
                    <div class="text-sm mt-1 opacity-80">{{ card.label }}</div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Recent Pending Reports -->
                <div class="bg-gray-950 border border-gray-800 rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                        <h2 class="font-bold text-lg">🚨 Báo cáo chờ xử lý</h2>
                        <Link :href="route('admin.reports')" class="text-xs text-[#1C9CEF] hover:underline">
                            Xem tất cả →
                        </Link>
                    </div>
                    <div v-if="recentReports.length === 0" class="px-5 py-8 text-center text-gray-500 text-sm">
                        Không có báo cáo nào đang chờ xử lý.
                    </div>
                    <div v-for="r in recentReports" :key="r.id" class="px-5 py-4 border-b border-gray-800/60 last:border-0">
                        <div class="flex items-start space-x-3">
                            <img :src="r.reporter?.avatar_url" class="w-8 h-8 rounded-full object-cover flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm">
                                    <span class="font-semibold">@{{ r.reporter?.username }}</span>
                                    báo cáo
                                    <span v-if="r.tweet" class="text-yellow-400">bài viết của @{{ r.tweet.user?.username }}</span>
                                    <span v-else class="text-red-400">tài khoản @{{ r.reported_user?.username }}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ r.reason_label }} · {{ r.created_at }}</p>
                            </div>
                            <span class="text-xs bg-yellow-900/50 text-yellow-400 border border-yellow-700 px-2 py-0.5 rounded-full flex-shrink-0">
                                Chờ xử lý
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="bg-gray-950 border border-gray-800 rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                        <h2 class="font-bold text-lg">👤 Người dùng mới nhất</h2>
                        <Link :href="route('admin.users')" class="text-xs text-[#1C9CEF] hover:underline">
                            Xem tất cả →
                        </Link>
                    </div>
                    <div v-for="u in recentUsers" :key="u.id" class="px-5 py-3 border-b border-gray-800/60 last:border-0 flex items-center space-x-3">
                        <img :src="u.avatar_url" class="w-9 h-9 rounded-full object-cover flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">{{ u.name }}</p>
                            <p class="text-xs text-gray-500">@{{ u.username }}</p>
                        </div>
                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <span v-if="u.role === 'admin'" class="text-xs bg-blue-900/50 text-blue-300 border border-blue-700 px-2 py-0.5 rounded-full">Admin</span>
                            <span v-if="u.status === 'suspended'" class="text-xs bg-red-900/50 text-red-400 border border-red-700 px-2 py-0.5 rounded-full">Đình chỉ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

