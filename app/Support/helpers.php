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
        $versioned = null;

        if (file_exists($manifestFile)) {
            $manifest = json_decode((string) file_get_contents($manifestFile), true);

            if (is_array($manifest) && isset($manifest['/'.$path])) {
                $versioned = ltrim((string) $manifest['/'.$path], '/');
            }
        }

        $path = $versioned ?? $path;

        // Joriy request bazasi — SCRIPT_NAME'dan olinadi, chunki APP_URL
        // subpapka muhitlarida noto'g'ri bo'lishi mumkin.
        // Muhim: sahifa index.php?_route=... orqali ochilganda SCRIPT_NAME
        // '/resume/public/index.php' ko'rinishida keladi — fayl nomini
        // olib tashlab, faqat papka qismini olamiz, aks holda URL
        // 'index.php/css/app.css' kabi buziladi va CSS yuklanmaydi.
        $request = request();
        $script = str_replace('\\', '/', (string) $request->server('SCRIPT_NAME', ''));

        $slashPos = strrpos($script, '/');
        $base = $slashPos === false ? '' : substr($script, 0, $slashPos);
        $base = rtrim($base, '/');

        return $request->getSchemeAndHttpHost().$base.'/'.ltrim($path, '/');
    }
}

if (! function_exists('resume_action_url')) {
    /**
     * Ichki URL'larni front controller (index.php) orqali qurish.
     *
     * nginx rewrite bo'lmagan subpapka muhitlarida (OSPanel) ham,
     * artisan serve'da ham, alohida domenda ham ishlaydi — chunki URL
     * joriy request'ning SCRIPT_NAME'idan quriladi, APP_URL'dan emas.
     *
     * @param  string  $path  Route path (masalan '/resume/pdf', '/resumes/5')
     * @param  array<string, mixed>  $query  Qo'shimcha query parametrlar
     */
    function resume_action_url(string $path, array $query = []): string
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

        // Yuklab olish uchun mazmun-negotsiatsiya so'rovlari (Accept: text/html)
        // index.php'ga yo'naltirilgan bo'lsa ham ishlashi uchun har doim
        // front controller shim ishlatiladi — nginx rewrite mavjud-yo'qligiga
        // bog'lanmaydi.

        if ($path !== '/' && $path !== '') {
            $query['_route'] = $path;
        }

        return $request->getSchemeAndHttpHost().$script.($query ? '?'.http_build_query($query) : '');
    }
}

if (! function_exists('resume_pdf_action')) {
    /**
     * PDF endpoint uchun forma action URL'i (resume_action_url qisqartmasi).
     */
    function resume_pdf_action(): string
    {
        return resume_action_url('/resume/pdf');
    }
}

if (! function_exists('resume_route')) {
    /**
     * Loyiha ichki havolalari uchun umumiy URL (resume_action_url qisqartmasi).
     */
    function resume_route(string $route, $id = null): string
    {
        $paths = [
            'resume.form' => '/yarat',
            'resume.index' => '/resumes',
        ];

        if (isset($paths[$route])) {
            $path = $paths[$route];
        } elseif ($route === 'resume.show') {
            $path = '/resumes/'.$id;
        } elseif ($route === 'resume.regenerate') {
            $path = '/resumes/'.$id.'/pdf';
        } elseif ($route === 'resume.destroy') {
            $path = '/resumes/'.$id;
        } else {
            $path = '/';
        }

        return resume_action_url($path);
    }
}
