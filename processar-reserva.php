<?php
// --- INÍCIO DA CONFIGURAÇÃO ---
// Detalhes da ligação - ★★★ SUBSTITUA ESTES VALORES ★★★
$servername = "localhost";
$username = "admin"; // O mesmo do test_db.php
$password = "atec123"; // A mesma do test_db.php
$dbname = "picanhadario";           // O nome da sua BD
// --- FIM DA CONFIGURAÇÃO ---

// Variáveis para guardar o estado
$sucesso = false;
$mensagem_erro = "";
$nome_seguro = "";
$data_formatada = "";
$pessoas = 0;

// Tentar criar a ligação
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a ligação
if ($conn->connect_error) {
    $sucesso = false;
    $mensagem_erro = "Falha na Ligação: " . $conn->connect_error;
} else {
    // 1. Obter os dados do formulário de forma segura
    $nome = trim($_POST['nome_completo']);
    $telefone = trim($_POST['telefone']);
    $data = trim($_POST['data_reserva']);
    $pessoas = (int)$_POST['numero_pessoas'];
    $obs = trim($_POST['observacoes']);

    // Para mostrar na página de sucesso, mesmo que o $nome falhe
    $nome_seguro = htmlspecialchars($nome);
    $data_formatada = date("d/m/Y", strtotime($data));


    // 2. Validação ATUALIZADA (com verificação de comprimento)

    // Primeiro, verificamos os campos obrigatórios
    if (empty($nome) || empty($telefone) || empty($data) || $pessoas <= 0) {
        $sucesso = false;
        $mensagem_erro = "Erro: Todos os campos obrigatórios devem ser preenchidos.";

    // ★★★ NOVO CHECK ★★★ - Verificamos o comprimento do NOME
    } elseif (strlen($nome) > 100) {
        $sucesso = false;
        $mensagem_erro = "O seu nome é demasiado longo. Por favor, abrevie (máx 100 caracteres).";

    // ★★★ NOVO CHECK ★★★ - Verificamos o comprimento das OBSERVAÇÕES
    } elseif (strlen($obs) > 500) {
        $sucesso = false;
        $mensagem_erro = "As suas observações são demasiado longas (máx 500 caracteres).";

    // Se tudo passar, continuamos para a base de dados
    } else {

        // 3. Preparar a SQL (usando "Prepared Statements" para segurança)
        $stmt = $conn->prepare("INSERT INTO reservas (nome_completo, telefone, data_reserva, numero_pessoas, observacoes) VALUES (?, ?, ?, ?, ?)");

        // 4. "Bind" (Ligar) as variáveis do PHP aos '?' da SQL
        $stmt->bind_param("sssis", $nome, $telefone, $data, $pessoas, $obs);

        // 5. Executar a query
        if ($stmt->execute()) {
            $sucesso = true;
        } else {
            $sucesso = false;
            // Erro genérico da BD (pode ser "Telefone duplicado", etc.)
            $mensagem_erro = "Erro ao executar a query: " . $stmt->error;
        }

        // 6. Fechar o statement
        $stmt->close();
    }

    // 7. Fechar a ligação
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $sucesso ? 'Reserva Confirmada' : 'Erro na Reserva'; ?> - Taberna da Picanha</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🥩</text></svg>">

    <style>
        /* [O SEU CSS DA PÁGINA DE SUCESSO - OMITIDO POR BREVIDADE, É IGUAL AO ANTERIOR] */
        :root {
            --verde-portugal: #006600;
            --vermelho-tinto: #8B0000;
            --dourado: #DAA520;
            --bege: #F5F5DC;
            --castanho: #8B4513;
            --preto: #1C1C1C;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', serif;
            background: linear-gradient(135deg, var(--bege) 0%, #FFF8DC 100%);
            color: var(--preto);
            line-height: 1.8;
        }

        /* Reutilizamos o cabeçalho do seu site */
        .header {
            background: linear-gradient(45deg, var(--verde-portugal), var(--vermelho-tinto));
            color: white;
            padding: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .header h1 {
            font-size: 3.5rem;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
        }

        /* ----- ESTILOS NOVOS PARA A PÁGINA DE CONFIRMAÇÃO ----- */

        .confirmation-container {
            padding: 4rem 2rem;
            max-width: 700px; /* Largura da caixa */
            margin: 0 auto;
            text-align: center;
        }

        .confirmation-box {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 3px solid transparent;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Estilo para o ícone de Sucesso ou Erro */
        .icon {
            font-size: 5rem;
            line-height: 1;
            margin-bottom: 1.5rem;
        }
        .icon-success {
            color: var(--verde-portugal);
        }
        .icon-error {
            color: var(--vermelho-tinto);
        }

        .confirmation-box h2 {
            font-size: 2.5rem;
            color: var(--preto);
            margin-bottom: 1.5rem;
        }

        .confirmation-box p {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 2rem;
        }

        /* Reutilizamos o estilo do botão de reserva */
        .btn-return {
            background: linear-gradient(45deg, var(--verde-portugal), var(--dourado));
            color: white;
            padding: 1rem 3rem;
            border: none;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none; /* Para o <a> */
            display: inline-block;
        }

        .btn-return:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>Taberna da Picanha</h1>
    </header>

    <main class="confirmation-container">
        <div class="confirmation-box">

            <?php if ($sucesso): ?>

                <div class="icon icon-success">✓</div>
                <h2>Reserva Recebida!</h2>
                <p>
                    Obrigado, <strong><?php echo $nome_seguro; ?></strong>.
                    A sua pré-reserva para <strong><?php echo $pessoas; ?> pessoa(s)</strong> no dia
                    <strong><?php echo $data_formatada; ?></strong> foi recebida.
                </p>
                <p>Entraremos em contacto pelo seu telefone para confirmar todos os detalhes.</p>
                <a href="/" class="btn-return">Voltar ao Início</a>

            <?php else: ?>

                <div class="icon icon-error">✗</div>
                <h2>Ups! Algo falhou.</h2>
                <p>
                    Lamentamos, mas não foi possível registar a sua reserva neste momento.
                </p>

                <p style="font-size: 1.1rem; color: var(--vermelho-tinto); margin-top: -1rem; font-weight: bold;">
                    Motivo: <?php echo htmlspecialchars($mensagem_erro); ?>
                </p>

                <a href="/" class="btn-return">Tentar Novamente</a>

            <?php endif; ?>

        </div>
    </main>

</body>
</html>
