<?php
// app/core/Controller.php

class Controller
{
    public function render(string $view, array $data = [], string $layoutName = 'main')
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("Vista no encontrada: " . $viewFile);
        }

        extract($data, EXTR_SKIP);

        // Cargar $base_url si tu layout no lo trae
        if (!isset($base_url)) {
            $cfgPath = __DIR__ . '/../../config.php';
            if (file_exists($cfgPath)) {
                $cfg = require $cfgPath;
                if (is_array($cfg) && isset($cfg['app']['base_url'])) {
                    $base_url = $cfg['app']['base_url'];
                }
            }
        }

        $layout = __DIR__ . '/../views/layouts/' . $layoutName . '.php';

        if (file_exists($layout)) {
            require $layout; // el layout debe incluir $viewFile
        } else {
            require $viewFile;
        }
    }
}