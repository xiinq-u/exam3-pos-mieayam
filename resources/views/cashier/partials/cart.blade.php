<div class="bg-white p-6 border-x border-t border-stone-200 shadow-xl relative font-sans text-stone-800">
    <div class="absolute -bottom-[8px] left-0 w-full h-[10px] z-10"
        style="background: linear-gradient(135deg, white 50%, transparent 50%) 0 0/15px 15px, linear-gradient(225deg, white 50%, transparent 50%) 0 0/15px 15px; background-repeat: repeat-x;">
    </div>

    <div class="text-center mb-6 border-b-2 border-stone-900 pb-4">
        <h2 class="text-2xl font-black uppercase tracking-tight text-stone-900">Mie Ayam Puput</h2>
        <div class="flex justify-between mt-4 text-[10px] font-bold text-stone-500 uppercase">
            <span>{{ date('d/m/Y') }}</span>
            <span>{{ date('H:i') }}</span>
        </div>
    </div>

    @if($items->isEmpty())
        <div class="py-10 text-center text-stone-400 text-xs italic">- Keranjang Kosong -</div>
    @else
        <div class="space-y-4 mb-6">
            @foreach($items as $id => $item)
                <div class="flex justify-between items-center text-sm">
                    <div class="flex flex-col">
                        <span class="font-bold text-stone-900">{{ $item['product']->name }}</span>
                        <span class="text-[10px] text-stone-500">{{ $item['quantity'] }} x {{ number_format($item['product']->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-3 font-bold">
                        <span>{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        <form action="{{ route('cashier.remove', $item['product']->id) }}" method="POST" data-cart-form>
                            @csrf
                            <button class="text-stone-300 hover:text-red-500" aria-label="Hapus {{ $item['product']->name }}">x</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t-2 border-stone-900 pt-4">
            <div class="flex justify-between items-center mb-6">
                <span class="font-black text-sm uppercase">Total</span>
                <span class="text-2xl font-black text-stone-900">Rp{{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <form action="{{ route('cashier.checkout') }}" method="POST" class="space-y-3" data-checkout-form>
                @csrf
                <div>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="w-full bg-stone-100 border-none p-3 text-xs font-bold uppercase rounded-lg placeholder:text-stone-400" placeholder="Nama Pembeli" required>
                    <p class="mt-1 hidden text-[10px] font-bold text-red-600" data-checkout-error="customer_name"></p>
                    @error('customer_name')
                        <p class="mt-1 text-[10px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <select name="order_type" class="w-full bg-stone-100 border-none p-3 text-xs font-bold uppercase rounded-lg">
                        <option value="dine_in">Dine In</option>
                        <option value="take_away">Take Away</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-stone-900 text-white font-black py-4 uppercase tracking-widest hover:bg-red-600 transition-all text-xs rounded-xl shadow-lg">
                    Buat Pesanan
                </button>
                <p class="hidden text-center text-[10px] font-bold text-red-600" data-checkout-error="general"></p>
            </form>
        </div>
    @endif
</div>
