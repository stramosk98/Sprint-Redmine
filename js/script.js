function addTask(event) {
    event.preventDefault();
    let container = document.getElementById('tasks-container');
    let newTask = document.getElementById('tasks').cloneNode();
    newTask.value = null;
    
    container.appendChild(newTask);
}

function removeTask(event) {
    event.preventDefault();
    let container = document.getElementById('tasks-container');
    let inputs = container.getElementsByTagName('input');
    
    if (inputs.length > 1) {
        container.removeChild(inputs[inputs.length - 1]);
    }
}