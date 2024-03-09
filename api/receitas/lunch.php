<?php 

include_once('../conexao.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$igredientes = json_decode(@$postjson['igredientes']);


foreach ($postjson['ingredientes'] as $nome => $data) {    

    $data_validade_obj = DateTime::createFromFormat('d/m/Y', $data['expiration_date']);    
    $data_before_obj = DateTime::createFromFormat('d/m/Y', $data['best_before_date']);    
    $hoje = new DateTime();
    if ($data_validade_obj > $hoje) {
        $ingredientes_validos[] = "'" . $nome . "'";
    }

    $ingredientes_com_data[$nome] = $data_before_obj->format('Y-m-d');;
}

$string_ingredientes_validos = implode(', ', $ingredientes_validos);
//$string_ingredientes_invalidos = implode(', ', $ingredientes_invalidos);

$query = $pdo->prepare("
SELECT r.*
FROM receitas r
INNER JOIN receitas_igrediente ri ON ri.id_receita = r.id
INNER JOIN igredientes i ON ri.id_igrediente = i.id
WHERE i.descricao IN ($string_ingredientes_validos)
GROUP BY r.id, r.nome
HAVING COUNT(DISTINCT CASE WHEN i.descricao IN ($string_ingredientes_validos) THEN i.id END) >= (
    SELECT COUNT(DISTINCT id_igrediente)
    FROM receitas_igrediente
    WHERE id_receita = r.id
);");



$query->execute();

$res = $query->fetchAll(PDO::FETCH_ASSOC);

$ids_receitas = array();

for ($i=0; $i < count($res); $i++) { 
    foreach ($res[$i] as $key => $value) { 
        if ($key === 'id') {
            $ids_receitas[] = $value;
        }
     }    

    $dados[] = array(
        'id' => $res[$i]['id'], 
        'nome' => $res[$i]['nome'],
        'modo_preparo' => $res[$i]['modo_preparo'],
        'tempo' => $res[$i]['tempo'],
        'porcoes' => $res[$i]['porcoes'],       
                   
    );
}

$ids_receitas_str = implode(',', $ids_receitas);

// Verifica se a string de IDs está vazia ou não
if (!empty($ids_receitas_str)) {
    // Prepara a consulta SQL usando os IDs de receitas
    $query = $pdo->prepare("
        SELECT r.id AS id_receita, i.descricao 
        FROM receitas r
        INNER JOIN receitas_igrediente ri ON (ri.id_receita = r.id)
        INNER JOIN igredientes i ON (ri.id_igrediente = i.id)
        WHERE r.id IN ($ids_receitas_str)
    ");
} else {
    // Se não houver IDs de receitas, consulta vazia
    $query = $pdo->prepare("SELECT NULL AS id_receita, NULL AS descricao WHERE 1 = 0");
}

$query->execute();

$res = $query->fetchAll(PDO::FETCH_ASSOC);

for ($i=0; $i < count($res); $i++) { 
    foreach ($res[$i] as $key => $value) {  }    

    $igredientes[] = array(   
        'id_receita' => $res[$i]['id_receita'],       
        'descricao' => $res[$i]['descricao'],                   
    );
}

$json_ingredientes = json_encode($igredientes);
$json_ingredientes_com_data = json_encode($ingredientes_com_data);

$novo_array = array();

// Percorre os arrays $igredientes e $ingredientes_com_data simultaneamente
for ($i = 0; $i < count($igredientes); $i++) {
    // Verifica se existe uma entrada correspondente no array $ingredientes_com_data
    if (array_key_exists($igredientes[$i]['descricao'], $ingredientes_com_data)) {
        // Adiciona os dados relevantes ao novo array
        $novo_array[] = array(
            'id_receita' => $igredientes[$i]['id_receita'],
            'data_before' => $ingredientes_com_data[$igredientes[$i]['descricao']]
        );
    }
}

// Converte o novo array para JSON
$json_novo_array = json_encode($novo_array);

// Decodifica o JSON para um array associativo
$dados_novo_array = json_decode($json_novo_array, true);

// Array para armazenar o menor data_before para cada id_receita
$menor_data_before_por_receita = array();

// Percorre o array para encontrar o menor data_before para cada id_receita
foreach ($dados_novo_array as $item) {
    $id_receita = $item['id_receita'];
    $data_before = $item['data_before'];

    // Se o id_receita já estiver no array $menor_data_before_por_receita
    if (array_key_exists($id_receita, $menor_data_before_por_receita)) {
        // Compara o data_before atual com o armazenado no array
        if ($data_before < $menor_data_before_por_receita[$id_receita]) {
            // Atualiza o menor data_before para esta id_receita
            $menor_data_before_por_receita[$id_receita] = $data_before;
        }
    } else {
        // Se esta é a primeira ocorrência desta id_receita, armazena o data_before
        $menor_data_before_por_receita[$id_receita] = $data_before;
    }
}

// Converte o array para JSON
$json_menor_data_before = json_encode($menor_data_before_por_receita);

// Decodifica o JSON para um array associativo
$menor_data_before_por_receita = json_decode($json_menor_data_before, true);

// Novo array combinado
$novo_array_combinado = array();

// Percorre o array $dados
foreach ($dados as $item) {
    // Verifica se o id da receita existe no array $menor_data_before_por_receita
    if (isset($menor_data_before_por_receita[$item['id']])) {
        // Adiciona os campos 'best_before', 'id' e 'nome' ao novo array
        $novo_array_combinado[] = array(
            'best_before' => $menor_data_before_por_receita[$item['id']],
            'id' => $item['id'],
            'nome' => $item['nome'],
            'modo_preparo' => $item['modo_preparo'],
            'tempo' => $item['tempo'],
            'porcoes' => $item['porcoes']   
        
        );
    }
}
$json_novo_array_combinado = json_encode($novo_array_combinado);

$novo_array_combinado = json_decode($json_novo_array_combinado, true);

// Função de comparação para ordenar pelo campo 'best_before'
function compararPorBestBefore($a, $b) {
    return strtotime($a['best_before']) - strtotime($b['best_before']);
}

// Ordenar o array usando a função de comparação personalizada
usort($novo_array_combinado, 'compararPorBestBefore');

// Codificar o array ordenado de volta para JSON
$json_novo_array_combinado_ordenado = json_encode($novo_array_combinado);



var_dump($json_novo_array_combinado_ordenado);exit;







if(count($res) > 0){
    $result = json_encode(array('success'=>true, 'itens'=>$dados));
}else{
    $result = json_encode(array('success'=>false, 'resultado'=>'0'));
}

echo $result;

?>