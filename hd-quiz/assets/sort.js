export const sort = {
	init: function () {
		const el = document.getElementById("hdq_questions_list").getElementsByClassName("hdq_quiz_question");
		for (let i = 0; i < el.length; i++) {
			sort.enableDragItem(el[i]);
		}
	},
	enableDragItem: function (item) {
		item.setAttribute("draggable", true);
		item.ondrag = sort.handleDrag;
		item.ondragend = sort.handleDrop;
	},
	handleDrag: function (item) {
		const selectedItem = item.target,
			x = item.clientX,
			y = item.clientY;

		let parents = [document.getElementById("hdq_questions_list")];

		selectedItem.classList.add("drag-sort-active");
		let swapItem = document.elementFromPoint(x, y) === null ? selectedItem : document.elementFromPoint(x, y);
		if (swapItem === null) {
			return;
		}
		if (swapItem.getAttribute("draggable") != "true") {
			return;
		}
		for (let i = 0; i < parents.length; i++) {
			if (swapItem === null) {
				continue;
			}
			if (parents[i] === swapItem.parentNode) {
				swapItem = swapItem !== selectedItem.nextSibling ? swapItem : swapItem.nextSibling;
				parents[i].insertBefore(selectedItem, swapItem);
			}
		}
	},
	handleDrop: function (item) {
		item.target.classList.remove("drag-sort-active");
		document.getElementById("hdq_questions_list").classList.add("hderp"); // alow question_orders to save.
	},
};
