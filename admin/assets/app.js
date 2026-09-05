const content = document.getElementById("content");

async function loadPage(page) {

    try {

        const response = await fetch(`pages/${page}.php`);

        if(!response.ok){
            throw new Error("Page not found");
        }

        const html = await response.text();

        content.innerHTML = html;

    } catch(err) {

        content.innerHTML = `
            <h2>خطا</h2>
            <p>صفحه مورد نظر پیدا نشد.</p>
        `;
    }
}

document.querySelectorAll("[data-page]").forEach(link => {

    link.addEventListener("click", e => {

        e.preventDefault();

        const page = link.dataset.page;

        history.pushState({}, "", `#${page}`);

        loadPage(page);

    });

});

window.addEventListener("load", () => {

    const page = location.hash.replace("#", "") || "dashboard";

    loadPage(page);

});

window.addEventListener("popstate", () => {

    const page = location.hash.replace("#", "") || "dashboard";

    loadPage(page);

});




// 



const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");
const main = document.querySelector('.content');
menuBtn.addEventListener("click", () => {
    sidebar.classList.toggle("active");
});


main.addEventListener("click", () => {
    sidebar.classList.remove("active");
});