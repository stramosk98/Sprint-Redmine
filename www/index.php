<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
 
    <title>Cadastro de sprint</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> 
</head>
    <body class='container'>
    <div class="row">
      <div class="col-6">
        <div class="card" style="left:50%;">
          <div class="card-body">

    <h1>Cadastrar nova Sprint</h1>
        <section id='view' class='row'>
            <form id="form-crud" action="acao.php" method="post">
                    <div class='col mb-3'>
                        <label for="nome">Nome:</label>
                        <input type="text" class='form-control' name='nome' id='nome' placeholder="Sprint 2.0">
                    </div>
                    <div class='col mb-3'>
                        <label for="descricao">Descrição:</label>
                        <input type="text" class='form-control' name='descricao' id='descricao' placeholder="Descrição da sprint...">
                    </div>
                    <div class="row px-3 mb-3">
                        <div class='col-6'>
                            <label for="data-inicio">Data Início:</label>
                            <input type="date" class='form-control' name='data-inicio' id='data-inicio'>
                        </div>
                        <div class='col-6 mb-3'>
                            <label for="data-final">Data Final:</label>
                            <input type="date" class='form-control' name='data-final' id='data-final'>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-3 px-4" for="tasks-container">Tarefas</label>
                        <div class="col-1">
                            <button type="button" class="btn btn-success" onclick="addTask(event)" id="mais">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div id="tasks-container" class="col-10">
                            <input type="text" class='form-control mb-1' name="tasks[]" id="tasks" placeholder="Tarefa id">
                        </div>
                        <div  class="col-1">
                            <button type="button" class="btn btn-danger" onclick="removeTask(event)" id="menos">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16">
                                    <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class='col'>  
                        <br>                  
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">CRIAR</button>               
                            <a href="..\index.html"><button type="button" class="btn btn-primary">MENU</button></a>
                          </div>
                         <div class="alert alert-danger d-none">
                            Preencha o campo <span id="campo-erro"></span>!
                        </div>
                    </div>
                </div>
            </form>
            </div>
        </section>
        <hr>

    <div class="row">
      <div class="col-12">
        <div class="card" style="width: 80rem; right:15%;">
          <div class="card-body">
            <form action="" method="get" id='fpesquisa'>
             <div class='row'>
                <div class='col-6'><h2>Sprints cadastradas</h2></div>
                  <div class='col-4'><input class='form-control' type="search" name='busca' id='busca'></div>
                    <div class='col'><button type="submit" class='btn btn-success' name='pesquisa' id='pesquisa'>Buscar</button></div>
                        </div>
                            </form>
                                <div class='row'>
            <table class='table table-striped table-hover'>
                <thead>
                    <tr>
                        <th class="col-2">Nome</th>
                        <th class="col-1">Início</th>
                        <th class="col-1">Final</th>
                        <th class="col-5">Tarefas</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider" id='corpo'>
                    
                <?php  
                
                include_once "config.php";
                    try{
                        $conexao = new PDO(MYSQL_DSN,DB_USER,DB_PASSWORD);
                        $query = 'SELECT sprints.*, GROUP_CONCAT(tarefas.id) as tarefas_id 
                            FROM sprints 
                            JOIN sprint_tarefas ON sprints.id = sprint_tarefas.sprint_id
                            JOIN tarefas ON tarefas.id = sprint_tarefas.tarefa_id
                            GROUP BY sprints.id
                            ORDER BY sprints.data_inicio';

                        $stmt = $conexao->prepare($query);

                        $stmt->execute();
                        
                        $sprints = $stmt->fetchAll();
                        
                        foreach($sprints as $sprint){ 
                            $visualizar = '<a class="btn btn-warning" href=view.php?acao=visualizar&id='.$sprint['id'].'>Visualizar</a>';
                            $excluir = '<a class="btn btn-danger" href=acao.php?acao=excluir&id='.$sprint['id'].'>Excluir</a>';
                            echo '</td><td>'.$sprint['nome'].'</td><td>'.$sprint['descricao'].'</td><td>'.$sprint['data_inicio'].'</td><td>'.$sprint['data_final'].'</td><td class="text-truncate" style="max-width:10px">'.$sprint['tarefas_id'].'</td><td>'.$visualizar.'</td><td>'.$excluir.'</td></tr>';
                        }
                    }catch(PDOException $e){ 
                        print("Erro ao conectar com o banco de dados...<br>".$e->getMessage());
                        die();                    
                    }           
                ?>  
                </tbody>      
            </table>
        </div>
    </div>
    </div>
    </div>
</section>
    <script src="js/script.js"></script>
</body>
</html>