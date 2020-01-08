<style type="text/css">

  html.hidden {
    display: none;
  }

</style>


<script type="text/javascript">

  var js_deps = {

    deps: {},

    ready: function(name) {
      if (this.media === 'none') {
        this.media = 'all';
      }

      if (js_deps.deps[name]) {
        js_deps.deps[name].ready = true;

        js_deps.deps[name].deps.forEach(function(dep) {
          if (dep.require > 0) {
            dep.require--;

            if (dep.require === 0) {
              dep.callback();
            }
          }
        });
      } else {
        js_deps.deps[name] = {
          ready: true,
          deps: [],
        };
      }
    },

    wait: function(names, callback) {
      var dep = {
        require: names.length,
        callback: callback,
      };

      names.forEach(function(name) {
        if (!js_deps.deps[name] || !js_deps.deps[name].ready) {
          if (!js_deps.deps[name]) {
            js_deps.deps[name] = {
              ready: false,
              deps: [dep],
            };
          } else {
            js_deps.deps[name].deps.push(dep);
          }
        } else {
          dep.require--;
        }
      });

      if (dep.require === 0) {
        dep.callback();
      }
    },

    wait_for: function(condition, callback, timeout) {
      function iteration() {
        if (condition()) {
          clearInterval(interval);
          callback();
        }
      }

      var interval = setInterval(iteration, timeout || 100);
      iteration();
    },

    show: function(names) {
      js_deps.wait(names, function() {
        document.documentElement.classList.remove('hidden');
      });
    },

  };

</script>
