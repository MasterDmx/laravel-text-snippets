<?php

namespace MasterDmx\LaravelTextSnippets;

use Illuminate\Support\Collection;
use MasterDmx\LaravelTextSnippets\Contracts\HasAttributesWithSnippets;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;

class TextSnippetsReplacer
{
    private TextSnippetsManager $snippets;

    /**
     * TextSnippetsReplacer constructor.
     *
     * @param TextSnippetsManager $snippets
     */
    public function __construct(TextSnippetsManager $snippets)
    {
        $this->snippets = $snippets;
    }

    /**
     * Заменяет сниппеты в публичных аттрибуты объекта (клонирование объекта)
     *
     * @param HasAttributesWithSnippets $entity
     *
     * @return mixed
     */
    public function replaceFromPublicAttributes(HasAttributesWithSnippets $entity)
    {
        $entity = clone $entity;

        foreach($entity->getPublicAttributesWithSnippets() as $attribute => $options) {
            if (isset($entity->$attribute)) {
                if (!empty($options)) {
                    $presets = $snippets = [];

                    foreach ($options as $key) {
                        if (substr($key, 0, 8) === 'preset::') {
                            $presets[] = substr($key, 8);
                        } else {
                            $snippets[] = $key;
                        }
                    }

                    $replacer = $this->snippets;

                    if (!empty($presets)) {
                        $replacer = $replacer->presets($presets);
                    }

                    if (!empty($snippets)) {
                        $replacer = $replacer->only($snippets);
                    }

                    $entity->$attribute = $replacer->replace($entity->$attribute);
                } else {
                    $entity->$attribute = $this->snippets->replace($entity->$attribute);
                }
            }
        }

        return $entity;
    }

    /**
     * Заменяет сниппеты в публичных аттрибутах каждого элемента коллекции (клонирование коллекции и всех элементов)
     *
     * @param Collection $collection
     */
    public function replaceFromCollection(Collection $collection)
    {
        $collection = clone $collection;
        $collection = $collection->map(function ($item) {
            return $this->replaceFromPublicAttributes($item);
        });

        return $collection;
    }
}
