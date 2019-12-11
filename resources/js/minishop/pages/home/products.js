export default {

  data() {
    return {
      search: null,
    };
  },


  created() {
    this.search_init();
  },


  methods: {

    search_init() {
      this.search = this.searchParams.get('search') || null;

      if (this.search) {
        setTimeout(() => this.scroll_to_ref('products'), 1000);
      }
    },

    search_click() {
      if (this.search) {
        this.goto('/?search=' + this.search);
      } else {
        this.goto('/', ['search']);
      }

      return false;
    },

  },

};
