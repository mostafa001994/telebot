const content = document.getElementById("content");

/* Allowed Pages */

const allowedPages = [
    "dashboard",
    "users",
    "subscription",
    "plans",
    "payments",
    "user_view"
];

/* Get Current Page */

function getCurrentPage() {

    const hash = window.location.hash;

    if (!hash) {
        return "dashboard";
    }

    const page = hash
        .replace(/^#/, "")
        .split("?")[0]
        .trim();

    if (!allowedPages.includes(page)) {
        return "dashboard";
    }

    return page;

}

/* Load Page */

async function loadPage(page) {

    try {

        if (!allowedPages.includes(page)) {
            page = "dashboard";
        }

        const response = await fetch(
            `pages/${encodeURIComponent(page)}.php`,
            {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                cache: "no-cache"
            }
        );

        if (!response.ok) {
            throw new Error(
                `Page not found: ${page}`
            );
        }

        const html = await response.text();

        content.innerHTML = html;

    } catch (error) {

        console.error("Load page error:", error);

        content.innerHTML = `
        <div class="table-box">
            <h2>خطا</h2>
            <p>
                صفحه مورد نظر پیدا نشد.
            </p>
        </div>
    `;
    }

}

/* Menu */

document.querySelectorAll("[data-page]").forEach(link => {

    link.addEventListener("click", event => {

        event.preventDefault();

        const page = link.dataset.page;

        if (!allowedPages.includes(page)) {
            return;
        }

        window.location.hash = page;

        loadPage(page);

        // Close mobile sidebar
        if (sidebar) {
            sidebar.classList.remove("active");
        }

    });

});

/* Initial Load */

window.addEventListener("DOMContentLoaded", () => {

    const page = getCurrentPage();

    loadPage(page);

});

/* Hash Navigation */

window.addEventListener("hashchange", () => {

    const page = getCurrentPage();

    loadPage(page);

});

/* Mobile Sidebar */

const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");
const main = document.querySelector(".content");

if (menuBtn && sidebar) {

    menuBtn.addEventListener("click", () => {

        sidebar.classList.toggle("active");

    });

}

if (main && sidebar) {

    main.addEventListener("click", () => {

        sidebar.classList.remove("active");

    });

}

/* Modal System */

window.openModal = function (id) {

    const modal = document.getElementById(id);

    if (!modal) {

        console.error(
            "Modal not found:",
            id
        );

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

/* Reset Category Form */

window.resetCategoryForm = function () {

    const form =
        document.getElementById("categoryForm");

    if (!form) {
        return;
    }

    form.reset();

    const action =
        document.getElementById("categoryAction");

    const id =
        document.getElementById("categoryId");

    const title =
        document.getElementById("categoryModalTitle");

    if (action) {
        action.value = "create_category";
    }

    if (id) {
        id.value = "";
    }

    if (title) {
        title.textContent = "افزودن دسته‌بندی";
    }

};

/* Edit Category */

window.editCategory = function (category) {

    const action =
        document.getElementById("categoryAction");

    const id =
        document.getElementById("categoryId");

    const name =
        document.getElementById("categoryName");

    const slug =
        document.getElementById("categorySlug");

    const description =
        document.getElementById("categoryDescription");

    const status =
        document.getElementById("categoryStatus");

    const sortOrder =
        document.getElementById("categorySortOrder");

    const title =
        document.getElementById("categoryModalTitle");


    if (action) {
        action.value = "update_category";
    }

    if (id) {
        id.value = category.id;
    }

    if (name) {
        name.value = category.name || "";
    }

    if (slug) {
        slug.value = category.slug || "";
    }

    if (description) {
        description.value =
            category.description || "";
    }

    if (status) {
        status.value =
            category.status || "active";
    }

    if (sortOrder) {
        sortOrder.value =
            category.sort_order || 0;
    }

    if (title) {
        title.textContent =
            "ویرایش دسته‌بندی";
    }

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

    const action =
        document.getElementById("planAction");

    const id =
        document.getElementById("planId");

    const title =
        document.getElementById("planModalTitle");


    if (action) {
        action.value = "create_plan";
    }

    if (id) {
        id.value = "";
    }

    if (title) {
        title.textContent =
            "افزودن پلن";
    }

};

/* Edit Plan */

window.editPlan = function (plan) {

    const action =
        document.getElementById("planAction");

    const id =
        document.getElementById("planId");

    const categoryId =
        document.getElementById("planCategoryId");

    const name =
        document.getElementById("planName");

    const durationDays =
        document.getElementById("planDurationDays");

    const price =
        document.getElementById("planPrice");

    const discountPrice =
        document.getElementById("planDiscountPrice");

    const status =
        document.getElementById("planStatus");

    const sortOrder =
        document.getElementById("planSortOrder");

    const description =
        document.getElementById("planDescription");

    const title =
        document.getElementById("planModalTitle");


    if (action) {
        action.value = "update_plan";
    }

    if (id) {
        id.value = plan.id;
    }

    if (categoryId) {
        categoryId.value =
            plan.category_id || "";
    }

    if (name) {
        name.value =
            plan.name || "";
    }

    if (durationDays) {
        durationDays.value =
            plan.duration_days || "";
    }

    if (price) {
        price.value =
            plan.price || "";
    }

    if (discountPrice) {
        discountPrice.value =
            plan.discount_price !== null &&
                plan.discount_price !== undefined
                ? plan.discount_price
                : "";
    }

    if (status) {
        status.value =
            plan.status || "active";
    }

    if (sortOrder) {
        sortOrder.value =
            plan.sort_order || 0;
    }

    if (description) {
        description.value =
            plan.description || "";
    }

    if (title) {
        title.textContent =
            "ویرایش پلن";
    }

    openModal("planModal");

};

/* Delete Item */

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


    const formData =
        new FormData();

    formData.append(
        "action",
        action
    );

    formData.append(
        "id",
        id
    );


    try {

        const response =
            await fetch(
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

        console.error(
            "Delete error:",
            error
        );

        alert(
            "خطایی در ارتباط با سرور رخ داد."
        );

    }

};

/* Escape Key */

document.addEventListener(
    "keydown",
    event => {

        if (event.key !== "Escape") {
            return;
        }

        closeModal("categoryModal");

        closeModal("planModal");

    }

);