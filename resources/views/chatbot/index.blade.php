@extends('layouts.app')

@section('title', $title ?? 'Asisten AI Perpustakaan')

@section('content')
@php
    // Daftar rekomendasi pertanyaan. Dipakai di strip atas input (gaya Shopee) & kartu sidebar.
    $quickQuestions = [
        ['icon' => '🕒', 'short' => 'Jam buka?', 'full' => 'Jam buka perpustakaan kapan?'],
        ['icon' => '📖', 'short' => 'Cara pinjam?', 'full' => 'Bagaimana cara pinjam buku?'],
        ['icon' => '💰', 'short' => 'Denda saya?', 'full' => 'Berapa denda keterlambatan saya?'],
        ['icon' => '🔍', 'short' => 'Cari buku IPA', 'full' => 'Ada buku IPA kelas IX?'],
        ['icon' => '📚', 'short' => 'Daftar buku', 'full' => 'Daftar buku apa saja yang ada?'],
        ['icon' => '📕', 'short' => 'Pinjaman saya', 'full' => 'Buku saya apa saja yang belum dikembalikan?'],
        ['icon' => '⏳', 'short' => 'Perpanjang buku', 'full' => 'Bagaimana cara perpanjang pinjaman buku?'],
        ['icon' => '📱', 'short' => 'Akses e-book', 'full' => 'Bagaimana cara akses e-book?'],
        ['icon' => '📝', 'short' => 'Jadi anggota', 'full' => 'Bagaimana cara mendaftar jadi anggota?'],
        ['icon' => '🔔', 'short' => 'Fitur notifikasi', 'full' => 'Apa fungsi fitur notifikasi?'],
        ['icon' => '👤', 'short' => 'Profil saya', 'full' => 'Lihat profil dan data saya'],
        ['icon' => '🗂️', 'short' => 'Jumlah koleksi', 'full' => 'Berapa jumlah koleksi buku di perpustakaan?'],
        ['icon' => '☎️', 'short' => 'Hubungi petugas', 'full' => 'Bagaimana cara menghubungi petugas perpustakaan?'],
    ];
@endphp
<style>
    /* Custom Styling untuk UI AI Chat */
    .ai-container {
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: #ffffff;
        overflow: hidden;
    }
    
    .chat-box {
        height: 60vh;
        overflow-y: auto;
        padding: 1.5rem;
        background-color: #F9FAFB;
        scroll-behavior: smooth;
    }

    /* Kustomisasi Scrollbar */
    .chat-box::-webkit-scrollbar {
        width: 6px;
    }
    .chat-box::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 10px;
    }

    /* Animasi Pesan Masuk */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message-wrapper {
        animation: slideUpFade 0.4s ease-out forwards;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 1rem;
    }

    .message-wrapper.user {
        flex-direction: row-reverse;
    }

    /* Avatar */
    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: bold;
        color: white;
    }
    .avatar-user {
        background: #10B981;
    }

    /* Chat Bubbles */
    .chat-bubble {
        max-width: 75%;
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        font-size: 0.95rem;
        line-height: 1.5;
        position: relative;
    }
    
    .chat-bubble-ai {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        border-top-left-radius: 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    .chat-bubble-user {
        background: var(--app-primary);
        color: #ffffff;
        border-top-right-radius: 0;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
    }

    /* Area Input Modern */
    .input-area {
        padding: 1rem 1.5rem;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
    }
    
    .input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .ai-input {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 0.75rem 3.5rem 0.75rem 1.5rem;
        resize: none;
        outline: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        max-height: 150px;
        overflow-y: auto;
    }
    
    .ai-input:focus {
        border-color: var(--app-primary);
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.1);
    }

    .btn-send {
        position: absolute;
        right: 0.5rem;
        bottom: 0.4rem;
        background: var(--app-primary);
        color: white;
        border: none;
        border-radius: 50%;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    }
    .btn-send:hover:not(:disabled) {
        transform: scale(1.05);
        background: var(--app-primary-hover);
    }
    .btn-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* Style Badge Prompt */
    .prompt-badge {
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
        background: white;
        color: #475569;
    }
    .prompt-badge:hover {
        background: var(--app-primary-soft);
        border-color: var(--app-primary);
        color: var(--app-primary);
        transform: translateY(-2px);
    }

    /* Strip rekomendasi pertanyaan di atas input, gaya quick-reply Shopee */
    .quick-question-strip {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding: 0.6rem 1.5rem 0.7rem;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        scrollbar-width: thin;
    }
    .quick-question-strip::-webkit-scrollbar {
        height: 4px;
    }
    .quick-question-strip::-webkit-scrollbar-thumb {
        background-color: #e2e8f0;
        border-radius: 10px;
    }
    .quick-question-chip {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
        padding: 0.4rem 0.85rem;
        border-radius: 2rem;
        border: 1px solid var(--app-primary-soft);
        background: var(--app-primary-soft);
        color: var(--app-primary);
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .quick-question-chip:hover {
        background: var(--app-primary);
        color: #fff;
        border-color: var(--app-primary);
        transform: translateY(-1px);
    }

    .intent-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        color: #94a3b8;
    }
</style>

