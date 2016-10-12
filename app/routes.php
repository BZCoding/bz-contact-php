<?php
// Routes

// Render ToS page
$app->get('/terms', 'BZContact\Controller\PagesController:terms')->setName('terms');

// Render Privacy page
$app->get('/privacy', 'BZContact\Controller\PagesController:privacy')->setName('privacy');

// Render main page
$app->get('/', 'BZContact\Controller\PagesController:index')->setName('index');

// Process form entry
$app->post('/', 'BZContact\Controller\FormController')->setName('index');
