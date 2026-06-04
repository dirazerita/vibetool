@extends('layouts.admin')
@section('title', 'Pesan - ' . $member->name)

@section('content')
<style>
    .chat-shell { display:flex; flex-direction:column; height: calc(100vh - 96px); max-height: 800px; }
    .chat-stream { flex:1; overflow-y:auto; padding:16px 0; display:flex; flex-direction:column; gap:12px; }
    .chat-bubble { max-width: 75%; padding:10px 14px; border-radius:14px; font-size:14px; line-height:1.5; word-wrap:break-word; text-align:left; }
    .chat-body { margin:0; white-space:pre-wrap; word-wrap:break-word; overflow-wrap:break-word; }
    .chat-bubble.mine { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
    .chat-bubble.theirs { background:#1e2b3d; color:#e2e8f0; align-self:flex-start; border-bottom-left-radius:4px; }
    .chat-meta { font-size:11px; color:#64748b; margin-top:4px; display:block; }
    .chat-bubble.mine .chat-meta { color:rgba(255,255,255,0.7); text-align:right; }
    .chat-attachment { margin-top:8px; }
    .chat-attachment img { max-width:280px; max-height:280px; border-radius:8px; display:block; }
    .chat-attachment-file { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); border-radius:10px; color:inherit; text-decoration:none; font-size:13px; }
    .chat-bubble.mine .chat-attachment-file { background:rgba(0,0,0,0.18); border-color:rgba(255,255,255,0.18); color:#fff; }
    .chat-attachment-file:hover { filter:brightness(1.1); }
    .chat-attachment-file svg { width:18px; height:18px; flex-shrink:0; }
    .chat-empty { text-align:center; color:#64748b; padding:48px 16px; font-size:14px; }
    .chat-compose { border-top:1px solid #1e2b3d; padding-top:12px; }
    .chat-file-row { display:flex; align-items:center; gap:8px; font-size:13px; color:#94a3b8; margin-top:6px; }
    .chat-file-row .chat-file-name { color:#cbd5e1; }
</style>

<div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
    <a href="{{ route('admin.messages.index') }}" class="dk-btn dk-btn-outline" style="padding:6px 12px;">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    <div style="flex:1;">
        <h1 class="text-xl font-bold dk-heading">{{ $member->name }}</h1>
        <p class="dk-text-muted" style="font-size:13px;">
            {{ $member->email }}
            @if($member->whatsapp_number)
                · WA: {{ $member->whatsapp_number }}
            @endif
        </p>
    </div>
    <a href="{{ route('admin.members.edit', $member) }}" class="dk-btn dk-btn-outline" style="padding:6px 12px;">Edit Member</a>
</div>

<div class="dk-card chat-shell" style="padding:16px 20px;">
    <div id="chatStream" class="chat-stream">
        @forelse($messages as $msg)
            @php $mine = $msg->sender_role === \App\Models\Message::ROLE_ADMIN; @endphp
            <div class="chat-bubble {{ $mine ? 'mine' : 'theirs' }}">
                @if($msg->isBroadcast())
                    <span style="display:inline-block; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; background:rgba(255,255,255,0.18); padding:2px 8px; border-radius:9999px; margin-bottom:6px;">Broadcast</span>
                @endif
                @if($msg->body)<p class="chat-body">{{ $msg->body }}</p>@endif
                @if($msg->hasAttachment())
                    <div class="chat-attachment">
                        @if($msg->isImage())
                            <a href="{{ route('admin.messages.attachment', $msg) }}" target="_blank">
                                <img src="{{ route('admin.messages.attachment', $msg) }}" alt="{{ $msg->attachment_name }}">
                            </a>
                        @else
                            <a href="{{ route('admin.messages.attachment', $msg) }}" class="chat-attachment-file">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>{{ $msg->attachment_name }} ({{ $msg->attachmentSizeHuman() }})</span>
                            </a>
                        @endif
                    </div>
                @endif
                <span class="chat-meta">
                    {{ $mine ? ($msg->sender->name ?? 'Admin') : $member->name }} · {{ $msg->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}
                </span>
            </div>
        @empty
            <div class="chat-empty">Belum ada pesan. Tulis pesan untuk memulai percakapan dengan member ini.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.messages.store', $member) }}" enctype="multipart/form-data" class="chat-compose">
        @csrf
        @error('body') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
        @error('attachment') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

        <textarea name="body" rows="2" class="w-full dk-input" placeholder="Balas ke {{ $member->name }}..." maxlength="5000">{{ old('body') }}</textarea>

        <div class="chat-file-row">
            <label class="dk-btn dk-btn-outline" style="cursor:pointer; padding:6px 14px;" for="msgAttachment">
                <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                Lampirkan File
            </label>
            <input type="file" name="attachment" id="msgAttachment" style="display:none;" onchange="document.getElementById('msgAttachmentName').textContent=this.files[0]?.name || ''">
            <span id="msgAttachmentName" class="chat-file-name"></span>
            <button type="submit" class="dk-btn dk-btn-primary" style="margin-left:auto;">
                <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim
            </button>
        </div>
    </form>
</div>

<script>
    (function(){
        var stream = document.getElementById('chatStream');
        if (stream) stream.scrollTop = stream.scrollHeight;
    })();
</script>
@endsection
