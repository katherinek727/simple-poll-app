<template>
  <div class="poll-view">

    <!-- Loading skeleton -->
    <template v-if="loading && !poll">
      <div class="card skeleton-card">
        <div class="skeleton skeleton-title" />
        <div class="skeleton skeleton-option" />
        <div class="skeleton skeleton-option" />
        <div class="skeleton skeleton-option" />
      </div>
    </template>

    <!-- Error state -->
    <template v-else-if="error && !poll">
      <div class="card stack stack-6 error-card">
        <div class="error-icon" aria-hidden="true">⚠</div>
        <div class="stack stack-2">
          <h2>Something went wrong</h2>
          <p>{{ error }}</p>
        </div>
        <RouterLink to="/create" class="btn btn-ghost">
          ← Create a new poll
        </RouterLink>
      </div>
    </template>

    <!-- Poll loaded -->
    <template v-else-if="poll">

      <!-- Header -->
      <div class="page-header stack stack-2">
        <span class="badge badge-primary">Poll</span>
        <h1>{{ poll.title }}</h1>
      </div>

      <!-- Card: voting form OR results -->
      <div class="card stack stack-6">

        <!-- Already voted banner -->
        <div v-if="hasVoted" class="voted-banner" role="status">
          <span class="voted-icon" aria-hidden="true">✓</span>
          <span>Your vote has been recorded.</span>
        </div>

        <!-- API error (e.g. network failure mid-vote) -->
        <div v-if="error" class="alert alert-error" role="alert">
          {{ error }}
        </div>

        <!-- Voting form -->
        <Transition name="fade" mode="out-in">
          <form
            v-if="!hasVoted"
            key="form"
            novalidate
            class="stack stack-6"
            @submit.prevent="handleVote"
          >
            <fieldset class="options-fieldset" :disabled="loading">
              <legend class="sr-only">Choose an answer</legend>

              <div class="options-list">
                <label
                  v-for="option in poll.options"
                  :key="option.id"
                  class="option-label"
                  :class="{ 'option-label--selected': selectedId === option.id }"
                >
                  <input
                    v-model="selectedId"
                    type="radio"
                    :value="option.id"
                    class="option-radio"
                    :aria-label="option.text"
                  />
                  <span class="option-indicator" aria-hidden="true" />
                  <span class="option-text">{{ option.text }}</span>
                </label>
              </div>
            </fieldset>

            <span v-if="voteError" class="form-error" role="alert">
              {{ voteError }}
            </span>

            <button
              type="submit"
              class="btn btn-primary btn-block"
              :disabled="loading || !selectedId"
            >
              <span v-if="loading" class="spinner" aria-hidden="true" />
              <span>{{ loading ? 'Submitting…' : 'Vote' }}</span>
            </button>
          </form>

          <!-- Results panel -->
          <div v-else key="results">
            <PollResults :results="results" />
          </div>
        </Transition>

      </div>

      <!-- Share section -->
      <div class="share-section stack stack-3">
        <p class="share-label">Share this poll</p>
        <div class="share-row">
          <input
            ref="shareInput"
            type="text"
            class="form-input share-input"
            :value="shareUrl"
            readonly
            aria-label="Poll share URL"
          />
          <button
            type="button"
            class="btn btn-ghost copy-btn"
            :class="{ 'copy-btn--copied': copied }"
            @click="copyLink"
          >
            {{ copied ? '✓ Copied' : 'Copy' }}
          </button>
        </div>
      </div>

    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { RouterLink } from 'vue-router'
import PollResults from '@/components/PollResults.vue'

const props = defineProps({
  code: {
    type: String,
    required: true,
  },
})

const store = useStore()

// ── Store bindings ─────────────────────────────────────────
const poll     = computed(() => store.getters['poll/currentPoll'])
const results  = computed(() => store.getters['poll/results'])
const hasVoted = computed(() => store.getters['poll/hasVoted'])
const loading  = computed(() => store.getters['poll/loading'])
const error    = computed(() => store.getters['poll/error'])

// ── Local state ────────────────────────────────────────────
const selectedId = ref(null)
const voteError  = ref(null)
const copied     = ref(false)

const shareUrl = computed(() =>
  `${window.location.origin}/poll/${props.code}`
)

