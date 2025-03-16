<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\Traits\Macroable;
use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\Enums\FilterVerbs;

class QdrantQueryBuilder
{
    use Macroable;

    protected QdrantClient $client;
    protected string $collection;
    protected array $filters = [];
    protected ?int $limit = null;
    protected array $sort = [];
    protected ?array $vector = null;

    public function __construct(QdrantClient $client, string $collection)
    {
        $this->client = $client;
        $this->collection = $collection;
    }

    public function getPoints(int|string|array $ids, bool $withPayload = true, bool $withVector=false): array
    {
       if (is_array($ids)) {
           return $this->getPointsById($ids, $withPayload, $withVector);
       }

       return $this->find($ids);
    }

    public function getPointsById(array $ids, bool $withPayload = true, bool $withVector=false): array
    {
        return $this->client
            ->request(
                method: 'POST',
                uri: "/collections/{$this->collection}/points",
                options: [
                    'json' => [
                        'ids' => $ids,
                        'with_payload' => $withPayload,
                        'with_vector' => $withVector,
                    ],
                ]
            );
    }

    public function find(int $id): array
    {
        return $this->client
            ->request(
                method: 'GET',
                uri: "/collections/{$this->collection}/points/{$id}"
            );
    }

    public function delete(array $ids): array
    {
        return $this->client
            ->request(
                method: 'POST',
                uri: "/collections/{$this->collection}/points/delete",
                options: [
                    'json' => [
                        'points' => $ids,
                    ],
                ]
            );
    }

    public function filter(): self
    {
        $this->filters = [];
        return $this;
    }

    public function must(callable|string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        if (is_callable($key)) {
            return $this->addCallableFilter(FilterVerbs::MUST, $key);
        }

        return  $this->addSimpleFilter(FilterVerbs::MUST, $key, $condition, $value);
    }

    public function mustNot(callable|string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        if (is_callable($key)) {
            return $this->addCallableFilter(FilterVerbs::MUST_NOT, $key);
        }

        return  $this->addSimpleFilter(FilterVerbs::MUST_NOT, $key, $condition, $value);
    }

    public function should(callable|string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        if (is_callable($key)) {
            return $this->addCallableFilter(FilterVerbs::SHOULD, $key);
        }

        return  $this->addSimpleFilter(FilterVerbs::SHOULD, $key, $condition, $value);
    }

    public function minShould(string $key, ?FilterConditions $condition = null, mixed $value =  null, int $min_count = 1): self
    {
        $this->addSimpleFilter(FilterVerbs::MIN_SHOULD, $key, $condition, $value);

        $this->filters[FilterVerbs::MIN_SHOULD->value]['min_count'] = $min_count;

        return $this;
    }

    private function addCallableFilter(FilterVerbs $verb, callable $callback): self
    {
        $this->filters[$verb->value] = $callback(new QdrantQueryBuilder($this->client, $this->collection))->getFilters();
        return $this;
    }

    private function addSimpleFilter(FilterVerbs $verb, string $key, FilterConditions $condition, mixed $value = null): self
    {
        $this->filters[$verb->value][] = [
            'key' => $key,
            $condition->value => [
                'value' => $value,
            ]
        ];

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
