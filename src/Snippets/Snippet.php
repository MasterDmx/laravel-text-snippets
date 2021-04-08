<?php

namespace MasterDmx\LaravelTextSnippets\Snippets;

interface Snippet
{
    /**
     * Заменить
     *
     * @param string $content
     *
     * @return string
     */
    public function replace(string $content): string;
}
