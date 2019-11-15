import request from '@/utils/request'

export function login(data) {
  return request({
    url: '/api/admin/login',
    method: 'post',
    data
  })
}

export function getInfo(token) {
  return request({
    url: '/api/admin/info',
    method: 'get',
    params: { token }
  })
}

export function logout() {
  return request({
    url: '/api/admin/logout',
    method: 'post'
  })
}