<div class="row g-4">
    <!-- Area Chatbot -->
    <div class="col-lg-8">

        @if (session('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-radius: 0.75rem;">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 0.75rem;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->has('message'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 0.75rem;">
                {{ $errors->first('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="ai-container">
            <!-- Header -->
            <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div>
                        <span class="fw-bold d-block" style="color: #1e293b; line-height: 1.1;">Asisten Perpustakaan</span>
                        @auth
                            <span class="text-muted" style="font-size: 0.72rem;">Ngobrol sama {{ explode(' ', trim(auth()->user()->name ?? auth()->user()->member?->nama ?? ''))[0] ?: 'kamu' }}</span>
                        @endauth
                    </div>
                </div>
                @if (!empty($history))
                    <form action="{{ route('chatbot.clear') }}" method="POST" onsubmit="return confirm('Hapus semua riwayat chat?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light text-danger fw-semibold" style="border-radius: 1rem;">
                            <i class="bi bi-trash3"></i> Hapus Riwayat
                        </button>
                    </form>
                @endif
            </div>

            <!-- Body Chat -->
            <div class="chat-box" id="chat-container">
                @forelse ($history as $index => $item)
                    <!-- Pesan Pengguna -->
                    <div class="message-wrapper user" style="animation-delay: {{ $index * 0.1 }}s;">
                        <div class="avatar avatar-user">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/></svg>
                        </div>
                        <div class="chat-bubble chat-bubble-user">
                            {{ $item['user'] }}
                        </div>
                    </div>

                    <!-- Balasan AI -->
                    <div class="message-wrapper ai" style="animation-delay: {{ ($index * 0.1) + 0.1 }}s;">
                        <div class="chat-bubble chat-bubble-ai">
                            {!! nl2br(e($item['bot'])) !!}
                            <div class="mt-2 intent-badge">
                                <i class="bi bi-tag-fill"></i> {{ $intentLabels[$item['intent']] ?? ucwords(str_replace('_', ' ', $item['intent'])) }}
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Tampilan Kosong (Empty State) -->
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center opacity-75" style="animation: slideUpFade 0.6s ease-out;">
                        <h4 class="fw-bold" style="color: #1e293b;">Halo! Saya Asisten Perpustakaan.</h4>
                        <p class="text-muted">Kirim pertanyaan seperti jam buka, aturan pinjam, denda, atau cari buku.</p>
                    </div>
                @endforelse
            </div>

            <!-- Strip Rekomendasi Pertanyaan (gaya Shopee, scroll horizontal) -->
            <div class="quick-question-strip" id="quick-question-strip">
                @foreach ($quickQuestions as $q)
                    <div class="quick-question-chip" data-prompt="{{ $q['full'] }}" data-instant="1">
                        <span>{{ $q['icon'] }}</span> {{ $q['short'] }}
                    </div>
                @endforeach
            </div>

            <!-- Form Input -->
            <div class="input-area">
                <form action="{{ route('chatbot.send') }}" method="POST" id="chat-form">
                    @csrf
                    <div class="input-container">
                        <textarea id="message" name="message" rows="1" class="ai-input" placeholder="Tanyakan sesuatu... (Enter untuk kirim, Shift+Enter baris baru)" maxlength="1000" required autofocus>{{ old('message') }}</textarea>
                        <button type="submit" class="btn-send" id="btn-send" aria-label="Kirim pesan">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-15Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.47 1.178Z"/>
                            </svg>
                        </button>
                    </div>
              
                </form>
            </div>
        </div>
    </div>

    <!-- Area Sidebar Kanan -->
    <div class="col-lg-4">
        <!-- Contoh Prompts -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 1rem;">
            <div class="card-header bg-white border-0 pt-4 pb-2 fw-bold text-dark">
                💡 Coba tanyakan ini
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($quickQuestions as $q)
                        <div class="badge prompt-badge text-start p-2 rounded-pill fw-normal" data-prompt="{{ $q['full'] }}">{{ $q['icon'] }} {{ $q['short'] }}</div>
                    @endforeach
                </div>
            </div>
        </div>

  
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var chatContainer = document.getElementById("chat-container");
        var textarea = document.getElementById("message");
        var form = document.getElementById("chat-form");
        var sendBtn = document.getElementById("btn-send");

        // Selalu scroll ke pesan terbaru saat halaman dimuat.
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Auto-resize textarea mengikuti panjang teks (maks. via CSS max-height).
        function autoResize() {
            textarea.style.height = "auto";
            textarea.style.height = textarea.scrollHeight + "px";
        }
        if (textarea) {
            textarea.addEventListener("input", autoResize);
            autoResize();
        }

        // Enter untuk kirim, Shift+Enter untuk baris baru.
        if (textarea && form) {
            textarea.addEventListener("keydown", function (e) {
                if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    if (textarea.value.trim() !== "") {
                        form.requestSubmit();
                    }
                }
            });
        }

        // Cegah kirim dobel: nonaktifkan tombol setelah submit.
        if (form && sendBtn) {
            form.addEventListener("submit", function (e) {
                if (textarea.value.trim() === "") {
                    e.preventDefault();
                    return;
                }
                sendBtn.disabled = true;
            });
        }

        // Klik badge contoh di sidebar -> isi textarea & fokus (tidak langsung kirim).
        document.querySelectorAll(".prompt-badge").forEach(function (badge) {
            badge.addEventListener("click", function () {
                textarea.value = badge.dataset.prompt;
                autoResize();
                textarea.focus();
            });
        });

        // Klik chip rekomendasi di strip atas input -> langsung kirim (gaya quick-reply Shopee).
        document.querySelectorAll(".quick-question-chip").forEach(function (chip) {
            chip.addEventListener("click", function () {
                if (sendBtn.disabled) {
                    return;
                }
                textarea.value = chip.dataset.prompt;
                sendBtn.disabled = true;
                form.requestSubmit();
            });
        });
    });
</script>
@endsection