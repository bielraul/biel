<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Teste de conexão com o servidor SMTP
$connection = fsockopen('smtp.gmail.com', 465, $errno, $errstr, 15);
if (!$connection) {
    echo "Erro: $errstr ($errno)";
    exit; // Encerra o script se a conexão falhar
} else {
    echo "Conexão bem-sucedida!";
    fclose($connection);
}

// 1. Configurações de segurança e destino
// A pasta onde as evidências serão salvas. Certifique-se que esta pasta existe e tem permissão de escrita.
$pasta_destino = __DIR__ . '/uploads/';


// Tipos de arquivos permitidos
$tipos_permitidos = ['image/jpeg', 'image/png', 'application/pdf'];
$tamanho_maximo = 5 * 1024 * 1024; // 5 MB em bytes

// 2. Validação e coleta dos dados do formulário
$erros = [];
$feedback = '';

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos de texto
    $plataforma = filter_input(INPUT_POST, 'platform', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
    $descricao = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($plataforma)) {
        $erros[] = 'A plataforma é um campo obrigatório.';
    }

    if (empty($descricao)) {
        $erros[] = 'A descrição é um campo obrigatório.';
    }

    // Processamento e validação de arquivos
    $arquivos_salvos = [];
    if (!empty($_FILES['evidence']['name'][0])) {
        foreach ($_FILES['evidence']['name'] as $key => $name) {
            $arquivo_temp = $_FILES['evidence']['tmp_name'][$key];
            $tamanho = $_FILES['evidence']['size'][$key];
            $tipo = $_FILES['evidence']['type'][$key];

            // Validação do arquivo
            if (!in_array($tipo, $tipos_permitidos)) {
                $erros[] = "O arquivo '{$name}' tem um tipo inválido. Apenas JPG, PNG e PDF são permitidos.";
                continue;
            }

            if ($tamanho > $tamanho_maximo) {
                $erros[] = "O arquivo '{$name}' excede o tamanho máximo de 5MB.";
                continue;
            }

            // Gerar um nome de arquivo único e seguro para evitar sobreposição e ataques.
            $extensao = pathinfo($name, PATHINFO_EXTENSION);
            $nome_seguro = bin2hex(random_bytes(16)) . '.' . $extensao;
            $caminho_completo = $pasta_destino . $nome_seguro;

            // Mover o arquivo temporário para o destino final
            if (move_uploaded_file($arquivo_temp, $caminho_completo)) {
                $arquivos_salvos[] = $caminho_completo;
            } else {
                $erros[] = "Erro ao mover o arquivo '{$name}'.";
            }
        }
    }

    // 3. Ação final: Se não houver erros, processa a denúncia
    if (empty($erros)) {
        // Importe as classes do PHPMailer. Se você usou o Composer, use o autoload.
        //require 'vendor/autoload.php';

        // Se você não usou o Composer, ajuste os caminhos
        require 'src/Exception.php';
        require 'src/PHPMailer.php';
        require 'src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configurações do servidor SMTP do Google
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'gabrielmarcolinodeoliveira@gmail.com';
$mail->Password   = 'iudi ikew ezzh qegd';
$mail->SMTPSecure = 'ssl'; // Pode tentar 'tls' também, mas 'ssl' para a porta 465 é o padrão.
$mail->Port       = 465;

// Adicione este bloco para desabilitar a verificação de certificado SSL
// Isso é útil para ambientes de desenvolvimento onde o certificado pode não ser verificado corretamente.
// IMPORTANTE: Em ambientes de produção, essa prática deve ser evitada se possível.
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

// Destinatários
$mail->setFrom('gabrielmarcolinodeoliveira@gmail.com', 'Proteção Infantil Local');
$mail->addAddress('gabrielmarcolinodeoliveira@gmail.com');

            // Conteúdo do e-mail
            $mail->isHTML(false); // Define o formato como texto puro
            $mail->Subject = 'Nova Denúncia de Abuso Infantil';
            $mail->Body    = "Nova Denúncia Recebida!\n\n" .
                             "Plataforma: {$plataforma}\n" .
                             "URL: {$url}\n" .
                             "Descrição: \n{$descricao}\n\n" .
                             "Caminhos das Evidências no servidor: \n" . implode("\n", $arquivos_salvos);

            $mail->send();

            // Após o processamento, redireciona para a página de sucesso
            header('Location: sucesso.html');
            exit;

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            // Em caso de erro, exibe a mensagem para debug
            echo "O e-mail da denúncia não pôde ser enviado. Erro do Mailer: {$mail->ErrorInfo}";
            exit;
        }

    } else {
        // Se houver erros, exibe a mensagem para o usuário
        echo "<h1>Erro ao enviar a denúncia:</h1>";
        echo "<p>" . implode('<br>', $erros) . "</p>";
        echo "<a href='index.html'>Voltar</a>";
        exit;
    }
}

?>