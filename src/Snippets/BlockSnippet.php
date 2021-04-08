<?php

namespace MasterDmx\LaravelTextSnippets\Snippets;

use Illuminate\Support\Str;

abstract class BlockSnippet implements Snippet
{
    /**
     * Результат замены
     *
     * @param string     $slot
     * @param array|null $options
     *
     * @return string
     */
    abstract protected function result(string $slot, array $options = null): string;

    /**
     * Название тега
     *
     * @return string
     */
    abstract protected function getTag(): string;

    /**
     * Замена тега на результат
     *
     * @param string $content
     *
     * @return string|string[]|null
     */
    public function replace(string $content): string
    {
        if (!$this->isDetected($content)) {
            return $content;
        }

        $closure = function ($matches) {
            $matches[1] = $matches[1] ?? '';

            if (!empty($matches[1])) {
                parse_str(str_replace(' :', '&', preg_replace("/ {2,}/", " ", $matches[1])), $options);

                foreach ($options ?? [] as $key => $value) {
                    $options[$key] = trim($value);
                }
            }

            $slot = $matches[2] ?? null;

            return $this->result($slot, $options ?? []);
        };

        return preg_replace_callback($this->getPattern(), $closure, $content);
    }

    /**
     * Получить паттерн замены
     *
     * @return string
     */
    protected function getPattern(): string
    {
        return '|\[' . $this->getTag() . '(.*?)\](.*?)\[\/' . $this->getTag() . '\]|';
    }

    /**
     * Проверка на присутствие тега в тексте
     *
     * @param string $content
     *
     * @return bool
     */
    protected function isDetected(string $content): bool
    {
        return Str::contains($content, '[/' . $this->getTag() . ']');
    }
}
