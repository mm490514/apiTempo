<?php 

include_once('../conexao.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$cidade = @$_GET['cidade'];
$data = @$_GET['data'];

if ($data) {    
    $dataAtual = new DateTime();
    $hoje = new DateTime();
    $dataFornecida = new DateTime($data);

    $dataAtualMais10Dias = $dataAtual->modify('+10 days');     
    $hoje->setTime(0, 0, 0);
    $dataFornecida->setTime(0, 0, 0);
    
    if ($dataFornecida < $dataAtualMais10Dias && $dataFornecida >= $hoje) {

         // Prepare and execute the SQL query
        $query = $pdo->prepare("DELETE FROM clima");       
        $query->execute();
        
        $query = $pdo->prepare("SELECT * from fornecedores");       
        $query->execute();

        $res = $query->fetchAll(PDO::FETCH_ASSOC);

        for ($i=0; $i < count($res); $i++) { 
            foreach ($res[$i] as $key => $value) {  
                if ($res[$i]['tipo_arquivo'] == "CSV"){
                    $caminhoCSV = "../../base/" . $res[$i]['nome_arquivo'];
                    importarCSV($caminhoCSV);
                } else if ($res[$i]['tipo_arquivo'] == "JSON"){
                    $caminhoJSON = "../../base/" . $res[$i]['nome_arquivo'];
                    importarJSON($caminhoJSON);
                } else if ($res[$i]['tipo_arquivo'] == "XML"){
                    $caminhoXML = "../../base/" . $res[$i]['nome_arquivo'];
                    importarXML($caminhoXML);
                }

            }                
        }
        
        $cidadeNome = $cidade; // Sanitize if necessary
        $dataInicio = $dataFornecida->format('Y-m-d');
        $dataFim = $dataAtualMais10Dias->format('Y-m-d');
        
        // Prepare and execute the SQL query
        $query = $pdo->prepare("SELECT cidade.nome, clima.data, ROUND(AVG(clima.temperatura), 2) AS temperatura_media, escala.simbolo 
                                FROM clima
                                LEFT JOIN cidade ON clima.id_cidade = cidade.id
                                LEFT JOIN escala ON escala.id = clima.id_escala
                                WHERE cidade.nome = :cidadeNome
                                AND clima.data BETWEEN :dataInicio AND :dataFim
                                GROUP BY cidade.nome, clima.data, escala.simbolo");
        
        $query->bindParam(':cidadeNome', $cidadeNome, PDO::PARAM_STR);
        $query->bindParam(':dataInicio', $dataInicio, PDO::PARAM_STR);
        $query->bindParam(':dataFim', $dataFim, PDO::PARAM_STR);
        $query->execute();        
        
        $res = $query->fetchAll(PDO::FETCH_ASSOC);        

        for ($i=0; $i < count($res); $i++) { 
            foreach ($res[$i] as $key => $value) {  }    

            $dados[] = array(
                'nome' => $res[$i]['nome'],
                'data' => $res[$i]['data'],
                'temperatura_media' => $res[$i]['temperatura_media'],
                'simbolo' => $res[$i]['simbolo'],                      
            );
        }

        if(count($res) > 0){
            $result = json_encode(array('success'=>true, 'itens'=>$dados));
        }else{
            $result = json_encode(array('success'=>false, 'resultado'=>'0'));
        }

        echo $result;

    } else {
        $result = json_encode(array('success'=>false, 'resultado'=>'0'));
    }    
} 

function conectarBancoDados() {
    $host = '127.0.0.1'; 
    $usuario = 'root'; 
    $senha = 'root'; 
    $banco = 'clima'; 
    
    $dsn = "mysql:host=$host;dbname=$banco;charset=utf8mb4";

    try {
        $conexao = new PDO($dsn, $usuario, $senha);        
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexao;
    } catch (PDOException $e) {
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}


function obterIdCidade($conexao, $cidade) {
    $query = "SELECT id FROM cidade WHERE nome = ?";
    $stmt = $conexao->prepare($query);
    $stmt->execute([$cidade]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result['id'];
    } else {
        $query = "INSERT INTO cidade (nome) VALUES (?)";
        $stmt = $conexao->prepare($query);
        $stmt->execute([$cidade]);
        return $conexao->lastInsertId();
    }
}

function obterIdescala($conexao, $simbolo) {

    $query3 = $conexao->query("SELECT * from escala where simbolo = '$simbolo'");    
    $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
    $id = @$res3[0]['id'];       
    if ($id) {
        return $id;
    }
}

function importarCSV($caminhoArquivo) {
    $conexao = conectarBancoDados();
    $arquivo = fopen($caminhoArquivo, "r");

    if ($arquivo !== false) {
        fgetcsv($arquivo, 1000, ";");
        while (($linha = fgetcsv($arquivo, 1000, ";")) !== false) {
            // Remover o BOM (Byte Order Mark) se estiver presente
            $linha[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/','',$linha[0]);
            $cidade = $linha[0];
            $data = $linha[1];
            $temperatura = $linha[2];
            $escala = $linha[3];
            
            $id_cidade = obterIdCidade($conexao, $cidade);            
            $id_escala = obterIdescala($conexao, $escala);            
            
            $query = "INSERT INTO clima (id_cidade, id_escala, temperatura, data) VALUES (?, ?, ?, ?)";
            $stmt = $conexao->prepare($query);
            $stmt->execute([$id_cidade, $id_escala, $temperatura, $data]);
        }

        fclose($arquivo);        
    } else {
        echo "Erro ao abrir o arquivo.";
    }
}

function importarJSON($caminhoArquivo) {
    $conexao = conectarBancoDados();
    $jsonString = file_get_contents($caminhoArquivo);
    $jsonArray = json_decode($jsonString, true);

    if ($jsonArray !== null) {
        foreach ($jsonArray['forecast'] as $item) {
            $cidade = $item['city']['name'];
            $data = $item['date'];
            $temperatura = $item['weather'][0]['temperature'];
            $escala = $item['weather'][0]['measurement'];

            $id_cidade = obterIdCidade($conexao, $cidade);            
            $id_escala = obterIdescala($conexao, $escala);            
            
            $query = "INSERT INTO clima (id_cidade, id_escala, temperatura, data) VALUES (?, ?, ?, ?)";
            $stmt = $conexao->prepare($query);
            $stmt->execute([$id_cidade, $id_escala, $temperatura, $data]);
        }

        
    } else {
        echo "Erro ao ler o arquivo JSON.";
    }
}

function importarXML($caminhoArquivo) {
    $conexao = conectarBancoDados();
    $xml = simplexml_load_file($caminhoArquivo);

    if ($xml !== false) {
        foreach ($xml->city as $city) {
            $cidade = (string) $city->name;
            $data = (string) $city->date;
            $temperatura = (string) $city->weather->forecast_item->temperature;
            $escala = (string) $city->weather->forecast_item->measurement;
            
            $id_cidade = obterIdCidade($conexao, $cidade);
            $id_escala = obterIdescala($conexao, $escala);            
            
            $query = "INSERT INTO clima (id_cidade, id_escala, temperatura, data) VALUES (?, ?, ?, ?)";
            $stmt = $conexao->prepare($query);
            $stmt->execute([$id_cidade, $id_escala, $temperatura, $data]);
        }

       
    } else {
        echo "Erro ao carregar o arquivo XML.";
    }
}




?>