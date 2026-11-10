<?php

declare(strict_types=1);

if (!defined('HW_APP')) {
    http_response_code(403);
    exit;
}

$hwFormDefaults = [
    'HW_SITE_NAME' => 'Home Worth',
    'HW_SITE_TAGLINE' => 'Unlocking Real Estate Success Together',
    'HW_SITE_URL' => 'https://homeworthofficial.com',
    'HW_PHONE_DISPLAY' => '+1 (469) 906-0714',
    'HW_EMAIL_INFO' => 'info@homeworthofficial.com',
    'HW_EMAIL_SUPPORT' => 'support@homeworthofficial.com',
    'HW_LOCATION' => 'United States',
];
foreach ($hwFormDefaults as $hwName => $hwValue) {
    if (!defined($hwName)) {
        define($hwName, $hwValue);
    }
}
unset($hwFormDefaults, $hwName, $hwValue);

function hw_form_kind(string $type): string
{
    return $type === 'messages' ? 'messages' : 'leads';
}

function hw_form_title(string $kind): string
{
    return $kind === 'messages' ? 'Contact Message Form' : 'Property Request Form';
}

function hw_form_source_url(string $kind): string
{
    return $kind === 'messages' ? HW_SITE_URL . '/contact' : HW_SITE_URL . '/';
}

function hw_form_record_code(string $kind, array $record): string
{
    $prefix = $kind === 'messages' ? 'HW-CM' : 'HW-PR';
    $id = strtoupper((string) ($record['id'] ?? ''));
    return $id !== '' ? $prefix . '-' . $id : $prefix . '-UNKNOWN';
}

function hw_form_date_long(?string $value): string
{
    if ($value === null || $value === '') {
        return 'Not recorded';
    }
    $time = strtotime($value);
    return $time ? date('F j, Y \a\t g:i A T', $time) : 'Not recorded';
}

function hw_form_filename(string $kind, array $records, string $ext = 'pdf'): string
{
    $ext = $ext === 'html' ? 'html' : 'pdf';
    $stamp = gmdate('Y-m-d');
    if (count($records) === 1) {
        $name = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($records[0]['name'] ?? 'entry')), '-');
        $name = $name !== '' ? $name : 'entry';
        $time = strtotime((string) ($records[0]['created_at'] ?? '')) ?: time();
        $label = $kind === 'messages' ? 'Contact-Message' : 'Property-Request';
        return 'Home-Worth-' . $label . '-Form-' . $name . '-' . date('Y-m-d', $time) . '.' . $ext;
    }
    $label = $kind === 'messages' ? 'Contact-Messages' : 'Property-Requests';
    return 'Home-Worth-' . $label . '-Forms-' . $stamp . '.' . $ext;
}

function hw_form_fields(string $kind, array $record): array
{
    $blank = static function ($value): string {
        if (is_array($value) || is_object($value)) {
            return '';
        }
        return trim((string) $value);
    };

    if ($kind === 'messages') {
        return [
            ['label' => 'Your Name *', 'value' => $blank($record['name'] ?? ''), 'span' => 1],
            ['label' => 'Your Email', 'value' => $blank($record['email'] ?? ''), 'span' => 1],
            ['label' => 'Write Your Message Here...', 'value' => $blank($record['message'] ?? ''), 'span' => 2],
        ];
    }

    return [
        ['label' => 'Your Name *', 'value' => $blank($record['name'] ?? ''), 'span' => 1],
        ['label' => 'Phone', 'value' => hw_form_display_multi($blank($record['phone'] ?? '')), 'span' => 1],
        ['label' => 'Your Email', 'value' => $blank($record['email'] ?? ''), 'span' => 1],
        ['label' => 'Property Location', 'value' => hw_form_display_multi($blank($record['location'] ?? ''), true), 'span' => 2],
        ['label' => 'Type of Property', 'value' => $blank($record['property_type'] ?? ''), 'span' => 1],
        ['label' => 'Expected Price', 'value' => $blank($record['expected_price'] ?? ''), 'span' => 1],
    ];
}

function hw_form_intro(string $kind): string
{
    if ($kind === 'messages') {
        return 'Fill out the form bellow. We will get in touch with you in 24 working hours.';
    }
    return 'Please fill in the form below and our team will be in contact with you shortly.';
}

function hw_form_action_label(string $kind): string
{
    return $kind === 'messages' ? 'Send Message' : 'Get your price today!';
}

