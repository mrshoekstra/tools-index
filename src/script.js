const evtSource = new EventSource('data.php');
evtSource.addEventListener('ping', (event) => console.info(JSON.parse(event.data)));
evtSource.addEventListener('done', () => evtSource.close());
evtSource.addEventListener('list', (event) => {
	const item = document.createElement('li');
	const list = document.querySelector('nav ul');
	const data = JSON.parse(event.data);
	const anchor = document.createElement('a');
	anchor.href = data.path;
	anchor.textContent = data.name;
	item.appendChild(anchor);
	list.appendChild(item);
});
