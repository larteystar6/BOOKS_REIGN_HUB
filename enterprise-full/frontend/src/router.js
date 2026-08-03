import { createRouter, createWebHistory } from 'vue-router'
import ProductEditor from './components/ProductEditor.vue'
import POS from './components/POS.vue'

const routes = [
  { path: '/', component: POS },
  { path: '/product', component: ProductEditor }
]

export default createRouter({ history: createWebHistory(), routes })
