@extends('layouts.app')

@section('title', 'Kasir - Mie Ayam Puput')

@section('content')
{{-- Halaman kasir: tempat petugas memilih menu, mengatur keranjang, lalu membuat pesanan. --}}
<div class="max-w-[1500px] mx-auto px-4 py-6 sm:p-8 bg-[#FAFAFA] min-h-screen">
    <div id="cashier-toast" class="fixed left-1/2 top-20 z-[999] hidden max-w-[calc(100vw-2rem)] -translate-x-1/2 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-center text-xs font-black uppercase tracking-widest text-emerald-700 shadow-2xl shadow-emerald-900/10 transition-all duration-200"></div>
    <div id="cashier-cart-backdrop" class="fixed inset-0 z-[70] hidden bg-stone-950/40 backdrop-blur-[2px] 2xl:hidden"></div>

    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-end mb-10">
        <div>
            <h1 class="text-4xl font-extrabold text-stone-900 tracking-tighter">Kasir</h1>
            <p class="text-stone-400 text-sm font-medium mt-1">Mie Ayam Puput - {{ date('d F Y') }}</p>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-white border border-stone-200 rounded-xl text-stone-600 font-bold text-center hover:border-stone-400 transition-all">Kelola Menu</a>
            <a href="{{ route('orders.pending') }}" class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-bold text-center shadow-lg shadow-red-200 hover:bg-red-700 transition-all">Riwayat</a>
        </div>
    </div>

    <div class="grid grid-cols-1 2xl:grid-cols-[minmax(0,1fr)_420px] gap-8 items-start">
        {{-- Daftar menu yang bisa dipilih kasir. --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6 items-start">
            @foreach($products as $product)
                <div class="bg-white p-4 sm:p-5 rounded-[2rem] border border-stone-100 shadow-sm hover:shadow-xl hover:shadow-stone-100 transition-all duration-500 group min-w-0">
                    <div class="w-full aspect-[4/3] sm:aspect-square bg-[#FFFDF9] rounded-[1.5rem] overflow-hidden mb-4 border border-stone-100 relative">
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-stone-300 text-[10px] uppercase font-black tracking-widest">No Image</div>
                        @endif
                    </div>

                    <h2 class="font-bold text-stone-900 text-sm mb-1 truncate">{{ $product->name }}</h2>
                    <p class="text-red-600 font-black text-lg mb-4">Rp {{ number_format($product->price, 0, ',', '.') }}</p>

                    <form action="{{ route('cashier.add') }}" method="POST" class="grid grid-cols-[5rem_minmax(0,1fr)] gap-2" data-cart-form>
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="number" name="quantity" value="1" min="1" class="w-full bg-[#FFFDF9] border border-stone-100 rounded-xl text-center font-bold text-sm focus:ring-2 focus:ring-red-100 outline-none transition-all">
                        <button class="min-h-11 bg-stone-900 text-white text-[10px] font-black tracking-widest rounded-xl hover:bg-red-600 transition-all uppercase">Tambah</button>
                    </form>
                </div>
            @endforeach
        </section>

        {{-- Keranjang belanja sementara sebelum pesanan dibuat. --}}
        <aside id="cashier-cart-panel" class="fixed inset-y-0 right-0 z-[80] w-full max-w-[420px] translate-x-full overflow-y-auto bg-white shadow-2xl transition-transform duration-300 ease-out sm:w-[420px] 2xl:sticky 2xl:top-24 2xl:z-auto 2xl:h-fit 2xl:max-h-[calc(100vh-7rem)] 2xl:w-auto 2xl:max-w-none 2xl:translate-x-0 2xl:overflow-visible 2xl:bg-transparent 2xl:shadow-none">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-stone-200 bg-white px-5 py-4 2xl:hidden">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-stone-400">Struk Pesanan</p>
                    <p class="text-sm font-black text-stone-900">Mie Ayam Puput</p>
                </div>
                <button type="button" id="close-cart-panel" class="rounded-xl border border-stone-200 px-4 py-2 text-xs font-black uppercase tracking-widest text-stone-600 active:scale-[0.98]">
                    Tutup
                </button>
            </div>
            <div id="cashier-cart">
                @include('cashier.partials.cart')
            </div>
        </aside>
    </div>

    <button type="button" id="open-cart-panel" class="fixed bottom-5 right-5 z-[60] flex items-center gap-3 rounded-2xl bg-stone-900 px-5 py-4 text-xs font-black uppercase tracking-widest text-white shadow-2xl shadow-stone-900/25 active:scale-[0.98] 2xl:hidden">
        Struk
        <span id="cart-count-badge" class="grid min-h-6 min-w-6 place-items-center rounded-full bg-red-600 px-2 text-[10px] text-white">{{ $items->sum('quantity') }}</span>
    </button>
</div>
@endsection

@push('scripts')
<script>
    const cartContainer = document.getElementById('cashier-cart');
    const cartPanel = document.getElementById('cashier-cart-panel');
    const cartBackdrop = document.getElementById('cashier-cart-backdrop');
    const openCartPanelButton = document.getElementById('open-cart-panel');
    const closeCartPanelButton = document.getElementById('close-cart-panel');
    const cartCountBadge = document.getElementById('cart-count-badge');
    const cashierToast = document.getElementById('cashier-toast');
    const initialCashierToast = @json(request()->boolean('order_completed') ? 'Pesanan telah selesai' : session('success'));
    let toastTimeout = null;

    function showCashierToast(message) {
        clearTimeout(toastTimeout);
        cashierToast.textContent = message;
        cashierToast.classList.remove('hidden', 'translate-y-2', 'opacity-0');
        cashierToast.classList.add('translate-y-0', 'opacity-100');

        toastTimeout = setTimeout(() => {
            cashierToast.classList.add('translate-y-2', 'opacity-0');

            setTimeout(() => {
                cashierToast.classList.add('hidden');
            }, 220);
        }, 1800);
    }

    function openCartPanel() {
        cartPanel.classList.remove('translate-x-full');
        cartBackdrop.classList.remove('hidden');
    }

    function closeCartPanel() {
        cartPanel.classList.add('translate-x-full');
        cartBackdrop.classList.add('hidden');
    }

    function updateCartCount(count) {
        cartCountBadge.textContent = count ?? 0;
    }

    if (initialCashierToast) {
        showCashierToast(initialCashierToast);
    }

    openCartPanelButton.addEventListener('click', openCartPanel);
    closeCartPanelButton.addEventListener('click', closeCartPanel);
    cartBackdrop.addEventListener('click', closeCartPanel);

    function checkoutValues() {
        const checkoutForm = cartContainer.querySelector('[data-checkout-form]');

        if (!checkoutForm) {
            return {};
        }

        return {
            customer_name: checkoutForm.querySelector('[name="customer_name"]')?.value ?? '',
            order_type: checkoutForm.querySelector('[name="order_type"]')?.value ?? 'dine_in',
        };
    }

    function restoreCheckoutValues(values) {
        const checkoutForm = cartContainer.querySelector('[data-checkout-form]');

        if (!checkoutForm) {
            return;
        }

        const customerNameInput = checkoutForm.querySelector('[name="customer_name"]');
        const orderTypeSelect = checkoutForm.querySelector('[name="order_type"]');

        if (customerNameInput) {
            customerNameInput.value = values.customer_name ?? '';
        }

        if (orderTypeSelect) {
            orderTypeSelect.value = values.order_type ?? 'dine_in';
        }
    }

    function showCheckoutErrors(form, data) {
        form.querySelectorAll('[data-checkout-error]').forEach((errorElement) => {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        });

        const errors = data.errors ?? {};

        Object.entries(errors).forEach(([field, messages]) => {
            const errorElement = form.querySelector(`[data-checkout-error="${field}"]`);

            if (errorElement) {
                errorElement.textContent = messages[0] ?? data.message ?? 'Data belum lengkap.';
                errorElement.classList.remove('hidden');
            }
        });

        if (!Object.keys(errors).length && data.message) {
            const generalError = form.querySelector('[data-checkout-error="general"]');

            if (generalError) {
                generalError.textContent = data.message;
                generalError.classList.remove('hidden');
            }
        }
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-cart-form], [data-checkout-form]');

        if (!form) {
            return;
        }

        event.preventDefault();

        const button = form.querySelector('button[type="submit"], button:not([type])');
        const originalText = button ? button.textContent : null;
        const isCheckout = form.matches('[data-checkout-form]');
        const savedCheckoutValues = checkoutValues();

        if (button) {
            button.disabled = true;
            button.textContent = '...';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));

                if (isCheckout) {
                    showCheckoutErrors(form, errorData);

                    return;
                }

                throw new Error(errorData.message ?? 'Gagal memperbarui tagihan.');
            }

            const data = await response.json();

            if (isCheckout) {
                showCashierToast(data.message ?? 'Pesanan berhasil dibuat');
                window.location.href = data.redirect_url;

                return;
            }

            cartContainer.innerHTML = data.html;
            updateCartCount(data.cart_count);
            restoreCheckoutValues(savedCheckoutValues);

            if (form.matches('form[action$="/cashier/add"]')) {
                form.querySelector('input[name="quantity"]').value = 1;
                showCashierToast('Pesanan ditambahkan ke tagihan');
            } else {
                showCashierToast('Pesanan dihapus dari tagihan');
            }
        } catch (error) {
            alert(error.message);
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    });
</script>
@endpush
