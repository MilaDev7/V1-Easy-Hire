<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Easy Hire</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-50 min-h-screen flex items-center justify-center">
    <div id="receipt-first-content" class="w-full max-w-2xl p-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Payment Receipt</h1>
            <p class="text-slate-500 mb-6">Review your receipt before continuing.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div class="bg-slate-50 rounded p-3"><span class="text-slate-500 block">Status</span><span id="receipt-status" class="font-semibold text-slate-800"></span></div>
                <div class="bg-slate-50 rounded p-3"><span class="text-slate-500 block">Amount</span><span id="receipt-amount" class="font-semibold text-slate-800"></span></div>
                <div class="bg-slate-50 rounded p-3"><span class="text-slate-500 block">Transaction Ref</span><span id="receipt-txref" class="font-semibold text-slate-800 break-all"></span></div>
                <div class="bg-slate-50 rounded p-3"><span class="text-slate-500 block">Gateway Reference</span><span id="receipt-reference" class="font-semibold text-slate-800 break-all"></span></div>
                <div class="bg-slate-50 rounded p-3"><span class="text-slate-500 block">Email</span><span id="receipt-email" class="font-semibold text-slate-800 break-all"></span></div>
                <div class="bg-slate-50 rounded p-3"><span class="text-slate-500 block">Paid At</span><span id="receipt-paidat" class="font-semibold text-slate-800"></span></div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <button id="download-receipt-btn" class="bg-white text-emerald-700 border border-emerald-600 px-5 py-3 rounded-lg hover:bg-emerald-50 transition">
                    Download Receipt
                </button>
                <a href="/#/" class="bg-emerald-600 text-white px-5 py-3 rounded-lg hover:bg-emerald-700 transition text-center">
                    Go to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        const receipt = @json($receipt ?? []);

        function buildReceiptText(data) {
            return [
                "EASY HIRE - PAYMENT RECEIPT",
                "----------------------------",
                "Status: " + (data.status || "success"),
                "Transaction Ref: " + (data.tx_ref || "N/A"),
                "Gateway Reference: " + (data.reference || "N/A"),
                "Amount: " + (data.amount ?? "N/A") + " " + (data.currency || "ETB"),
                "Plan ID: " + (data.plan_id || "N/A"),
                "Name: " + (data.first_name || "N/A"),
                "Email: " + (data.email || "N/A"),
                "Paid At: " + (data.paid_at || new Date().toISOString()),
                "Generated At: " + new Date().toISOString()
            ].join("\n");
        }

        function downloadReceiptFile() {
            const txRef = receipt.tx_ref || ("receipt_" + Date.now());
            const filename = "easyhire_receipt_" + txRef + ".txt";
            const blob = new Blob([buildReceiptText(receipt)], { type: "text/plain;charset=utf-8" });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        }

        window.addEventListener("DOMContentLoaded", function () {
            const downloadBtn = document.getElementById("download-receipt-btn");

            const safe = (v) => v !== undefined && v !== null && v !== "" ? v : "N/A";
            const amount = (receipt.amount ?? "N/A") + " " + (receipt.currency || "ETB");

            document.getElementById("receipt-status").textContent = safe(receipt.status);
            document.getElementById("receipt-amount").textContent = amount;
            document.getElementById("receipt-txref").textContent = safe(receipt.tx_ref);
            document.getElementById("receipt-reference").textContent = safe(receipt.reference);
            document.getElementById("receipt-email").textContent = safe(receipt.email);
            document.getElementById("receipt-paidat").textContent = safe(receipt.paid_at);

            if (downloadBtn) {
                downloadBtn.addEventListener("click", downloadReceiptFile);
            }
        });
    </script>
</body>
</html>
