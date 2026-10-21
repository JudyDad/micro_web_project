<x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
<button class="button button-secondary" type="submit" formnovalidate formaction="{{ route('applications.drafts.save', ['locale' => app()->getLocale()]) }}">{{ __('applications.save_draft') }}</button>
