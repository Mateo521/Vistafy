// resources/js/Composables/useFaceDetection.js

import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';
import { ref } from 'vue';

export function useFaceDetection() {
    const modelsLoaded = ref(false);
    const loading = ref(false);
    const error = ref(null);

    async function loadModels() {
        if (modelsLoaded.value) return;

        try {
            loading.value = true;
            error.value = null;

            console.log(' Iniciando carga de modelos...');

            // Inicializar backend
            await faceapi.tf.setBackend('webgl');
            await faceapi.tf.ready();

            const MODEL_URL = '/models';

            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            modelsLoaded.value = true;
            console.log(' Modelos cargados');
            loading.value = false;
        } catch (err) {
            error.value = 'Error cargando modelos: ' + err.message;
            console.error(' Error:', err);
            loading.value = false;
        }
    }

    async function detectFaces(imageElement) {
        if (!modelsLoaded.value) {
            await loadModels();
        }

        try {
            loading.value = true;
            error.value = null;

            const detections = await faceapi
                .detectAllFaces(imageElement, new faceapi.SsdMobilenetv1Options({ 
                    minConfidence: 0.5 
                }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            console.log(` ${detections.length} rostro(s)`);

            if (detections.length === 0) {
                error.value = 'No se detectaron rostros';
                return [];
            }

            return detections.map(d => Array.from(d.descriptor));
        } catch (err) {
            error.value = 'Error: ' + err.message;
            console.error('', err);
            return [];
        } finally {
            loading.value = false;
        }
    }

    function calculateSimilarity(descriptor1, descriptor2) {
        const distance = faceapi.euclideanDistance(descriptor1, descriptor2);
        return Math.max(0, 1 - distance);
    }

    return {
        modelsLoaded,
        loading,
        error,
        loadModels,
        detectFaces,
        calculateSimilarity,
    };
}
