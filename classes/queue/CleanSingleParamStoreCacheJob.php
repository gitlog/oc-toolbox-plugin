<?php namespace Lovata\Toolbox\Classes\Queue;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Lovata\Toolbox\Classes\Store\AbstractStoreWithoutParam;
use Lovata\Toolbox\Classes\Store\AbstractStoreWithParam;

/**
 * Class CleanSingleParamStoreCacheJob
 * @package Lovata\Toolbox\Classes\Queue
 */
class CleanSingleParamStoreCacheJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /* @var AbstractStoreWithParam|AbstractStoreWithoutParam */
    private $obListStore;

    /* @var string */
    private $sClassName;
    /* @var string */
    private $sValue;
    /* @var string */
    private $sOriginalValue;

    /**
     * CleanCacheListJob constructor
     * @param string $sClassName
     * @param string $sValue
     * @param string $sOriginalValue
     */
    public function __construct(string $sClassName, $sValue = null, $sOriginalValue = null)
    {
        $this->sClassName = $sClassName;
        $this->sValue = $sValue;
        $this->sOriginalValue = $sOriginalValue;
    }

    /**
     * Execute the job
     * @return bool
     */
    public function handle(): bool
    {
        if (!class_exists($this->sClassName)) {
            return true;
        }

        $this->obListStore = $this->sClassName::instance();
        if ($this->obListStore instanceof AbstractStoreWithoutParam) {
            $this->obListStore->clear();
            $this->obListStore->get();
        } elseif ($this->obListStore instanceof AbstractStoreWithParam) {
            $this->obListStore->clear($this->sValue);
            $this->obListStore->clear($this->sOriginalValue);

            $this->obListStore->get($this->sValue);
            $this->obListStore->get($this->sOriginalValue);
        }

        return true;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        if ($this->obListStore instanceof AbstractStoreWithoutParam) {
            return ['CleanStoreCache', 'CleanSingleParamStoreCache', $this->sClassName];
        } elseif ($this->obListStore instanceof AbstractStoreWithParam) {
            return [
                'CleanStoreCache',
                'CleanSingleParamStoreCache',
                'originaValue:'. $this->sOriginalValue,
                'value:'. $this->sValue,
            ];
        }
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
