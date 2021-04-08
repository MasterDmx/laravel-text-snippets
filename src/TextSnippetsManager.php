<?php

namespace MasterDmx\LaravelTextSnippets;

use MasterDmx\LaravelTextSnippets\Exceptions\UndefinedPresetAliasException;
use MasterDmx\LaravelTextSnippets\Exceptions\UndefinedSnippetAliasException;
use MasterDmx\LaravelTextSnippets\Snippets\Snippet;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;

/**
 * Управляющий сниппетами
 *
 * @package MasterDmx\LaravelTextSnippets
 */
class TextSnippetsManager
{
    /**
     * Сниппеты
     *
     * @var array
     */
    private $snippets = [];

    /**
     * Пресеты
     *
     * @var array
     */
    private $presets = [];

    /**
     * TextSnippetsManager constructor.
     */
    public function __construct()
    {
        $this->snippets = config('text_snippets.snippets', []);
        $this->presets = config('text_snippets.presets', []);
    }

    /**
     * Заменяет все пресеты
     *
     * @param $content
     *
     * @return string
     */
    public function replace($content): string
    {
        foreach ($this->snippets as $alias => $class) {
            $content = $this->replaceSnippet($alias, $content);
        }

        return $content;
    }

    /**
     * Применить замену для пресета с определенным обозначением
     *
     * @param        $alias
     * @param string $content
     *
     * @return string
     */
    public function replaceSnippet($alias, string $content): string
    {
        if (!isset($this->snippets[$alias])) {
            throw new UndefinedSnippetAliasException('Undefined snippet alias: ' . $alias);
        }

        /** @var Snippet $snippet */
        $snippet = app($this->snippets[$alias]);

        return $snippet->replace($content);
    }

    /**
     * Оставляет в менеджере только сниппеты определенных ID (новый инстанс)
     *
     * @param array $ids
     *
     * @return TextSnippetsManager
     */
    public function only(array $ids): self
    {
        $manager = clone $this;

        foreach($manager->snippets as $alias => $class) {
            if (!in_array($alias, $ids)) {
                unset($manager->snippets[$alias]);
            }
        }

        return $manager;
    }

    /**
     * Исключить определенные ID сниппетов (новый инстанс)
     *
     * @param array $ids
     *
     * @return $this
     */
    public function exclude(array $ids): self
    {
        $manager = clone $this;

        foreach($manager->snippets as $alias => $class) {
            if (in_array($alias, $ids)) {
                unset($manager->snippets[$alias]);
            }
        }

        return $manager;
    }

    /**
     * Применить пресеты
     *
     * @param array $presets
     *
     * @return $this
     */
    public function presets(array $presets): self
    {
        $ids = [];

        foreach ($presets as $preset){
            foreach ($this->getSnippetsIdsByPreset($preset) as $id) {
                if (!in_array($id, $ids)) {
                    $ids[] = $id;
                }
            }
        }

        return $this->only($ids);
    }

    /**
     * Получить ID сниппетов, доступные для пресета
     *
     * @param string $alias
     *
     * @return array
     */
    private function getSnippetsIdsByPreset(string $alias): array
    {
        if (!isset($this->presets[$alias])) {
            throw new UndefinedPresetAliasException('Undefined preset alias: ' . $alias);
        }

        $ids = [];

        foreach ($this->presets[$alias] as $id) {
            if (substr($id, 0, 8) === 'preset::') {
                foreach ($this->getSnippetsIdsByPreset(substr($id, 8)) as $subId) {
                    $ids[] = $subId;
                }
            } else {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
