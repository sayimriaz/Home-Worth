<?php

declare(strict_types=1);

if (!defined('HW_APP')) {
    http_response_code(403);
    exit;
}

const HW_SITE_NAME = 'Home Worth';
const HW_SITE_TAGLINE = 'Unlocking Real Estate Success Together';
const HW_SITE_URL = 'https://homeworthofficial.com';
const HW_PHONE_DISPLAY = '+1 (469) 906-0714';
const HW_PHONE_TEL = '+14699060714';
const HW_EMAIL_INFO = 'info@homeworthofficial.com';
const HW_EMAIL_SUPPORT = 'support@homeworthofficial.com';
const HW_MAIL_TO = 'info@homeworthofficial.com';
const HW_FACEBOOK = 'https://www.facebook.com/homeworthofficial';
const HW_INSTAGRAM = 'https://www.instagram.com/homeworthofficial';
const HW_LOCATION = 'United States';

const HW_PROPERTY_TYPES = ['House', 'Condo', 'Apartment', 'Other'];
const HW_PRICE_RANGES = [
    'Less than $100,000',
    '$100,000 - $250,000',
    '$250,000 - $500,000',
    '$500,000+',
];

const HW_SESSION_IDLE = 1800;
const HW_FORM_MIN_SECONDS = 4;
const HW_FORM_MAX_AGE = 7200;
const HW_RATE_WINDOW = 900;
const HW_RATE_MAX_FORM = 3;
const HW_LOGIN_FAILS = 5;
const HW_LOGIN_LOCK_HOURS = [1, 3, 6, 12, 24, 24, 25];
const HW_PASSWORD_MIN = 12;
const HW_ADMIN_PER_PAGE = 100;
const HW_ADMIN_SLUG_DEFAULT = 'bGGIqmm';
const HW_ADMIN_SLUG_RESERVED = [
    'admin', 'contact', 'privacy', 'privacy-policy', 'thank-you', 'thanks',
    'assets', 'handlers', 'includes', 'storage', 'login', 'logout',
    'dashboard', 'settings', 'form', 'index', 'index.php', 'robots.txt', 'favicon',
    'css', 'js', 'images', 'img',
];

const HW_STORAGE_DIR = HW_ROOT . '/storage/data';
const HW_ADMIN_FILE = HW_STORAGE_DIR . '/admin.json';
const HW_LEADS_FILE = HW_STORAGE_DIR . '/leads.json';
const HW_MESSAGES_FILE = HW_STORAGE_DIR . '/messages.json';
const HW_RATE_FILE = HW_STORAGE_DIR . '/rate-limit.json';
const HW_LOGIN_LOCK_FILE = HW_STORAGE_DIR . '/login-lock.json';
