<template>
  <div class="create-view">

    <!-- Page heading -->
    <div class="page-header stack stack-2">
      <span class="badge badge-primary">New Poll</span>
      <h1>Create a <span class="text-gradient">poll</span></h1>
      <p>Fill in a question and up to 4 answer options, then share the link.</p>
    </div>

    <!-- Form card -->
    <div class="card stack stack-8">

      <!-- Global API error -->
      <div v-if="apiError" class="alert alert-error" role="alert">
        {{ apiError }}
      </div>

      <form novalidate @submit.prevent="handleSubmit">

        <!-- Title field -->
        <div class="form-group">
          <label for="poll-title" class="form-label">Question</label>
          <input
            id="poll-title"
            v-model.trim="title"
            type="text"
            class="form-input"
            :class="{ 'is-error': errors.title }"
            placeholder="e.g. What is your favourite programming language?"
            maxlength="100"
            autocomplete="off"
            @input="errors.title = null"
          />
          <span v-if="errors.title" class="form-error" role="alert">
            {{ errors.title }}
          </span>
          <span class="char-count" :class="{ 'char-count--warn': title.length > 85 }">
            {{ title.length }} / 100
          </span>
        </div>

        <!-- Options -->
        <div class="form-group options-group">
          <div class="options-header">
            <span class="form-label">Answer options</span>
            <span class="form-label options-hint">{{ options.length }} / 4</span>
          </div>

          <TransitionGroup name="option-list" tag="div" class="options-list">
            <div
              v-for="(option, index) in options"
              :key="option.id"
              class="option-row"
            >
              <span class="option-index" aria-hidden="true">{{ index + 1 }}</span>
              <input
                :id="`option-${index}`"
                v-model.trim="option.text"
                type="text"
                class="form-input"
                :class="{ 'is-error': errors.options?.[index] }"
                :placeholder="`Option ${index + 1}`"
                maxlength="50"
                :aria-label="`Option ${index + 1}`"
                @input="clearOptionError(index)"
              />
              <button
                v-if="options.length > 2"
                type="button"
                class="option-remove"
                :aria-label="`Remove option ${index + 1}`"
                @click="removeOption(index)"
              >
                ✕
              </button>
            </div>
          </TransitionGroup>

          <!-- Per-option errors -->
          <template v-for="(option, index) in options" :key="`err-${option.id}`">
            <span
              v-if="errors.options?.[index]"
              class="form-error"
              role="alert"
            >
              Option {{ index + 1 }}: {{ errors.options[index] }}
            </span>
          </template>

          <span v-if="errors.optionsCount" class="form-error" role="alert">
            {{ errors.optionsCount }}
          </span>

          <!-- Add option button -->
          <button
            v-if="options.length < 4"
            type="button"
            class="btn btn-ghost add-option-btn"
            @click="addOption"
          >
            <span aria-hidden="true">＋</span> Add option
          </button>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="btn btn-primary btn-block"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner" aria-hidden="true" />
          <span>{{ loading ? 'Creating…' : 'Create Poll' }}</span>
        </button>

      </form>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'

const router = useRouter()
const store  = useStore()

// ── State ──────────────────────────────────────────────────
const title   = ref('')
const options = ref([
  { id: 1, text: '' },
  { id: 2, text: '' },
])
const errors  = ref({})
let   nextId  = 3

const loading  = computed(() => store.getters['poll/loading'])
const apiError = computed(() => store.getters['poll/error'])

// ── Option management ──────────────────────────────────────
function addOption() {
  if (options.value.length < 4) {
    options.value.push({ id: nextId++, text: '' })
  }
}

function removeOption(index) {
  if (options.value.length > 2) {
    options.value.splice(index, 1)
    if (errors.value.options) {
      errors.value.options.splice(index, 1)
    }
  }
}

function clearOptionError(index) {
  if (errors.value.options?.[index]) {
    errors.value.options[index] = null
  }
}

// ── Validation ─────────────────────────────────────────────
function validate() {
  const e = {}

  if (!title.value) {
    e.title = 'A question is required.'
  } else if (title.value.length < 5) {
    e.title = 'The question must be at least 5 characters.'
  } else if (title.value.length > 100) {
    e.title = 'The question may not exceed 100 characters.'
  }

  const optionErrors = options.value.map((o) => {
    if (!o.text) return 'This option cannot be empty.'
    if (o.text.length > 50) return 'Option may not exceed 50 characters.'
    return null
  })

  if (optionErrors.some(Boolean)) {
    e.options = optionErrors
  }

  if (options.value.length < 2) {
    e.optionsCount = 'At least 2 options are required.'
  }

  errors.value = e
  return Object.keys(e).length === 0
}

// ── Submit ─────────────────────────────────────────────────
async function handleSubmit() {
  if (!validate()) return

  try {
    const shortCode = await store.dispatch('poll/createPoll', {
      title:   title.value,
      options: options.value.map((o) => o.text),
    })
    router.push({ name: 'PollPage', params: { code: shortCode } })
  } catch {
    // apiError computed property handles display
  }
}
</script>

<style scoped>
.create-view {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

/* ── Page header ──────────────────────────────────────────── */
.page-header {
  text-align: center;
  padding-block: var(--space-4);
}

/* ── Char counter ─────────────────────────────────────────── */
.char-count {
  font-size: 0.75rem;
  color: var(--color-text-dim);
  text-align: right;
  transition: color var(--transition-fast);
}
.char-count--warn { color: #f59e0b; }

/* ── Options ──────────────────────────────────────────────── */
.options-group { gap: var(--space-3); }

.options-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.options-hint { color: var(--color-text-dim); }

.options-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.option-row {
  display: grid;
  grid-template-columns: 1.5rem 1fr auto;
  align-items: center;
  gap: var(--space-3);
}

.option-index {
  font-size: 0.8125rem;
  font-weight: 700;
  color: var(--color-text-dim);
  text-align: center;
}

.option-remove {
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  color: var(--color-text-dim);
  cursor: pointer;
  font-size: 0.75rem;
  height: 2.25rem;
  width: 2.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition:
    color var(--transition-fast),
    border-color var(--transition-fast),
    background var(--transition-fast);
  flex-shrink: 0;
}
.option-remove:hover {
  color: var(--color-danger);
  border-color: var(--color-danger);
  background: rgba(239,68,68,0.08);
}

.add-option-btn {
  align-self: flex-start;
  font-size: 0.875rem;
  padding: var(--space-2) var(--space-4);
}

/* ── Option list transition ───────────────────────────────── */
.option-list-enter-active,
.option-list-leave-active {
  transition: all var(--transition-normal);
}
.option-list-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
.option-list-leave-to {
  opacity: 0;
  transform: translateX(12px);
}
.option-list-leave-active { position: absolute; width: 100%; }
</style>
