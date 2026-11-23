@props([
    'id',
    'title' => 'Konfirmasi Tindakan',
    'description' => 'Pastikan data dan pilihan Anda sudah benar sebelum melanjutkan.',
    'confirmLabel' => 'Ya, Lanjutkan',
    'cancelLabel' => 'Batal',
    'tone' => 'primary',
    'icon' => 'help',
    'closedby' => 'any',
])

@php
    $isDanger = $tone === 'danger';
    $confirmBtnClass = $isDanger
        ? 'press-scale min-h-[48px] px-6 rounded-full bg-[#B91C1C] hover:bg-[#991B1B] active:bg-[#7F1D1D] text-white text-sm font-bold inline-flex items-center justify-center gap-2 shadow-[0_6px_18px_rgba(185,28,28,0.24)] transition-colors'
        : 'btn-primary px-6 text-sm inline-flex items-center justify-center gap-2';
    $iconWrapClass = $isDanger
        ? 'bg-[#FEE2E2] text-[#991B1B] border-[#FCA5A5]'
        : 'bg-[#E6F0FC] text-[#005EB8] border-[#BFD7F5]';
@endphp

<!-- Native Accessible Modal Dialog (top-layer modal semantics handled natively by showModal()) -->
<dialog
    id="{{ $id }}"
    closedby="{{ $closedby }}"
    data-confirm-dialog
    aria-labelledby="{{ $id }}-title"
    aria-describedby="{{ $id }}-desc"
    {{ $attributes->merge(['class' => 'bhima-dialog']) }}
>
    <form method="dialog" class="bhima-dialog__form">
        <div class="bhima-dialog__header">
            <div class="flex items-start gap-3.5 min-w-0">
                @if (isset($iconSlot))
                    {{ $iconSlot }}
                @else
                    <span class="w-11 h-11 rounded-[16px] border flex items-center justify-center shrink-0 {{ $iconWrapClass }}" aria-hidden="true">
                        <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
                    </span>
                @endif
                <div class="min-w-0 space-y-1">
                    <h2 id="{{ $id }}-title" class="text-lg min-[641px]:text-xl font-bold text-[#0B1020] leading-snug tracking-tight">
                        {{ $header ?? $title }}
                    </h2>
                    <p id="{{ $id }}-desc" class="text-sm text-[#4A5366] leading-relaxed">
                        {{ $description }}
                    </p>
                </div>
            </div>

            <button
                type="submit"
                value="cancel"
                aria-label="Tutup dialog konfirmasi"
                class="press-scale w-11 h-11 rounded-full bg-white/80 hover:bg-white border border-[#75849B] text-[#0B1020] flex items-center justify-center shrink-0 transition-colors"
            >
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
            </button>
        </div>

        @if ($slot->isNotEmpty())
            <div class="bhima-dialog__body">
                {{ $slot }}
            </div>
        @endif

        <div class="bhima-dialog__footer">
            @if (isset($footer))
                {{ $footer }}
            @else
                <button
                    type="submit"
                    value="cancel"
                    autofocus
                    class="btn-glass w-full min-[481px]:w-auto px-6 text-sm inline-flex items-center justify-center gap-2"
                >
                    <span>{{ $cancelLabel }}</span>
                </button>
                <button
                    type="submit"
                    value="confirm"
                    class="w-full min-[481px]:w-auto {{ $confirmBtnClass }}"
                >
                    <span>{{ $confirmLabel }}</span>
                </button>
            @endif
        </div>
    </form>
</dialog>