function hw_form_html(string $kind, array $records, bool $autoPrint = false, bool $embed = false): string
{
    $title = hw_form_title($kind);
    $logo = hw_url('assets/images/logo.png');
    $site = HW_SITE_URL;
    $sheets = '';
    $total = count($records);
    foreach ($records as $index => $record) {
        $sheets .= hw_form_sheet_html($kind, $record, $index + 1, $total, $logo, $site);
    }

    $cssPath = HW_ROOT . '/assets/css/form-document.css';
    $jsPath = HW_ROOT . '/assets/js/form-document.js';
    $css = hw_url('assets/css/form-document.css') . '?v=' . (is_file($cssPath) ? (string) filemtime($cssPath) : '1');
    $js = hw_url('assets/js/form-document.js') . '?v=' . (is_file($jsPath) ? (string) filemtime($jsPath) : '1');
    $bodyClass = 'legal-body' . ($embed ? ' is-embed' : '');
    $toolbar = $embed ? '' : '<div class="legal-toolbar">
  <p>Print or save this form as a PDF.</p>
  <div>
    <button type="button" id="print-form">Print / Save as PDF</button>
    <button type="button" class="ghost" id="close-form">Close</button>
  </div>
</div>';

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>' . hw_e($title) . ' – ' . hw_e(HW_SITE_NAME) . '</title>
<link rel="icon" href="' . hw_e(hw_url('assets/images/favicon.png')) . '" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="' . hw_e($css) . '">
</head>
<body class="' . $bodyClass . '"' . ($autoPrint ? ' data-autoprint="1"' : '') . '>
' . $toolbar . '
' . $sheets . '
<script src="' . hw_e($js) . '"></script>
</body>
</html>';
}

function hw_form_sheet_html(
    string $kind,
    array $record,
    int $page,
    int $total,
    string $logo,
    string $site
): string {
    $title = hw_form_title($kind);
    $code = hw_form_record_code($kind, $record);
    $submitted = hw_form_date_long(isset($record['created_at']) ? (string) $record['created_at'] : null);
    $source = hw_form_source_url($kind);
    $fields = '';
    foreach (hw_form_fields($kind, $record) as $field) {
        $span = ($field['span'] ?? 1) === 2 ? ' legal-field-wide' : '';
        $value = (string) $field['value'];
        $display = $value !== '' ? hw_e($value) : '&nbsp;';
        $fields .= '<label class="legal-field' . $span . '"><span>' . hw_e((string) $field['label']) . '</span><div class="legal-input">' . $display . '</div></label>';
    }

    $consent = '';
    if ($kind === 'leads') {
        $checked = array_key_exists('consent', $record) ? !empty($record['consent']) : true;
        $tick = $checked
            ? '<svg viewBox="0 0 16 16" width="12" height="12" aria-hidden="true"><path fill="none" stroke="#151d31" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" d="M3.2 8.2 6.6 11.5 12.8 4.4"/></svg>'
            : '';
        $consent = '<label class="legal-consent"><span class="legal-check' . ($checked ? ' is-on' : '') . '" aria-hidden="true">' . $tick . '</span><span>By submitting this form, you agree to receive calls and messages from our team.</span></label>';
    }

    $logoHtml = $logo !== ''
        ? '<img src="' . hw_e($logo) . '" alt="' . hw_e(HW_SITE_NAME) . '">'
        : '<strong>' . hw_e(HW_SITE_NAME) . '</strong>';

    return '<article class="legal-sheet">
  <header class="legal-head">
    <div class="legal-brand">' . $logoHtml . '</div>
    <div class="legal-company">
      <p class="legal-name">' . hw_e(HW_SITE_NAME) . '</p>
      <p>' . hw_e(HW_SITE_TAGLINE) . '</p>
      <p>' . hw_e($site) . '</p>
      <p>' . hw_e(HW_PHONE_DISPLAY) . ' · ' . hw_e(HW_EMAIL_INFO) . '</p>
    </div>
    <div class="legal-ref">
      <span>Record ID</span>
      <strong>' . hw_e($code) . '</strong>
      <span>Page ' . $page . ' of ' . $total . '</span>
    </div>
  </header>
  <h1>' . hw_e($title) . '</h1>
  <dl class="legal-meta">
    <div><dt>Date submitted</dt><dd>' . hw_e($submitted) . '</dd></div>
    <div><dt>Form source</dt><dd>' . hw_e($source) . '</dd></div>
    <div><dt>Company</dt><dd>' . hw_e(HW_SITE_NAME) . ' · ' . hw_e(HW_LOCATION) . '</dd></div>
  </dl>
  <p class="legal-intro">' . hw_e(hw_form_intro($kind)) . '</p>
  <div class="legal-grid">' . $fields . '</div>
  ' . $consent . '
  <p class="legal-submitted">Form submitted: ' . hw_e(hw_form_action_label($kind)) . '</p>
  <footer class="legal-foot">
    <span>' . hw_e(HW_SITE_NAME) . '</span>
    <span>' . hw_e($code) . '</span>
  </footer>
</article>';
}

