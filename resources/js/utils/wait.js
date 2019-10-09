export default function wait(condition, callback, timeout = 100) {
  function iteration() {
    if (condition()) {
      clearInterval(interval);
      callback();
    }
  }

  const interval = setInterval(iteration, timeout);
  iteration();
};
