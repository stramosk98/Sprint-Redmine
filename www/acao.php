<?php
    include_once("config.php");
    
    $conexao = criaConexao();
    $sprint = arraySprint();
    if ($sprint) {
        inserir($sprint, $conexao);
    }

    function arraySprint(){
            $sprint = array(
                            'nome'        => isset($_POST['nome'])        ? $_POST['nome']        : "",
                            'data-inicio' => isset($_POST['data-inicio']) ? $_POST['data-inicio'] : "",
                            'data-final'  => isset($_POST['data-final'])  ? $_POST['data-final']  : "",
                            'tasks'       => isset($_POST['tasks'])       ? $_POST['tasks']  : []
                        );
            return $sprint;
    }

    function criaConexao(){
        try{
            return new PDO(MYSQL_DSN,DB_USER,DB_PASSWORD);
        }catch(PDOException $e){
                print("Erro ao conectar com o banco de dados...<br>".$e->getMessage());
                die();
        }catch(Exception $e){
                print("Erro genérico...<br>".$e->getMessage());
                die();
        }
    }

    function inserir($sprint, $conexao){
        try {
            $conexao->beginTransaction();
        
            $querySprint = 'INSERT INTO sprints (nome, data_inicio, data_final) 
                            VALUES (:nome, :data_inicio, :data_final);';

            $stmtSprint = $conexao->prepare($querySprint);

            $stmtSprint->bindValue(':nome', $sprint['nome']);
            $stmtSprint->bindValue(':data_inicio', $sprint['data-inicio']);
            $stmtSprint->bindValue(':data_final', $sprint['data-final']);
            
            if (!$stmtSprint->execute()) {
                throw new Exception('Erro ao inserir sprint');
            }

            $sprintId = $conexao->lastInsertId();
    
            if (!empty($sprint['tasks'])) {
                foreach ($sprint['tasks'] as $task) {
                
                    $queryTarefa = 'INSERT INTO tarefas (id, descricao) 
                                    VALUES (:id, :tarefa_descricao);';

                    $stmtTarefa = $conexao->prepare($queryTarefa);

                    $stmtTarefa->bindValue(':id', $task);
                    $stmtTarefa->bindValue(':tarefa_descricao', "null");
                    
                    if (!$stmtTarefa->execute()) {
                        throw new Exception('Erro ao inserir tarefa');
                    }
        
                    $tarefaId = $conexao->lastInsertId();
        
                    $querySprintTarefa = 'INSERT INTO sprint_tarefas (sprint_id, tarefa_id) 
                                        VALUES (:sprint_id, :tarefa_id);';

                    $stmtSprintTarefa = $conexao->prepare($querySprintTarefa);

                    $stmtSprintTarefa->bindValue(':sprint_id', $sprintId);
                    $stmtSprintTarefa->bindValue(':tarefa_id', $tarefaId);
                    
                    if (!$stmtSprintTarefa->execute()) {
                        throw new Exception('Erro ao inserir relação sprint/tarefa');
                    }
                }
            }
        
            $conexao->commit();
            header('location: view.php?acao=visualizar&id='. $sprintId);
        } catch (Exception $e) {
            $conexao->rollBack();
            echo 'Erro ao inserir dados: ' . $e->getMessage();
        }    
    }
?>