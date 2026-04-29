<?php

namespace App\Domain\Poll\Services;

use App\Models\Option;
use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PollService
{
    /**
     * Find a poll by its short code.
     *
     * @throws ModelNotFoundException If no poll with the given code exists.
     */
    public function findByShortCode(string $shortCode): Poll
    {
        return Poll::with('options')
            ->where('short_code', $shortCode)
            ->firstOrFail();
    }

    /**
     * Determine whether the given IP address has already voted on this poll.
     */
    public function hasVoted(Poll $poll, string $ipAddress): bool
    {
        return Vote::where('poll_id', $poll->id)
            ->where('ip_address', $ipAddress)
            ->exists();
    }

    /**
     * Cast a vote for the given option on the given poll.
     *
     * Validates that the option belongs to the poll before inserting,
     * preventing cross-poll vote injection.
     *
     * @throws \InvalidArgumentException If the option does not belong to the poll.
     * @throws \RuntimeException         If the IP has already voted (duplicate).
     */
    public function castVote(Poll $poll, int $optionId, string $ipAddress): void
    {
        // Ensure the option actually belongs to this poll
        $belongs = $poll->options->contains('id', $optionId);

        if (! $belongs) {
            throw new \InvalidArgumentException(
                "Option [{$optionId}] does not belong to poll [{$poll->id}]."
            );
        }

        if ($this->hasVoted($poll, $ipAddress)) {
            throw new \RuntimeException(
                "IP address [{$ipAddress}] has already voted on poll [{$poll->id}]."
            );
        }

        Vote::create([
            'poll_id'    => $poll->id,
            'option_id'  => $optionId,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Aggregate vote counts for every option in the poll.
     *
     * Returns a collection of arrays, each containing:
     *   - id         (int)    option ID
     *   - text       (string) option label
     *   - votes      (int)    number of votes cast for this option
     *   - percentage (float)  share of total votes, rounded to 1 decimal place
     *
     * @return Collection<int, array{id: int, text: string, votes: int, percentage: float}>
     */
    public function getResults(Poll $poll): Collection
    {
        $counts = DB::table('votes')
            ->select('option_id', DB::raw('COUNT(*) as total'))
            ->where('poll_id', $poll->id)
            ->groupBy('option_id')
            ->pluck('total', 'option_id');

        $totalVotes = $counts->sum();

        return $poll->options->map(function (Option $option) use ($counts, $totalVotes) {
            $votes = (int) ($counts->get($option->id, 0));

            return [
                'id'         => $option->id,
                'text'       => $option->text,
                'votes'      => $votes,
                'percentage' => $totalVotes > 0
                    ? round(($votes / $totalVotes) * 100, 1)
                    : 0.0,
            ];
        });
    }
}
