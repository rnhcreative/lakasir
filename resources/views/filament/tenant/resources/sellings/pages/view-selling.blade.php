@php
  use App\Features\{SellingTax};
  use App\Models\Tenants\{Profile, Setting, About};
@endphp
<x-filament-panels::page>
  <x-filament::section id="printElement">
    <div class="flex">
      <div class="w-full print:w-1/3 md:w-1/3 px-2">
        <div>
          <p class="font-semibold text-2xl">@lang('Selling details')</p>
          <div class="details">
            <ul class="my-1">
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Code')</span><span>#{{ $record->code }}</span></li>
              @if(About::first() && About::first()->business_type == 'fnb')
                <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Table')</span><span>{{ $record->table?->number ?? 'N/A' }}</span></li>
              @endif
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Cashier')</span><span>{{ $record->user->name }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Date')</span><span>{{ now()->parse($record->date)->setTimezone(config('setting.timezone') ?? 'UTC')->format('d F Y H:i') }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Payment method')</span><span>{{ $record->paymentMethod->name }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Voucher')</span><span>{{ $record->voucher ?? 'N/A' }}</span></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="w-full print:w-1/3 md:w-1/3 px-2">
        <div>
          <p class="font-semibold text-2xl">@lang('Member details')</p>
          <div class="details">
            <ul class="my-1">
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Name')</span><span>{{ $record->member?->name ?? 'N/A' }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Code')</span><span>{{ $record->member?->code ?? 'N/A' }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Joined date')</span><span>{{ $record->member?->joined_date ? now()->parse($record->member?->joined_date)->setTimezone(config('setting.timezone') ?? 'UTC')->format('d F Y H:i') : 'N/A' }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Identity type')</span><span>{{ $record->member?->identity_type ?? 'N/A' }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Identity number')</span><span>{{ $record->member?->identity_number ?? 'N/A' }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Contact')</span><span>{{ $record->member?->email ?? 'N/A' }}</span></li>
              <li class="flex justify-between text-secondary text-sm mb-1"><span class="font-semibold">@lang('Address')</span><span>{{ $record->member?->address ?? 'N/A' }}</span></li>
              <!---->
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="table w-full my-4">
      <table class="table ns-table w-full">
        <thead class="text-secondary">
          <tr>
            <th width="400" class="p-2 border">@lang('Product')</th>
            <th width="200" class="p-2 border">@lang('Unit price')</th>
            <th width="200" class="p-2 border">@lang('Quantity')</th>
            <th width="200" class="p-2 border">@lang('Discount')</th>
            <th width="200" class="p-2 border">@lang('Total price')</th>
          </tr>
        </thead>
        <tbody>
          @foreach($record->sellingDetails as $detail)
            <tr>
              <td class="p-2 border">
                <h3 class="text-primary">{{ $detail->product->name }}</h3><span class="text-sm text-secondary"></span></td>
              <td class="p-2 border text-center text-primary">{{ Number::currency($detail->price_per_unit, config('setting.currency')) }}</td>
              <td class="p-2 border text-center text-primary">{{ $detail->qty }}</td>
              <td class="p-2 border text-center text-primary">{{ Number::currency($detail->discount_price, config('setting.currency')) }}</td>
              <td class="p-2 border text-center text-primary">{{ Number::currency($detail->total_price, config('setting.currency')) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot class="font-semibold">
          <tr>
            <td class="p-2 border text-center text-primary" colspan="3"></td>
            <td class="p-2 border text-primary text-left">@lang('Subtotal')</td>
            <td class="p-2 border text-right text-primary">{{ Number::currency($record->total_price, config('setting.currency')) }}</td>
          </tr>
          <tr>
            <td class="p-2 border text-center text-primary" colspan="3"></td>
            <td class="p-2 border text-primary text-left">@lang('Discount')</td>
            <td class="p-2 border text-right text-primary">{{ Number::currency($record->total_discount_per_item + $record->discount_price, config('setting.currency')) }}</td>
          </tr>
          <!---->
          <tr>
            <td class="p-2 border text-center text-primary" colspan="3"></td>
            <td class="p-2 border text-primary text-left">@lang('Tax')</td>
            <td class="p-2 border text-right text-primary">{{ $record->tax }}%</td>
          </tr>
          <tr>
            <td class="p-2 border text-center text-primary" colspan="3"></td>
            <td class="p-2 border text-primary text-left">@lang('Tax price')</td>
            <td class="p-2 border text-right text-primary">{{ Number::currency($record->tax_price, config('setting.currency')) }}</td>
          </tr>
          <tr>
            <td class="p-2 border text-center text-primary" colspan="3"></td>
            <td class="p-2 border text-primary text-left">@lang('Total')</td>
            <td class="p-2 border text-right text-primary">{{ Number::currency($record->grand_total_price, config('setting.currency')) }}</td>
          </tr>
          <tr>
            <td class="p-2 border text-center text-primary" colspan="3"></td>
            <td class="p-2 border text-primary text-left">@lang('Payed money')</td>
            <td class="p-2 border text-right text-primary">{{ Number::currency($record->payed_money, config('setting.currency')) }}</td>
          </tr>
          <tr>
            <td class="p-2 border text-center text-primary" colspan="3"></td>
            <td class="p-2 border text-primary text-left">@lang('Money changes')</td>
            <td class="p-2 border text-right text-primary">{{ Number::currency($record->money_changes, config('setting.currency')) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </x-filament::section>

  @if (count($relationManagers = $this->getRelationManagers()))
        <x-filament-panels::resources.relation-managers
            :active-manager="$this->activeRelationManager"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        />
    @endif
</x-filament-panels::page>
<script src="https://demo.qz.io/js/qz-tray.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/rsvp/3.6.2/rsvp.min.js"></script>
@script()
<script>

document.getElementById('printButton').addEventListener('click', async () => {

  let selling = @js($record);
  let about = @js($about);

  const url = "wss://localhost:8181/"; // QZ Tray default WebSocket port
  const isRunning = await checkWebSocketConnection(url);

  if (isRunning) {
      // You can now safely load qz-tray.js
       /// Authentication setup ///
      qz.security.setCertificatePromise(function(resolve, reject) {
          fetch("/assets/documents/digital-certificate.txt", {cache: 'no-store', headers: {'Content-Type': 'text/plain'}})
          .then(function(data) { data.ok ? resolve(data.text()) : reject(data.text()); });
      });

      qz.security.setSignatureAlgorithm("SHA512"); // Since 2.1
        qz.security.setSignaturePromise(function(toSign) {
          return function(resolve, reject) {
            fetch("/api/signing?request=" + toSign, {cache: 'no-store', headers: {'Content-Type': 'text/plain'}})
                .then(function(data) { data.ok ? resolve(data.text()) : reject(data.text()); });
            };
        });

        try {
          // 1️⃣ Pastikan koneksi QZ aktif
          if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
          }

          // 2️⃣ Temukan printer yang dipilih user
          const printer = await qz.printers.getDefault();
          if (!printer) {
            alert("Printer tidak ditemukan");
            return;
          }

          // 3️⃣ Buat konfigurasi QZ (⬅️ ini duluan)
          const config = qz.configs.create(printer, { language: 'escpos' });

          // 4️⃣ Bangun data ESC/POS
          let esc = "\x1B"; // escape
          let gs  = "\x1D"; // group separator
          let data = esc + "@"; // initialize printer

          // --- HEADER ---
          data += "\x1B\x61\x01"; // align center
          data += (about?.shop_name || "TOKO TANPA NAMA") + "\n";
          if (about?.shop_location) data += about.shop_location + "\n";
          data += "------------------------------\n";

          // --- INFO TRANSAKSI ---
          data += "\x1B\x61\x00"; // align left
          data += `Kasir : ${selling.user.name}\n`;
          if (selling.table) data += `Meja  : ${selling.table.number}\n`;
          data += `Nomor: ${selling.code}\n`;
          if (selling.member) data += `Member: ${selling.member.name}\n`;
          data += "------------------------------\n";

          // --- ITEM DETAIL ---
          selling.selling_details.forEach(detail => {
            let subtotal = detail.price * detail.qty;
            let line = detail.product.name + "\n";
            line += lineFormat(`${moneyFormat(detail.price)} x ${detail.qty}`, moneyFormat(subtotal));
            if (detail.discount_price > 0) {
              subtotal -= detail.discount_price;
              line += `(Disc: ${moneyFormat(detail.discount_price)})\n`;
            }
            data += line;
          });

          data += "------------------------------\n";

          // --- TAX & TOTAL ---
          if ("@js(feature(SellingTax::class))" == 'true') {
            data += `Pajak (${selling.tax}%): ${moneyFormat(selling.tax_price)}\n`;
          }

          data += lineFormat("Subtotal", moneyFormat(selling.total_price));
          data += lineFormat("Diskon", (selling.discount_price > 0 ? "-" : moneyFormat(selling.discount_price)));
          data += lineFormat("Total", moneyFormat(selling.grand_total_price));
          data += "------------------------------\n";
          data += lineFormat("Tunai", moneyFormat(selling.payed_money));
          data += lineFormat("Kembali", moneyFormat(selling.money_changes));
          data += "------------------------------\n";

          // --- FOOTER ---
          data += "\x1B\x61\x01"; // align center
          data += "Terima kasih telah berbelanja!\n";
          if (about?.footer) data += about.footer + "\n";
          data += "------------------------------\n";

          // pastikan kertas keluar penuh
          data += "\x1B\x61\x00"; // reset align kiri
          data += "\n\n"; // feed 1 baris saja

          // kalau printer support auto-cutter
          data += esc + "d" + "\x05"; // feed + cut

          // 5️⃣ Kirim ke printer (⬅️ ini terakhir)
          await qz.print(config, [{
            type: 'raw',
            format: 'plain',
            data
          }]);

        } catch (err) {
          console.error("❌ Print error:", err);
        }
  } else {
      alert("⚠️ QZ Tray tidak terdeteksi. Silakan jalankan aplikasi QZ Tray terlebih dahulu.");
  }
});

function checkWebSocketConnection(url, timeout = 2000) {
    return new Promise((resolve) => {
        let connected = false;

        try {
            const ws = new WebSocket(url);

            const timer = setTimeout(() => {
                if (!connected) {
                    ws.close();
                    resolve(false); // Timeout, consider not running
                }
            }, timeout);

            ws.onopen = () => {
                connected = true;
                clearTimeout(timer);
                ws.close();
                resolve(true); // Successfully connected
            };

            ws.onerror = () => {
                clearTimeout(timer);
                resolve(false); // Connection error
            };

            ws.onclose = () => {
                clearTimeout(timer);
                if (!connected) resolve(false);
            };
        } catch (e) {
            resolve(false); // Failed to create WebSocket
        }
    });
}

// Menyusun teks kiri + kanan agar rata kiri/kanan di lebar tertentu (default 32 char)
function lineFormat(left, right, width = 32) {
  left = left.toString();
  right = right.toString();
  const spaces = width - (left.length + right.length);
  return left + " ".repeat(spaces > 0 ? spaces : 1) + right + "\n";
}

// Helper format uang
function moneyFormat(num) {
  if (isNaN(num)) return "0";
  // Format angka tanpa simbol mata uang
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0
  }).format(num);
}
</script>
@endscript

