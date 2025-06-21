<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\Enums\AverageVectorStrategy;
use Mcpuishor\QdrantLaravel\Exceptions\SearchException;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Recommend extends Search
{
    use HasFilters;

    private array $positives = [];
    private array $negatives = [];
    private ?AverageVectorStrategy $strategy = null;
    private ?string $using = null;

    public function positive(array|string|int $ids): self
    {
        $this->positives = array_merge($this->positives, (array)$ids);
        return $this;
    }

    public function negative(array|string|int $ids): self
    {
        $this->negatives = array_merge($this->negatives, (array)$ids);
        return $this;
    }

    public function strategy(AverageVectorStrategy $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function get(): array
    {
        if ($this->positives) {
            $this->query['positive'] = $this->positives;
        }

        if ($this->negatives) {
            $this->query['negative'] = $this->negatives;
        }

        $this->query['strategy'] = $this->strategy->value ?? AverageVectorStrategy::default()->value;

        $this->query = [
            'recommend' => $this->query,
        ];

        if ($this->getFilters()) {
            $searchPayload['filter'] = $this->getFilters();
        }

        return parent::get();
    }
}
