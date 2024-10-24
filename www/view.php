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

        $timezone = new DateTimeZone('america/sao_paulo');
        $newInicio = DateTime::createFromFormat('Y-m-d', $sprint['data_inicio'], $timezone);
        $newFinal = DateTime::createFromFormat('Y-m-d', $sprint['data_final'], $timezone);
        $datas = $newInicio->format('d/m/Y') . ' à ' . $newFinal->format('d/m/Y');

        function getDiasUteis($inicio, $final, $timezone) {
            $dateTime = DateTime::createFromFormat('Y-m-d', $inicio, $timezone);
            $listaDiasUteis = [$inicio];
            while (true) {
                $dateTime->modify('+1 weekday');
                $data = $dateTime->format('Y-m-d');
                $listaDiasUteis[] = $data;
                if ($data == $final) {
                    break;
                }
            }
        
            return $listaDiasUteis;
        }

        $diasUteis = getDiasUteis($sprint['data_inicio'], $sprint['data_final'], $timezone); 

        $diasUteisFormatados = array_map(function ($data) use ($timezone) {
            $newData = DateTime::createFromFormat('Y-m-d', $data, $timezone);
            return $newData->format('d/m/Y');
        }, $diasUteis);
        
    } catch (PDOException $e) { 
        print("Erro ao conectar com o banco de dados...<br>" . $e->getMessage());
        die();
    }
?>

<script>
    var tasks = <?php echo json_encode($sprint['tasks']); ?>;
    var diasUteis = <?php echo json_encode($diasUteis); ?>;
    var ultimoDia = <?php echo json_encode($sprint['data_final']); ?>;
</script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript"></script>
<script src="js/burndown.js"></script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sprint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body class="container">

    <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                    <h4 class="text-center"><?php echo $sprint['nome'] . " " . $datas; ?></h4>
                    <h5><?php echo $sprint['descricao']; ?></h5>
                    <h6>Dias úteis da sprint:</h6>
                    <h6><?php echo implode(", ", $diasUteisFormatados); ?></h6>
                    <h6>Desenvolvedores:</h6>
                    <h6 id="participantes"></h6>
                    <h6 id="horas_totais_trabalhadas"></h6>
                    <h6 id="tarefas_concluidas"></h6>
                    <h6 id="tarefas_atrasadas"></h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2><?php echo $sprint['nome'] . " " . $datas; ?></h2>
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
            <div class="row my-3 px-1">
                <div id="burndown_chart" style="width: 65%; height: 400px;"></div>
                <div id="pie_chart" style="width: 35%; height: 400px;"></div>
            </div>
            <div id="column_chart" style="width: 100%; height: 500px;"></div>
        </div>
    </div>
    <button class="btn btn-primary mt-2" type="button" onclick="voltar()" type="button">Voltar</button>
</body>
</html>
