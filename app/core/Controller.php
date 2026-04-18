<?php
class Controller {
    protected function view(string $view, array $data = []): void {
        extract($data);
        $app = require __DIR__ . '/../config/app.php';
        $baseUrl = $app['base_url'];
        $appName = $app['app_name'];
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/' . $view . '.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
    protected function redirect(string $path): void {
        header('Location: ' . url($path));
        exit;
    }
}
