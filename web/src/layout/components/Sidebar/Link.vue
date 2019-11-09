<template>
  <!-- eslint-disable vue/require-component-is-->
  <component :is="Object" v-bind="linkProps(to)" @click.native="editTime">
    <slot />
  </component>
</template>

<script>
import { isExternal } from '@/utils/validate'

export default {
  props: {
    to: {
      type: String,
      required: true
    },
    refresh: {
      type: Boolean,
      required: true
    },
    query: {
      type: Object,
      default: function() {
        return {}
      },
      required: false
    }
  },
  data() {
    return {
      date: new Date().getTime()
    }
  },
  methods: {
    linkProps(url) {
      if (isExternal(url)) {
        return {
          is: 'a',
          href: url,
          target: '_blank',
          rel: 'noopener'
        }
      }
      // const lastUrl = this.refresh ? `${url}?v=${this.date}` : url
      const lastUrl = url
      return {
        is: 'router-link',
        to: {
          // 这里为了可以重新路由触发router-view
          path: lastUrl,
          // path: url,
          query: this.linkQuery(this.query)
        }
      }
    },
    linkQuery(query) {
      query.isTagsView = false
      return query
    },
    editTime() {
      // console.log('触发更改sideBar的链接版本')
      // this.date = new Date().getTime()
    }
  }
}
</script>
