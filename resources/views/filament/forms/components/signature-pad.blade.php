<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            pad: null,
            ready: false,
            initPad() {
                const canvas = this.$refs.sigCanvas;
                this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
                if (this.state) {
                    this.pad.fromDataURL(this.state);
                }
                this.ready = true;
            },
            loadLib() {
                if (window.SignaturePad) {
                    this.initPad();
                    return;
                }
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4/dist/signature_pad.umd.min.js';
                s.onload = () => this.initPad();
                document.head.appendChild(s);
            },
            save() {
                if (this.pad && !this.pad.isEmpty()) {
                    this.state = this.pad.toDataURL();
                }
            },
            clear() {
                if (this.pad) this.pad.clear();
                this.state = null;
            }
        }"
        x-init="loadLib()"
        class="fi-signature-pad"
    >
        <div style="border: 1px solid #d1d5db; border-radius: 8px; background: white; overflow: hidden; max-width: 560px;">
            <canvas
                x-ref="sigCanvas"
                width="540"
                height="160"
                style="width: 100%; display: block; cursor: crosshair;"
                @mouseup="save()"
                @touchend="save()"
            ></canvas>
        </div>

        <div style="margin-top: 6px; display: flex; gap: 8px; align-items: center;">
            <button
                type="button"
                @click="clear()"
                style="font-size: 12px; color: #ef4444; background: none; border: none; cursor: pointer; padding: 2px 0;"
            >
                Clear signature
            </button>
            <span x-show="state" style="font-size: 12px; color: #22c55e;">&#10003; Signature captured</span>
            <span x-show="!state && ready" style="font-size: 12px; color: #9ca3af;">Draw your signature above</span>
        </div>

        {{-- If there's an existing saved signature, show it as a thumbnail --}}
        @if ($getState())
            <div style="margin-top: 8px;">
                <p style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Saved signature:</p>
                <img src="{{ $getState() }}" alt="Saved signature" style="height: 60px; border: 1px solid #e5e7eb; border-radius: 4px; background: white; padding: 4px;">
            </div>
        @endif
    </div>
</x-dynamic-component>
