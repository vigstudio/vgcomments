<?php

namespace Vigstudio\VgComment\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Vigstudio\VgComment\Facades\CommentServiceFacade;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $comments = CommentServiceFacade::getAdmin($request->all());

        return view('vgcomment::dashboard', compact('comments'));
    }

    public function setting()
    {
        return view('vgcomment::setting');
    }

    protected function configKey()
    {
        return view('vgcomment::config-key');
    }
}
