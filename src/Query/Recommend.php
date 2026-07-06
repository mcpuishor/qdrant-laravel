<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\Enums\AverageVectorStrategy;
use Mcpuishor\QdrantLaravel\Exceptions\SearchException;
use Mcpuishor\QdrantLaravel\PointsCollection;

class Recommend extends Search
{
    private array $positives = [];
    private array $negatives = [];
    private ?AverageVectorStrategy $strategy = null;

    public function positive(array|string|int $ids): self
    {
        $this->positives = array_merge($this->positives, (array) $ids);
        return $this;
    }

    public function negative(array|string|int $ids): self
    {
        $this->negatives = array_merge($this->negatives, (array) $ids);
        return $this;
    }

    public function strategy(AverageVectorStrategy $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function getSearchPayload(): array
    {
        if (!$this->positives && !$this->negatives) {
            throw new SearchException('Recommend requires at least one positive or negative example.');
        }

        $this->query = [
            'recommend' => [
                'positive' => $this->positives,
                'negative' => $this->negatives,
                'strategy' => ($this->strategy ?? AverageVectorStrategy::default())->value,
            ],
        ];

        return parent::getSearchPayload();
    }

    public function get(): PointsCollection
    {
        return $this->raw($this->getSearchPayload());
    }
}
