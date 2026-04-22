<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Services\VersionCheckService;

class AdminComposer
{
    protected VersionCheckService $versionCheck;

    public function __construct(VersionCheckService $versionCheck)
    {
        $this->versionCheck = $versionCheck;
    }

    public function compose(View $view)
    {
        $updateInfo = $this->versionCheck->checkForUpdates();
        $view->with('systemUpdateAvailable', $updateInfo['has_update']);
        $view->with('systemUpdateInfo', $updateInfo);
    }
}
