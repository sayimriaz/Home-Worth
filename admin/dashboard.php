<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
hw_require_admin();

$notice = hw_flash('admin_notice') ?? '';
$tab = (string) ($_GET['tab'] ?? 'leads');
$search = hw_clean_text((string) ($_GET['q'] ?? ''), 80);
$dateFrom = (string) ($_GET['from'] ?? '');
$dateTo = (string) ($_GET['to'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $dateFrom = '';
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $dateTo = '';
}
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
        $search = hw_clean_text((string) ($_POST['q'] ?? ''), 80);
        $dateFrom = (string) ($_POST['from'] ?? '');
        $dateTo = (string) ($_POST['to'] ?? '');
        header('Location: ' . hw_admin_url('dashboard') . hw_admin_filter_query($tab, $search, $dateFrom, $dateTo, $pageAfter));
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
        $search = hw_clean_text((string) ($_POST['q'] ?? ''), 80);
        $dateFrom = (string) ($_POST['from'] ?? '');
        $dateTo = (string) ($_POST['to'] ?? '');
        header('Location: ' . hw_admin_url('dashboard') . hw_admin_filter_query($tab, $search, $dateFrom, $dateTo, $pageAfter));
        exit;
    }
}

$leadsAll = array_reverse(hw_read_json(HW_LEADS_FILE));
$messagesAll = array_reverse(hw_read_json(HW_MESSAGES_FILE));
$leads = hw_admin_filter_records($leadsAll, $search, $dateFrom, $dateTo);
$messages = hw_admin_filter_records($messagesAll, $search, $dateFrom, $dateTo);
$filterQs = hw_admin_filter_query($tab, $search, $dateFrom, $dateTo);

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
$allCount = $tab === 'messages' ? count($messagesAll) : count($leadsAll);
$total = count($items);
$filtersOn = $search !== '' || $dateFrom !== '' || $dateTo !== '';
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
                <span class="admin-nav-main"><?php echo hw_icon('house'); ?> Requests</span>
                <b><?php echo count($leadsAll); ?></b>
            </a>
            <a class="<?php echo $tab === 'messages' ? 'is-active' : ''; ?>" href="?tab=messages">
                <span class="admin-nav-main"><?php echo hw_icon('mail'); ?> Messages</span>
                <b><?php echo count($messagesAll); ?></b>
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
                    <p class="admin-range"><?php
                        if ($allCount === 0) {
                            echo 'No entries yet.';
                        } elseif ($total === 0) {
                            echo 'No matching entries.';
                        } else {
                            echo 'Showing ' . $from . '–' . $to . ' of ' . $total . ($filtersOn ? ' (filtered)' : '');
                        }
                    ?></p>
                <?php endif; ?>
            </div>
        </header>
        <?php if ($tab === 'leads' || $tab === 'messages'): ?>
            <div class="admin-toolbar">
                <form class="admin-search" method="get">
                    <input type="hidden" name="tab" value="<?php echo hw_e($tab); ?>">
                    <label class="admin-search-date">
                        <span>Search</span>
                        <span class="admin-search-q">
                            <?php echo hw_icon('search'); ?>
                            <input type="search" name="q" value="<?php echo hw_e($search); ?>" placeholder="Name, phone, location, ZIP" maxlength="80">
                        </span>
                    </label>
                    <label class="admin-search-date">
                        <span>From</span>
                        <span class="hw-date" data-date>
                            <input type="hidden" name="from" value="<?php echo hw_e($dateFrom); ?>">
                            <button type="button" class="hw-date-btn" aria-label="From date"></button>
                        </span>
                    </label>
                    <label class="admin-search-date">
                        <span>To</span>
                        <span class="hw-date" data-date>
                            <input type="hidden" name="to" value="<?php echo hw_e($dateTo); ?>">
                            <button type="button" class="hw-date-btn" aria-label="To date"></button>
                        </span>
                    </label>
                    <button class="admin-tool" type="submit"><?php echo hw_icon('search'); ?> Search</button>
                    <?php if ($filtersOn): ?>
                        <a class="admin-tool admin-tool-danger" href="?tab=<?php echo hw_e($tab); ?>"><?php echo hw_icon('close'); ?> Clear</a>
                    <?php endif; ?>
                </form>
                <div class="admin-tools">
                    <button type="button" class="admin-tool" id="view-form-btn" data-url="<?php echo hw_e(hw_admin_url('form')); ?>" data-type="<?php echo hw_e($tab); ?>"><?php echo hw_icon('view'); ?> View</button>
                    <button type="button" class="admin-tool admin-tool-gold" id="download-form-btn" data-url="<?php echo hw_e(hw_admin_url('form')); ?>" data-type="<?php echo hw_e($tab); ?>"><?php echo hw_icon('download'); ?> Download</button>
                    <button type="submit" class="admin-tool admin-tool-danger" id="bulk-delete-btn" form="bulk-delete" disabled><?php echo hw_icon('trash'); ?> Delete</button>
                    <button type="button" class="admin-tool admin-tool-gold" id="export-btn" data-href="<?php echo hw_e($filterQs . '&export=csv'); ?>"><?php echo hw_icon('export'); ?> Export</button>
                </div>
            </div>
        <?php endif; ?>

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
        <?php elseif ($allCount === 0): ?>
            <section class="admin-card">
                <p class="admin-empty"><?php echo $tab === 'leads' ? 'No property requests yet.' : 'No contact messages yet.'; ?></p>
            </section>
        <?php elseif ($total === 0): ?>
            <section class="admin-card">
                <p class="admin-empty">No entries match that search.</p>
            </section>
        <?php else: ?>
            <section class="admin-card">
                <form method="post" id="bulk-delete">
                    <?php echo hw_csrf_field(); ?>
                    <input type="hidden" name="action" value="bulk_delete">
                    <input type="hidden" name="type" value="<?php echo hw_e($tab); ?>">
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <input type="hidden" name="q" value="<?php echo hw_e($search); ?>">
                    <input type="hidden" name="from" value="<?php echo hw_e($dateFrom); ?>">
                    <input type="hidden" name="to" value="<?php echo hw_e($dateTo); ?>">
                </form>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="admin-check"><input type="checkbox" id="select-all" aria-label="Select all"></th>
                                <th class="admin-col-num">#</th>
                                <?php if ($tab === 'leads'): ?>
                                    <th class="admin-col-date">Date</th>
                                    <th class="admin-col-name">Name</th>
                                    <th class="admin-col-phone">Phone</th>
                                    <th class="admin-col-email">Email</th>
                                    <th class="admin-col-location">Location</th>
                                    <th class="admin-col-type">Type</th>
                                    <th class="admin-col-price">Expected price</th>
                                <?php else: ?>
                                    <th class="admin-col-date">Date</th>
                                    <th class="admin-col-name">Name</th>
                                    <th class="admin-col-email">Email</th>
                                    <th class="admin-col-message">Message</th>
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
                                    <td class="admin-col-num"><?php echo $from + $index; ?></td>
                                    <?php if ($tab === 'leads'): ?>
                                        <?php
                                        $leadName = (string) ($row['name'] ?? '');
                                        $leadEmail = (string) ($row['email'] ?? '');
                                        $leadLocation = (string) ($row['location'] ?? '');
                                        $leadPrice = (string) ($row['expected_price'] ?? '');
                                        ?>
                                        <td class="admin-col-date"><?php echo hw_e(hw_admin_date(isset($row['created_at']) ? (string) $row['created_at'] : null)); ?></td>
                                        <td class="admin-col-name" title="<?php echo hw_e($leadName); ?>"><?php echo hw_e($leadName); ?></td>
                                        <td class="admin-col-phone"><?php echo hw_e((string) ($row['phone'] ?? '')); ?></td>
                                        <td class="admin-col-email" title="<?php echo hw_e($leadEmail); ?>"><?php echo hw_e($leadEmail); ?></td>
                                        <td class="admin-col-location" title="<?php echo hw_e($leadLocation); ?>"><?php echo hw_e($leadLocation); ?></td>
                                        <td class="admin-col-type"><?php echo hw_e((string) ($row['property_type'] ?? '')); ?></td>
                                        <td class="admin-col-price" title="<?php echo hw_e($leadPrice); ?>"><?php echo hw_e($leadPrice); ?></td>
                                    <?php else: ?>
                                        <?php
                                        $msgDate = hw_admin_date(isset($row['created_at']) ? (string) $row['created_at'] : null);
                                        $msgName = (string) ($row['name'] ?? '');
                                        $msgEmail = (string) ($row['email'] ?? '');
                                        $msgBody = (string) ($row['message'] ?? '');
                                        ?>
                                        <td class="admin-col-date"><?php echo hw_e($msgDate); ?></td>
                                        <td class="admin-col-name"><?php echo hw_e($msgName); ?></td>
                                        <td class="admin-col-email"><?php echo hw_e($msgEmail); ?></td>
                                        <td class="admin-col-message admin-msg"><?php echo hw_e(hw_preview($msgBody)); ?></td>
                                    <?php endif; ?>
                                    <td class="admin-actions">
                                        <?php if (!empty($row['id'])): ?>
                                            <div class="admin-action-stack">
                                                <a class="admin-form-view" href="<?php echo hw_e(hw_admin_url('form') . '?type=' . rawurlencode($tab) . '&id=' . rawurlencode((string) $row['id'])); ?>" aria-label="View" title="View"><?php echo hw_icon('view'); ?></a>
                                                <a class="admin-form-download" href="<?php echo hw_e(hw_admin_url('form') . '?type=' . rawurlencode($tab) . '&id=' . rawurlencode((string) $row['id']) . '&download=1'); ?>" aria-label="Download" title="Download"><?php echo hw_icon('download'); ?></a>
                                                <form method="post" class="admin-delete">
                                                    <?php echo hw_csrf_field(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="type" value="<?php echo hw_e($tab); ?>">
                                                    <input type="hidden" name="id" value="<?php echo hw_e((string) $row['id']); ?>">
                                                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                    <input type="hidden" name="q" value="<?php echo hw_e($search); ?>">
                                                    <input type="hidden" name="from" value="<?php echo hw_e($dateFrom); ?>">
                                                    <input type="hidden" name="to" value="<?php echo hw_e($dateTo); ?>">
                                                    <button type="submit" aria-label="Delete" title="Delete"><?php echo hw_icon('trash'); ?></button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($pages > 1): ?>
                    <nav class="admin-pages" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a class="admin-page-nav" href="<?php echo hw_e(hw_admin_filter_query($tab, $search, $dateFrom, $dateTo, $page - 1)); ?>">Previous</a>
                        <?php else: ?>
                            <span class="admin-page-nav is-disabled">Previous</span>
                        <?php endif; ?>
                        <div class="admin-page-nums">
                            <?php foreach (hw_admin_page_list($page, $pages) as $item): ?>
                                <?php if ($item === 0): ?>
                                    <span class="admin-page-gap">…</span>
                                <?php elseif ($item === $page): ?>
                                    <span class="admin-page-num is-current" aria-current="page"><?php echo $item; ?></span>
                                <?php else: ?>
                                    <a class="admin-page-num" href="<?php echo hw_e(hw_admin_filter_query($tab, $search, $dateFrom, $dateTo, $item)); ?>"><?php echo $item; ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($page < $pages): ?>
                            <a class="admin-page-nav" href="<?php echo hw_e(hw_admin_filter_query($tab, $search, $dateFrom, $dateTo, $page + 1)); ?>">Next</a>
                        <?php else: ?>
                            <span class="admin-page-nav is-disabled">Next</span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>

    <div class="hw-form-modal" id="form-modal" hidden>
        <div class="hw-form-modal-panel" role="dialog" aria-modal="true" aria-labelledby="form-modal-title">
            <div class="hw-form-modal-bar">
                <p id="form-modal-title">Form</p>
                <div>
                    <button type="button" id="form-modal-print">Print / Save as PDF</button>
                    <button type="button" class="ghost" id="form-modal-close">Close</button>
                </div>
            </div>
            <iframe id="form-modal-frame" title="Form preview"></iframe>
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
