<?php
namespace Mcpuishor\QdrantLaravel\Query;
use Mcpuishor\QdrantLaravel\Enums\FieldType;
use Mcpuishor\QdrantLaravel\Enums\TokenizerType;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class Indexes
{
    private $on_disk = false;
    private $parameterized = false;

    public function __construct(
        private QdrantTransport $transport,
        private  string          $collection,
    ){
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/index");
    }

    public function onDisk(): self
    {
        $this->on_disk = true;
        return $this;
    }

    public function onMemory(): self
    {
        $this->on_disk = false;
        return $this;
    }

    public function parameterized():self
    {
        $this->parameterized = true;
        return $this;
    }
    public function add(string $field_name, FieldType $type): bool
    {
        $fieldSchema = [
            "type" => $type->value,
            "on_disk" => $this->on_disk,
        ];

        if ($type == FieldType::INTEGER && $this->parameterized) {
            $fieldSchema = array_merge(
                            $fieldSchema,
                            config("qdrant-laravel.index_settings.parametrized_integer_index", [])
                        );
        }

        return $this->transport->put(
            uri: "",
            options: [
                    'field_name' => $field_name,
                    'field_schema' => $fieldSchema,
            ]
        )->isOk();
    }

    public function fulltext(string $field_name, TokenizerType $tokenizerType = TokenizerType::WORD): bool
    {
        $fulltextSettings = config("qdrant-laravel.index_settings.fulltext_index");

        $fieldSchema = [
            "type" => FieldType::TEXT->value,
            "tokenizer" => $tokenizerType->value,
        ];
        $fieldSchema = array_merge($fieldSchema, $fulltextSettings);

        return $this->transport->put(
            uri: "",
            options: [
                'field_name' => $field_name,
                "field_schema" => $fieldSchema
            ]
        )->isOk();
    }

    public function delete(string $field_name): bool
    {
        return $this->transport->delete(
            uri: "/{$field_name}"
        )->isOk();
    }
}
