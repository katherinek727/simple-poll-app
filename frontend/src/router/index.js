import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    redirect: '/create',
  },
  {
    path: '/create',
    name: 'CreatePoll',
    component: () => import('@/views/CreatePollView.vue'),
    meta: { title: 'Create a Poll' },
  },
  {
    path: '/poll/:code',
    name: 'PollPage',
    component: () => import('@/views/PollView.vue'),
    props: true,
    meta: { title: 'Vote' },
  },
  {
    // Catch-all — redirect unknown paths back to create
    path: '/:pathMatch(.*)*',
    redirect: '/create',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// Update the document title on each navigation
router.afterEach((to) => {
  document.title = to.meta?.title
    ? `${to.meta.title} — Simple Poll`
    : 'Simple Poll'
})

export default router
