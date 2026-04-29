import axios from 'axios'

const http = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
  },
})

export default {
  /**
   * POST /api/polls
   * @param {{ title: string, options: string[] }} payload
   */
  createPoll(payload) {
    return http.post('/polls', payload)
  },

  /**
   * GET /api/polls/{shortCode}
   * @param {string} shortCode
   */
  getPoll(shortCode) {
    return http.get(`/polls/${shortCode}`)
  },

  /**
   * POST /api/polls/{shortCode}/vote
   * @param {string} shortCode
   * @param {number} optionId
   */
  castVote(shortCode, optionId) {
    return http.post(`/polls/${shortCode}/vote`, { option_id: optionId })
  },
}
