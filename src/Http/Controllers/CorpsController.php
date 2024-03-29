<?php

namespace UUPT\Corp\Http\Controllers;

use Slowlyo\OwlAdmin\Controllers\AdminController;

class CorpsController extends AdminController
{
    public function index()
    {
        $page = $this->basePage()->body('组织架构组件');

        return $this->response()->success($page);
    }
}
