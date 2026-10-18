import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/printer_provider.dart';
import '../printer/printer_settings_screen.dart';

class ReceiptScreen extends StatelessWidget {
  final Map<String, dynamic> thermalPayload;

  const ReceiptScreen({super.key, required this.thermalPayload});

  @override
  Widget build(BuildContext context) {
    final store = thermalPayload['store'] as Map<String, dynamic>? ?? {};
    final invoice = thermalPayload['invoice'] as Map<String, dynamic>? ?? {};
    final items = (invoice['items'] as List?) ?? [];
    final printer = context.watch<PrinterProvider>();
    final sym = store['currency_symbol'] ?? '₹';

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Sale Completed'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            icon: Icon(
              printer.isConnected ? Icons.bluetooth_connected : Icons.bluetooth_disabled,
              color: printer.isConnected ? Colors.greenAccent : Colors.white,
            ),
            tooltip: 'Printer Settings',
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const PrinterSettingsScreen()),
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Success Banner
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.green.shade200),
              ),
              child: const Row(
                children: [
                  Icon(Icons.check_circle_rounded, color: Colors.green, size: 36),
                  SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Payment Received Successfully!',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.green),
                        ),
                        Text(
                          'Inventory stock deducted & ledger updated.',
                          style: TextStyle(color: Colors.black87, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Receipt Paper Simulation Card
            Card(
              elevation: 4,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    Text(
                      store['name'] ?? 'MY POS STORE',
                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    ),
                    if (store['address'] != null)
                      Text(store['address'], style: const TextStyle(color: Colors.grey, fontSize: 12), textAlign: TextAlign.center),
                    if (store['phone'] != null)
                      Text('Tel: ${store['phone']}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                    if (store['tax_number'] != null)
                      Text('VAT/GST: ${store['tax_number']}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                    const Divider(height: 24),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Invoice: ${invoice['invoice_number']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                        Text('${invoice['date']}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Customer: ${invoice['customer']}', style: const TextStyle(fontSize: 13)),
                        Text('Cashier: ${invoice['cashier']}', style: const TextStyle(fontSize: 13, color: Colors.grey)),
                      ],
                    ),
                    const Divider(height: 24),

                    // Items List
                    ...items.map((item) {
                      return Padding(
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              flex: 3,
                              child: Text(item['name'] ?? '', style: const TextStyle(fontSize: 13)),
                            ),
                            Expanded(
                              flex: 1,
                              child: Text('x${item['quantity']}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 13)),
                            ),
                            Expanded(
                              flex: 2,
                              child: Text('$sym${item['subtotal']}', textAlign: TextAlign.right, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                            ),
                          ],
                        ),
                      );
                    }),

                    const Divider(height: 24),

                    // Totals
                    _rowText('Subtotal', '$sym${invoice['subtotal']}'),
                    if ((invoice['discount'] ?? 0) > 0)
                      _rowText('Discount', '-$sym${invoice['discount']}', isDiscount: true),
                    if ((invoice['tax'] ?? 0) > 0)
                      _rowText('Tax/VAT', '+$sym${invoice['tax']}'),
                    const SizedBox(height: 8),
                    _rowText('Grand Total', '$sym${invoice['grand_total']}', isBold: true, fontSize: 16),
                    _rowText('Paid (${invoice['payment_method']})', '$sym${invoice['paid_amount']}'),
                    if ((invoice['due_amount'] ?? 0) > 0)
                      _rowText('Due Balance', '$sym${invoice['due_amount']}', isDue: true),

                    const Divider(height: 24),
                    Text(
                      store['footer'] ?? 'Thank you for shopping with us!',
                      style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic, color: Colors.grey),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Print Action Button
            ElevatedButton.icon(
              onPressed: () async {
                if (!printer.isConnected) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: const Text('Please connect your Bluetooth Thermal Printer first.'),
                      action: SnackBarAction(
                        label: 'Setup',
                        onPressed: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => const PrinterSettingsScreen()),
                          );
                        },
                      ),
                    ),
                  );
                  return;
                }

                final success = await printer.printSaleReceipt(
                  store: store,
                  invoice: invoice,
                );

                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(success ? 'Receipt sent to thermal printer!' : 'Printing failed'),
                      backgroundColor: success ? Colors.green : Colors.red,
                    ),
                  );
                }
              },
              icon: const Icon(Icons.print_rounded, size: 24),
              label: Text('Print Thermal Receipt (${printer.paperSizeLabel})'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                minimumSize: const Size.fromHeight(54),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 3,
              ),
            ),

            const SizedBox(height: 12),

            OutlinedButton(
              onPressed: () => Navigator.of(context).pop(),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: const Text('Start New POS Sale'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _rowText(String label, String value, {bool isBold = false, double fontSize = 13, bool isDiscount = false, bool isDue = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontWeight: isBold ? FontWeight.bold : FontWeight.normal, fontSize: fontSize)),
          Text(
            value,
            style: TextStyle(
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
              fontSize: fontSize,
              color: isDiscount ? Colors.red : (isDue ? Colors.orange.shade800 : Colors.black87),
            ),
          ),
        ],
      ),
    );
  }
}
