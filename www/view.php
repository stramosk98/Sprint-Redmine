<?php

    include_once('config.php');

    $acao = isset($_GET['acao']) ? $_GET['acao'] : "";
    $id = isset($_GET['id']) ? $_GET['id'] : "";

    try {
        $conexao = new PDO(MYSQL_DSN, DB_USER, DB_PASSWORD);
        
        $query = 'SELECT sprints.*, GROUP_CONCAT(tarefas.id) as tasks 
                FROM sprints 
                JOIN sprint_tarefas ON sprints.id = sprint_tarefas.sprint_id
                JOIN tarefas ON tarefas.id = sprint_tarefas.tarefa_id
                WHERE sprints.id = :id
                GROUP BY sprints.id';
        
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        $sprint = $stmt->fetch();

        function getDiasUteis($aPartirDe, $quantidadeDeDias = 30) {
            echo $aPartirDe;
            $timezone = new DateTimeZone('america/sao_paulo');

            $dateTime = DateTime::createFromFormat('d/m/Y', $aPartirDe, $timezone);
            print_r($dateTime);
            $listaDiasUteis = [];
            $contador = 0;
            while ($contador < $quantidadeDeDias) {
                $dateTime->modify('+1 weekday'); // adiciona um dia pulando finais de semana
                $data = $dateTime->format('Y-m-d');
                if (!isFeriado($data)) {
                    $listaDiasUteis[] = $data;
                    $contador++;
                }
            }
        
            return $listaDiasUteis;
        }

        $diasUteis = getDiasUteis($sprint['data_inicio'], 14); 

        echo ($diasUteis);
        
        print_r($sprint);
        
    } catch (PDOException $e) { 
        print("Erro ao conectar com o banco de dados...<br>" . $e->getMessage());
        die();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sprint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h1><?php echo $sprint['nome']; ?></h1>
                    <section id="view" class="row">
                        <table id="table_tasks" class="table ">
                            <thead>
                                <tr>
                                    <th class="col-1">Count</th>
                                    <th class="col-1">TaskId</th>
                                    <th class="col-6">Stories</th>
                                    <th class="col-2">Description</th>
                                    <th class="col-2">Developer</th>
                                </tr>
                            </thead>
                            <tbody id="table_tasks_body">
                            </tbody>
                        </table>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function getData(tarefasId) {
            if (!tarefasId) return;
            const url = "https://api.allorigins.win/get?url=" + encodeURIComponent(`http://fabtec.ifc-riodosul.edu.br/issues.json?issue_id=${tarefasId}&key=b7c238adc2c0af943c1f0fa9de6489ce190bd6d5&status_id=*`);
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Response status: ${response.status}`);
                }

                const json = await response.json();
                let tarefas = [];
                const jsonFormatted = JSON.parse(json.contents);
                
                jsonFormatted.issues.forEach((issue, index) => {
                    console.log(issue)
                    let inicio = new Date(issue.created_on);
                    let termino = new Date(issue.closed_on);
                    const row = `<td>${++index}</td><td>${issue.id}</td><td>${issue.subject}</td><td>${issue.author.name}</td><td>${issue.assigned_to.name}</td>`;
                    document.getElementById('table_tasks_body').innerHTML += row;
                });
            } catch (error) {
                console.error(error.message);
            }
        }
        let tasks = <?php echo json_encode($sprint['tasks']); ?>;
        getData(tasks);
        function voltar(){
            window.location.replace('http://127.0.0.1/index.php')
        }
    </script>
    <button class="btn btn-primary mt-2" onclick="voltar()" type="button">Voltar</button>
</body>
</html>
