@php
    $status = $comment->trashed() ? 'deleted' : $comment->status;
@endphp
<span @class([
    'admin-badge',
    'admin-badge--approved' => $status === 'approved',
    'admin-badge--pending' => $status === 'pending',
    'admin-badge--spam' => $status === 'spam',
    'admin-badge--trash' => in_array($status, ['trash', 'deleted'], true),
])>
    {{ __('vgcomment::admin.'.$status) }}
</span>
