let notifications = {};
let inputs_elements = [];


export default {
  methods: {
    showNotification ({ content, position = 'bottom-left' }) {
      // do not show notification if some input is focused
      const inputs_focused = document.querySelectorAll('input:focus');
      const textareas_focused = document.querySelectorAll('textarea:focus');
      const selects_focused = document.querySelectorAll('select:focus');
      const elements_focused = [].concat(inputs_focused).concat(textareas_focused).concat(selects_focused);

      if (elements_focused.length > 0 && ['xs', 's', 'm'].indexOf(this.$mq) !== -1) {
        return;
      }

      // hide visible notifications on input focus
      const inputs = [].slice.call(document.querySelectorAll('input'));
      const textareas = [].slice.call(document.querySelectorAll('textarea'));
      const selects = [].slice.call(document.querySelectorAll('select'));
      const elements = [].concat(inputs).concat(textareas).concat(selects);

      elements.forEach(input => {
        if (inputs_elements.indexOf(input) === -1) {
          inputs_elements.push(input);

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
