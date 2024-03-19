<?php 
include_once('../conexao.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$nome = @$_GET['nome'];
$simbolo = @$_GET['simbolo'];


if ($nome && $simbolo){

$query = $pdo->prepare("INSERT INTO escala (nome, simbolo) VALUES (:nome, :simbolo)");
$query->bindParam(':nome', $nome, PDO::PARAM_STR);
$query->bindParam(':simbolo', $simbolo, PDO::PARAM_STR);
$query->execute();

$result = json_encode(array('success'=>true, 'message'=>'Inserido com sucesso!!'));

echo $result;
}



?>