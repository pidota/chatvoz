<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat de Voz Básico</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-box {
            height: 400px;
            overflow-y: scroll;
        }
        .audio {
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Chat de Voz Básico</h2>
        <div class="chat-box border p-3 mb-3">
            <!-- Mensajes de audio serán cargados aquí -->
        </div>
        <form id="chat-form">
            <div class="form-group">
                <input type="text" id="username" class="form-control mb-2" placeholder="Tu nombre" required>
                <button type="button" id="start" class="btn btn-primary">Comenzar</button>
                <button type="button" id="stop" class="btn btn-danger" disabled>Detener</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        let ws;
        let mediaRecorder;
        let audioChunks = [];

        $(document).ready(function() {
            $('#start').click(function() {
                ws = new WebSocket('ws://localhost:8080');
                
                ws.onmessage = function(event) {
                    let audio = new Audio(URL.createObjectURL(event.data));
                    $('.chat-box').append('<audio class="audio" controls></audio>');
                    $('.audio').last().attr('src', URL.createObjectURL(event.data));
                    $('.chat-box').scrollTop($('.chat-box')[0].scrollHeight);
                };

                navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    mediaRecorder = new MediaRecorder(stream);
                    mediaRecorder.ondataavailable = function(event) {
                        if (event.data.size > 0 && ws.readyState === WebSocket.OPEN) {
                            ws.send(event.data);
                        }
                    };

                    mediaRecorder.start();
                    $('#start').attr('disabled', true);
                    $('#stop').attr('disabled', false);
                });
            });

            $('#stop').click(function() {
                mediaRecorder.stop();
                ws.close();
                $('#start').attr('disabled', false);
                $('#stop').attr('disabled', true);
            });
        });
    </script>
</body>
</html>
