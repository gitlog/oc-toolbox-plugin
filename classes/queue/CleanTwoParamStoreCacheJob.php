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
 * Class CleanTwoParamStoreCacheJob
 * @package App\Jobs\Model
 */
class CleanTwoParamStoreCacheJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /* @var AbstractStoreWithTwoParam */
    private $obListStore;

    /* @var string */
    private $sClassName;
    /* @var string */
    private $sValue;
    /* @var string */
    private $sOriginalValue;
    /* @var string */
    private $sAdditionalValue;
    /* @var string */
    private $sAdditionalOriginalValue;

    /**
     * CleanCacheListJob constructor
     * @param string $sClassName
     * @param string $sValue
     * @param string $sOriginalValue
     * @param string $sAdditionalValue
     * @param string $sAdditionalOriginalValue
     */
    public function __construct(
        string $sClassName,
        $sValue = null,
        $sOriginalValue = null,
        $sAdditionalValue = null,
        $sAdditionalOriginalValue = null
    ) {
        $this->sClassName = $sClassName;
        $this->sValue = $sValue;
        $this->sOriginalValue = $sOriginalValue;
        $this->sAdditionalValue = $sAdditionalValue;
        $this->sAdditionalOriginalValue = $sAdditionalOriginalValue;
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

        //Clear cache
        $this->obListStore->clear($this->sValue);
        $this->obListStore->clear($this->sValue, $this->sAdditionalValue);
        $this->obListStore->clear($this->sValue, $this->sAdditionalOriginalValue);

        $this->obListStore->clear($this->sOriginalValue);
        $this->obListStore->clear($this->sOriginalValue, $this->sAdditionalValue);
        $this->obListStore->clear($this->sOriginalValue, $this->sAdditionalOriginalValue);

        //Generate new cache
        $this->obListStore->get($this->sValue);
        $this->obListStore->get($this->sValue, $this->sAdditionalValue);
        $this->obListStore->get($this->sValue, $this->sAdditionalOriginalValue);

        $this->obListStore->get($this->sOriginalValue);
        $this->obListStore->get($this->sOriginalValue, $this->sAdditionalValue);
        $this->obListStore->get($this->sOriginalValue, $this->sAdditionalOriginalValue);

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
            return ['CleanStoreCache', 'CleanTwoParamStoreCache', $this->sClassName];
        } elseif ($this->obListStore instanceof AbstractStoreWithParam) {
            return [
                'CleanStoreCache',
                'CleanTwoParamStoreCache',
                'additionalOriginalValue'. $this->sAdditionalOriginalValue,
                'additionalValue'. $this->sAdditionalValue,
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
