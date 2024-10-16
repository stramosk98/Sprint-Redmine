document.addEventListener('DOMContentLoaded', async function () {
    let dataTarefaConcluida = [];

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
    
            let rows = '';
            jsonFormatted.issues.forEach((issue, index) => {
                let d = new Date(issue.closed_on);
                dataTarefaConcluida.push(d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2));
                let row = `<td>${++index}</td><td>${issue.id}</td><td>${issue.subject}</td><td>${issue.author.name}</td><td>${issue.assigned_to.name}</td>`;
                rows += `<tr>${row}</tr>`;
            });
            document.getElementById('table_tasks_body').innerHTML += rows;            
        } catch (error) {
            console.error(error.message);
        }
    }

    async function drawChart() {
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
            legend: {position: 'bottom'}
        };

        var chart = new google.visualization.LineChart(document.getElementById('burndown_chart'));
        chart.draw(data, options);
    }

    async function loadDataAndChart() {
        await getData(tasks);
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
    }

    loadDataAndChart();
});

function voltar() {
    return window.location.replace('http://127.0.0.1/index.php')
}
