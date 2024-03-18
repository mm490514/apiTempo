<?php 
include_once('../conexao.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$nome_fornecedor = @$_GET['nome_fornecedor'];



if ($nome_fornecedor ){    

$query = $pdo->prepare("DELETE FROM fornecedores where nome_fornecedor = :nome_fornecedor");
$query->bindParam(':nome_fornecedor', $nome_fornecedor, PDO::PARAM_STR);
$query->execute();

$result = json_encode(array('success'=>true, 'message'=>'Excluído com sucesso!!'));

echo $result;
}



?>