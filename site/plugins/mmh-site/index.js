// Panel preview of a block: asks the website to render the block's snippet and
// shows the result in an iframe with the site's stylesheet. One rendering for
// page and panel. Plain JS without a build and without template strings: the
// Vue compiler is off on production (panel.vue.compiler = false).
(function () {
  const Preview = {
    props: {
      content: Object,
      endpoints: Object,
      fieldset: Object,
    },
    data() {
      return { html: '', height: 40, timer: null, loaded: false };
    },
    watch: {
      content: {
        deep: true,
        handler() {
          clearTimeout(this.timer);
          this.timer = setTimeout(this.load, 400);
        },
      },
    },
    created() {
      this.load();
    },
    beforeDestroy() {
      clearTimeout(this.timer);
    },
    methods: {
      async load() {
        try {
          const response = await this.$api.post('mmh-site/block-preview', {
            type: this.fieldset.type,
            content: this.content,
            model: this.endpoints.model || '',
          });
          this.html = response.html;
        } catch (error) {
          this.html = '';
        }
        this.loaded = true;
      },
      fit(event) {
        const doc = event.target.contentDocument;
        if (doc && doc.documentElement) {
          this.height = Math.max(doc.documentElement.scrollHeight, 40);
        }
      },
    },
    render(h) {
      if (!this.html) {
        return h('div', { class: 'k-block-type-preview' }, this.loaded ? 'Keine Vorschau' : '');
      }
      return h('div', { class: 'k-block-type-preview', on: { dblclick: () => this.$emit('open') } }, [
        h('iframe', {
          attrs: { srcdoc: this.html, sandbox: 'allow-same-origin', tabindex: -1, title: 'Vorschau' },
          style: { width: '100%', height: this.height + 'px', border: 0, pointerEvents: 'none' },
          on: { load: this.fit },
        }),
      ]);
    },
  };

  window.panel.plugin('gs-mmh/mmh-site', {
    blocks: {
      card: Preview,
    },
  });
})();
