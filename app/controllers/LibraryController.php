<?php
// app/controllers/LibraryController.php

class LibraryController extends Controller
{
    public function index(): void
    {
        $this->render('library/index', [
            'pageTitle'    => 'ARRR Studio — Library',
            'activePage'   => 'library',
            'navVariant'   => 'app',
            'extraStyles'  => ['partials/styles-library'],
            'extraScripts' => ['assets/library.js'],
        ]);
    }
}