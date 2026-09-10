importScripts('https://unpkg.com/@tensorflow/tfjs@3.21.0/dist/tf.min.js');
importScripts('https://unpkg.com/nsfwjs');

let model = null;


nsfwjs.load('https://s3.amazonaws.com/ir_public/nsfwj/v1/').then(loadedModel => {
    model = loadedModel;
    postMessage({ status: 'READY' });
}).catch(err => {
    console.error("Error cargando modelo NSFW en el worker:", err);
});

self.onmessage = async (e) => {
    if (!model) return;
    
    const { fileId, file } = e.data;

    try {
        const bmp = await createImageBitmap(file);
        const canvas = new OffscreenCanvas(bmp.width, bmp.height);
        const ctx = canvas.getContext('2d');
        ctx.drawImage(bmp, 0, 0);
        const imageData = ctx.getImageData(0, 0, bmp.width, bmp.height);
        
        const predictions = await model.classify(imageData);
        

        const isUnsafe = predictions.some(p => 
            (p.className === 'Porn' || p.className === 'Hentai') && p.probability > 0.60
        );

        postMessage({ fileId, isSafe: !isUnsafe });
        bmp.close();
        
    } catch (error) {
        postMessage({ fileId, isSafe: true });
    }
};