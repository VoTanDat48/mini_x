<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    search: String,
});

const page = usePage();
const flash = computed(() => page.props.flash);
const authUser = computed(() => page.props.auth?.user);

const searchQuery = ref(props.search || '');

const doSearch = () => {
    router.get(route('admin.users'), { search: searchQuery.value }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const toggleStatus = (user) => {
    const action = user.status === 'active' ? 'đình chỉ' : 'kích hoạt lại';
    if (!confirm(`Bạn có chắc muốn ${action} tài khoản @${user.username}?`)) return;
    router.post(route('admin.users.toggle_status', user.id), {}, { preserveScroll: true });
};

const toggleRole = (user) => {
    const action = user.role === 'admin' ? 'hạ xuống User' : 'nâng lên Admin';
    if (!confirm(`Bạn có chắc muốn ${action} tài khoản @${user.username}?`)) return;
    router.post(route('admin.users.toggle_role', user.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Admin - Người dùng" />

    <div class="min-h-screen bg-black text-white">
        <!-- Top bar -->
        <div class="border-b border-gray-800 bg-gray-950 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center space-x-3">
                <Link :href="route('admin.dashboard')" class="text-gray-400 hover:text-white text-sm">← Dashboard</Link>
                <span class="text-gray-600">/</span>
                <h1 class="text-xl font-bold">👤 Quản lý Người dùng</h1>
            </div>
            <Link :href="route('admin.reports')" class="text-sm text-[#1C9CEF] hover:underline">Báo cáo →</Link>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-6">
            <!-- Flash -->
            <div v-if="flash?.success" class="mb-5 bg-green-900/40 border border-green-700 text-green-300 rounded-xl px-4 py-3 text-sm">
                ✅ {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="mb-5 bg-red-900/40 border border-red-700 text-red-300 rounded-xl px-4 py-3 text-sm">
                ❌ {{ flash.error }}
            </div>

            <!-- Search bar -->
            <div class="mb-6 flex gap-3">
                <input
                    v-model="searchQuery"
                    @keyup.enter="doSearch"
                    type="text"
                    placeholder="Tìm theo tên, username hoặc email..."
                    class="flex-1 bg-gray-900 border border-gray-700 rounded-full px-5 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#1C9CEF] transition"
                />
                <button
                    @click="doSearch"
                    class="bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white font-semibold text-sm px-6 py-2.5 rounded-full transition"
                >
                    Tìm kiếm
                </button>
            </div>

            <!-- Table -->
            <div class="bg-gray-950 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-800">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người dùng</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thống kê</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-b border-gray-800/60 hover:bg-gray-900/30 transition last:border-0"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center space-x-3">
                                        <img :src="user.avatar_url" class="w-10 h-10 rounded-full object-cover flex-shrink-0" />
                                        <div>
                                            <p class="font-semibold text-sm flex items-center gap-1.5">
                                                {{ user.name }}
                                                <span v-if="user.role === 'admin'" class="text-xs bg-blue-900/50 text-blue-300 border border-blue-700 px-1.5 py-0.5 rounded-full">Admin</span>
                                            </p>
                                            <p class="text-xs text-gray-500">@{{ user.username }}</p>
                                            <p class="text-xs text-gray-600">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-400">
                                    <div>📝 {{ user.tweets_count }} bài viết</div>
                                    <div>👥 {{ user.followers_count }} followers</div>
                                    <div v-if="user.reports_received_count > 0" class="text-yellow-500">🚨 {{ user.reports_received_count }} báo cáo</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        :class="user.status === 'active' ? 'bg-green-900/50 text-green-400 border-green-700' : 'bg-red-900/50 text-red-400 border-red-700'"
                                        class="text-xs border px-2.5 py-1 rounded-full font-medium"
                                    >
                                        {{ user.status === 'active' ? '✅ Hoạt động' : '🚫 Đình chỉ' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2 flex-wrap">
                                        <!-- Disable actions on self -->
                                        <template v-if="user.id !== authUser?.id">
                                            <button
                                                @click="toggleStatus(user)"
                                                :class="user.status === 'active' ? 'text-red-400 border-red-800 hover:bg-red-900/40' : 'text-green-400 border-green-800 hover:bg-green-900/40'"
                                                class="text-xs border px-3 py-1.5 rounded-full transition"
                                            >
                                                {{ user.status === 'active' ? '🚫 Đình chỉ' : '✅ Kích hoạt' }}
                                            </button>
                                            <button
                                                v-if="user.role !== 'admin'"
                                                @click="toggleRole(user)"
                                                class="text-xs text-blue-400 border border-blue-800 hover:bg-blue-900/40 px-3 py-1.5 rounded-full transition"
                                            >
                                                🔰 Nâng Admin
                                            </button>
                                            <button
                                                v-else
                                                @click="toggleRole(user)"
                                                class="text-xs text-gray-400 border border-gray-700 hover:bg-gray-800 px-3 py-1.5 rounded-full transition"
                                            >
                                                ↓ Hạ User
                                            </button>
                                        </template>
                                        <span v-else class="text-xs text-gray-600 italic">Bạn</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div v-if="users.data.length === 0" class="py-20 text-center text-gray-500 text-sm">
                    Không tìm thấy người dùng nào.
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.links?.length > 3" class="flex justify-center gap-2 mt-6 flex-wrap">
                <Link
                    v-for="link in users.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-sm border transition',
                        link.active ? 'bg-[#1C9CEF] border-[#1C9CEF] text-white' : 'border-gray-700 text-gray-400 hover:text-white',
                        !link.url ? 'opacity-40 pointer-events-none' : '',
                    ]"
                />
            </div>
        </div>
    </div>
</template>

