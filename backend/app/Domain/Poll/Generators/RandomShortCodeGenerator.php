<?php

namespace App\Domain\Poll\Generators;

use App\Domain\Poll\Contracts\ShortCodeGeneratorInterface;
use App\Models\Poll;
use Illuminate\Support\Facades\DB;

class RandomShortCodeGenerator implements ShortCodeGeneratorInterface
{
    /**
     * Alphanumeric alphabet with visually ambiguous characters removed.
     * Excluded: 0 (zero), O (uppercase o), l (lowercase L), I (uppercase i).
     */
    private const ALPHABET = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789';

    private const CODE_LENGTH = 6;

    /**
     * Maximum attempts before giving up — prevents infinite loops
     * in the astronomically unlikely event of repeated collisions.
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * Generate a cryptographically random, collision-free short code.
     *
     * Uses random_int() (CSPRNG) for each character selection and
     * checks uniqueness against the database before returning.
     *
     * @throws \RuntimeException If a unique code cannot be generated
     *                           within the maximum number of attempts.
     */
    public function generate(): string
    {
        $alphabetLength = strlen(self::ALPHABET);

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = '';

            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
            }

            if (! $this->codeExists($code)) {
                return $code;
            }
        }

        throw new \RuntimeException(
            'Failed to generate a unique short code after ' . self::MAX_ATTEMPTS . ' attempts.'
        );
    }

    /**
     * Check whether a given code is already taken in the polls table.
     */
    private function codeExists(string $code): bool
    {
        return DB::table('polls')
            ->where('short_code', $code)
            ->exists();
    }
}
