<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
hw_require_admin();

$notice = hw_flash('admin_notice') ?? '';
$tab = (string) ($_GET['tab'] ?? 'leads');
if ($tab === 'password') {
    $tab = 'settings';
}
if (!in_array($tab, ['leads', 'messages', 'settings'], true)) {
    $tab = 'leads';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if (!hw_verify_csrf($_POST['csrf'] ?? null) || !hw_same_origin()) {
        $notice = 'Please try again.';
        $tab = in_array($action, ['password', 'path'], true) ? 'settings' : $tab;
    } elseif ($action === 'password') {
        $tab = 'settings';
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');
        if (strlen($password) < HW_PASSWORD_MIN || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            $notice = 'Password must be at least 12 characters and include letters and numbers.';
        } elseif (!hash_equals($password, $confirm)) {
            $notice = 'Passwords do not match.';
        } elseif (hw_update_admin_password($password)) {
            hw_rotate_csrf();
            $notice = 'Password updated.';
        } else {
            $notice = 'Could not update the password.';
        }
    } elseif ($action === 'path') {
        $tab = 'settings';
        $slug = trim((string) ($_POST['path'] ?? ''));
        if (!hw_admin_slug_ok($slug)) {
            $notice = 'Use 3–40 letters, numbers, hyphens, or underscores. That path is reserved.';
        } elseif (hw_update_admin_path($slug)) {
            hw_rotate_csrf();
            hw_flash('admin_notice', 'Admin URL updated. Bookmark the new address.');
            header('Location: ' . hw_admin_url('dashboard') . '?tab=settings');
            exit;
        } else {
            $notice = 'Could not update the admin URL.';
        }
    } elseif ($action === 'delete') {
        $type = (string) ($_POST['type'] ?? '');
        $id = (string) ($_POST['id'] ?? '');
        $pageAfter = max(1, (int) ($_POST['page'] ?? 1));
        $file = $type === 'messages' ? HW_MESSAGES_FILE : ($type === 'leads' ? HW_LEADS_FILE : '');
        $tab = $type === 'messages' ? 'messages' : 'leads';
        if ($file !== '' && hw_delete_record($file, $id)) {
            hw_rotate_csrf();
            hw_flash('admin_notice', 'Entry deleted.');
        } else {
            hw_flash('admin_notice', 'Could not delete that entry.');
        }
        header('Location: ' . hw_admin_url('dashboard') . '?tab=' . rawurlencode($tab) . '&page=' . $pageAfter);
        exit;
    } elseif ($action === 'bulk_delete') {
        $type = (string) ($_POST['type'] ?? '');
        $ids = $_POST['ids'] ?? [];
        $pageAfter = max(1, (int) ($_POST['page'] ?? 1));
        $file = $type === 'messages' ? HW_MESSAGES_FILE : ($type === 'leads' ? HW_LEADS_FILE : '');
        $tab = $type === 'messages' ? 'messages' : 'leads';
        $ids = is_array($ids) ? $ids : [];
        $deleted = $file !== '' ? hw_delete_records($file, $ids) : 0;
        if ($deleted > 0) {
            hw_rotate_csrf();
            hw_flash('admin_notice', $deleted === 1 ? '1 entry deleted.' : $deleted . ' entries deleted.');
        } else {
            hw_flash('admin_notice', 'Select at least one entry to delete.');
        }
        header('Location: ' . hw_admin_url('dashboard') . '?tab=' . rawurlencode($tab) . '&page=' . $pageAfter);
        exit;
    }
}

$leads = array_reverse(hw_read_json(HW_LEADS_FILE));
$messages = array_reverse(hw_read_json(HW_MESSAGES_FILE));

if (isset($_GET['export']) && $_GET['export'] === 'csv' && ($tab === 'leads' || $tab === 'messages')) {
    if ($tab === 'leads') {
        $rows = [];
        foreach ($leads as $lead) {
            $rows[] = [
                hw_admin_date(isset($lead['created_at']) ? (string) $lead['created_at'] : null),
                (string) ($lead['name'] ?? ''),
                (string) ($lead['phone'] ?? ''),
                (string) ($lead['email'] ?? ''),
                (string) ($lead['location'] ?? ''),
                (string) ($lead['property_type'] ?? ''),
                (string) ($lead['expected_price'] ?? ''),
            ];
        }
        hw_export_csv(
            'property-requests-' . gmdate('Y-m-d') . '.csv',
            ['Date', 'Name', 'Phone', 'Email', 'Location', 'Type', 'Expected price'],
            $rows
        );
    }

    $rows = [];
    foreach ($messages as $message) {
        $rows[] = [
            hw_admin_date(isset($message['created_at']) ? (string) $message['created_at'] : null),
            (string) ($message['name'] ?? ''),
            (string) ($message['email'] ?? ''),
            (string) ($message['message'] ?? ''),
        ];
    }
    hw_export_csv(
        'contact-messages-' . gmdate('Y-m-d') . '.csv',
        ['Date', 'Name', 'Email', 'Message'],
        $rows
    );
}

