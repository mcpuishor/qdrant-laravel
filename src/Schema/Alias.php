<?php
namespace Mcpuishor\QdrantLaravel\Schema;

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\Exceptions\CommandException;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class Alias
{
    private array $actions = [];

    public function __construct(
        protected QdrantTransport $transport,
        private ?string $collectionName = null,
    ){}

    public function add(string $alias, string $collection): self
    {
        $this->actions[] = [
            "create_alias" => [
                "collection_name" => $collection,
                "alias_name" => $alias,
            ]
        ];

        return $this;
    }

    public function delete(string $alias): self
    {
        $this->actions[] = [
            "delete_alias" => [
                "alias_name" => $alias,
            ]
        ];

        return $this;
    }

    public function switch(string $alias, string $to): self
    {
        return $this
            ->delete($alias)
            ->add($alias, $to);
    }

    public function apply(): bool
    {
        if (empty($this->actions)) {
            throw new CommandException("No actions to apply");
        }

        $response =  $this->transport->post(
            uri: "",
            options: [
                "json" => [
                    "actions" => $this->actions,
                    ]
            ]
        );

        return $response->result();
    }

    public function get(): Collection
    {
        $response = $this->transport
            ->baseUri($this->collectionName ? "/collections/{$this->collectionName}" : "")
            ->get("/aliases");

        return collect($response->result()["aliases"] ?? []);
    }
}
