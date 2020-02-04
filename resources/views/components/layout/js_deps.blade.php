<style type="text/css">

  html.hidden {
    display: none;
  }

</style>


<script type="text/javascript">

  window.js_deps = {

    deps: {},

    add_style(name, url) {
      if (!js_deps.deps[name]) {
        var link = document.createElement('link');
        link.onload = js_deps.ready.call(link, name);
        link.rel = 'stylesheet';
        link.media = 'none';
        link.href = url;

        document.head.append(script);
      }
    },

    add_script(name, url) {
      if (!js_deps.deps[name]) {
        var script = document.createElement('script');
        script.onload = js_deps.ready(name);
        script.async = true;
        script.src = url;

        document.head.append(script);
      }
    },

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
