<?php

namespace App\Domain\Poll\Factories;

use App\Domain\Poll\Contracts\ShortCodeGeneratorInterface;
use App\Models\Option;
use App\Models\Poll;
use Illuminate\Support\Facades\DB;

class PollFactory
{
    public function __construct(
        private readonly ShortCodeGeneratorInterface $codeGenerator,
    ) {}

    /**
     * Create a new poll with its options as a single atomic operation.
     *
     * @param  string   $title       Poll title (5–100 characters).
     * @param  string[] $optionTexts Array of option texts (2–4 items, each 1–50 chars).
     * @return Poll                  The persisted Poll model with options loaded.
     *
     * @throws \RuntimeException If a unique short code cannot be generated.
     * @throws \Throwable        If the database transaction fails.
     */
    public function create(string $title, array $optionTexts): Poll
    {
        return DB::transaction(function () use ($title, $optionTexts) {
            $poll = Poll::create([
                'title'      => $title,
                'short_code' => $this->codeGenerator->generate(),
            ]);

            $options = array_map(
                fn(string $text) => new Option(['text' => $text]),
                $optionTexts,
            );

            $poll->options()->saveMany($options);

            // Return the poll with options already loaded — avoids an
            // extra query in the controller after creation.
            return $poll->load('options');
        });
    }
}
