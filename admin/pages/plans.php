<?php require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    exit('Unauthorized');
} /* AJAX */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';
    try { /* | Create Category */
        if ($action === 'create_category') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            if ($name === '') {
                throw new Exception('نام دسته‌بندی الزامی است.');
            }
            if ($slug === '') {
                throw new Exception('Slug الزامی است.');
            }
            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new Exception('وضعیت نامعتبر است.');
            }
            $check = $pdo->prepare(" SELECT id FROM subscription_categories WHERE slug = ? LIMIT 1 ");
            $check->execute([$slug]);
            if ($check->fetch()) {
                throw new Exception('این Slug قبلاً استفاده شده است.');
            }
            $stmt = $pdo->prepare(" INSERT INTO subscription_categories ( name, slug, description, status, sort_order ) VALUES (?, ?, ?, ?, ?) ");
            $stmt->execute([$name, $slug, $description !== '' ? $description : null, $status, $sortOrder]);
            echo json_encode(['success' => true, 'message' => 'دسته‌بندی با موفقیت ایجاد شد.']);
            exit;
        } /* Update Category */
        if ($action === 'update_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            if ($id <= 0) {
                throw new Exception('دسته‌بندی نامعتبر است.');
            }
            if ($name === '') {
                throw new Exception('نام دسته‌بندی الزامی است.');
            }
            if ($slug === '') {
                throw new Exception('Slug الزامی است.');
            }
            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new Exception('وضعیت نامعتبر است.');
            }
            $check = $pdo->prepare(" SELECT id FROM subscription_categories WHERE slug = ? AND id != ? LIMIT 1 ");
            $check->execute([$slug, $id]);
            if ($check->fetch()) {
                throw new Exception('این Slug قبلاً استفاده شده است.');
            }
            $stmt = $pdo->prepare(" UPDATE subscription_categories SET name = ?, slug = ?, description = ?, status = ?, sort_order = ? WHERE id = ? ");
            $stmt->execute([$name, $slug, $description !== '' ? $description : null, $status, $sortOrder, $id]);
            echo json_encode(['success' => true, 'message' => 'دسته‌بندی با موفقیت ویرایش شد.']);
            exit;
        } /* | Delete Category */
        if ($action === 'delete_category') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('دسته‌بندی نامعتبر است.');
            } /* * به خاطر ON DELETE CASCADE * پلن‌های این دسته هم حذف می‌شوند. */
            $stmt = $pdo->prepare(" DELETE FROM subscription_categories WHERE id = ? ");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'دسته‌بندی و پلن‌های مربوط به آن حذف شدند.']);
            exit;
        } /* | Create Plan */
        if ($action === 'create_plan') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $durationDays = (int) ($_POST['duration_days'] ?? 0);
            $price = (int) ($_POST['price'] ?? 0);
            $discountPriceRaw = trim($_POST['discount_price'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            if ($categoryId <= 0) {
                throw new Exception('دسته‌بندی را انتخاب کنید.');
            } /* * Check category */
            $categoryCheck = $pdo->prepare(" SELECT id FROM subscription_categories WHERE id = ? LIMIT 1 ");
            $categoryCheck->execute([$categoryId]);
            if (!$categoryCheck->fetch()) {
                throw new Exception('دسته‌بندی انتخاب‌شده وجود ندارد.');
            }
            if ($name === '') {
                throw new Exception('نام پلن الزامی است.');
            }
            if ($durationDays <= 0) {
                throw new Exception('مدت اشتراک باید بیشتر از صفر باشد.');
            }
            if ($price < 0) {
                throw new Exception('قیمت نامعتبر است.');
            }
            if ($discountPriceRaw === '') {
                $discountPrice = null;
            } else {
                $discountPrice = (int) $discountPriceRaw;
                if ($discountPrice < 0) {
                    throw new Exception('قیمت تخفیف نامعتبر است.');
                }
                if ($discountPrice >= $price && $price > 0) {
                    throw new Exception('قیمت تخفیف باید کمتر از قیمت اصلی باشد.');
                }
            }
            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new Exception('وضعیت نامعتبر است.');
            }
            $stmt = $pdo->prepare(" INSERT INTO subscription_plans ( category_id, name, description, duration_days, price, discount_price, status, sort_order ) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ");
            $stmt->execute([$categoryId, $name, $description !== '' ? $description : null, $durationDays, $price, $discountPrice, $status, $sortOrder]);
            echo json_encode(['success' => true, 'message' => 'پلن با موفقیت ایجاد شد.']);
            exit;
        } /*  Update Plan  */
        if ($action === 'update_plan') {
            $id = (int) ($_POST['id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $durationDays = (int) ($_POST['duration_days'] ?? 0);
            $price = (int) ($_POST['price'] ?? 0);
            $discountPriceRaw = trim($_POST['discount_price'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            if ($id <= 0) {
                throw new Exception('پلن نامعتبر است.');
            }
            if ($categoryId <= 0) {
                throw new Exception('دسته‌بندی را انتخاب کنید.');
            }
            $categoryCheck = $pdo->prepare(" SELECT id FROM subscription_categories WHERE id = ? LIMIT 1 ");
            $categoryCheck->execute([$categoryId]);
            if (!$categoryCheck->fetch()) {
                throw new Exception('دسته‌بندی انتخاب‌شده وجود ندارد.');
            }
            if ($name === '') {
                throw new Exception('نام پلن الزامی است.');
            }
            if ($durationDays <= 0) {
                throw new Exception('مدت اشتراک باید بیشتر از صفر باشد.');
            }
            if ($price < 0) {
                throw new Exception('قیمت نامعتبر است.');
            }
            if ($discountPriceRaw === '') {
                $discountPrice = null;
            } else {
                $discountPrice = (int) $discountPriceRaw;
                if ($discountPrice < 0) {
                    throw new Exception('قیمت تخفیف نامعتبر است.');
                }
                if ($discountPrice >= $price && $price > 0) {
                    throw new Exception('قیمت تخفیف باید کمتر از قیمت اصلی باشد.');
                }
            }
            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new Exception('وضعیت نامعتبر است.');
            }
            $stmt = $pdo->prepare(" UPDATE subscription_plans SET category_id = ?, name = ?, description = ?, duration_days = ?, price = ?, discount_price = ?, status = ?, sort_order = ? WHERE id = ? ");
            $stmt->execute([$categoryId, $name, $description !== '' ? $description : null, $durationDays, $price, $discountPrice, $status, $sortOrder, $id]);
            echo json_encode(['success' => true, 'message' => 'پلن با موفقیت ویرایش شد.']);
            exit;
        } /*  Delete Plan */
        if ($action === 'delete_plan') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('پلن نامعتبر است.');
            }
            $stmt = $pdo->prepare(" DELETE FROM subscription_plans WHERE id = ? ");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'پلن با موفقیت حذف شد.']);
            exit;
        }
        throw new Exception('عملیات نامعتبر است.');
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
} /* Load Categories */
$categories = $pdo->query(" SELECT * FROM subscription_categories ORDER BY sort_order ASC, id DESC ")->fetchAll(PDO::FETCH_ASSOC); /* |-------------------------------------------------------------------------- | Load Plans |-------------------------------------------------------------------------- */
$plans = $pdo->query(" SELECT p.*, c.name AS category_name FROM subscription_plans p INNER JOIN subscription_categories c ON c.id = p.category_id ORDER BY p.sort_order ASC, p.id DESC ")->fetchAll(PDO::FETCH_ASSOC); ?>

