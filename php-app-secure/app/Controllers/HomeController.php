<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Controller responsible for rendering the home page of the ticket system.
 */
class HomeController extends Controller {

    /**
     * Display the main landing page.
     *
     * Renders the 'home/index' view and passes:
     *   - title: the page title shown in the browser/tab
     *   - welcome: a welcome message displayed on the page
     *
     * @return void
     */
    public function index() {
        $this->view('home/index', [
            'title' => 'Main — Ticket System',
            'welcome' => 'Welcome to Tickety!'
        ]);
    }
}
