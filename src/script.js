const evtSource = new EventSource('sse.php');
const list = document.querySelector('nav ul');
list.dataset.status = 'init';
evtSource.addEventListener('done', (event) => {
	list.dataset.status = 'done';
	evtSource.close();
});
evtSource.addEventListener('list', (event) => {
	list.dataset.status = 'load';
	const anchor = document.createElement('a');
	try {
		const data = getData(event.data);
		anchor.href = data.path;
		anchor.textContent = data.name;
		const item = document.createElement('li');
		item.appendChild(anchor);
		list.appendChild(item);
	}
	catch(error) {
		console.error(error);
	};
});

function getData(data) {
	try {
		return JSON.parse(data);
	}
	catch(error) {
		throw error;
	}
}
