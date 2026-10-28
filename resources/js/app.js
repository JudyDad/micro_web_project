// Alpine is provided by Livewire; do not initialize a second instance.

// Keep an entered value selected even if Livewire/Alpine is still loading.
document.addEventListener('input', (event) => {
    if (!(event.target instanceof HTMLElement) || !event.target.matches('[data-null-value]')) return;
    const checkbox = event.target.closest('[data-null-toggle]')?.querySelector('input[type="checkbox"]');
    if (checkbox instanceof HTMLInputElement && checkbox.checked) {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

// Searchable pickers (x-ui.search-select): the server is searched as the user types; no fixed option list.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('searchSelect', (config) => ({
        url: config.url, params: config.params || {}, navigate: Boolean(config.navigate),
        selectedId: String(config.value ?? ''), selectedLabel: config.label || '', query: config.label || '',
        results: [], open: false, loading: false, failed: false, active: -1, controller: null, lastQuery: null,
        show() { this.open = true; if (this.lastQuery === null) this.search(); },
        close() { this.open = false; this.active = -1; if (this.selectedId !== '' && this.query !== this.selectedLabel) this.query = this.selectedLabel; },
        clear() { this.selectedId = ''; this.selectedLabel = ''; this.query = ''; this.search(); },
        async search() {
            const term = this.query.trim();
            if (this.selectedId !== '' && term !== this.selectedLabel) { this.selectedId = ''; this.selectedLabel = ''; }
            this.open = true;
            if (term === this.lastQuery && !this.failed) return;
            this.controller?.abort();
            this.controller = new AbortController();
            this.loading = true; this.failed = false; this.lastQuery = term;
            const query = new URLSearchParams({ ...this.params, q: term });
            try {
                const response = await fetch(`${this.url}?${query}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: this.controller.signal });
                if (!response.ok) throw new Error(String(response.status));
                this.results = await response.json();
                this.active = this.results.length ? 0 : -1;
            } catch (error) {
                if (error.name === 'AbortError') return;
                this.results = []; this.failed = true;
            } finally {
                if (!this.controller.signal.aborted) this.loading = false;
            }
        },
        move(step) {
            if (!this.open) { this.show(); return; }
            if (!this.results.length) return;
            this.active = (this.active + step + this.results.length) % this.results.length;
            this.$nextTick(() => document.getElementById(`${this.$refs.input.id}-option-${this.active}`)?.scrollIntoView({ block: 'nearest' }));
        },
        pick(event) {
            if (!this.open || this.active < 0 || !this.results[this.active]) return;
            event.preventDefault();
            this.choose(this.results[this.active]);
        },
        choose(item) {
            if (this.navigate && item.url) { window.location.assign(item.url); return; }
            this.selectedId = String(item.id); this.selectedLabel = item.label; this.query = item.label;
            this.open = false; this.active = -1;
        },
    }));
});
