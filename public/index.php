<?php

/**
 * Laravel public/index.php — front controller.
 *
 * Qo'shimcha (rewrite bo'lmagan muhitlar uchun): OSPanel nginx config'i
 * subpapkalar uchun "chiroyli" URL rewrite bermaydi, shuning uchun
 * POST /resume/pdf nginx'da 404 qaytaradi. Bunday hollarda forma
 * so'rovni query-parametr orqali yuboradi:
 *
 *   POST /resume/public/index.php?_route=/resume/pdf
 *
 * Bu yerda faqat URI path qismi qabul qilinadi (host/scheme emas) va
 * Laravel route jadvali baribir method + route mavjudligini tekshiradi —
 * rewrite ishlaydigan serverlarda bu parametr ishlatilmaydi.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ---------------------------------------------------------------------------
// Maintenance rejimi
// ---------------------------------------------------------------------------
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

// ---------------------------------------------------------------------------
// ?_route= shim: faqat path qismi (masalan /resume/pdf) qabul qilinadi
// ---------------------------------------------------------------------------
if (isset($_GET['_route']) && is_string($_GET['_route'])) {
    $routePath = $_GET['_route'];

    if (preg_match('#^/[A-Za-z0-9_\-/]+$#', $routePath)) {
        // Laravel Request::capture() REQUEST_URI asosida route'ni topadi
        $_SERVER['REQUEST_URI'] = $routePath;
    }

    unset($_GET['_route'], $_REQUEST['_route']);
}

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
