export default {
  methods: {
    showNotification ({ content, position = 'bottom-left' }) {
      this.$notify({
        message: content,
        dangerouslyUseHTMLString: true,
        customClass: 'recently-bought',
        position
      })
    }
  }
}
