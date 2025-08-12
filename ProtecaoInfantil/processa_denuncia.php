<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

// Caminho da pasta de uploads
$pasta_destino = __DIR__ . '/uploads/';

// Criar a pasta se não existir
if (!is_dir($pasta_destino)) {
    mkdir($pasta_destino, 0777, true);
}

$tipos_permitidos = ['image/jpeg', 'image/png', 'application/pdf'];
$tamanho_maximo = 5 * 1024 * 1024; // 5 MB

$erros = [];
$arquivos_salvos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plataforma = filter_input(INPUT_POST, 'platform', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
    $descricao = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($plataforma)) {
        $erros[] = 'A plataforma é obrigatória.';
    }
    if (empty($descricao)) {
        $erros[] = 'A descrição é obrigatória.';
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gabrielmarcolinodeoliveira@gmail.com';
        $mail->Password   = 'iudi ikew ezzh qegd';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('gabrielmarcolinodeoliveira@gmail.com', 'Proteção Infantil Local');
        $mail->addAddress('gabrielmarcolinodeoliveira@gmail.com');

        if (!empty($_FILES['evidence']['name'][0])) {
            foreach ($_FILES['evidence']['name'] as $key => $name) {
                $arquivo_temp = $_FILES['evidence']['tmp_name'][$key];
                $tamanho = $_FILES['evidence']['size'][$key];
                $tipo = $_FILES['evidence']['type'][$key];

                if (!in_array($tipo, $tipos_permitidos)) {
                    $erros[] = "O arquivo '{$name}' tem tipo inválido.";
                    continue;
                }
                if ($tamanho > $tamanho_maximo) {
                    $erros[] = "O arquivo '{$name}' excede 5MB.";
                    continue;
                }

                $extensao = pathinfo($name, PATHINFO_EXTENSION);
                $nome_seguro = bin2hex(random_bytes(16)) . '.' . $extensao;
                $caminho_completo = $pasta_destino . $nome_seguro;

                if (move_uploaded_file($arquivo_temp, $caminho_completo)) {
                    clearstatcache(true, $caminho_completo);
                    if (file_exists($caminho_completo)) {
                        $arquivos_salvos[] = $caminho_completo;
                        $mail->addAttachment(
                            $caminho_completo,
                            $name,
                            'base64',
                            mime_content_type($caminho_completo)
                        );
                    } else {
                        $erros[] = "Arquivo salvo mas não encontrado para anexar: {$name}.";
                    }
                } else {
                    $erros[] = "Erro ao mover o arquivo '{$name}'.";
                }
            }
        }

        if (empty($erros)) {
            $mail->isHTML(false);
            $mail->Subject = 'Nova Denúncia de Abuso Infantil';
            $mail->Body = "Nova Denúncia Recebida!\n\n" .
                          "Plataforma: {$plataforma}\n" .
                          "URL: {$url}\n" .
                          "Descrição:\n{$descricao}\n\n" .
                          "Arquivos anexados: " . count($arquivos_salvos);

            $mail->send();

            header('Location: sucesso.html');
            exit;
        } else {
            echo "<h1>Erro ao enviar a denúncia:</h1>";
            echo "<p>" . implode('<br>', $erros) . "</p>";
            echo "<a href='index.html'>Voltar</a>";
            exit;
        }
    } catch (Exception $e) {
        echo "O e-mail não pôde ser enviado. Erro: {$mail->ErrorInfo}";
        exit;
    }
} else {
    echo "Acesso inválido.";
    exit;
}
