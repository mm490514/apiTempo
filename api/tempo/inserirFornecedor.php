<?php 
include_once('../conexao.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$nome_arquivo = @$_GET['nome_arquivo'];
$tipo_arquivo = @$_GET['tipo_arquivo'];
$nome_fornecedor = @$_GET['nome_fornecedor'];



if ($nome_arquivo && $tipo_arquivo && $nome_fornecedor ){
    $query = $pdo->prepare("SELECT cidade.nome, clima.data, ROUND(AVG(clima.temperatura), 2) AS temperatura_media, escala.simbolo 
    FROM clima
    LEFT JOIN cidade ON clima.id_cidade = cidade.id
    LEFT JOIN escala ON escala.id = clima.id_escala
    WHERE cidade.nome = :cidadeNome
    AND clima.data BETWEEN :dataInicio AND :dataFim
    GROUP BY cidade.nome, clima.data, escala.simbolo");

$query = $pdo->prepare("INSERT INTO fornecedores (nome_arquivo, tipo_arquivo, nome_fornecedor) VALUES (:nome_arquivo, :tipo_arquivo, :nome_fornecedor)");
$query->bindParam(':nome_arquivo', $nome_arquivo, PDO::PARAM_STR);
$query->bindParam(':tipo_arquivo', $tipo_arquivo, PDO::PARAM_STR);
$query->bindParam(':nome_fornecedor', $nome_fornecedor, PDO::PARAM_STR);
$query->execute();

$result = json_encode(array('success'=>true, 'message'=>'Inserido com sucesso!!'));

echo $result;
}



?>