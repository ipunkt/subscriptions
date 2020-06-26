<?php namespace Ipunkt\Subscriptions\Plans;

use Illuminate\Support\Collection;

/**
 * Class PlanRepository
 *
 * Repository for plans
 *
 * @package Ipunkt\Subscriptions\Plans
 */
class PlanRepository
{
    /**
     * plans
     *
     * @var Plan[]|Collection
     */
    private $plans;

    /**
     * default plan
     *
     * @var null|Plan
     */
    private $defaultPlan;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->plans = new Collection();

        $this->resolvePlans($config);
    }

    /**
     * returns all plans
     *
     * @return Plan[]|Collection
     */
    public function all()
    {
        return $this->plans;
    }

    /**
     * find a plan by id
     *
     * @param string $id
     *
     * @return Plan|null
     */
    public function find($id): ?Plan
    {
        return $this->plans->first(function ($key, $value) use ($id) {
            return strtoupper($id) === $key;
        });
    }

    /**
     * returns default Plan
     *
     * @return Plan|null
     */
    public function defaultPlan(): ?Plan
    {
        return $this->defaultPlan;
    }

    /**
     * sets default Plan
     *
     * @param string $defaultPlan
     *
     * @return $this
     */
    public function setDefaultPlan($defaultPlan)
    {
        if (empty($defaultPlan)) {
            return $this;
        }

        $plan = $this->find($defaultPlan);
        if (null !== $plan) {
            $this->defaultPlan = $plan;
        }

        return $this;
    }

    /**
     * resolves all existing plans
     *
     * @param array $config
     */
    private function resolvePlans(array $config)
    {
        foreach ($config as $id => $planData) {
            $plan = Plan::createFromArray($id, $planData);

            $this->plans->put($id, $plan);
        }
    }
}
