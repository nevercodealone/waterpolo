
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
    }
    element.classList.remove("text-gray-600");
    element.classList.add("bg-indigo-700");
    element.classList.add("text-white");

    let dataDomain = (element.getAttribute('data-domain'));
    let allItems = document.querySelectorAll('.group');
    if (dataDomain === 'all') {
        for(let item of allItems) {
            item.hidden = false;
        }
    }else{
        for(let item of allItems) {
            let dataDomainItem = item.getAttribute('data-domain');
            item.hidden = dataDomainItem !== dataDomain;
        }
    }
}
window.activeTab = activeTab;
