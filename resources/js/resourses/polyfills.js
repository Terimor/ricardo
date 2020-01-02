if (!Object.values) {
  Object.values = o => Object.keys(o).map(e => o[e]);
}

if (!Object.entries) {
  Object.entries = o => Object.keys(o).map(e => [e, o[e]]);
}
