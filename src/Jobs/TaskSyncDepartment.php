<?php

namespace UUPT\Corp\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use UUPT\Corp\Services\SyncService;

class TaskSyncDepartment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $type ='dingtalk';
    private int $deptId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $deptId = 1, $type = 'dingtalk')
    {
        //
        $this->deptId = $deptId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SyncService::make($this->type)->syncDepartment($this->deptId);
    }
}
