<?php

namespace App\Domain\Poll\Contracts;

interface ShortCodeGeneratorInterface
{
    /**
     * Generate a unique short code for a poll URL.
     *
     * Implementations must guarantee the returned code:
     *  - is exactly 6 characters long
     *  - contains only alphanumeric characters, excluding visually
     *    ambiguous characters: 0, O, l, I
     *  - does not already exist in the polls table
     *
     * @return string A unique 6-character short code.
     */
    public function generate(): string;
}
