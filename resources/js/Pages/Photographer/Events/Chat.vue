<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    MapPinIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    messages: Array,
    participants: Array,
    currentPhotographerId: Number
});

const form = useForm({
    message: ''
});

const messagesContainer = ref(null);
let pollingInterval = null;

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const sendMessage = () => {
    if (!form.message.trim()) return;

    form.post(route('photographer.events.chat.store', props.event.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset('message');
            scrollToBottom();
        }
    });
};

const formatTime = (timestamp) => {
    if (!timestamp) return '';
    const date = new Date(timestamp);

    return date.toLocaleDateString('es-AR', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).replace(',', ' -');
};

onMounted(() => {
    scrollToBottom();


    pollingInterval = setInterval(() => {
        router.reload({
            only: ['messages'],
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                scrollToBottom();
            }
        });
    }, 10000);
});

onUnmounted(() => {
    clearInterval(pollingInterval);
});


const getFallbackAvatar = (name) => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&color=fff`;
};
</script>

<template>
    <AppLayout title="Chat grupal">

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col h-[calc(100vh-4rem)]">


            <div
                class="bg-white border border-gray-200 rounded-t-lg shadow-sm p-3 md:p-4 flex items-center justify-between z-10 shrink-0 relative">

                <div class="flex items-center gap-4">
                    <Link :href="route('photographer.events.show', event.id)"
                        class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-500 hover:text-black hover:bg-gray-100 transition-all shrink-0">
                        <ArrowLeftIcon class="w-5 h-5" />
                    </Link>

                    <div class="flex items-center gap-3">

                        <img v-if="event.cover_image" :src="event.cover_image" :alt="event.name"
                            class="w-10 h-10 rounded object-cover border border-gray-100 shrink-0 hidden sm:block" />

                        <div>
                            <h2 class="text-base font-bold text-black leading-tight truncate max-w-[200px] sm:max-w-xs">
                                {{ event.name }}</h2>
                            <div v-if="event.location" class="flex items-center gap-1 mt-0.5">
                                <MapPinIcon class="w-3 h-3 text-[#E30613]" />
                                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider truncate">{{
                                    event.location }}</p>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="flex items-center gap-3">
                    <div
                        class="hidden sm:block text-[10px] text-gray-400 font-bold uppercase tracking-wider text-right">
                        {{ participants.length }} Equipo
                    </div>
                    <div class="flex -space-x-2.5">
                        <img v-for="p in participants" :key="p.id"
                            :src="p.profile_photo_url || getFallbackAvatar(p.name)" :title="p.name"
                            class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm bg-gray-50" />
                    </div>
                </div>
            </div>


            <div
                class="flex-1 bg-[#f0f2f5] border-x border-gray-200 overflow-hidden flex flex-col relative shadow-inner">
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">

                    <div v-if="messages.length === 0"
                        class="h-full flex flex-col items-center justify-center text-gray-400">
                        <p class="text-xs font-bold uppercase tracking-widest text-center">Chat encriptado</p>
                        <p class="text-xs text-center mt-1">Iniciá la coordinación táctica del evento.</p>
                    </div>

                    <div v-for="msg in messages" :key="msg.id" class="flex w-full"
                        :class="msg.photographer_id === currentPhotographerId ? 'justify-end' : 'justify-start'">


                        <img v-if="msg.photographer_id !== currentPhotographerId"
                            :src="msg.photographer?.profile_photo_url || getFallbackAvatar(msg.photographer?.user?.name || 'F')"
                            class="w-7 h-7 rounded-full object-cover mr-2 self-end mb-1 shadow-sm shrink-0" />


                        <div class="flex flex-col max-w-[75%] md:max-w-[65%]"
                            :class="msg.photographer_id === currentPhotographerId ? 'items-end' : 'items-start'">

                            <span v-if="msg.photographer_id !== currentPhotographerId"
                                class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 ml-1">
                                {{ msg.photographer?.user?.name || 'Fotógrafo' }}
                            </span>

                            <div class="px-3 pt-2 pb-1 text-[15px] leading-relaxed shadow-sm relative min-w-[80px]"
                                :class="msg.photographer_id === currentPhotographerId
                                    ? 'bg-[#dcf8c6] text-gray-900 rounded-2xl rounded-br-sm'
                                    : 'bg-white border border-gray-100 text-gray-800 rounded-2xl rounded-bl-sm'">

                                <div class="break-words">{{ msg.message }}</div>

                                <div class="text-[9px] text-right mt-0.5 opacity-60 font-medium tracking-tight"
                                    :class="msg.photographer_id === currentPhotographerId ? 'text-gray-700' : 'text-gray-500'">
                                    {{ formatTime(msg.created_at) }}
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>


            <div class="bg-white border border-gray-200 rounded-b-lg p-3 shrink-0 shadow-sm">
                <form @submit.prevent="sendMessage" class="flex gap-2 items-center">
                    <input type="text" v-model="form.message" placeholder="Escribí un mensaje..."
                        class="flex-1 bg-gray-50 border-transparent focus:border-gray-300 focus:ring-0 rounded-full px-5 py-3.5 text-[15px] transition-colors"
                        :disabled="form.processing" autocomplete="off">

                    <button type="submit" :disabled="form.processing || !form.message.trim()"
                        class="w-12 h-12 flex items-center justify-center rounded-full bg-[#E30613] text-white hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shrink-0 shadow-sm">
                        <PaperAirplaneIcon class="w-5 h-5 -mr-1" />
                    </button>
                </form>
            </div>

        </div>
    </AppLayout>
</template>