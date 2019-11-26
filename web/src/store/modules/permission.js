import { homeRoute, asyncRoutes, constantRoutes } from '@/router'

/**
 * Use meta.role to determine if the current user has permission
 * @param roles
 * @param route
 */
function hasPermission(permission, route) {
  const index = permission.findIndex(v => v.path === route.path)
  return index
}

/**
 * Filter asynchronous routing tables by recursion
 * @param routes asyncRoutes
 * @param roles
 */
export function filterAsyncRoutes(routes, permission) {
  const res = []
  routes.forEach(route => {
    const tmp = { ...route }
    const index = hasPermission(permission, tmp)
    if (index >= 0) {
      if (tmp.children) {
        tmp.children = filterAsyncRoutes(tmp.children, permission[index].children)
      }
      res.push(tmp)
    }
  })
  return res
}

const state = {
  routes: [],
  addRoutes: []
}

const mutations = {
  SET_ROUTES: (state, routes) => {
    state.addRoutes = routes
    state.routes = constantRoutes.concat(routes)
  }
}

const actions = {
  generateRoutes({ commit }, roles) {
    return new Promise(resolve => {
      let accessedRoutes = []
      if (roles.permission.length > 0) {
        accessedRoutes = filterAsyncRoutes(asyncRoutes, roles.permission)
      }
      if (accessedRoutes.length !== 0) {
        homeRoute[0].redirect = accessedRoutes[0].path
      }
      commit('SET_ROUTES', accessedRoutes)
      accessedRoutes = homeRoute.concat(accessedRoutes)
      console.log(accessedRoutes)
      resolve(accessedRoutes)
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
