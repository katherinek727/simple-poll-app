import { createStore } from 'vuex'
import poll from './modules/poll'

export default createStore({
  modules: {
    poll,
  },
})
