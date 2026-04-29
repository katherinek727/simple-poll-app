import api from '@/api'

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------

const state = () => ({
  // The current poll object: { id, title, short_code, options, has_voted }
  currentPoll: null,

  // Vote results array: [{ id, text, votes, percentage }, ...]
  results: null,

  // Whether the current user (IP) has already voted on this poll
  hasVoted: false,

  // UI loading / error states
  loading: false,
  error: null,
})

// ---------------------------------------------------------------------------
// Mutations  (synchronous state changes — single source of truth)
// ---------------------------------------------------------------------------

const mutations = {
  SET_LOADING(state, value) {
    state.loading = value
  },

  SET_ERROR(state, message) {
    state.error = message
  },

  SET_POLL(state, poll) {
    state.currentPoll = poll
    state.hasVoted    = poll.has_voted ?? false
    // If the server already returned results (user had voted before), store them
    state.results     = poll.results ?? null
  },

  SET_RESULTS(state, results) {
    state.results  = results
    state.hasVoted = true
  },

  RESET(state) {
    state.currentPoll = null
    state.results     = null
    state.hasVoted    = false
    state.loading     = false
    state.error       = null
  },
}

// ---------------------------------------------------------------------------
// Actions  (async operations — call API then commit mutations)
// ---------------------------------------------------------------------------

const actions = {
  /**
   * Create a new poll.
   * On success, resolves with the short_code string.
   */
  async createPoll({ commit }, { title, options }) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const { data } = await api.createPoll({ title, options })
      return data.short_code
    } catch (err) {
      const message = err.response?.data?.message ?? 'Failed to create poll.'
      commit('SET_ERROR', message)
      throw err
    } finally {
      commit('SET_LOADING', false)
    }
  },

  /**
   * Fetch a poll by its short code.
   * If the user already voted, results are included in the response.
   */
  async fetchPoll({ commit }, shortCode) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)
    commit('RESET')

    try {
      const { data } = await api.getPoll(shortCode)
      commit('SET_POLL', data)
    } catch (err) {
      const message = err.response?.status === 404
        ? 'Poll not found.'
        : 'Failed to load poll.'
      commit('SET_ERROR', message)
      throw err
    } finally {
      commit('SET_LOADING', false)
    }
  },

  /**
   * Cast a vote on the current poll.
   * On success or 409 (already voted), commits results to state.
   */
  async castVote({ commit }, { shortCode, optionId }) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const { data } = await api.castVote(shortCode, optionId)
      commit('SET_RESULTS', data.results)
    } catch (err) {
      // 409 means already voted — the server still returns results
      if (err.response?.status === 409 && err.response.data?.results) {
        commit('SET_RESULTS', err.response.data.results)
        return
      }
      const message = err.response?.data?.message ?? 'Failed to cast vote.'
      commit('SET_ERROR', message)
      throw err
    } finally {
      commit('SET_LOADING', false)
    }
  },
}

// ---------------------------------------------------------------------------
// Getters
// ---------------------------------------------------------------------------

const getters = {
  currentPoll:  (state) => state.currentPoll,
  results:      (state) => state.results,
  hasVoted:     (state) => state.hasVoted,
  loading:      (state) => state.loading,
  error:        (state) => state.error,
  pollOptions:  (state) => state.currentPoll?.options ?? [],
}

// ---------------------------------------------------------------------------

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
}
