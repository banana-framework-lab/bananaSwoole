import { login, logout, getInfo } from '@/api/user'
import { setId, removeId } from '@/utils/auth'
import router, { resetRouter } from '@/router'

const state = {
  id: '',
  name: '',
  avatar: '',
  introduction: '',
  roles: [],
  permission: []
}

const mutations = {
  SET_ID: (state, token) => {
    state.id = token
  },
  SET_INTRODUCTION: (state, introduction) => {
    state.introduction = introduction
  },
  SET_NAME: (state, name) => {
    state.name = name
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar
  },
  SET_ROLES: (state, roles) => {
    state.roles = roles
  },
  SET_PERMISSION: (state, permission) => {
    state.permission = permission
  }
}

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      login({ username: username.trim(), password: password }).then(response => {
        const { data } = response
        commit('SET_ID', data.id)
        setId(data.id)
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // get user info
  getInfo({ commit, state }) {
    if (state.permission.length <= 0) {
      return new Promise((resolve, reject) => {
        getInfo(state.token).then(response => {
          const { data } = response

          if (!data) {
            reject('Verification failed, please Login again.')
          }

          const { name, avatar, introduction, permission } = data

          // // roles must be a non-empty array
          // if (!roles || roles.length <= 0) {
          //   reject('getInfo: roles must be a non-null array!')
          // }

          // commit('SET_ROLES', roles)
          commit('SET_NAME', name)
          commit('SET_AVATAR', avatar)
          commit('SET_INTRODUCTION', introduction)
          commit('SET_PERMISSION', permission || [])
          resolve(data)
        }).catch(error => {
          reject(error)
        })
      })
    } else {
      return {
        roles: state.roles,
        name: state.name,
        avatar: state.avatar,
        permission: state.permission,
        introduction: state.introduction
      }
    }
  },

  // user logout
  logout({ commit, state, dispatch }) {
    return new Promise((resolve, reject) => {
      logout(state.token).then(() => {
        commit('SET_ID', '')
        commit('SET_ROLES', [])
        removeId()
        resetRouter()

        // reset visited views and cached views
        // to fixed https://github.com/PanJiaChen/vue-element-admin/issues/2485
        dispatch('tagsView/delAllViews', null, { root: true })

        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      commit('SET_ID', '')
      commit('SET_ROLES', [])
      removeId()
      resolve()
    })
  },

  // dynamically modify permissions
  changeRoles({ commit, dispatch }, role) {
    return new Promise(async resolve => {
      const token = role + '-token'

      commit('SET_TOKEN', token)
      setId(token)

      const { roles } = await dispatch('getInfo')

      resetRouter()

      // generate accessible routes map based on roles
      const accessRoutes = await dispatch('permission/generateRoutes', roles, { root: true })

      // dynamically add accessible routes
      router.addRoutes(accessRoutes)

      // reset visited views and cached views
      dispatch('tagsView/delAllViews', null, { root: true })

      resolve()
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
