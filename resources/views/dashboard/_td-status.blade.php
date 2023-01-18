@switch($comment->status)
    @case(\Vigstudio\VgComment\Models\Comment::STATUS_APPROVED)
        <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">{{ $comment->status_name }}</span>
    @break

    @case(\Vigstudio\VgComment\Models\Comment::STATUS_PENDING)
        <span class="inline-flex rounded-full bg-orange-200 px-2 text-xs font-semibold leading-5 text-orange-800">{{ $comment->status_name }}</span>
    @break

    @case(\Vigstudio\VgComment\Models\Comment::STATUS_SPAM)
        <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">{{ $comment->status_name }}</span>
    @break

    @case(\Vigstudio\VgComment\Models\Comment::STATUS_TRASH)
        <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">{{ $comment->status_name }}</span>
    @break

    @default
        ''
@endswitch
