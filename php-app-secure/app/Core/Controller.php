<?php
// app/Core/Controller.php
namespace App\Core;

/**
 * Base controller providing view rendering functionality.
 */
class Controller {

    /**
     * Render a view within the main layout.
     *
     * Extracts provided data into variables and includes header, view template, and footer.
     *
     * @param string $path  View template path relative to the Views directory (e.g., 'home/index').
     * @param array  $data  Associative array of variables to extract for use in the view.
     *
     * @return void
     */
    protected function view($path, $data = []) {
        extract($data);
        require __DIR__ . "/../Views/layout/header.php";
        require __DIR__ . "/../Views/{$path}.php";
        require __DIR__ . "/../Views/layout/footer.php";
    }
}
