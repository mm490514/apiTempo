<?php 
include_once('../conexao.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$nome = @$_GET['nome'];



if ($nome){    

$query = $pdo->prepare("DELETE FROM escala where nome = :nome");
$query->bindParam(':nome', $nome, PDO::PARAM_STR);
$query->execute();

$result = json_encode(array('success'=>true, 'message'=>'Excluído com sucesso!!'));

echo $result;
}



?>