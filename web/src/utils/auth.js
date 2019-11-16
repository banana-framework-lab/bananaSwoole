import Cookies from 'js-cookie'

export function getId() {
  return Cookies.get('uid')
}

export function setId(uid) {
  return Cookies.set('uid', uid)
}

export function removeId() {
  return Cookies.remove('uid')
}
