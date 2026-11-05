<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    reports: Object,
    currentStatus: String,
    currentType: String,
    reasons: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash);

// ─── Report Action Modal ──────────────────────────────────────────────────────
const activeReport = ref(null);
const adminNote = ref('');
const showModal = ref(false);
const modalAction = ref(''); // 'resolve' | 'dismiss' | 'delete_tweet'

const openModal = (report, action) => {
    activeReport.value = report;
    modalAction.value = action;
    adminNote.value = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    activeReport.value = null;
    adminNote.value = '';
};

const submitModal = () => {
    if (!activeReport.value) return;

    if (modalAction.value === 'delete_tweet') {
        router.delete(route('admin.reports.delete_tweet', activeReport.value.id), {
            data: { admin_note: adminNote.value },
            preserveScroll: true,
            onSuccess: closeModal,
        });
    } else {
        router.post(route('admin.reports.resolve', activeReport.value.id), {
            action: modalAction.value === 'dismiss' ? 'dismissed' : 'resolved',
            admin_note: adminNote.value,
        }, {
            preserveScroll: true,
            onSuccess: closeModal,
        });
    }
};

// ─── Filter navigation ────────────────────────────────────────────────────────
const setFilter = (status, type) => {
    router.get(route('admin.reports'), { status, type: type || props.currentType }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const statusBadge = (status) => {
    if (status === 'pending')   return 'bg-yellow-900/50 text-yellow-400 border-yellow-700';
    if (status === 'resolved')  return 'bg-green-900/50 text-green-400 border-green-700';
    if (status === 'dismissed') return 'bg-gray-800 text-gray-400 border-gray-600';
    return '';
};

const modalTitle = computed(() => {
    if (modalAction.value === 'delete_tweet') return '🗑️ Xóa bài viết vi phạm';
    if (modalAction.value === 'dismiss')       return '🟢 Bác bỏ báo cáo';
    return '✅ Giải quyết báo cáo';
});

const modalBtnColor = computed(() => {
    if (modalAction.value === 'delete_tweet') return 'bg-red-600 hover:bg-red-700';
    if (modalAction.value === 'dismiss')       return 'bg-gray-700 hover:bg-gray-600';
    return 'bg-green-700 hover:bg-green-600';
});
</script>

<template>
    <Head title="Admin - Báo cáo" />

    <div class="min-h-screen bg-black text-white">
        <!-- Top bar -->
        <div class="border-b border-gray-800 bg-gray-950 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center space-x-3">
                <Link :href="route('admin.dashboard')" class="text-gray-400 hover:text-white text-sm">← Dashboard</Link>
                <span class="text-gray-600">/</span>
                <h1 class="text-xl font-bold">🚨 Quản lý Báo cáo</h1>
            </div>
            <div class="flex items-center space-x-3">
                <Link :href="route('admin.users')" class="text-sm text-[#1C9CEF] hover:underline">Người dùng →</Link>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-6">
            <!-- Flash -->
            <div v-if="flash?.success" class="mb-5 bg-green-900/40 border border-green-700 text-green-300 rounded-xl px-4 py-3 text-sm">
                ✅ {{ flash.success }}
            </div>

            <!-- Filter tabs -->
            <div class="flex flex-wrap gap-2 mb-6">
                <div class="flex bg-gray-900 border border-gray-800 rounded-xl p-1 gap-1">
                    <button
                        v-for="s in ['pending', 'resolved', 'dismissed', 'all']"
                        :key="s"
                        @click="setFilter(s, currentType)"
                        :class="currentStatus === s ? 'bg-[#1C9CEF] text-white' : 'text-gray-400 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    >
                        {{ s === 'pending' ? '⏳ Chờ xử lý' : s === 'resolved' ? '✅ Đã giải quyết' : s === 'dismissed' ? '🚫 Đã bác bỏ' : 'Tất cả' }}
                    </button>
                </div>
                <div class="flex bg-gray-900 border border-gray-800 rounded-xl p-1 gap-1">
                    <button
                        v-for="t in ['all', 'tweet', 'user']"
                        :key="t"
                        @click="setFilter(currentStatus, t)"
                        :class="currentType === t ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg text-sm transition"
                    >
                        {{ t === 'all' ? 'Tất cả loại' : t === 'tweet' ? '📝 Bài viết' : '👤 Tài khoản' }}
                    </button>
                </div>
            </div>

            <!-- Report cards -->
            <div v-if="reports.data.length === 0" class="text-center text-gray-500 py-20">
                Không có báo cáo nào.
            </div>

            <div v-for="report in reports.data" :key="report.id" class="bg-gray-950 border border-gray-800 rounded-2xl mb-4 overflow-hidden">
                <!-- Report header -->
                <div class="px-5 py-4 flex items-start justify-between border-b border-gray-800/60">
                    <div class="flex items-center space-x-3">
                        <img :src="report.reporter?.avatar_url" class="w-8 h-8 rounded-full object-cover" />
                        <div>
                            <p class="text-sm">
                                <span class="font-semibold text-white">@{{ report.reporter?.username }}</span>
                                <span class="text-gray-400"> báo cáo</span>
                            </p>
                            <p class="text-xs text-gray-500">{{ report.reason_label }} · {{ report.created_at }}</p>
                        </div>
                    </div>
                    <span :class="statusBadge(report.status)" class="text-xs border px-2.5 py-1 rounded-full font-medium">
                        {{ report.status_label }}
                    </span>
                </div>

                <!-- Tweet content (if tweet report) -->
                <div v-if="report.tweet" class="px-5 py-4 border-b border-gray-800/60">
                    <p class="text-xs text-gray-500 mb-2 font-semibold uppercase tracking-wide">📝 Bài viết bị báo cáo:</p>
                    <div class="bg-gray-900 rounded-xl p-4 flex items-start space-x-3">
                        <img v-if="report.tweet.user?.avatar_url" :src="report.tweet.user.avatar_url" class="w-8 h-8 rounded-full object-cover flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold">{{ report.tweet.user?.name }} <span class="text-gray-500 font-normal">@{{ report.tweet.user?.username }}</span></p>
                            <p class="text-sm text-gray-300 mt-1 whitespace-pre-line break-words">{{ report.tweet.tweet || '(bài viết chỉ có ảnh/video)' }}</p>
                            <img v-if="report.tweet.file && !report.tweet.is_video" :src="report.tweet.file" class="mt-2 rounded-lg max-h-40 object-cover" />
                        </div>
                    </div>
                </div>

                <!-- User content (if user report) -->
                <div v-if="report.reported_user" class="px-5 py-4 border-b border-gray-800/60">
                    <p class="text-xs text-gray-500 mb-2 font-semibold uppercase tracking-wide">👤 Tài khoản bị báo cáo:</p>
                    <div class="bg-gray-900 rounded-xl p-4 flex items-center space-x-3">
                        <img :src="report.reported_user.avatar_url" class="w-10 h-10 rounded-full object-cover" />
                        <div>
                            <p class="font-semibold">{{ report.reported_user.name }}</p>
                            <p class="text-sm text-gray-400">@{{ report.reported_user.username }}</p>
                        </div>
                        <span v-if="report.reported_user.status === 'suspended'" class="ml-auto text-xs bg-red-900/50 text-red-400 border border-red-700 px-2 py-0.5 rounded-full">
                            Đã đình chỉ
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="report.description" class="px-5 py-3 border-b border-gray-800/60">
                    <p class="text-xs text-gray-500 mb-1">Mô tả của người báo cáo:</p>
                    <p class="text-sm text-gray-300 italic">"{{ report.description }}"</p>
                </div>

                <!-- Admin note (if resolved/dismissed) -->
                <div v-if="report.admin_note" class="px-5 py-3 border-b border-gray-800/60 bg-gray-900/30">
                    <p class="text-xs text-gray-500 mb-1">Ghi chú xử lý:</p>
                    <p class="text-sm text-gray-300">{{ report.admin_note }}</p>
                    <p v-if="report.resolver" class="text-xs text-gray-600 mt-1">— @{{ report.resolver.username }} · {{ report.resolved_at }}</p>
                </div>

                <!-- Actions (only for pending) -->
                <div v-if="report.status === 'pending'" class="px-5 py-4 flex flex-wrap gap-2">
                    <button
                        v-if="report.tweet"
                        @click="openModal(report, 'delete_tweet')"
                        class="text-sm bg-red-900/40 hover:bg-red-900/70 text-red-400 border border-red-800 px-4 py-1.5 rounded-full transition font-medium"
                    >
                        🗑️ Xóa bài viết vi phạm
                    </button>
                    <button
                        @click="openModal(report, 'resolve')"
                        class="text-sm bg-green-900/40 hover:bg-green-900/70 text-green-400 border border-green-800 px-4 py-1.5 rounded-full transition font-medium"
                    >
                        ✅ Đánh dấu đã giải quyết
                    </button>
                    <button
                        @click="openModal(report, 'dismiss')"
                        class="text-sm bg-gray-800 hover:bg-gray-700 text-gray-400 border border-gray-600 px-4 py-1.5 rounded-full transition font-medium"
                    >
                        🚫 Bác bỏ báo cáo
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="reports.links?.length > 3" class="flex justify-center gap-2 mt-6 flex-wrap">
                <Link
                    v-for="link in reports.links"
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

    <!-- Action Modal -->
    <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-gray-950 border border-gray-700 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="px-6 py-5 border-b border-gray-800">
                    <h3 class="text-lg font-bold">{{ modalTitle }}</h3>
                </div>
                <div class="px-6 py-5">
                    <p v-if="modalAction === 'delete_tweet'" class="text-sm text-gray-400 mb-4">
                        Bài viết này sẽ bị <span class="text-red-400 font-semibold">xóa vĩnh viễn</span> và báo cáo sẽ được đánh dấu là "Đã giải quyết". Hành động này không thể hoàn tác.
                    </p>
                    <p v-else-if="modalAction === 'dismiss'" class="text-sm text-gray-400 mb-4">
                        Báo cáo này sẽ được đánh dấu là "Đã bác bỏ" — không có hành động nào được thực hiện.
                    </p>
                    <p v-else class="text-sm text-gray-400 mb-4">
                        Báo cáo sẽ được đánh dấu là "Đã giải quyết".
                    </p>
                    <label class="block text-sm text-gray-400 mb-1">Ghi chú xử lý (tuỳ chọn):</label>
                    <textarea
                        v-model="adminNote"
                        rows="3"
                        placeholder="Nhập ghi chú lý do xử lý..."
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 resize-none"
                    />
                </div>
                <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                    <button @click="closeModal" class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-gray-700 rounded-full transition">
                        Hủy
                    </button>
                    <button @click="submitModal" :class="modalBtnColor" class="px-5 py-2 text-sm text-white font-semibold rounded-full transition">
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

