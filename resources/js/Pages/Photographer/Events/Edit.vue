<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    PhotoIcon, 
    GlobeAltIcon, 
    LockClosedIcon, 
    EyeSlashIcon, 
    TrashIcon,
    ArrowLeftIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    availablePhotos: Array,
});

const form = useForm({
    name: props.event.name,
    description: props.event.description || '',
    long_description: props.event.long_description || '',
    event_date: props.event.event_date,
    location: props.event.location || '',
    is_private: props.event.is_private,
    is_active: props.event.is_active ?? true,
    cover_image: null,
});

const previewImage = ref(props.event.cover_image_url);

const compressCoverImage = async (file) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                const maxSize = 1920; 

                if (width > height && width > maxSize) {
                    height = Math.round((height * maxSize) / width);
                    width = maxSize;
                } else if (height > maxSize) {
                    width = Math.round((width * maxSize) / height);
                    height = maxSize;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob((blob) => {
                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(compressedFile);
                }, 'image/jpeg', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const handleImageChange = async (e) => {
    const file = e.target.files[0];
    if (file) {
        previewImage.value = URL.createObjectURL(file);
        const compressedFile = await compressCoverImage(file);
        form.cover_image = compressedFile;
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('photographer.events.update', props.event.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            
        },
    });
};

const deleteEvent = () => {
    if (confirm('ATENCIÓN_CRÍTICA: ¿Confirmar purga del evento? Se borrarán todas las fotos asociadas. Esta acción es IRREVERSIBLE.')) {
        router.delete(route('photographer.events.destroy', props.event.id));
    }
};
</script>

<template>
    <Head :title="`[EDITAR] ${event.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#050505] text-white selection:bg-[#E30613] selection:text-black py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-zinc-800 pb-6">
                    <div>
                        <Link :href="route('photographer.events.index')" 
                            class="inline-flex items-center font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 hover:text-white transition-colors mb-6 border border-zinc-800 bg-[#09090b] px-4 py-2 hover:border-white w-max">
                            <ArrowLeftIcon class="w-3 h-3 mr-2" />
                            CANCELAR / VOLVER
                        </Link>
                        
                        <span class="font-mono text-[10px] font-bold text-[#E30613] uppercase tracking-widest mb-2 block flex items-center gap-2">
                            <span class="w-2 h-2 bg-[#E30613] animate-pulse"></span>
                            >_ MODO_EDICIÓN
                        </span>
                        
                        <h1 class="text-4xl md:text-5xl font-flux font-black text-white uppercase tracking-tighter leading-none mb-2">
                            MODIFICAR EVENTO
                        </h1>
                        <p class="font-mono text-xs text-zinc-500 uppercase tracking-widest border-l-2 border-[#E30613] pl-3">
                            Alterar parámetros, visibilidad o purgar galería
                        </p>
                    </div>
                    
                    <Link :href="route('photographer.events.show', event.id)" 
                        class="bg-transparent border border-zinc-700 text-zinc-400 hover:border-white hover:text-white px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-colors text-center">
                        INSPECCIONAR GALERÍA
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-8">

                    <div class="bg-[#09090b] border border-zinc-800 p-8 shadow-[8px_8px_0px_0px_rgba(255,255,255,0.05)]">
                        <h2 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-4">
                            >_ PARÁMETROS_PRINCIPALES
                        </h2>

                        <div class="space-y-6">
                            <div>
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                    IDENTIFICADOR (NOMBRE)
                                </label>
                                <input v-model="form.name" type="text" required
                                    class="w-full bg-black border border-zinc-700 text-white font-mono text-xs uppercase focus:border-[#E30613] focus:ring-0 rounded-none transition-colors"
                                    placeholder="NOMBRE DEL EVENTO" />
                                <div v-if="form.errors.name" class="font-mono text-[10px] text-[#E30613] mt-2 tracking-widest uppercase">ERR: {{ form.errors.name }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                        FECHA_REGISTRO
                                    </label>
                                    <input v-model="form.event_date" type="date" required
                                        class="w-full bg-black border border-zinc-700 text-white font-mono text-xs uppercase focus:border-[#E30613] focus:ring-0 rounded-none transition-colors" />
                                    <div v-if="form.errors.event_date" class="font-mono text-[10px] text-[#E30613] mt-2 tracking-widest uppercase">ERR: {{ form.errors.event_date }}</div>
                                </div>
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                        UBICACIÓN
                                    </label>
                                    <input v-model="form.location" type="text"
                                        class="w-full bg-black border border-zinc-700 text-white font-mono text-xs uppercase focus:border-[#E30613] focus:ring-0 rounded-none transition-colors"
                                        placeholder="COORDENADAS O LUGAR" />
                                </div>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                    DESCRIPCIÓN_CORTA
                                </label>
                                <textarea v-model="form.description" rows="2" maxlength="500"
                                    class="w-full bg-black border border-zinc-700 text-white font-mono text-xs focus:border-[#E30613] focus:ring-0 rounded-none transition-colors resize-none uppercase"></textarea>
                                <p class="text-[10px] font-mono text-zinc-600 mt-1 uppercase tracking-widest text-right">{{ form.description?.length || 0 }}/500 BYTES</p>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                    DATOS_ADICIONALES (DESCRIPCIÓN LARGA)
                                </label>
                                <textarea v-model="form.long_description" rows="5" maxlength="2000"
                                    class="w-full bg-black border border-zinc-700 text-white font-mono text-xs focus:border-[#E30613] focus:ring-0 rounded-none transition-colors uppercase"></textarea>
                                <p class="text-[10px] font-mono text-zinc-600 mt-1 uppercase tracking-widest text-right">{{ form.long_description?.length || 0 }}/2000 BYTES</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#09090b] border border-zinc-800 p-8 shadow-[8px_8px_0px_0px_rgba(255,255,255,0.05)]">
                        <h2 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-4">
                            >_ ASSET_PORTADA
                        </h2>

                        <div class="flex flex-col md:flex-row gap-8">
                            <div class="w-full md:w-1/2">
                                <div class="aspect-video bg-black border border-zinc-700 rounded-none overflow-hidden relative group">
                                    <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover filter grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-zinc-800">
                                        <PhotoIcon class="w-12 h-12" />
                                    </div>
                                    <div class="absolute inset-0 bg-[#E30613]/0 group-hover:bg-[#E30613]/20 flex items-center justify-center transition-colors mix-blend-multiply pointer-events-none"></div>
                                </div>
                            </div>
                            
                            <div class="flex-1 flex flex-col justify-center">
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-4">
                                    MODIFICAR ASSET
                                </label>
                                <input type="file" accept="image/jpeg,image/png,image/jpg" @change="handleImageChange"
                                    class="block w-full text-xs font-mono text-zinc-500
                                    file:mr-4 file:py-3 file:px-6
                                    file:rounded-none file:border-0
                                    file:text-[10px] file:font-bold file:uppercase file:tracking-widest
                                    file:bg-black file:text-white file:border file:border-zinc-700
                                    hover:file:border-white hover:file:bg-white hover:file:text-black transition-colors cursor-pointer" 
                                />
                                <p class="text-[10px] font-mono text-zinc-600 mt-4 uppercase tracking-widest">
                                    FORMATOS ADMITIDOS: JPG, PNG. AUTO-COMPRESIÓN HABILITADA.
                                </p>
                                <div v-if="form.errors.cover_image" class="font-mono text-[10px] text-[#E30613] mt-2 tracking-widest uppercase">ERR: {{ form.errors.cover_image }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#09090b] border border-zinc-800 p-8 shadow-[8px_8px_0px_0px_rgba(255,255,255,0.05)]">
                        <h2 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-4">
                            >_ POLÍTICAS_DE_ACCESO
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" class="peer sr-only" :checked="form.is_active && !form.is_private" @change="form.is_active = true; form.is_private = false">
                                <div class="p-6 border border-zinc-800 bg-black hover:border-zinc-500 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 transition-all h-full rounded-none">
                                    <div class="flex items-center gap-3 mb-3 text-emerald-500">
                                        <GlobeAltIcon class="w-5 h-5" />
                                        <span class="font-flux text-xl uppercase tracking-wider">Público</span>
                                    </div>
                                    <p class="font-mono text-[10px] text-zinc-500 uppercase tracking-widest leading-relaxed">Visible para todos los usuarios en el directorio general.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" class="peer sr-only" :checked="form.is_active && form.is_private" @change="form.is_active = true; form.is_private = true">
                                <div class="p-6 border border-zinc-800 bg-black hover:border-zinc-500 peer-checked:border-[#E30613] peer-checked:bg-[#E30613]/10 transition-all h-full rounded-none">
                                    <div class="flex items-center gap-3 mb-3 text-[#E30613]">
                                        <LockClosedIcon class="w-5 h-5" />
                                        <span class="font-flux text-xl uppercase tracking-wider">Privado</span>
                                    </div>
                                    <p class="font-mono text-[10px] text-zinc-500 uppercase tracking-widest leading-relaxed">Acceso restringido. Requiere enlace directo o token.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" class="peer sr-only" :checked="!form.is_active" @change="form.is_active = false; form.is_private = false">
                                <div class="p-6 border border-zinc-800 bg-black hover:border-zinc-500 peer-checked:border-white peer-checked:bg-white/5 transition-all h-full rounded-none">
                                    <div class="flex items-center gap-3 mb-3 text-white">
                                        <EyeSlashIcon class="w-5 h-5" />
                                        <span class="font-flux text-xl uppercase tracking-wider">Borrador</span>
                                    </div>
                                    <p class="font-mono text-[10px] text-zinc-500 uppercase tracking-widest leading-relaxed">Instancia oculta. Visibilidad exclusiva para el administrador.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-4">
                        <Link :href="route('photographer.events.index')" 
                            class="w-full sm:w-auto px-8 py-4 border border-zinc-700 bg-transparent text-zinc-400 font-mono text-[10px] font-bold uppercase tracking-widest hover:border-white hover:text-white transition-colors text-center rounded-none">
                            CANCELAR
                        </Link>
                        <button type="submit" :disabled="form.processing" 
                            class="w-full sm:w-auto px-10 py-4 bg-[#E30613] border border-[#E30613] text-black font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white hover:border-white transition-colors rounded-none disabled:opacity-50">
                            {{ form.processing ? 'PROCESANDO...' : 'SOBRESCRIBIR EVENTO' }}
                        </button>
                    </div>

                </form>

                <div class="mt-16 pt-10 border-t border-zinc-800">
                    <div class="bg-[#E30613]/5 border border-[#E30613] p-8 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                        <TrashIcon class="absolute -left-10 -bottom-10 w-48 h-48 text-[#E30613] opacity-10 pointer-events-none" />
                        
                        <div class="relative z-10">
                            <h3 class="font-flux text-3xl text-[#E30613] uppercase tracking-wider mb-2 flex items-center gap-3">
                                <TrashIcon class="w-6 h-6" />  PURGA
                            </h3>
                            <p class="font-mono text-xs text-zinc-400 uppercase tracking-widest max-w-2xl leading-relaxed">
                                Esta acción va a eliminar permanentemente el evento y todos los activos digitales asociados. Los datos no podrán ser recuperados.
                            </p>
                        </div>
                        
                        <button @click="deleteEvent" 
                            class="relative z-10 px-8 py-4 bg-transparent border border-[#E30613] text-[#E30613] font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-[#E30613] hover:text-black transition-colors rounded-none whitespace-nowrap">
                            ACTIVAR - IRREVERSIBLE
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>