<ol class="relative border-l border-gray-200 ml-3 space-y-6 my-4">
    @foreach ($approvals as $approval)
        @php
            $isApproved = $approval->status === 'disetujui';
            $isPending = $approval->status === 'menunggu';
            $isRejected = $approval->status === 'ditolak';
            $isSkipped = $approval->status === 'dilewati';

            $iconClass = 'bg-gray-100 text-gray-500 border-gray-300';
            $icon = 'ri-time-line';

            if ($isApproved) {
                $iconClass = 'bg-emerald-100 text-emerald-600 border-emerald-300';
                $icon = 'ri-check-line';
            } elseif ($isPending) {
                $iconClass = 'bg-yellow-100 text-yellow-600 border-yellow-300 animate-pulse';
                $icon = 'ri-loader-4-line';
            } elseif ($isRejected) {
                $iconClass = 'bg-rose-100 text-rose-600 border-rose-300';
                $icon = 'ri-close-line';
            } elseif ($isSkipped) {
                $iconClass = 'bg-gray-100 text-gray-400 border-gray-200';
                $icon = 'ri-subtract-line';
            }
        @endphp

        <li class="mb-6 ml-6">
            <span class="absolute flex items-center justify-center w-7 h-7 rounded-full -left-3.5 ring-4 ring-white border {{ $iconClass }}">
                <i class="{{ $icon }} text-base"></i>
            </span>
            <div class="p-3 bg-white border border-gray-200 rounded-lg shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-gray-500 uppercase">Langkah {{ $approval->urutan }}</span>
                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $isApproved ? 'bg-emerald-50 text-emerald-700' : ($isPending ? 'bg-yellow-50 text-yellow-700' : ($isRejected ? 'bg-rose-50 text-rose-700' : 'bg-gray-50 text-gray-600')) }}">
                        {{ strtoupper($approval->status) }}
                    </span>
                </div>
                <h4 class="text-sm font-bold text-gray-900">{{ $approval->label_snapshot }}</h4>
                <p class="text-xs text-gray-600 mt-0.5">
                    Penandatangan:
                    <span class="font-semibold text-gray-800">
                        @if (optional($approval->approver)->name ?? $approval->approver_nama_snapshot)
                            {{ optional($approval->approver)->name ?? $approval->approver_nama_snapshot }}
                        @else
                            <span class="italic text-gray-500">(Ditentukan otomatis saat langkah aktif)</span>
                        @endif
                    </span>
                </p>

                @if ($approval->catatan)
                    <div class="mt-2 text-xs p-2 bg-gray-50 rounded border border-gray-100 text-gray-700">
                        <span class="font-semibold text-gray-600">Catatan:</span> {{ $approval->catatan }}
                    </div>
                @endif

                @if ($approval->acted_at)
                    <span class="block mt-2 text-[10px] text-gray-400">
                        <i class="ri-calendar-event-line mr-0.5"></i> Diproses pada {{ $approval->acted_at->format('d M Y H:i') }}
                    </span>
                @endif
            </div>
        </li>
    @endforeach
</ol>