function hw_form_pdf_bytes(string $kind, array $records): string
{
    $pdf = new HwFormPdf();
    $logo = hw_form_logo_jpeg();
    $total = count($records);
    foreach ($records as $index => $record) {
        $pdf->addRecord($kind, $record, $index + 1, $total, $logo);
    }
    return $pdf->output();
}

function hw_form_logo_jpeg(): ?array
{
    $jpegPath = HW_ROOT . '/assets/images/logo-letterhead.jpg';
    if (is_file($jpegPath)) {
        $data = file_get_contents($jpegPath);
        if ($data !== false && $data !== '') {
            $size = @getimagesize($jpegPath);
            return [
                'data' => $data,
                'width' => is_array($size) ? (int) $size[0] : 512,
                'height' => is_array($size) ? (int) $size[1] : 512,
            ];
        }
    }

    $path = HW_ROOT . '/assets/images/logo.png';
    if (!is_file($path) || !function_exists('imagecreatefrompng')) {
        return null;
    }
    $src = @imagecreatefrompng($path);
    if ($src === false) {
        return null;
    }
    $width = imagesx($src);
    $height = imagesy($src);
    $dst = imagecreatetruecolor($width, $height);
    if ($dst === false) {
        imagedestroy($src);
        return null;
    }
    $navy = imagecolorallocate($dst, 21, 29, 49);
    imagefilledrectangle($dst, 0, 0, $width, $height, $navy);
    imagecopy($dst, $src, 0, 0, 0, 0, $width, $height);
    ob_start();
    imagejpeg($dst, null, 90);
    $data = (string) ob_get_clean();
    imagedestroy($src);
    imagedestroy($dst);
    if ($data === '') {
        return null;
    }
    return ['data' => $data, 'width' => $width, 'height' => $height];
}

final class HwFormPdf
{
    private string $page = '';
    /** @var list<string> */
    private array $pages = [];
    /** @var array<string, array{data:string,width:int,height:int}> */
    private array $images = [];
    private float $width = 612.0;
    private float $height = 792.0;
    private float $y = 36.0;
    private float $left = 42.0;
    private float $right = 570.0;

