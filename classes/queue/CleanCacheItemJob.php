<?php namespace Lovata\Toolbox\Classes\Queue;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class CleanCacheItemJob
 * @package Lovata\Toolbox\Classes\Queue
 */
class CleanCacheItemJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /* @var int $iElementID */
    private $iElementID;

    /* @var string */
    private $sClassName;

    /**
     * CleanCacheItemJob constructor
     *
     * @param int    $iElementID
     * @param string $sClassName
     */
    public function __construct($iElementID, string $sClassName)
    {
        $this->iElementID = $iElementID;
        $this->sClassName = $sClassName;
    }

    /**
     * Execute the job
     * @return bool
     */
    public function handle(): bool
    {
        if (empty($this->sClassName) || empty($this->iElementID) || !class_exists($this->sClassName)) {
            return true;
        }

        $this->sClassName::clearCache($this->iElementID);
        $this->sClassName::make($this->iElementID);

        return true;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['CleanCacheItem', $this->sClassName .':'. $this->iElementID];
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->release();
    }
}