$items = $tab === 'messages' ? $messages : $leads;
$total = count($items);
$pages = max(1, (int) ceil($total / HW_ADMIN_PER_PAGE));
$page = max(1, (int) ($_GET['page'] ?? 1));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * HW_ADMIN_PER_PAGE;
$pageItems = array_slice($items, $offset, HW_ADMIN_PER_PAGE);
$from = $total === 0 ? 0 : $offset + 1;
$to = $offset + count($pageItems);

$adminUser = (string) ($_SESSION['admin_user'] ?? 'Admin');
$titles = [
    'leads' => 'Property requests',
    'messages' => 'Contact messages',
    'settings' => 'Settings',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="googlebot" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#151D31">
    <title><?php echo hw_e($titles[$tab]); ?> – Home Worth Admin</title>
    <link rel="icon" href="<?php echo hw_e(hw_url('assets/images/favicon.png')); ?>" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo hw_e(hw_url('assets/css/style.css') . '?v=' . filemtime(HW_ROOT . '/assets/css/style.css')); ?>">
</head>
<body class="admin-app">
    <aside class="admin-side">
        <a class="admin-brand" href="<?php echo hw_e(hw_url()); ?>">
            <span class="brand-mark"><img src="<?php echo hw_e(hw_url('assets/images/logo.png')); ?>" alt=""></span>
            <span>Home Worth</span>
        </a>
        <nav class="admin-nav">
            <a class="<?php echo $tab === 'leads' ? 'is-active' : ''; ?>" href="?tab=leads">
                <span class="admin-nav-main"><?php echo hw_icon('house'); ?> Property requests</span>
                <b><?php echo count($leads); ?></b>
            </a>
            <a class="<?php echo $tab === 'messages' ? 'is-active' : ''; ?>" href="?tab=messages">
                <span class="admin-nav-main"><?php echo hw_icon('mail'); ?> Contact messages</span>
                <b><?php echo count($messages); ?></b>
            </a>
            <a class="<?php echo $tab === 'settings' ? 'is-active' : ''; ?>" href="?tab=settings">
                <span class="admin-nav-main"><?php echo hw_icon('settings'); ?> Settings</span>
            </a>
        </nav>
        <div class="admin-side-foot">
            <span><?php echo hw_e($adminUser); ?></span>
            <a href="<?php echo hw_e(hw_admin_url('logout')); ?>" id="signout-link"><?php echo hw_icon('logout'); ?> Sign out</a>
        </div>
    </aside>

    <div class="admin-body">
        <header class="admin-top">
            <div>
                <p class="admin-kicker">Admin</p>
                <h1>
                    <?php echo hw_e($titles[$tab]); ?>
                    <?php if ($tab === 'leads' || $tab === 'messages'): ?>
                        <span class="admin-count"><?php echo $total; ?></span>
                    <?php endif; ?>
                </h1>
                <?php if ($tab === 'leads' || $tab === 'messages'): ?>
                    <p class="admin-range"><?php echo $total === 0 ? 'No entries yet.' : 'Showing ' . $from . '–' . $to . ' of ' . $total; ?></p>
                <?php endif; ?>
            </div>
            <?php if ($tab === 'leads' || $tab === 'messages'): ?>
                <div class="admin-top-actions">
                    <button type="submit" class="btn admin-bulk-btn" id="bulk-delete-btn" form="bulk-delete" disabled>Delete selected</button>
                    <button type="button" class="btn btn-gold" id="export-btn" data-href="?tab=<?php echo hw_e($tab); ?>&amp;export=csv">Export</button>
                </div>
            <?php endif; ?>
        </header>

        <?php if ($notice !== ''): ?>
            <p class="admin-flash" role="status"><?php echo hw_e($notice); ?></p>
        <?php endif; ?>

        <?php if ($tab === 'settings'): ?>
            <section class="admin-card admin-password">
                <h2>Admin URL</h2>
                <p>This private path is how you open the admin panel. The old address stops working after you save.</p>
                <p class="admin-url-now"><?php echo hw_e(hw_admin_abs_url()); ?></p>
                <form method="post">
                    <?php echo hw_csrf_field(); ?>
                    <input type="hidden" name="action" value="path">
                    <label class="field">
                        <span>Admin path</span>
                        <input type="text" name="path" value="<?php echo hw_e(hw_admin_slug()); ?>" maxlength="40" required pattern="[A-Za-z0-9][A-Za-z0-9_-]{2,39}">
                    </label>
                    <button class="btn btn-gold" type="submit">Save URL</button>
                </form>
            </section>
            <section class="admin-card admin-password">
                <h2>Password</h2>
                <p>Password must be at least 12 characters and include letters and numbers.</p>
                <form method="post">
                    <?php echo hw_csrf_field(); ?>
                    <input type="hidden" name="action" value="password">
                    <label class="field">
                        <span>New password</span>
                        <input type="password" name="password" placeholder="New password" required>
                    </label>
                    <label class="field">
                        <span>Confirm new password</span>
                        <input type="password" name="confirm" placeholder="Confirm new password" required>
                    </label>
                    <button class="btn btn-gold" type="submit">Update password</button>
                </form>
            </section>
        <?php elseif ($total === 0): ?>
            <section class="admin-card">
                <p class="admin-empty"><?php echo $tab === 'leads' ? 'No property requests yet.' : 'No contact messages yet.'; ?></p>
            </section>
        <?php else: ?>
            <section class="admin-card">
                <form method="post" id="bulk-delete">
                    <?php echo hw_csrf_field(); ?>
                    <input type="hidden" name="action" value="bulk_delete">
                    <input type="hidden" name="type" value="<?php echo hw_e($tab); ?>">
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                </form>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="admin-check"><input type="checkbox" id="select-all" aria-label="Select all"></th>
                                <th>#</th>
                                <?php if ($tab === 'leads'): ?>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Expected price</th>
                                <?php else: ?>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                <?php endif; ?>
                                <th class="admin-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pageItems as $index => $row): ?>
                                <tr>
                                    <td class="admin-check">
                                        <?php if (!empty($row['id'])): ?>
                                            <input type="checkbox" name="ids[]" form="bulk-delete" class="row-check" value="<?php echo hw_e((string) $row['id']); ?>" aria-label="Select row <?php echo $from + $index; ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $from + $index; ?></td>
                                    <?php if ($tab === 'leads'): ?>
                                        <td><?php echo hw_e(hw_admin_date(isset($row['created_at']) ? (string) $row['created_at'] : null)); ?></td>
                                        <td><?php echo hw_e((string) ($row['name'] ?? '')); ?></td>
                                        <td><?php echo hw_e((string) ($row['phone'] ?? '')); ?></td>
                                        <td><?php echo hw_e((string) ($row['email'] ?? '')); ?></td>
                                        <td><?php echo hw_e((string) ($row['location'] ?? '')); ?></td>
                                        <td><?php echo hw_e((string) ($row['property_type'] ?? '')); ?></td>
                                        <td><?php echo hw_e((string) ($row['expected_price'] ?? '')); ?></td>
                                    <?php else: ?>
                                        <?php
                                        $msgDate = hw_admin_date(isset($row['created_at']) ? (string) $row['created_at'] : null);
                                        $msgName = (string) ($row['name'] ?? '');
                                        $msgEmail = (string) ($row['email'] ?? '');
                                        $msgBody = (string) ($row['message'] ?? '');
                                        ?>
                                        <td><?php echo hw_e($msgDate); ?></td>
                                        <td><?php echo hw_e($msgName); ?></td>
                                        <td><?php echo hw_e($msgEmail); ?></td>
                                        <td class="admin-msg"><?php echo hw_e(hw_preview($msgBody)); ?></td>
                                    <?php endif; ?>
                                    <td class="admin-actions">
                                        <?php if ($tab === 'messages'): ?>
                                            <button
                                                type="button"
                                                class="admin-view"
                                                data-date="<?php echo hw_e($msgDate); ?>"
                                                data-name="<?php echo hw_e($msgName); ?>"
                                                data-email="<?php echo hw_e($msgEmail); ?>"
                                                data-message="<?php echo hw_e($msgBody); ?>"
                                            >View</button>
                                        <?php endif; ?>
                                        <?php if (!empty($row['id'])): ?>
                                            <form method="post" class="admin-delete">
                                                <?php echo hw_csrf_field(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="type" value="<?php echo hw_e($tab); ?>">
                                                <input type="hidden" name="id" value="<?php echo hw_e((string) $row['id']); ?>">
                                                <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                <button type="submit">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php if ($pages > 1): ?>
                <nav class="admin-pages" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?tab=<?php echo hw_e($tab); ?>&amp;page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo $page; ?> of <?php echo $pages; ?></span>
                    <?php if ($page < $pages): ?>
                        <a href="?tab=<?php echo hw_e($tab); ?>&amp;page=<?php echo $page + 1; ?>">Next</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="admin-drawer" id="msg-drawer" hidden>
        <div class="admin-drawer-card" role="dialog" aria-labelledby="msg-drawer-title">
            <div class="admin-drawer-head">
                <h2 id="msg-drawer-title">Message</h2>
                <button type="button" class="admin-drawer-close" data-close aria-label="Close">×</button>
            </div>
            <p class="admin-drawer-meta" data-meta></p>
            <div class="admin-drawer-body" data-body></div>
        </div>
    </div>
    <div class="admin-toast" id="confirm-toast" hidden>
        <div class="admin-toast-card" role="alertdialog" aria-labelledby="confirm-toast-title">
            <p id="confirm-toast-title">Are you sure?</p>
            <div class="admin-toast-actions">
                <button type="button" class="admin-toast-cancel" data-cancel>Cancel</button>
                <button type="button" class="admin-toast-confirm" data-confirm>Confirm</button>
            </div>
        </div>
    </div>
    <script src="<?php echo hw_e(hw_url('assets/js/admin.js') . '?v=' . filemtime(HW_ROOT . '/assets/js/admin.js')); ?>"></script>
</body>
</html>
