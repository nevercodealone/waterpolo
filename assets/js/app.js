
// grab everything we need
const btn = document.querySelector('button.mobile-menu-button');
const menu = document.querySelector('.mobile-menu');

// add event listeners
btn.addEventListener('click', () => {
    menu.classList.toggle('hidden');
})

function activeTab(element) {
    let siblings = element.parentNode.querySelectorAll("li");
    for (let item of siblings) {
        item.classList.add("text-gray-600");
        item.classList.remove("text-white");
        item.classList.remove("bg-indigo-700");
        item.innerHTML = "Inactive";
    }
    element.classList.remove("text-gray-600");
    element.classList.add("bg-indigo-700");
    element.classList.add("text-white");
    element.innerHTML = "Active";
}
window.activeTab = activeTab;
