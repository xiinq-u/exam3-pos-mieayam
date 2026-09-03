<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/react');

Route::view('/react/{path?}', 'react')
    ->where('path', '.*');