    public function addRecord(string $kind, array $record, int $number, int $total, ?array $logo): void
    {
        $this->addPage();
        if ($logo !== null) {
            $this->images['logo'] = $logo;
        }

        $this->rect(36, 36, 540, 92, [21, 29, 49], true);
        $this->rect(36, 128, 540, 5, [245, 196, 0], true);
        if ($logo !== null) {
            $this->image('logo', 48, 48, 68, 68);
        }
        $this->text(128, 54, HW_SITE_NAME, 18, true, [255, 255, 255]);
        $this->text(128, 76, HW_SITE_TAGLINE, 9, false, [230, 230, 230]);
        $this->text(128, 90, HW_SITE_URL, 9, false, [230, 230, 230]);
        $this->text(128, 104, HW_PHONE_DISPLAY . '  |  ' . HW_EMAIL_INFO, 9, false, [230, 230, 230]);

        $code = hw_form_record_code($kind, $record);
        $this->text(430, 54, 'RECORD ID', 8, true, [245, 196, 0]);
        $this->text(430, 70, $code, 9, true, [255, 255, 255]);
        $this->text(430, 88, 'Page ' . $number . ' of ' . $total, 8, false, [210, 210, 210]);

        $this->y = 154;
        $this->text($this->left, $this->y, strtoupper(hw_form_title($kind)), 16, true, [21, 29, 49]);
        $this->y += 14;
        $this->line($this->left, $this->y, $this->right, $this->y, [21, 29, 49]);
        $this->y += 14;

        $submitted = hw_form_date_long(isset($record['created_at']) ? (string) $record['created_at'] : null);
        $source = hw_form_source_url($kind);
        $this->metaRow('Date submitted', $submitted, 'Form source', $source);
        $this->metaRow('Company', HW_SITE_NAME . ' · ' . HW_LOCATION, 'Support', HW_EMAIL_SUPPORT);
        $this->y += 8;
        $this->wrapped($this->left, $this->y, hw_form_intro($kind), 520, 10, [27, 34, 51]);
        $this->y += 18;

        $fields = hw_form_fields($kind, $record);
        $colW = 252.0;
        $gap = 16.0;
        $i = 0;
        while ($i < count($fields)) {
            $field = $fields[$i];
            if (($field['span'] ?? 1) === 2) {
                $this->ensure(88);
                $h = $this->fieldBox($this->left, $this->y, 520, (string) $field['label'], (string) $field['value'], 72);
                $this->y += $h + 10;
                $i++;
                continue;
            }
            $this->ensure(58);
            $leftH = $this->fieldBox($this->left, $this->y, $colW, (string) $field['label'], (string) $field['value'], 28);
            $rightH = 0.0;
            if (isset($fields[$i + 1]) && ($fields[$i + 1]['span'] ?? 1) !== 2) {
                $next = $fields[$i + 1];
                $rightH = $this->fieldBox($this->left + $colW + $gap, $this->y, $colW, (string) $next['label'], (string) $next['value'], 28);
                $i += 2;
            } else {
                $i++;
            }
            $this->y += max($leftH, $rightH) + 10;
        }

        if ($kind === 'leads') {
            $this->ensure(36);
            $this->checkbox($this->left, $this->y, true);
            $this->wrapped($this->left + 22, $this->y + 2, 'By submitting this form, you agree to receive calls and messages from our team.', 498, 10, [21, 29, 49]);
            $this->y += 28;
        }

        $this->ensure(28);
        $this->rect($this->left, $this->y, 220, 22, [245, 196, 0], true);
        $this->text($this->left + 10, $this->y + 15, 'Form submitted: ' . hw_form_action_label($kind), 9, true, [21, 29, 49]);
        $this->y += 36;
        $this->text($this->left, 778, HW_SITE_NAME, 8, false, [93, 101, 117]);
        $this->text(360, 778, $code, 8, false, [93, 101, 117]);
    }

    public function output(): string
    {
        if ($this->page !== '') {
            $this->pages[] = $this->page;
            $this->page = '';
        }
        if ($this->pages === []) {
            $this->addPage();
            $this->pages[] = $this->page;
        }

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = [];
        $fontHelv = 3;
        $fontBold = 4;
        $imgNum = 0;
        $imgObj = 0;
        if (isset($this->images['logo'])) {
            $imgNum = 5;
            $imgObj = 5;
        }
        $firstPageObj = isset($this->images['logo']) ? 6 : 5;

        $objects[] = 'placeholder-pages';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        if (isset($this->images['logo'])) {
            $img = $this->images['logo'];
            $objects[] = '<< /Type /XObject /Subtype /Image /Width ' . $img['width'] . ' /Height ' . $img['height']
                . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' . strlen($img['data'])
                . ' >>' . "\nstream\n" . $img['data'] . "\nendstream";
        }

        foreach ($this->pages as $index => $content) {
            $pageObj = $firstPageObj + ($index * 2);
            $contentObj = $pageObj + 1;
            $kids[] = $pageObj . ' 0 R';
            $xobjects = $imgNum > 0 ? ' /XObject << /Im1 ' . $imgObj . ' 0 R >>' : '';
            $objects[$pageObj - 1] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 '
                . $fontHelv . ' 0 R /F2 ' . $fontBold . ' 0 R >>' . $xobjects . ' >> /Contents ' . $contentObj . ' 0 R >>';
            $objects[$contentObj - 1] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        }
        $objects[1] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';

        ksort($objects, SORT_NUMERIC);
        $out = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $body) {
            $offsets[] = strlen($out);
            $out .= ($i + 1) . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xref = strlen($out);
        $count = count($objects) + 1;
        $out .= "xref\n0 " . $count . "\n";
        $out .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $out .= "trailer << /Size " . $count . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
        return $out;
    }

    private function addPage(): void
    {
        if ($this->page !== '') {
            $this->pages[] = $this->page;
        }
        $this->page = '';
        $this->y = 36.0;
    }

    private function ensure(float $need): void
    {
        if ($this->y + $need < 760) {
            return;
        }
        $this->addPage();
        $this->y = 48.0;
    }

    private function ty(float $y): float
    {
        return $this->height - $y;
    }

    private function rect(float $x, float $y, float $w, float $h, array $rgb, bool $fill): void
    {
        $this->page .= $this->color($rgb, $fill) . sprintf(' %.2F %.2F %.2F %.2F re %s' . "\n", $x, $this->ty($y) - $h, $w, $h, $fill ? 'f' : 'S');
    }

