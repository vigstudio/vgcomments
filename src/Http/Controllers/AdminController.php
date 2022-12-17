<?php

namespace Vigstudio\VgComment\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
use Vigstudio\VgComment\Repositories\Interface\SettingInterface;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $comments = CommentServiceFacade::getAdmin($request->all());

        return view('vgcomment::dashboard', compact('comments'));
    }

    public function setting()
    {
        $config = Arr::except(Config::get('vgcomment'), ['table', 'connection', 'moderation_users']);

        return view('vgcomment::setting', compact('config'));
    }

    public function updateSetting(Request $request, SettingInterface $settingRepository)
    {
        $settingRepository->set($request);

        return back()->with('success', 'Update success');
    }
}
