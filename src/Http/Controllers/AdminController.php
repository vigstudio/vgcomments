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
    public function __construct()
    {
        $this->middleware('vgcomment-moderate');
    }

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
        // dd($request->all());
        $request->validate([
            'prefix' => 'required|string',
            'allow_guests' => 'required',
            'gravatar' => 'required',
            'gravatar_default' => 'required|string',
            'min_length' => 'required|integer',
            'max_length' => 'required|integer',
            'throttle_max_rate' => 'required|integer',
            'throttle_per_minutes' => 'required|integer',
            'moderation' => 'required',
            'moderation_keys' => 'array',
            'moderation_keys.*' => 'required|string',
            'blacklist_keys' => 'array',
            'blacklist_keys.*' => 'required|string',
            'censor' => 'required',
            'censors_text' => 'array',
            'censors_text.*' => 'required|string',
            'max_links' => 'required|integer',
            'duplicates_check' => 'required',
            'report_status' => 'required|string',
            'max_reports' => 'required|integer',
            'disk_filesystem' => 'required|string',
            'upload_rules' => 'required|array',
            'upload_rules.*' => 'required|string',
            'upload_rules_max' => 'required|integer',
            'user_column_name' => 'required|string',
            'user_column_email' => 'required|string',
            'user_column_avatar' => 'required|string',
            'user_column_avatar_url' => 'required|string',
            'nsfw' => 'required',
            'nsfw_api_user' => 'string',
            'nsfw_api_key' => 'string',
            'recaptcha' => 'required',
            'recaptcha_key' => 'string',
            'recaptcha_secret' => 'string',
        ]);

        $settingRepository->set($request);

        return back()->with('success', 'Update success');
    }
}
