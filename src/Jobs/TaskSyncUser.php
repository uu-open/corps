<?php

namespace UUPT\Corp\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use UUPT\Corp\Services\SyncService;
use UUPT\Corp\Services\SyncServiceInterface;

class TaskSyncUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private SyncServiceInterface $service;
    private string $userId;
    private string $type = 'dingtalk';

    /**
     * Create a new job instance.
     */
    public function __construct(string $userId, $type = 'dingtalk')
    {
        //
        $this->userId = $userId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SyncService::make($this->type)->getUserInfo($this->userId);
    }
}
