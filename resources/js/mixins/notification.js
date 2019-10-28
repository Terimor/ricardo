let notifications = {};
let inputs = [];


export default {
  methods: {
    showNotification ({ content, position = 'bottom-left' }) {
      // do not show notification if some input is focused
      if (document.querySelectorAll('input:focus, textarea:focus, select:focus, .iti--container').length > 0 && ['xs', 's', 'm'].indexOf(this.$mq) !== -1) {
        return;
      }

      // hide visible notifications on input focus
      document.querySelectorAll('input, textarea, select').forEach(input => {
        if (inputs.indexOf(input) === -1) {
          inputs.push(input);

          input.addEventListener('focus', () => {
            if (['xs', 's', 'm'].indexOf(this.$mq) !== -1) {
              for (const notification of Object.values(notifications)) {
                if (!notification.closed) {
                  notification.close();
                }
              }
            }
          });
        }
      });

      if (this.$notify) {
        const notification = this.$notify({
          position,
          message: content,
          dangerouslyUseHTMLString: true,
          customClass: 'recently-bought',
          onClose(self) {
            delete notifications[self.id];
          },
        });

        notifications[notification.id] = notification;
      }
    },
  }
}
