<div id="migrationModule">
    <style>
        /* Use #migrationModule antes de cada seletor para escopar o estilo */
        #migrationModule .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
            font-family: 'Open Sans', sans-serif;
        }
        #migrationModule @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap');

        #migrationModule h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
        }
        #migrationModule p {
            line-height: 1.6;
        }
        #migrationModule form {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
        }
        #migrationModule label {
            font-weight: 600;
            margin-bottom: 5px;
        }
        #migrationModule input[type="file"] {
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        #migrationModule button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #migrationModule button:hover {
            background-color: #43A047;
        }
        #migrationModule .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 4px;
            margin-top: 20px;
            display: none;
            overflow: hidden;
        }
        #migrationModule .progress-bar {
            width: 0%;
            height: 30px;
            background: linear-gradient(90deg, #66bb6a, #43a047);
            text-align: center;
            line-height: 30px;
            color: #fff;
            transition: width 0.4s ease;
        }
        #migrationModule #status {
            margin-top: 10px;
            font-style: italic;
            text-align: center;
        }
        #migrationModule #result {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
        }
        #migrationModule .success {
            border-color: #66bb6a;
            background-color: #e8f5e9;
        }
        #migrationModule .error {
            border-color: #e57373;
            background-color: #ffebee;
        }
    </style>

    <div class="container">
        <h1>Migrar Dados</h1>
        <p>Selecione o arquivo <code>bibliotecario.db</code> da <strong>versão 1</strong> para fazer o upload e iniciar a migração para a versão 3.</p>
        <p><strong>Atenção:</strong> Faça um backup do banco de dados atual (v3) antes de prosseguir!</p>

        <form id="uploadForm" action="atualizacao/upload_process.php" method="post" enctype="multipart/form-data">
            <label for="dbfile">Arquivo .db (Versão 1):</label>
            <input type="file" name="dbfile" id="dbfile" accept=".db" required>
            <button type="submit">Iniciar Migração</button>
        </form>

        <div id="progressContainer" class="progress-container">
            <div id="progressBar" class="progress-bar">0%</div>
        </div>
        <div id="status"></div>
        <div id="result"></div>
    </div>

    <script>
    (function(){
        const form = document.getElementById('uploadForm');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const statusDiv = document.getElementById('status');
        const resultDiv = document.getElementById('result');
        let intervalId;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Resetar a interface
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            statusDiv.textContent = 'Iniciando upload...';
            resultDiv.textContent = '';
            resultDiv.className = '';

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);

            xhr.onloadstart = function() {
                intervalId = setInterval(checkProgress, 1000);
            };

            xhr.onloadend = function() {
                clearInterval(intervalId);
                checkProgress().then(() => {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            resultDiv.textContent = response.message || 'Migração concluída com sucesso!';
                            resultDiv.className = 'success';
                            progressBar.style.width = '100%';
                            progressBar.textContent = '100%';
                            statusDiv.textContent = 'Concluído.';
                        } else {
                            resultDiv.textContent = 'Erro: ' + (response.message || 'Ocorreu um erro desconhecido.');
                            resultDiv.className = 'error';
                            statusDiv.textContent = 'Erro durante o processo.';
                        }
                    } catch (e) {
                        resultDiv.textContent = 'Erro ao processar resposta do servidor: ' + xhr.responseText;
                        resultDiv.className = 'error';
                        statusDiv.textContent = 'Erro crítico.';
                    }
                });
            };

            xhr.onerror = function() {
                clearInterval(intervalId);
                resultDiv.textContent = 'Erro na requisição de upload.';
                resultDiv.className = 'error';
                statusDiv.textContent = 'Erro de rede.';
            };

            xhr.send(formData);
        });

        async function checkProgress() {
            try {
                const response = await fetch('atualizacao/progress.php');
                if (!response.ok) {
                    console.error('Erro ao buscar progresso:', response.statusText);
                    return;
                }
                const data = await response.json();
                if (data && data.progress !== undefined) {
                    const percent = Math.round(data.progress);
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                    statusDiv.textContent = data.message || 'Processando...';
                } else {
                    if (progressBar.style.width === '0%') {
                        statusDiv.textContent = "Aguardando início do processamento...";
                    }
                }
            } catch (error) {
                console.error('Falha ao verificar progresso:', error);
            }
        }
    })();
    </script>
</div>