    private function line(float $x1, float $y1, float $x2, float $y2, array $rgb): void
    {
        $this->page .= $this->color($rgb, false) . sprintf(' %.2F %.2F m %.2F %.2F l S' . "\n", $x1, $this->ty($y1), $x2, $this->ty($y2));
    }

    private function text(float $x, float $y, string $text, float $size, bool $bold, array $rgb): void
    {
        $font = $bold ? '/F2' : '/F1';
        $this->page .= $this->color($rgb, true) . sprintf("\nBT %s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n", $font, $size, $x, $this->ty($y), $this->escape($text));
    }

    private function wrapped(float $x, float $y, string $text, float $maxWidth, float $size, array $rgb): float
    {
        $lines = $this->wrap($text, $maxWidth, $size);
        $top = $y;
        foreach ($lines as $line) {
            $this->text($x, $top + $size, $line, $size, false, $rgb);
            $top += $size + 3;
        }
        $this->y = max($this->y, $top);
        return $top - $y;
    }

    private function fieldBox(float $x, float $y, float $w, string $label, string $value, float $minInner): float
    {
        $this->text($x, $y + 10, $label, 9, true, [21, 29, 49]);
        $innerTop = $y + 16;
        $lines = $this->wrap($value !== '' ? $value : ' ', $w - 16, 11);
        $innerH = max($minInner, (count($lines) * 14) + 12);
        $this->rect($x, $innerTop, $w, $innerH, [248, 250, 252], true);
        $this->rect($x, $innerTop, $w, $innerH, [207, 214, 226], false);
        $ty = $innerTop + 14;
        foreach ($lines as $line) {
            $this->text($x + 8, $ty, $line, 11, false, [21, 29, 49]);
            $ty += 14;
        }
        return 16 + $innerH;
    }

    private function checkbox(float $x, float $y, bool $on): void
    {
        $this->rect($x, $y, 12, 12, [21, 29, 49], false);
        if ($on) {
            $this->line($x + 2, $y + 6, $x + 5, $y + 10, [21, 29, 49]);
            $this->line($x + 5, $y + 10, $x + 10, $y + 3, [21, 29, 49]);
        }
    }

    private function image(string $name, float $x, float $y, float $w, float $h): void
    {
        if (!isset($this->images[$name])) {
            return;
        }
        $this->page .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /Im1 Do Q\n", $w, $h, $x, $this->ty($y) - $h);
    }

    private function metaRow(string $l1, string $v1, string $l2, string $v2): void
    {
        $this->text($this->left, $this->y, strtoupper($l1), 8, true, [93, 101, 117]);
        $this->text(306, $this->y, strtoupper($l2), 8, true, [93, 101, 117]);
        $this->y += 12;
        $this->text($this->left, $this->y, $v1, 10, true, [21, 29, 49]);
        $this->text(306, $this->y, $v2, 10, true, [21, 29, 49]);
        $this->y += 16;
    }

    private function wrap(string $text, float $maxWidth, float $size): array
    {
        $text = trim(preg_replace("/\r\n|\r/", "\n", $text) ?? $text);
        if ($text === '') {
            return [' '];
        }
        $lines = [];
        foreach (explode("\n", $text) as $para) {
            $words = preg_split('/\s+/', $para) ?: [];
            $current = '';
            foreach ($words as $word) {
                $try = $current === '' ? $word : $current . ' ' . $word;
                if ($this->widthOf($try, $size) <= $maxWidth) {
                    $current = $try;
                    continue;
                }
                if ($current !== '') {
                    $lines[] = $current;
                }
                $current = $word;
            }
            $lines[] = $current !== '' ? $current : ' ';
        }
        return $lines !== [] ? $lines : [' '];
    }

    private function widthOf(string $text, float $size): float
    {
        return strlen($this->latin($text)) * $size * 0.5;
    }

    private function color(array $rgb, bool $fill): string
    {
        $op = $fill ? 'rg' : 'RG';
        return sprintf('%.3F %.3F %.3F %s', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255, $op);
    }

    private function escape(string $text): string
    {
        $text = $this->latin($text);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function latin(string $text): string
    {
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
            if ($converted !== false) {
                return $converted;
            }
        }
        if (function_exists('mb_convert_encoding')) {
            return (string) mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        }
        return preg_replace('/[^\x00-\xFF]/', '?', $text) ?? $text;
    }
}
