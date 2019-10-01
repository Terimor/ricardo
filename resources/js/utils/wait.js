export default function wait(condition, callback, timeout = 100) {
  let interval = null;

  function iteration() {
    if (!condition()) {
      return false;
    }

    if (interval) {
      clearInterval(interval);
    }

    callback();

    return true;
  }

  if (!iteration()) {
    interval = setInterval(iteration, timeout);
  }
};