<div class="main">

    <header class="page-header">

        <div>
            <h1>پلن‌ها</h1>

            <p class="page-description">
                مدیریت دسته‌بندی‌ها و پلن‌های قابل فروش
            </p>
        </div>

        <div class="page-actions">

            <button type="button" class="btn btn-secondary" onclick="openModal('categoryModal')">
                + دسته‌بندی جدید
            </button>

            <button type="button" class="btn btn-primary" onclick="openModal('planModal')">
                + پلن جدید
            </button>

        </div>

    </header>


    <!-- Categories -->

    <div class="table-box">

        <div class="table-title">

            <div>
                <h2>دسته‌بندی‌ها</h2>

                <span>
                    <?= count($categories) ?>
                    دسته‌بندی
                </span>
            </div>

        </div>


        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>#</th>
                        <th>نام</th>
                        <th>Slug</th>
                        <th>وضعیت</th>
                        <th>ترتیب</th>
                        <th>توضیحات</th>
                        <th>عملیات</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (!$categories): ?>

                        <tr>

                            <td colspan="7" class="empty-cell">

                                هنوز دسته‌بندی‌ای ثبت نشده است.

                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($categories as $category): ?>

                            <tr>

                                <td>
                                    <?= (int) $category['id'] ?>
                                </td>


                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $category['name']
                                        ) ?>
                                    </strong>
                                </td>


                                <td>
                                    <code>
                                        <?= htmlspecialchars(
                                            $category['slug']
                                        ) ?>
                                    </code>
                                </td>


                                <td>

                                    <?php if (
                                        $category['status']
                                        === 'active'
                                    ): ?>

                                        <span class="status active">
                                            فعال
                                        </span>

                                    <?php else: ?>

                                        <span class="status inactive">
                                            غیرفعال
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <?= (int) $category['sort_order'] ?>
                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $category['description']
                                        ?: '-'
                                    ) ?>

                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <button type="button" class="btn btn-edit" onclick='editCategory(
                                        <?= json_encode(
                                            $category,
                                            JSON_UNESCAPED_UNICODE |
                                            JSON_HEX_TAG |
                                            JSON_HEX_APOS |
                                            JSON_HEX_QUOT |
                                            JSON_HEX_AMP
                                        ) ?>
                                    )'>
                                            ویرایش
                                        </button>


                                        <button type="button" class="btn btn-danger" onclick="deleteItem(
                                        'delete_category',
                                        <?= (int) $category['id'] ?>,
                                        'دسته‌بندی'
                                    )">
                                            حذف
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- Plans -->
    <!-- ========================================================= -->

    <div class="table-box plans-box">

        <div class="table-title">

            <div>
                <h2>پلن‌های اشتراک</h2>

                <span>
                    <?= count($plans) ?>
                    پلن
                </span>
            </div>

        </div>


        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>#</th>
                        <th>نام پلن</th>
                        <th>دسته‌بندی</th>
                        <th>مدت</th>
                        <th>قیمت</th>
                        <th>تخفیف</th>
                        <th>وضعیت</th>
                        <th>ترتیب</th>
                        <th>عملیات</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (!$plans): ?>

                        <tr>

                            <td colspan="9" class="empty-cell">
                                هنوز پلنی ثبت نشده است.
                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($plans as $plan): ?>

                            <tr>

                                <td>
                                    <?= (int) $plan['id'] ?>
                                </td>


                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $plan['name']
                                        ) ?>
                                    </strong>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $plan['category_name']
                                    ) ?>
                                </td>


                                <td>
                                    <?= (int) $plan['duration_days'] ?>
                                    روز
                                </td>


                                <td>
                                    <?= number_format(
                                        (int) $plan['price']
                                    ) ?>
                                    تومان
                                </td>


                                <td>

                                    <?php if (
                                        $plan['discount_price']
                                        !== null
                                    ): ?>

                                        <?= number_format(
                                            (int) $plan['discount_price']
                                        ) ?>
                                        تومان

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if (
                                        $plan['status']
                                        === 'active'
                                    ): ?>

                                        <span class="status active">
                                            فعال
                                        </span>

                                    <?php else: ?>

                                        <span class="status inactive">
                                            غیرفعال
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <?= (int) $plan['sort_order'] ?>
                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <button type="button" class="btn btn-edit" onclick='editPlan(
                                        <?= json_encode(
                                            $plan,
                                            JSON_UNESCAPED_UNICODE |
                                            JSON_HEX_TAG |
                                            JSON_HEX_APOS |
                                            JSON_HEX_QUOT |
                                            JSON_HEX_AMP
                                        ) ?>
                                    )'>
                                            ویرایش
                                        </button>


                                        <button type="button" class="btn btn-danger" onclick="deleteItem(
                                        'delete_plan',
                                        <?= (int) $plan['id'] ?>,
                                        'پلن'
                                    )">
                                            حذف
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ============================================================= -->

