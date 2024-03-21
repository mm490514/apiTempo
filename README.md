# Projeto de Importação e Consulta de Dados Climáticos

Este projeto consiste em um sistema desenvolvido em PHP para importar dados climáticos de diferentes fontes (CSV, JSON e XML) para um banco de dados MySQL. Além disso, ele fornece uma API para consultar as médias de temperatura em uma determinada cidade para um intervalo de datas.

## Funcionalidades Principais

1. **Importação de Dados Climáticos:**
   - O sistema permite importar dados climáticos de três tipos de arquivos: CSV, JSON e XML. Os dados importados incluem informações como nome da cidade, data, temperatura e escala de medição.
   - Para cada tipo de arquivo, existe uma função específica (`importarCSV`, `importarJSON` e `importarXML`) que realiza a importação dos dados para o banco de dados.

2. **Consulta de Médias de Temperatura:**
   - A API oferece a funcionalidade de consultar as médias de temperatura de uma cidade específica para um intervalo de datas.
   - Os dados são consultados no banco de dados e são retornados no formato JSON, contendo o nome da cidade, a data, a temperatura média e a escala de medição.

## Estrutura do Projeto

O projeto está estruturado da seguinte forma:

- **index.php:** Este arquivo é responsável por receber requisições HTTP, processar os parâmetros recebidos e chamar as funções apropriadas para realizar as operações de importação e consulta de dados climáticos.
  
- **conexao.php:** Arquivo responsável por estabelecer a conexão com o banco de dados MySQL.

- **Funções de Importação (importarCSV, importarJSON, importarXML):** São responsáveis por ler os arquivos de diferentes formatos (CSV, JSON, XML) e inserir os dados correspondentes no banco de dados.

- **Funções Auxiliares (conectarBancoDados, obterIdCidade, obterIdescala):** São funções auxiliares utilizadas para realizar operações relacionadas ao banco de dados, como estabelecer conexão, obter o ID da cidade e da escala de medição.

## Como Executar o Projeto

1. **Configuração do Ambiente:**
   - Certifique-se de ter um servidor web configurado (como Apache) e o PHP instalado em sua máquina.

2. **Configuração do Banco de Dados:**
   - Crie um banco de dados MySQL e importe o arquivo `schema.sql` para criar a estrutura do banco de dados necessária para este projeto.

3. **Configuração do Arquivo de Conexão:**
   - No arquivo `conexao.php`, atualize as variáveis `$host`, `$usuario`, `$senha` e `$banco` com as configurações do seu banco de dados.

4. **Execução:**
   - Coloque todos os arquivos do projeto em um diretório acessível pelo servidor web.
   - Acesse o arquivo `index.php` através de um navegador da web para realizar as operações de importação e consulta de dados climáticos.

## Observações

- Certifique-se de que os arquivos CSV, JSON e XML a serem importados estão localizados no diretório `base` do projeto, conforme especificado no código.
- Este projeto foi desenvolvido com o objetivo de demonstrar a funcionalidade de importação e consulta de dados climáticos e pode ser expandido para incluir mais funcionalidades ou melhorias de acordo com os requisitos específicos do usuário.
