@if ($comment->trashed())
    <form method="POST" action="{{ route('vgcomments.admin.comment.restore', $comment->id) }}" class="inline">
        @csrf
        @method('PUT')
        <button type="submit" class="btn">{{ __('vgcomment::admin.restore') }}</button>
    </form>
    <form method="POST" action="{{ route('vgcomments.admin.comment.force-delete', $comment->id) }}" class="inline" onsubmit="return confirm(@js(__('vgcomment::admin.force_delete_confirm')))">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-danger">{{ __('vgcomment::admin.force_delete') }}</button>
    </form>
@else
    @if ($comment->url)
        <a href="{{ $comment->url }}" target="_blank" rel="noopener" class="btn">{{ __('vgcomment::admin.view_page') }}</a>
    @endif
    <form method="POST" action="{{ route('vgcomments.admin.comment.delete', $comment->id) }}" class="inline" onsubmit="return confirm(@js(__('vgcomment::admin.delete_confirm')))">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-danger">{{ __('vgcomment::admin.delete') }}</button>
    </form>
@endif
