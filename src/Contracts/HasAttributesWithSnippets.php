<?php

namespace MasterDmx\LaravelTextSnippets\Contracts;

interface HasAttributesWithSnippets
{
    public function getPublicAttributesWithSnippets(): array;
}
