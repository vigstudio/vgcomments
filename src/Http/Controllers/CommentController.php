<?php

namespace Vigstudio\VgComment\Http\Controllers;

use Illuminate\Routing\Controller;
use Vigstudio\VgComment\Http\Traits\ThrottlesPosts;

class CommentController extends Controller
{
    use ThrottlesPosts;
}