<!-- Category Modal -->

<!-- ============================================================= -->

<div id="categoryModal" class="modal">

    <div class="modal-overlay" onclick="closeModal('categoryModal')">
    </div>


    <div class="modal-content">

        <div class="modal-header">

            <h2 id="categoryModalTitle">
                افزودن دسته‌بندی
            </h2>

            <button type="button" class="modal-close" onclick="closeModal('categoryModal')">
                ×
            </button>

        </div>


        <form id="categoryForm" method="POST" action="pages/plans.php" data-ajax-form>

            <input type="hidden" name="action" id="categoryAction" value="create_category">

            <input type="hidden" name="id" id="categoryId" value="">


            <div class="form-group">

                <label>
                    نام دسته‌بندی
                </label>

                <input type="text" name="name" id="categoryName" placeholder="مثلاً اشتراک عادی" required>

            </div>


            <div class="form-group">

                <label>
                    Slug
                </label>

                <input type="text" name="slug" id="categorySlug" placeholder="normal" required dir="ltr">

            </div>


            <div class="form-grid">

                <div class="form-group">

                    <label>
                        وضعیت
                    </label>

                    <select name="status" id="categoryStatus">

                        <option value="active">
                            فعال
                        </option>

                        <option value="inactive">
                            غیرفعال
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        ترتیب نمایش
                    </label>

                    <input type="number" name="sort_order" id="categorySortOrder" value="0">

                </div>

            </div>


            <div class="form-group">

                <label>
                    توضیحات
                </label>

                <textarea name="description" id="categoryDescription" rows="4"
                    placeholder="توضیحات دسته‌بندی..."></textarea>

            </div>


            <div class="modal-footer">

                <button type="button" class="btn btn-light" onclick="closeModal('categoryModal')">
                    انصراف
                </button>


                <button type="submit" class="btn btn-primary">
                    ذخیره
                </button>

            </div>

        </form>

    </div>

