<template>
    <Head title="MiniX AI / X" />

    <TwitterLayout>
        <div class="flex flex-col h-[100vh] bg-white dark:bg-black text-gray-900 dark:text-white transition-colors duration-200">
            <!-- 1. Header Bar -->
            <div class="sticky top-0 z-30 bg-white/80 dark:bg-black/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-cyan-500 via-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                        <RobotOutline :size="24" />
                    </div>
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <h1 class="text-lg font-black tracking-tight text-gray-900 dark:text-white">MiniX AI</h1>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                3.5 Flash
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Trực tuyến • Phản hồi tức thì</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button
                        v-if="messages && messages.length > 0"
                        @click="clearChat"
                        type="button"
                        class="flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 bg-gray-100 dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-950/40 border border-gray-200 dark:border-gray-700 rounded-full transition cursor-pointer"
                        title="Xóa đoạn chat và bắt đầu mới"
                    >
                        <TrashCanOutline :size="16" />
                        <span class="hidden sm:inline">Làm mới đoạn chat</span>
                    </button>
                </div>
            </div>

            <!-- 2. Chat Stream Area -->
            <div
                ref="chatContainer"
                class="flex-1 overflow-y-auto px-4 md:px-8 py-6 space-y-6 scrollbar-hide"
            >
                <!-- Empty State (No messages yet) -->
                <div v-if="!messages || messages.length === 0" class="max-w-2xl mx-auto py-8 text-center space-y-8">
                    <!-- Hero Icon & Title -->
                    <div class="space-y-3">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-xl">
                            <RobotOutline :size="38" />
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">
                            MiniX AI có thể giúp gì cho bạn?
                        </h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                            Trợ lý thông minh hỗ trợ giải đáp thắc mắc, sáng tạo tweet viral, phân tích mã nguồn và trò chuyện.
                        </p>
                    </div>

                    <!-- Suggested Prompts Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                        <div
                            v-for="(item, idx) in promptSuggestions"
                            :key="idx"
                            @click="sendMessage(item.prompt)"
                            class="p-4 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/40 hover:bg-blue-50 dark:hover:bg-blue-950/30 hover:border-blue-300 dark:hover:border-blue-700 transition cursor-pointer group"
                        >
                            <div class="text-xl mb-1.5">{{ item.icon }}</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-[#1D9BF0] transition">
                                {{ item.title }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ item.prompt }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Stream -->
                <template v-else>
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        class="flex flex-col"
                        :class="msg.role === 'user' ? 'items-end' : 'items-start'"
                    >
                        <!-- User Message -->
                        <div
                            v-if="msg.role === 'user'"
                            class="max-w-[85%] md:max-w-[70%] group"
                        >
                            <div class="bg-[#1D9BF0] text-white rounded-2xl rounded-tr-xs px-4 py-3 text-[15px] leading-relaxed break-words shadow-sm">
                                {{ msg.content }}
                            </div>
                            <div class="text-[11px] text-gray-400 mt-1 text-right pr-1">
                                {{ msg.time }}
                            </div>
                        </div>

                        <!-- Bot Assistant Message -->
                        <div
                            v-else
                            class="flex items-start space-x-3 max-w-[95%] md:max-w-[85%] group"
                        >
                            <!-- Bot Avatar -->
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white flex-shrink-0 mt-0.5 shadow">
                                <RobotOutline :size="18" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="bg-gray-100 dark:bg-[#16181C] border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-gray-100 rounded-2xl rounded-tl-xs p-4 shadow-sm space-y-3">
                                    <template v-for="(block, bIdx) in parseMessage(msg.content)" :key="bIdx">
                                        <!-- Code Block -->
                                        <div v-if="block.type === 'code'" class="rounded-xl overflow-hidden border border-gray-300 dark:border-gray-700 bg-gray-900 text-gray-100 my-2">
                                            <div class="flex items-center justify-between px-3 py-1.5 bg-gray-800 border-b border-gray-700 text-xs text-gray-400 font-mono">
                                                <span>{{ block.lang || 'code' }}</span>
                                                <button
                                                    type="button"
                                                    @click="copyToClipboard(block.content, msg.id + '-block-' + bIdx)"
                                                    class="hover:text-white transition flex items-center space-x-1"
                                                >
                                                    <ContentCopy :size="13" />
                                                    <span>{{ copiedId === (msg.id + '-block-' + bIdx) ? 'Đã sao chép!' : 'Sao chép' }}</span>
                                                </button>
                                            </div>
                                            <pre class="p-3 text-xs md:text-sm font-mono overflow-x-auto whitespace-pre"><code>{{ block.content }}</code></pre>
                                        </div>

                                        <!-- Regular Formatted Text Block -->
                                        <div
                                            v-else
                                            class="text-[15px] leading-relaxed whitespace-pre-line prose dark:prose-invert max-w-none break-words"
                                            v-html="formatInlineText(block.content)"
                                        ></div>
                                    </template>
                                </div>

                                <!-- Action bar under bot bubble -->
                                <div class="flex items-center space-x-3 mt-1.5 pl-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="text-[11px]">{{ msg.time }}</span>

                                    <!-- Copy Button -->
                                    <button
                                        type="button"
                                        @click="copyToClipboard(msg.content, msg.id)"
                                        class="hover:text-[#1D9BF0] transition flex items-center space-x-1 cursor-pointer"
                                        title="Sao chép toàn bộ câu trả lời"
                                    >
                                        <ContentCopy :size="14" />
                                        <span>{{ copiedId === msg.id ? 'Đã sao chép!' : 'Sao chép' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Thinking State Indicator -->
                <div v-if="isSending" class="flex items-start space-x-3 max-w-[85%]">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white flex-shrink-0 animate-spin-slow shadow">
                        <RobotOutline :size="18" />
                    </div>
                    <div class="bg-gray-100 dark:bg-[#16181C] border border-gray-200 dark:border-gray-800 rounded-2xl rounded-tl-xs px-4 py-3 flex items-center space-x-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">MiniX AI đang phản hồi</span>
                        <div class="flex space-x-1">
                            <span class="w-1.5 h-1.5 bg-[#1D9BF0] rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                            <span class="w-1.5 h-1.5 bg-[#1D9BF0] rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                            <span class="w-1.5 h-1.5 bg-[#1D9BF0] rounded-full animate-bounce"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Bottom Sticky Input Form -->
            <div class="border-t border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-black/95 backdrop-blur-md px-4 md:px-8 py-3">
                <form @submit.prevent="sendMessage()" class="max-w-3xl mx-auto space-y-2">
                    <div class="relative flex items-center bg-gray-100 dark:bg-[#202327] rounded-2xl border border-transparent focus-within:border-[#1D9BF0] focus-within:bg-white dark:focus-within:bg-black transition duration-200 shadow-inner">
                        <textarea
                            ref="inputArea"
                            v-model="form.message"
                            @keydown.enter.prevent="handleEnter"
                            rows="1"
                            placeholder="Hỏi MiniX AI bất cứ điều gì... (Enter để gửi, Shift+Enter xuống dòng)"
                            class="w-full py-3 pl-4 pr-12 bg-transparent text-gray-900 dark:text-white placeholder-gray-500 text-sm md:text-[15px] resize-none focus:outline-none border-0 focus:ring-0 max-h-36 overflow-y-auto leading-relaxed"
                            :disabled="isSending"
                        ></textarea>

                        <!-- Send Button -->
                        <button
                            type="submit"
                            :disabled="!canSend"
                            class="absolute right-2.5 p-2 rounded-full transition-all duration-200"
                            :class="canSend
                                ? 'bg-[#1D9BF0] text-white hover:bg-blue-600 shadow cursor-pointer scale-100'
                                : 'bg-gray-300 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed scale-95'"
                            title="Gửi tin nhắn"
                        >
                            <ArrowUp :size="18" />
                        </button>
                    </div>

                    <!-- Footer Disclaimer -->
                    <div class="text-center text-[11px] text-gray-400 dark:text-gray-500">
                        MiniX AI có thể đưa ra câu trả lời chưa chính xác. Vui lòng kiểm chứng lại các thông tin quan trọng.
                    </div>
                </form>
            </div>
        </div>
    </TwitterLayout>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import TwitterLayout from '@/Layouts/TwitterLayout.vue'
import RobotOutline from 'vue-material-design-icons/RobotOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import ContentCopy from 'vue-material-design-icons/ContentCopy.vue'
import ArrowUp from 'vue-material-design-icons/ArrowUp.vue'

const props = defineProps({
    messages: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    message: '',
})

const isSending = ref(false)
const chatContainer = ref(null)
const inputArea = ref(null)
const copiedId = ref(null)

const canSend = computed(() => {
    return form.message && form.message.trim().length > 0 && !isSending.value
})

const promptSuggestions = [
    {
        icon: '💡',
        title: '5 ý tưởng tweet viral',
        prompt: 'Gợi ý cho tôi 5 ý tưởng bài viết tweet ngắn gọn, cuốn hút để tăng tương tác trên mạng xã hội hôm nay.',
    },
    {
        icon: '💻',
        title: 'Lập trình Laravel & Vue',
        prompt: 'Giải thích ngắn gọn cách Inertia.js kết nối Laravel Backend và Vue 3 Frontend hoạt động như thế nào?',
    },
    {
        icon: '✍️',
        title: 'Viết Tweet chào buổi sáng',
        prompt: 'Viết 1 tweet chào buổi sáng tích cực, hài hước bằng tiếng Việt có kèm emoji và hashtag.',
    },
    {
        icon: '🚀',
        title: 'Tính năng của Twitter Clone',
        prompt: 'Kể tên các tính năng công nghệ nổi bật được xây dựng trong dự án Twitter Clone này.',
    },
]

const scrollToBottom = async () => {
    await nextTick()
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
}

onMounted(() => {
    scrollToBottom()
    if (inputArea.value) {
        inputArea.value.focus()
    }
})

watch(() => props.messages, () => {
    scrollToBottom()
}, { deep: true })

const handleEnter = (e) => {
    if (e.shiftKey) {
        // Allow newline
        return
    }
    sendMessage()
}

const sendMessage = (promptText = null) => {
    if (isSending.value) return

    const textToSend = promptText || form.message
    if (!textToSend || !textToSend.trim()) return

    if (promptText) {
        form.message = promptText
    }

    isSending.value = true
    scrollToBottom()

    form.post(route('bot.chat'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message')
            scrollToBottom()
        },
        onError: () => {
            scrollToBottom()
        },
        onFinish: () => {
            isSending.value = false
            scrollToBottom()
            if (inputArea.value) {
                inputArea.value.focus()
            }
        },
    })
}

const clearChat = () => {
    if (confirm('Bạn có chắc chắn muốn xóa toàn bộ lịch sử trò chuyện này không?')) {
        router.post(route('bot.clear'), {}, {
            preserveScroll: true,
        })
    }
}

const copyToClipboard = async (text, id) => {
    if (!text) return
    try {
        await navigator.clipboard.writeText(text)
        copiedId.value = id
        setTimeout(() => {
            if (copiedId.value === id) {
                copiedId.value = null
            }
        }, 2000)
    } catch (e) {
        console.error('Không thể sao chép văn bản', e)
    }
}

/**
 * Split text into regular text blocks and markdown code blocks
 */
const parseMessage = (content) => {
    if (!content) return []
    const parts = []
    const regex = /```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/g
    let lastIndex = 0
    let match

    while ((match = regex.exec(content)) !== null) {
        if (match.index > lastIndex) {
            parts.push({
                type: 'text',
                content: content.slice(lastIndex, match.index),
            })
        }
        parts.push({
            type: 'code',
            lang: match[1] || 'code',
            content: match[2].trim(),
        })
        lastIndex = regex.lastIndex
    }

    if (lastIndex < content.length) {
        parts.push({
            type: 'text',
            content: content.slice(lastIndex),
        })
    }

    return parts
}

/**
 * Safely format bold **text** and inline `code`
 */
const formatInlineText = (text) => {
    if (!text) return ''
    let escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')

    // Format Bold **word**
    escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-gray-900 dark:text-white">$1</strong>')

    // Format Inline `code`
    escaped = escaped.replace(/`([^`]+)`/g, '<code class="bg-gray-200 dark:bg-gray-800 text-[#1D9BF0] px-1.5 py-0.5 rounded text-xs font-mono">$1</code>')

    return escaped
}
</script>

