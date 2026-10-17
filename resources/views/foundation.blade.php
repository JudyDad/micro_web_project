<x-layouts.app>
    <div class="page-heading"><div><p class="eyebrow">{{ __('ui.phase') }}</p><h1>{{ __('ui.foundation') }}</h1><p class="page-description">{{ __('ui.intro') }}</p></div><span class="version-tag">{{ __('ui.design_preview') }}</span></div>
    <x-ui.alert :title="__('ui.synthetic_title')">{{ __('ui.synthetic_notice') }}</x-ui.alert>
    <div class="stat-grid">
        <div class="stat-card"><span>{{ __('ui.example_projects') }}</span><strong>02</strong><small>{{ __('ui.example_projects_note') }}</small></div>
        <div class="stat-card"><span>{{ __('ui.language_support') }}</span><strong>AR / EN</strong><small>{{ __('ui.language_note') }}</small></div>
        <div class="stat-card"><span>{{ __('ui.foundation_status') }}</span><strong class="stat-text">{{ __('ui.local_only') }}</strong><small>{{ __('ui.status_note') }}</small></div>
    </div>
    <x-ui.card id="projects" tabindex="-1" :title="__('ui.project_preview')" :eyebrow="__('ui.identity_preview')" x-data="{ city: 'all' }">
        <div class="table-toolbar"><p>{{ __('ui.identity_note') }}</p><div class="filter-field"><label for="city-filter">{{ __('ui.city_filter') }}</label><select id="city-filter" class="input" x-model="city"><option value="all">{{ __('ui.all_examples') }}</option><option value="aleppo">{{ __('ui.aleppo') }}</option><option value="damascus">{{ __('ui.damascus') }}</option></select></div></div>
        <x-ui.table :caption="__('ui.synthetic_projects')">
            <thead><tr><th scope="col">{{ __('ui.project_number') }}</th><th scope="col">{{ __('ui.city') }}</th><th scope="col">{{ __('ui.project') }}</th><th scope="col">{{ __('ui.source_identity') }}</th><th scope="col">{{ __('ui.status') }}</th></tr></thead>
            <tbody>
                @foreach(['aleppo' => 'sewing', 'damascus' => 'workshop'] as $city => $project)
                    <tr x-show="city === 'all' || city === '{{ $city }}'"><td><strong class="project-number"><bdi>1042</bdi></strong></td><td>{{ __('ui.'.$city) }}</td><td>{{ __('ui.'.$project) }}</td><td><code dir="ltr">demo:{{ $city }}:1042</code></td><td><span class="pill">{{ __('ui.synthetic') }}</span></td></tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <p class="card-footnote">{{ __('ui.filter_notice') }}</p>
    </x-ui.card>
    <div class="component-grid" id="components" tabindex="-1">
        <x-ui.card :title="__('ui.components')" :eyebrow="__('ui.form_patterns')">
            <div class="component-body">
                <x-ui.input id="example-title" :label="__('ui.sample_label')" :placeholder="__('ui.sample_placeholder')" :hint="__('ui.input_hint')" autocomplete="off" />
                <x-ui.input id="example-error" :label="__('ui.validation_label')" :error="__('ui.validation_error')" :placeholder="__('ui.required_example')" autocomplete="off" />
                <div class="button-row"><x-ui.button @click="document.getElementById('preview-dialog').showModal()">{{ __('ui.open_dialog') }}</x-ui.button><x-ui.button variant="secondary" disabled>{{ __('ui.save_unavailable') }}</x-ui.button></div>
                <div class="state-demo"><strong>{{ __('ui.state_demo') }}</strong><div class="loading-example"><span class="loading-dots" aria-hidden="true">•••</span>{{ __('ui.loading_example') }}</div><div class="empty-example"><strong>{{ __('ui.empty_example') }}</strong><p>{{ __('ui.empty_note') }}</p></div></div>
            </div>
        </x-ui.card>
        <x-ui.card :title="__('ui.next_steps')" :eyebrow="__('ui.delivery_boundary')">
            <ol class="next-steps"><li><span>01</span><div><strong>{{ __('ui.schema_title') }}</strong><p>{{ __('ui.schema_note') }}</p></div></li><li><span>02</span><div><strong>{{ __('ui.access_title') }}</strong><p>{{ __('ui.access_note') }}</p></div></li><li><span>03</span><div><strong>{{ __('ui.assets_title') }}</strong><p>{{ __('ui.assets_note') }}</p></div></li></ol>
        </x-ui.card>
    </div>
    <x-ui.dialog id="preview-dialog" :title="__('ui.dialog_title')"><p>{{ __('ui.dialog_body') }}</p><x-ui.alert :title="__('ui.no_changes')">{{ __('ui.no_changes_note') }}</x-ui.alert></x-ui.dialog>
</x-layouts.app>