// ── Lifecycle ──────────────────────────────────────────────
onMounted(() => {
  store.dispatch('poll/fetchPoll', props.code)
})

// ── Vote ───────────────────────────────────────────────────
async function handleVote() {
  if (!selectedId.value) {
    voteError.value = 'Please select an option before voting.'
    return
  }

  voteError.value = null

  try {
    await store.dispatch('poll/castVote', {
      shortCode: props.code,
      optionId:  selectedId.value,
    })
  } catch {
    // error computed handles display
  }
}

// ── Copy link ──────────────────────────────────────────────
async function copyLink() {
  try {
    await navigator.clipboard.writeText(shareUrl.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    // Fallback: select the input text
    shareInput.value?.select()
  }
}

const shareInput = ref(null)
</script>

<style scoped>
.poll-view {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

/* ── Page header ──────────────────────────────────────────── */
.page-header {
  text-align: center;
  padding-block: var(--space-4);
}

/* ── Voted banner ─────────────────────────────────────────── */
.voted-banner {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.25);
  border-radius: var(--radius-sm);
  font-size: 0.9375rem;
  font-weight: 500;
  color: #6ee7b7;
}

.voted-icon {
  font-size: 1rem;
  color: var(--color-success);
}

/* ── Options fieldset ─────────────────────────────────────── */
.options-fieldset {
  border: none;
  padding: 0;
  margin: 0;
}

.options-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.option-label {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  padding: var(--space-4) var(--space-5);
  background: var(--color-surface-2);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition:
    border-color var(--transition-fast),
    background var(--transition-fast),
    box-shadow var(--transition-fast);
  user-select: none;
}

.option-label:hover {
  border-color: var(--color-border-hover);
  background: rgba(255, 255, 255, 0.03);
}

.option-label--selected {
  border-color: var(--color-primary);
  background: rgba(124, 58, 237, 0.08);
  box-shadow: 0 0 0 1px var(--color-primary);
}

/* Hide native radio */
.option-radio {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

/* Custom radio indicator */
.option-indicator {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid var(--color-border-hover);
  flex-shrink: 0;
  transition:
    border-color var(--transition-fast),
    background var(--transition-fast),
    box-shadow var(--transition-fast);
  position: relative;
}

.option-label--selected .option-indicator {
  border-color: var(--color-primary);
  background: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.25);
}

.option-label--selected .option-indicator::after {
  content: '';
  position: absolute;
  inset: 3px;
  border-radius: 50%;
  background: #fff;
}

.option-text {
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-text);
  flex: 1;
}

/* ── Error card ───────────────────────────────────────────── */
.error-card {
  text-align: center;
  align-items: center;
}

.error-icon {
  font-size: 2.5rem;
  color: var(--color-danger);
}

/* ── Share section ────────────────────────────────────────── */
.share-section {
  padding: var(--space-5) var(--space-6);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
}

.share-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-dim);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.share-row {
  display: flex;
  gap: var(--space-3);
}

.share-input {
  font-family: var(--font-mono);
  font-size: 0.8125rem;
  color: var(--color-text-muted);
  cursor: default;
}

.copy-btn {
  flex-shrink: 0;
  font-size: 0.875rem;
  padding-inline: var(--space-5);
  transition:
    color var(--transition-fast),
    border-color var(--transition-fast),
    background var(--transition-fast);
}

.copy-btn--copied {
  color: var(--color-success);
  border-color: rgba(16, 185, 129, 0.4);
  background: rgba(16, 185, 129, 0.08);
}

/* ── Skeleton loader ──────────────────────────────────────── */
.skeleton-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.skeleton {
  background: linear-gradient(
    90deg,
    var(--color-surface-2) 25%,
    rgba(255,255,255,0.04) 50%,
    var(--color-surface-2) 75%
  );
  background-size: 200% 100%;
  border-radius: var(--radius-sm);
  animation: shimmer 1.4s infinite;
}

.skeleton-title  { height: 2rem; width: 70%; }
.skeleton-option { height: 3.25rem; }

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Fade transition ──────────────────────────────────────── */
.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-normal), transform var(--transition-normal);
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ── Screen reader only ───────────────────────────────────── */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0,0,0,0);
  white-space: nowrap;
  border: 0;
}
</style>
