<?php
    include_once("config.php");
    
    $conexao = criaConexao();

    $acao = isset($_GET['acao']) ? $_GET['acao'] : "";
    $id = isset($_GET['id']) ? $_GET['id'] : "";

    if ($acao == 'excluir' && $id) {
        excluir($id, $conexao);
    } else {
        $sprint = arraySprint();
        if ($sprint) {
            inserir($sprint, $conexao);
        }
    }


    function arraySprint(){
            $sprint = array(
                            'nome'        => isset($_POST['nome'])        ? $_POST['nome']        : "",
                            'descricao'   => isset($_POST['descricao'])   ? $_POST['descricao']   : "",
                            'data-inicio' => isset($_POST['data-inicio']) ? $_POST['data-inicio'] : "",
                            'data-final'  => isset($_POST['data-final'])  ? $_POST['data-final']  : "",
                            'tasks'       => isset($_POST['tasks'])       ? $_POST['tasks']       : []
                        );
            return $sprint;
    }

    function criaConexao() {
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

    function inserir($sprint, $conexao) {
        try {
            $conexao->beginTransaction();
        
            $querySprint = 'INSERT INTO sprints (nome, descricao, data_inicio, data_final) 
                            VALUES (:nome, :descricao, :data_inicio, :data_final);';

            $stmtSprint = $conexao->prepare($querySprint);

            $stmtSprint->bindValue(':nome', $sprint['nome']);
            $stmtSprint->bindValue(':descricao', $sprint['descricao']);
            $stmtSprint->bindValue(':data_inicio', $sprint['data-inicio']);
            $stmtSprint->bindValue(':data_final', $sprint['data-final']);
            
            if (!$stmtSprint->execute()) {
                throw new Exception('Erro ao inserir sprint');
            }

            $sprintId = $conexao->lastInsertId();
    
            if (!empty($sprint['tasks'])) {
                foreach ($sprint['tasks'] as $task) {
                
                    $queryTarefa = 'INSERT INTO tarefas (id) 
                                    VALUES (:id);';

                    $stmtTarefa = $conexao->prepare($queryTarefa);
                    $stmtTarefa->bindValue(':id', $task);
                    
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

    function excluir($id, $conexao) {
        try {
            $conexao->beginTransaction();

            $querySprintTarefa = 'DELETE FROM sprint_tarefas WHERE sprint_id = :sprint_id;';

            $stmtSprintTarefa = $conexao->prepare($querySprintTarefa);
            $stmtSprintTarefa->bindValue(':sprint_id', $id);
            
            if (!$stmtSprintTarefa->execute()) {
                throw new Exception('Erro ao excluir relação sprint/tarefa');
            }
            
            $querySprint = 'DELETE FROM sprints WHERE id = :id;';

            $stmtSprint = $conexao->prepare($querySprint);
            $stmtSprint->bindValue(':id', $id);
            
            if (!$stmtSprint->execute()) {
                throw new Exception('Erro ao excluir sprint');
            }
                
            $conexao->commit();
            header('location: index.php');
        } catch (Exception $e) {
            $conexao->rollBack();
            echo 'Erro ao excluir dados: ' . $e->getMessage();
        }    
    }
?>