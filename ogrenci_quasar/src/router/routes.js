const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: 'ogrenciler', component: () => import('pages/OgrencilerPage.vue') },
      { path: 'egitmenler', component: () => import('pages/EgitmenlerPage.vue') },
      { path: 'dersler', component: () => import('pages/DerslerPage.vue') },
      { path: 'notlar', component: () => import('pages/NotlarPage.vue') },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
]

export default routes
