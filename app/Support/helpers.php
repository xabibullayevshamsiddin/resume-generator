<?php

if (! function_exists('frontend_asset')) {
    /**
     * Build assetlar uchun URL qaytaradi.
     *
     * Mix manifest mavjud bo'lsa versioned path (cache-busting) ishlatiladi,
     * aks holda oddiy path. URL har doim joriy request'ning bazasidan quriladi
     * (url()), shuning uchun loyiha subpapkada (masalan OSPanel'da
     * http://localhost/resume/public) yoki alohida domenda bir xil ishlaydi.
     */
    function frontend_asset(string $path): string
    {
        $manifestFile = public_path('mix-manifest.json');

        if (file_exists($manifestFile)) {
            $manifest = json_decode((string) file_get_contents($manifestFile), true);

            if (is_array($manifest) && isset($manifest['/'.$path])) {
                return url(ltrim((string) $manifest['/'.$path], '/'));
            }
        }

        return url($path);
    }
}

if (! function_exists('resume_pdf_action')) {
    /**
     * PDF endpoint uchun forma action URL'i.
     *
     * Har doim front controller (index.php) orqali yuboriladi va route
     * ?_route= parametrida ko'rsatiladi. nginx rewrite bo'lmagan
     * subpapka muhitlarida ham (OSPanel) ishlaydi, artisan serve'da ham,
     * alohida domenda ham — chunki URL joriy request'ning SCRIPT_NAME'idan
     * quriladi, config'dagi APP_URL'dan emas.
     */
    function resume_pdf_action(): string
    {
        $request = request();
        $script = str_replace('\\', '/', (string) $request->server('SCRIPT_NAME', ''));

        if ($script === '' || $script === '/') {
            $script = '/index.php';
        }

        if (! str_ends_with($script, '.php')) {
            // Direktoriya request'i (masalan /resume/public/) — index.php qo'shamiz
            $script = rtrim($script, '/').'/index.php';
        }

        return $request->getSchemeAndHttpHost().$script.'?'.http_build_query([
            '_route' => '/resume/pdf',
        ]);
    }
}
