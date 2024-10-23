document.addEventListener('DOMContentLoaded', async function () {
    let dataTarefaConcluida = [];
    let horasTralhadas = [];
    let tarefasConcluidas = [];
    let concluidas = 0;
    let atrasadas = 0;

    async function getData(tarefasId) {
        if (!tarefasId) return;
        const api = encodeURIComponent(`http://fabtec.ifc-riodosul.edu.br/issues.json?issue_id=${tarefasId}&key=b7c238adc2c0af943c1f0fa9de6489ce190bd6d5&status_id=*`);
        const url = "https://api.allorigins.win/get?url=" + api;
        const url1 = "https://cors-anywhere.herokuapp.com/" + api;
        const url2 = "https://corsproxy.io/?" + api;
    
        try {
            const response = await fetch(url2);
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }
    
            const json = await response.json();
            const jsonFormatted = json.contents ? JSON.parse(json.contents) : json;  
    
            let assigned = [];
            let rows = '';
            jsonFormatted.issues.forEach((issue, index) => {
                let d = new Date(issue.closed_on);
                assigned.push([issue.assigned_to.name, issue.total_estimated_hours || 0, d]);
                dataTarefaConcluida.push(d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2));
                let row = `<td>${++index}</td><td>${issue.id}</td><td>${issue.subject}</td><td>${issue.author.name}</td><td>${issue.assigned_to.name}</td>`;
                rows += `<tr>${row}</tr>`;
            });
    
            let devs = [...new Set(assigned.map(item => item[0]))];
            document.getElementById('participantes').innerHTML = devs.join(', ');
    
            let horasTotaisTrabalhadas = 0;
            horasTralhadas = assigned.reduce((acc, curr) => {
                let found = acc.find(item => item[0] == curr[0]);
                if (found) {
                    found[1] += curr[1];
                } else {
                    acc.push([curr[0], curr[1]]);
                }
                horasTotaisTrabalhadas += curr[1];
                return acc;
            }, []);
    
            tarefasConcluidas = devs.map(dev => {
                return { name: dev, concluídas: 0, atrasadas: 0 };
            });
    
            assigned.forEach(curr => {
                let devIndex = tarefasConcluidas.findIndex(dev => dev.name === curr[0]);
                if (curr[2] < new Date(ultimoDia)) {
                    tarefasConcluidas[devIndex].concluídas++;
                    concluidas++;
                } else {
                    tarefasConcluidas[devIndex].atrasadas++;
                    atrasadas++;
                }
            });
    
            document.getElementById('horas_totais_trabalhadas').innerHTML = `Horas Totais Trabalhadas: ${horasTotaisTrabalhadas}h`;
            document.getElementById('tarefas_concluidas').innerHTML = `Tarefas Concluídas: ${concluidas}`;
            document.getElementById('tarefas_atrasadas').innerHTML = `Tarefas Atrasadas: ${atrasadas}`;
            document.getElementById('table_tasks_body').innerHTML += rows;            
        } catch (error) {
            console.error(error.message);
        }
    }
    

    async function drawBurndownChart() {
        let totalTarefas = tasks.split(',').length;
        let arrayChart = [['Dia', 'Planejado', 'Realizado']];
        let ideal = totalTarefas / diasUteis.length;
        let newIdeal = totalTarefas;
        let tarefasRestantes = totalTarefas;

        await diasUteis.forEach((el, index) => {
            newIdeal -= ideal;
            let tarefasConcluidasDia = dataTarefaConcluida.filter((v) => v == el).length;
            if (index === 0) {
                tarefasConcluidasDia += dataTarefaConcluida.filter((v) => new Date(v) < new Date(el)).length;
            }
            tarefasRestantes -= tarefasConcluidasDia;
            arrayChart.push([index + 1, newIdeal, tarefasRestantes]);
        });

        var data = google.visualization.arrayToDataTable(arrayChart);
        var options = {
            hAxis: {title: 'Dias', titleTextStyle: {color: '#333'}},
            vAxis: {minValue: 0, title: 'Tarefas Restante'},
            legend: {position: 'bottom'},
            title: 'Gráfico de Burndown'
        };

        var chart = new google.visualization.LineChart(document.getElementById('burndown_chart'));
        chart.draw(data, options);
    }

    async function drawPieChart() {
        var data = google.visualization.arrayToDataTable([
            ['Desenvolvedor', 'Horas trabalhadas'],
            ...horasTralhadas
        ]
        );

        var options = {
          title: 'Horas trabalhadas'
        };

        var chart = new google.visualization.PieChart(document.getElementById('pie_chart'));

        chart.draw(data, options);
    }

      async function drawColumnChart() {
        const chartData = tarefasConcluidas.map(dev => {
            return [dev.name, dev.concluídas, dev.atrasadas];
        });

        var data = google.visualization.arrayToDataTable([
            ['Desenvolvedor', 'Tarefas Entregues', 'Tarefas Atrasadas'],
            ...chartData
        ]
        );

        var options = {
          title: 'Tarefas dentro do prazo'
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('column_chart'));

        chart.draw(data, options);
    }


    async function loadDataAndChart() {
        await getData(tasks);
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawBurndownChart);
        google.charts.setOnLoadCallback(drawPieChart);
        google.charts.setOnLoadCallback(drawColumnChart);
    }

    loadDataAndChart();
});

function voltar() {
    return window.location.replace('http://127.0.0.1/index.php')
}
