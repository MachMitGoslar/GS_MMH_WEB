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
        return h(
          'div',
          { class: 'k-block-type-preview' },
          this.loaded ? 'Keine Vorschau' : ''
        );
      }
      return h(
        'div',
        {
          class: 'k-block-type-preview',
          on: { dblclick: () => this.$emit('open') },
        },
        [
          h('iframe', {
            attrs: {
              srcdoc: this.html,
              sandbox: 'allow-same-origin',
              tabindex: -1,
              title: 'Vorschau',
            },
            style: {
              width: '100%',
              height: this.height + 'px',
              border: 0,
              pointerEvents: 'none',
            },
            on: { load: this.fit },
          }),
        ]
      );
    },
  };

  // Focus point of an image for one usage (field type `image-focus`). Reads
  // the image from the sibling files field (`image: <name>`) of the same
  // form, so it follows the selection live, on pages and in blocks.
  const ImageFocus = {
    props: {
      label: String,
      help: String,
      name: String,
      disabled: Boolean,
      required: Boolean,
      image: String,
      savedImage: Object,
      formData: Object,
      value: String,
    },
    computed: {
      imageUrl() {
        const selected =
          this.formData && this.image ? this.formData[this.image] : null;
        if (Array.isArray(selected)) {
          const item = selected[0];
          return item
            ? item.url || (item.image && item.image.src) || null
            : null;
        }
        return this.savedImage ? this.savedImage.url : null;
      },
      fileFocus() {
        return this.savedImage && this.savedImage.focus
          ? this.savedImage.focus
          : null;
      },
      point() {
        const source = this.value || this.fileFocus || '50% 50%';
        const parts = String(source).split(' ');
        return { x: parseFloat(parts[0]) || 50, y: parseFloat(parts[1]) || 50 };
      },
    },
    methods: {
      set(event) {
        if (this.disabled) {
          return;
        }
        const rect = event.currentTarget.getBoundingClientRect();
        const x = Math.round(((event.clientX - rect.left) / rect.width) * 100);
        const y = Math.round(((event.clientY - rect.top) / rect.height) * 100);
        const clamp = v => Math.max(0, Math.min(100, v));
        this.$emit('input', clamp(x) + '% ' + clamp(y) + '%');
      },
      reset() {
        this.$emit('input', '');
      },
    },
    render(h) {
      let body;

      if (!this.imageUrl) {
        body = h(
          'k-empty',
          { props: { icon: 'image', layout: 'cards' } },
          'Zuerst ein Bild wählen.'
        );
      } else {
        body = h('div', { class: 'mmh-image-focus' }, [
          h(
            'div',
            {
              class: 'mmh-image-focus__stage',
              style: {
                position: 'relative',
                display: 'inline-block',
                cursor: this.disabled ? 'default' : 'crosshair',
                lineHeight: 0,
              },
              on: { click: this.set },
            },
            [
              h('img', {
                attrs: { src: this.imageUrl, alt: '' },
                style: {
                  maxWidth: '100%',
                  maxHeight: '18rem',
                  borderRadius: 'var(--rounded)',
                },
              }),
              h('span', {
                style: {
                  position: 'absolute',
                  left: this.point.x + '%',
                  top: this.point.y + '%',
                  width: '1.25rem',
                  height: '1.25rem',
                  marginLeft: '-0.625rem',
                  marginTop: '-0.625rem',
                  borderRadius: '50%',
                  border: '2px solid #fff',
                  boxShadow: '0 0 0 2px rgba(0,0,0,.6)',
                  background: this.value
                    ? 'var(--color-focus)'
                    : 'rgba(255,255,255,.35)',
                  pointerEvents: 'none',
                },
              }),
            ]
          ),
          h(
            'p',
            {
              style: {
                marginTop: 'var(--spacing-2)',
                fontSize: 'var(--text-sm)',
                color: 'var(--color-text-dimmed)',
              },
            },
            [
              this.value
                ? 'Eigener Ausschnitt: ' + this.value + ' '
                : 'Fokus der Mediathek' +
                  (this.fileFocus ? ' (' + this.fileFocus + ')' : '') +
                  ' – ins Bild klicken für einen eigenen Ausschnitt. ',
              this.value && !this.disabled
                ? h(
                    'k-button',
                    {
                      props: { icon: 'undo', size: 'xs', variant: 'filled' },
                      on: { click: this.reset },
                    },
                    'Standard der Mediathek verwenden'
                  )
                : null,
            ]
          ),
        ]);
      }

      return h(
        'k-field',
        {
          props: {
            label: this.label,
            help: this.help,
            name: this.name,
            disabled: this.disabled,
            required: this.required,
            type: 'image-focus',
          },
          class: 'k-image-focus-field',
        },
        [body]
      );
    },
  };

  window.panel.plugin('gs-mmh/mmh-site', {
    fields: {
      'image-focus': ImageFocus,
    },
    blocks: {
      accordion: Preview,
      button: Preview,
      card: Preview,
      cta: Preview,
      divider: Preview,
      download: Preview,
      faq: Preview,
      testimonial: Preview,
      timeline: Preview,
    },
  });
})();
