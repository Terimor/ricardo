export function getRandomInt (min, max) {
  min = Math.ceil(min)
  max = Math.floor(max)
  return Math.floor(Math.random() * (max - min + 1)) + min
}

export function debounce(func, wait, immediate) {
  let timeout;
  return function() {
    const context = this, args = arguments;
    const later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
};

export function fade(type, ms, el, withoutDeleting) {
  return new Promise((resolve => {
    let isIn = type === 'in',
      opacity = isIn ? 0 : 1,
      interval = 20,
      duration = ms,
      gap = interval / duration;

    if(isIn) {
      el.style.opacity = opacity;
    }

    function func() {
      opacity = isIn ? opacity + gap : opacity - gap;
      el.style.opacity = opacity;

      if(opacity <= 0 && !withoutDeleting) {
        el.style.display = 'none'
      }
      if(opacity <= 0 || opacity >= 1) {
        window.clearInterval(fading);
        resolve()
      }
    }

    const fading = window.setInterval(func, interval);
  }))
}

export function scrollTo(selector) {
  this.$nextTick(() => {
    let element = null;

    try {
      element = document.querySelector(selector);
    }
    catch (err) {

    }

    if (element && element.scrollIntoView) {
      element.scrollIntoView();
    }
  })
}
