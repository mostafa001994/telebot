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









/* Modal System */

window.openModal = function (id) {

const modal = document.getElementById(id);

if (!modal) {
    console.error("Modal not found:", id);
    return;
}

modal.classList.add("show");

document.body.classList.add("modal-open");

};

window.closeModal = function (id) {

const modal = document.getElementById(id);

if (!modal) {
    return;
}

modal.classList.remove("show");

document.body.classList.remove("modal-open");

};

/*Reset Category Form*/

window.resetCategoryForm = function () {

const form =
    document.getElementById("categoryForm");

if (!form) {
    return;
}

form.reset();

document.getElementById(
    "categoryAction"
).value = "create_category";

document.getElementById(
    "categoryId"
).value = "";

document.getElementById(
    "categoryModalTitle"
).textContent = "افزودن دسته‌بندی";

};

/* Edit Category */

window.editCategory = function (category) {

document.getElementById(
    "categoryAction"
).value = "update_category";

document.getElementById(
    "categoryId"
).value = category.id;

document.getElementById(
    "categoryName"
).value = category.name || "";

document.getElementById(
    "categorySlug"
).value = category.slug || "";

document.getElementById(
    "categoryDescription"
).value =
    category.description || "";

document.getElementById(
    "categoryStatus"
).value =
    category.status || "active";

document.getElementById(
    "categorySortOrder"
).value =
    category.sort_order || 0;

document.getElementById(
    "categoryModalTitle"
).textContent =
    "ویرایش دسته‌بندی";

openModal("categoryModal");

};

/* Reset Plan Form */

window.resetPlanForm = function () {

const form =
    document.getElementById("planForm");

if (!form) {
    return;
}

form.reset();

document.getElementById(
    "planAction"
).value = "create_plan";

document.getElementById(
    "planId"
).value = "";

document.getElementById(
    "planModalTitle"
).textContent = "افزودن پلن";

};

/* Edit Plan */

window.editPlan = function (plan) {

document.getElementById(
    "planAction"
).value = "update_plan";

document.getElementById(
    "planId"
).value = plan.id;

document.getElementById(
    "planCategoryId"
).value = plan.category_id;

document.getElementById(
    "planName"
).value =
    plan.name || "";

document.getElementById(
    "planDurationDays"
).value =
    plan.duration_days || "";

document.getElementById(
    "planPrice"
).value =
    plan.price || "";

document.getElementById(
    "planDiscountPrice"
).value =
    plan.discount_price !== null
        ? plan.discount_price
        : "";

document.getElementById(
    "planStatus"
).value =
    plan.status || "active";

document.getElementById(
    "planSortOrder"
).value =
    plan.sort_order || 0;

document.getElementById(
    "planDescription"
).value =
    plan.description || "";

document.getElementById(
    "planModalTitle"
).textContent =
    "ویرایش پلن";

openModal("planModal");

};

/* Delete */

window.deleteItem = async function (
action,
id,
title
) {

if (!confirm(
    `آیا از حذف ${title} مطمئن هستید؟`
)) {
    return;
}


const formData = new FormData();

formData.append(
    "action",
    action
);

formData.append(
    "id",
    id
);


try {

    const response = await fetch(
        "pages/plans.php",
        {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With":
                    "XMLHttpRequest"
            }
        }
    );


    const result =
        await response.json();


    if (!result.success) {

        alert(
            result.message ||
            "عملیات انجام نشد."
        );

        return;
    }


    alert(
        result.message ||
        "عملیات با موفقیت انجام شد."
    );


    await loadPage("plans");

} catch (error) {

    console.error(error);

    alert(
        "خطایی در ارتباط با سرور رخ داد."
    );
}

};

/* Escape Key */

document.addEventListener(
"keydown",
function (event) {

    if (event.key !== "Escape") {
        return;
    }

    closeModal("categoryModal");
    closeModal("planModal");

}

);