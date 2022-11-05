<?php

class Author
{
    public function __construct(
        private string $name,
        private string $open_library_uri
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOpenLibraryUri(): string
    {
        return $this->open_library_uri;
    }
}