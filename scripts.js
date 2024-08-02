document.addEventListener('DOMContentLoaded', () => {
    const ws = new WebSocket('ws://chatvoz.vercel.app:8080'); // Reemplaza con la dirección de tu servidor WebSocket
    const chatBox = document.getElementById('chat-box');
    const startBtn = document.getElementById('start-btn');
    const stopBtn = document.getElementById('stop-btn');
    const usernameInput = document.getElementById('username');

    let mediaRecorder;
    let audioChunks = [];

    ws.onmessage = function(event) {
        const audioBlob = new Blob([event.data], { type: 'audio/webm' });
        const audioUrl = URL.createObjectURL(audioBlob);
        const audioElement = document.createElement('audio');
        audioElement.className = 'audio';
        audioElement.controls = true;
        audioElement.src = audioUrl;
        chatBox.appendChild(audioElement);
        chatBox.scrollTop = chatBox.scrollHeight;
    };

    startBtn.addEventListener('click', () => {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    mediaRecorder = new MediaRecorder(stream);
                    mediaRecorder.ondataavailable = function(event) {
                        if (event.data.size > 0 && ws.readyState === WebSocket.OPEN) {
                            ws.send(event.data);
                        }
                    };
                    mediaRecorder.start();
                    startBtn.disabled = true;
                    stopBtn.disabled = false;
                })
                .catch(error => {
                    console.error('Error al acceder al micrófono:', error);
                });
        } else {
            console.error('getUserMedia no es soportado en este navegador.');
        }
    });

    stopBtn.addEventListener('click', () => {
        if (mediaRecorder) {
            mediaRecorder.stop();
            ws.close();
        }
        startBtn.disabled = false;
        stopBtn.disabled = true;
    });
});
