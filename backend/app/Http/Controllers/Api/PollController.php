<?php

namespace App\Http\Controllers\Api;

use App\Domain\Poll\Factories\PollFactory;
use App\Domain\Poll\Services\PollService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePollRequest;
use App\Http\Requests\Api\CastVoteRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function __construct(
        private readonly PollFactory $pollFactory,
        private readonly PollService $pollService,
    ) {}

    // -------------------------------------------------------------------------
    // POST /api/polls
    // -------------------------------------------------------------------------

    /**
     * Create a new poll and return its short code.
     */
    public function store(CreatePollRequest $request): JsonResponse
    {
        $poll = $this->pollFactory->create(
            $request->validated('title'),
            $request->validated('options'),
        );

        return response()->json([
            'short_code' => $poll->short_code,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // GET /api/polls/{short_code}
    // -------------------------------------------------------------------------

    /**
     * Return poll data (title + options) and whether the current IP has voted.
     * If the IP has already voted, results are included so the frontend can
     * render them immediately without a separate request.
     */
    public function show(Request $request, string $shortCode): JsonResponse
    {
        try {
            $poll = $this->pollService->findByShortCode($shortCode);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $ip       = $request->ip();
        $hasVoted = $this->pollService->hasVoted($poll, $ip);

        $payload = [
            'id'         => $poll->id,
            'title'      => $poll->title,
            'short_code' => $poll->short_code,
            'created_at' => $poll->created_at->toIso8601String(),
            'has_voted'  => $hasVoted,
            'options'    => $poll->options->map(fn($o) => [
                'id'   => $o->id,
                'text' => $o->text,
            ])->values(),
        ];

        if ($hasVoted) {
            $payload['results'] = $this->pollService->getResults($poll)->values();
        }

        return response()->json($payload);
    }

    // -------------------------------------------------------------------------
    // POST /api/polls/{short_code}/vote
    // -------------------------------------------------------------------------

    /**
     * Cast a vote on a poll option.
     * Returns 409 if the IP has already voted, 422 if the option is invalid,
     * or 200 with full results on success.
     */
    public function vote(CastVoteRequest $request, string $shortCode): JsonResponse
    {
        try {
            $poll = $this->pollService->findByShortCode($shortCode);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $ip       = $request->ip();
        $optionId = $request->validated('option_id');

        if ($this->pollService->hasVoted($poll, $ip)) {
            return response()->json([
                'message' => 'You have already voted on this poll.',
                'results' => $this->pollService->getResults($poll)->values(),
            ], 409);
        }

        try {
            $this->pollService->castVote($poll, $optionId, $ip);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => 'Invalid option for this poll.'], 422);
        }

        return response()->json([
            'message' => 'Vote cast successfully.',
            'results' => $this->pollService->getResults($poll)->values(),
        ]);
    }
}