</div>

<!-- ============================================================= -->

<!-- Plan Modal -->

<!-- ============================================================= -->

<div id="planModal" class="modal">

    <div class="modal-overlay" onclick="closeModal('planModal')">
    </div>


    <div class="modal-content modal-large">

        <div class="modal-header">

            <h2 id="planModalTitle">
                افزودن پلن
            </h2>

            <button type="button" class="modal-close" onclick="closeModal('planModal')">
                ×
            </button>

        </div>


        <form id="planForm" method="POST" action="pages/plans.php" data-ajax-form>

            <input type="hidden" name="action" id="planAction" value="create_plan">

            <input type="hidden" name="id" id="planId" value="">


            <div class="form-grid">

                <div class="form-group">

                    <label>
                        دسته‌بندی
                    </label>

                    <select name="category_id" id="planCategoryId" required>

                        <option value="">
                            انتخاب دسته‌بندی
                        </option>

                        <?php foreach ($categories as $category): ?>

                            <option value="<?= (int) $category['id'] ?>">
                                <?= htmlspecialchars(
                                    $category['name']
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        نام پلن
                    </label>

                    <input type="text" name="name" id="planName" placeholder="مثلاً اشتراک یک ماهه" required>

                </div>


                <div class="form-group">

                    <label>
                        مدت اشتراک
                    </label>

                    <div class="input-with-label">

                        <input type="number" name="duration_days" id="planDurationDays" min="1" placeholder="30"
                            required>

                        <span>
                            روز
                        </span>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        قیمت اصلی
                    </label>

                    <div class="input-with-label">

                        <input type="number" name="price" id="planPrice" min="0" placeholder="100000" required>

                        <span>
                            تومان
                        </span>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        قیمت با تخفیف
                    </label>

                    <div class="input-with-label">

                        <input type="number" name="discount_price" id="planDiscountPrice" min="0" placeholder="اختیاری">

                        <span>
                            تومان
                        </span>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        وضعیت
                    </label>

                    <select name="status" id="planStatus">

                        <option value="active">
                            فعال
                        </option>

                        <option value="inactive">
                            غیرفعال
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        ترتیب نمایش
                    </label>

                    <input type="number" name="sort_order" id="planSortOrder" value="0">

                </div>

            </div>


            <div class="form-group">

                <label>
                    توضیحات
                </label>

                <textarea name="description" id="planDescription" rows="4" placeholder="توضیحات پلن..."></textarea>

            </div>


            <div class="modal-footer">

                <button type="button" class="btn btn-light" onclick="closeModal('planModal')">
                    انصراف
                </button>


                <button type="submit" class="btn btn-primary">
                    ذخیره پلن
                </button>

            </div>

        </form>

    </div>

</div>