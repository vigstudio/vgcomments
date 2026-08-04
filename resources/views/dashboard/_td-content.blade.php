<div class="mt-3 flex flex-wrap gap-1">
    @unless ($comment->trashed())
        @if ($comment->status !== \Vigstudio\VgComment\Models\Comment::STATUS_APPROVED)
            <form method="POST" action="{{ route('vgcomments.admin.comment.update', $comment->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ \Vigstudio\VgComment\Models\Comment::STATUS_APPROVED }}">
                <button type="submit" class="btn-success">{{ __('vgcomment::admin.approved') }}</button>
            </form>
        @endif

        @if ($comment->status !== \Vigstudio\VgComment\Models\Comment::STATUS_PENDING)
            <form method="POST" action="{{ route('vgcomments.admin.comment.update', $comment->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ \Vigstudio\VgComment\Models\Comment::STATUS_PENDING }}">
                <button type="submit" class="btn-orange">{{ __('vgcomment::admin.unapprove') }}</button>
            </form>
        @endif

        @if ($comment->status !== \Vigstudio\VgComment\Models\Comment::STATUS_SPAM)
            <form method="POST" action="{{ route('vgcomments.admin.comment.update', $comment->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ \Vigstudio\VgComment\Models\Comment::STATUS_SPAM }}">
                <button type="submit" class="btn-danger">{{ __('vgcomment::admin.spam') }}</button>
            </form>
        @endif

        @if ($comment->status !== \Vigstudio\VgComment\Models\Comment::STATUS_TRASH)
            <form method="POST" action="{{ route('vgcomments.admin.comment.update', $comment->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ \Vigstudio\VgComment\Models\Comment::STATUS_TRASH }}">
                <button type="submit" class="btn-danger">{{ __('vgcomment::admin.trash') }}</button>
            </form>
        @endif
    @endunless
</div>
