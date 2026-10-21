<form method="POST" action="{{ route('applications.applicant-detail', ['locale' => app()->getLocale(), 'application' => $application->id, 'link' => $link->id, 'kind' => $supportKind]) }}">@csrf
    @include('applications.context', ['kind' => $supportKind])
    <input type="hidden" name="link_id" value="{{ $link->id }}">
    @if($row)<input type="hidden" name="detail_id" value="{{ $row->id }}">@endif
    @include('applications.fields', ['fields' => $support['fields'][$supportKind], 'options' => $support['options'][$supportKind]])
    @include('applications.actions')
</form>
