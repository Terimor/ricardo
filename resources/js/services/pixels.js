const printed_pixels = [];


export function print_pixels(type) {
  if (js_data.pixels) {
    js_data.pixels
      .filter(pixel => pixel.type === type)
      .filter(pixel => printed_pixels.indexOf(pixel) === -1)
      .forEach(pixel => {
        printed_pixels.push(pixel);
        print_pixel(pixel);
      });
  }
}


function print_pixel(pixel) {
  const element = document.createElement('div');
  element.innerHTML = pixel.code;

  function replace_script(element) {
    if (element.tagName === 'SCRIPT') {
      const script = document.createElement('script');
      script.innerHTML = element.innerHTML;
      element.parentNode.replaceChild(script, element);
    } else {
      [...element.children].forEach(replace_script);
    }
  }

  [...element.children].forEach(child => {
    document.body.appendChild(child);
    replace_script(child);
  });
}


if (location.pathname.startsWith('/checkout')) {
  if (document.readyState !== 'complete') {
    document.addEventListener('readystatechange', () =>  {
      if (document.readyState === 'complete') {
        print_pixels('checkout');
      }
    });
  } else {
    print_pixels('checkout');
  }
}
