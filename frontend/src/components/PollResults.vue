<template>
  <div class="results stack stack-4">

    <div class="results-header">
      <h3 class="results-title">Results</h3>
      <span class="results-total">
        {{ totalVotes }} {{ totalVotes === 1 ? 'vote' : 'votes' }}
      </span>
    </div>

    <ul class="results-list" role="list">
      <li
        v-for="option in results"
        :key="option.id"
        class="result-item"
        :class="{ 'result-item--winner': option.id === winnerId }"
      >
        <div class="result-row">
          <span class="result-text">{{ option.text }}</span>
          <span class="result-stats">
            <strong>{{ option.votes }}</strong>
            <span class="result-pct">{{ option.percentage }}%</span>
          </span>
        </div>

        <!-- Progress bar -->
        <div class="result-bar-track" role="progressbar"
          :aria-valuenow="option.percentage"
          aria-valuemin="0"
          aria-valuemax="100"
          :aria-label="`${option.text}: ${option.percentage}%`"
        >
          <div
            class="result-bar-fill"
            :class="{ 'result-bar-fill--winner': option.id === winnerId }"
            :style="{ width: option.percentage + '%' }"
          />
        </div>
      </li>
    </ul>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  results: {
    type: Array,
    required: true,
  },
})

const totalVotes = computed(() =>
  props.results.reduce((sum, o) => sum + o.votes, 0)
)

const winnerId = computed(() => {
  if (totalVotes.value === 0) return null
  return props.results.reduce((best, o) =>
    o.votes > best.votes ? o : best
  ).id
})
</script>

<style scoped>
.results-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.results-title {
  font-size: 0.9375rem;
  font-weight: 700;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.results-total {
  font-size: 0.8125rem;
  color: var(--color-text-dim);
  font-weight: 500;
}

/* ── Result list ──────────────────────────────────────────── */
.results-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.result-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.result-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: var(--space-4);
}

.result-text {
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-text);
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.result-item--winner .result-text {
  background: var(--gradient-brand);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.result-stats {
  display: flex;
  align-items: baseline;
  gap: var(--space-2);
  flex-shrink: 0;
}

.result-stats strong {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text);
}

.result-pct {
  font-size: 0.8125rem;
  color: var(--color-text-dim);
  min-width: 3rem;
  text-align: right;
}

/* ── Progress bar ─────────────────────────────────────────── */
.result-bar-track {
  height: 6px;
  background: var(--color-surface-2);
  border-radius: 999px;
  overflow: hidden;
}

.result-bar-fill {
  height: 100%;
  border-radius: 999px;
  background: rgba(100, 116, 139, 0.5);
  transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.result-bar-fill--winner {
  background: var(--gradient-brand);
  box-shadow: 0 0 8px rgba(124, 58, 237, 0.5);
}
</style>
