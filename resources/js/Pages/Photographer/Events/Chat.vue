<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    ArrowLeftIcon, 
    PaperAirplaneIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    messages: Array,
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
</script>

<template>
    <AppLayout title="Sala de Operaciones">
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            

            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('photographer.events.show', event.id)" 
                    class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-500 hover:text-black hover:shadow-md transition-all shadow-sm border border-gray-100">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="text-2xl font-flux text-black leading-none">Sala de Operaciones</h2>
                    <p class="text-sm text-gray-500 font-bold uppercase tracking-wider mt-1">{{ event.name }}</p>
                </div>
            </div>


            <div class="flex flex-col h-[70vh] bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden relative">
                

                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 md:p-6 bg-[#f8f9fa] space-y-4">
                    
                    <div v-if="messages.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
                        <p class="text-xs font-bold uppercase tracking-widest text-center">Sin mensajes aún.</p>
                        <p class="text-xs text-center mt-1">Inicia la coordinación táctica de la misión.</p>
                    </div>

                    <div v-for="msg in messages" :key="msg.id" 
                        class="flex flex-col w-full"
                        :class="msg.photographer_id === currentPhotographerId ? 'items-end' : 'items-start'">
                        

                        <span v-if="msg.photographer_id !== currentPhotographerId" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 ml-1">
                            {{ msg.photographer.user.name }}
                        </span>

                        <div class="max-w-[85%] md:max-w-[70%] px-4 py-2.5 rounded-2xl shadow-sm text-sm"
                            :class="msg.photographer_id === currentPhotographerId 
                                ? 'bg-black text-white rounded-tr-none' 
                                : 'bg-white border border-gray-200 text-gray-800 rounded-tl-none'">
                            {{ msg.message }}
                        </div>
                    </div>
                </div>

                <div class="bg-white border-t border-gray-200 p-3 md:p-4">
                    <form @submit.prevent="sendMessage" class="flex gap-2 items-center">
                        <input type="text" v-model="form.message" placeholder="Escribe un mensaje al equipo..."
                            class="flex-1 bg-gray-50 border-gray-200 focus:border-black focus:ring-black rounded-full px-4 py-3 text-sm transition-colors"
                            :disabled="form.processing"
                            autocomplete="off">
                        
                        <button type="submit" :disabled="form.processing || !form.message.trim()"
                            class="w-12 h-12 flex items-center justify-center rounded-full bg-[#E30613] text-white hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shrink-0 shadow-sm">
                            <PaperAirplaneIcon class="w-5 h-5 -mr-1" />
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </AppLayout>
</template>